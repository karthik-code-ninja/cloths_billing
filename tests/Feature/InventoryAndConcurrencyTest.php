<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\BillingService;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventoryAndConcurrencyTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryService $inventoryService;

    protected BillingService $billingService;

    protected Branch $branch;

    protected StockLocation $location;

    protected ProductVariant $variant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = app(InventoryService::class);
        $this->billingService = app(BillingService::class);

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Salem Store',
            'code' => 'SLM1',
            'state_code' => '33',
            'city' => 'Salem',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->location = StockLocation::firstOrCreate(
            ['branch_id' => $this->branch->id, 'type' => 'retail'],
            ['name' => 'Main Sales Floor', 'is_default' => true]
        );

        $this->user = User::first() ?? User::create([
            'name' => 'Inventory Staff',
            'email' => 'inv_test_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'inventory_staff',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'linen-trousers'],
            ['name' => 'Linen Trousers', 'hsn_code' => '6203', 'default_tax_rate' => 5.00]
        );

        $product = Product::firstOrCreate(
            ['article_code' => 'TEST-TROUSER-01'],
            [
                'category_id' => $category->id,
                'name' => 'Slim Fit Linen Trousers',
                'hsn_code' => '6203',
                'tax_rate' => 5.00,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => '32',
            'color' => 'Beige',
            'sku' => 'TEST-LIN-BEI-32-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'purchase_cost' => 600.00,
            'selling_price' => 1499.00,
            'mrp' => 1899.00,
            'is_active' => true,
        ]);
    }

    public function test_decrement_stock_blocks_negative_stock_when_not_allowed(): void
    {
        // Set negative stock to false
        AppSetting::set('allow_negative_stock', false);

        // Set initial stock = 1
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variant->id, 'stock_location_id' => $this->location->id],
            ['quantity' => 1]
        );

        // 1st decrement of 1 unit succeeds
        $m1 = $this->inventoryService->decrementStock(
            $this->variant,
            1,
            $this->location->id,
            'sale',
            remarks: 'First sale'
        );

        $this->assertEquals(-1, $m1->quantity);
        $this->assertEquals(0, $m1->balance_after);

        // 2nd decrement of 1 unit must fail because available = 0
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/');

        $this->inventoryService->decrementStock(
            $this->variant,
            1,
            $this->location->id,
            'sale',
            remarks: 'Second sale concurrent attempt'
        );
    }

    public function test_stock_movement_ledger_tracks_audit_trail_and_balance_after(): void
    {
        // 1. Initial purchase receipt +20
        $m1 = $this->inventoryService->incrementStock(
            $this->variant,
            20,
            $this->location->id,
            'purchase_receive',
            'PurchaseOrder',
            101,
            'Initial GRN batch'
        );
        $this->assertEquals(20, $m1->quantity);
        $this->assertEquals(20, $m1->balance_after);

        // 2. Sale -5
        $m2 = $this->inventoryService->decrementStock(
            $this->variant,
            5,
            $this->location->id,
            'sale',
            'Order',
            201,
            'Retail invoice'
        );
        $this->assertEquals(-5, $m2->quantity);
        $this->assertEquals(15, $m2->balance_after);

        // 3. Damage adjustment -2
        $m3 = $this->inventoryService->decrementStock(
            $this->variant,
            2,
            $this->location->id,
            'damage',
            remarks: 'Stained during display'
        );
        $this->assertEquals(-2, $m3->quantity);
        $this->assertEquals(13, $m3->balance_after);

        // Verify balance in InventoryStock table
        $finalStock = InventoryStock::where('product_variant_id', $this->variant->id)
            ->where('stock_location_id', $this->location->id)
            ->value('quantity');

        $this->assertEquals(13, $finalStock);

        // Verify audit trail count
        $movements = StockMovement::where('product_variant_id', $this->variant->id)->get();
        $this->assertCount(3, $movements);
    }

    public function test_allow_negative_stock_setting_permits_overdraw_when_enabled(): void
    {
        AppSetting::set('allow_negative_stock', true);

        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variant->id, 'stock_location_id' => $this->location->id],
            ['quantity' => 2]
        );

        // Decrement 5 when only 2 on hand -> -3
        $movement = $this->inventoryService->decrementStock(
            $this->variant,
            5,
            $this->location->id,
            'sale',
            remarks: 'Negative stock permitted'
        );

        $this->assertEquals(-5, $movement->quantity);
        $this->assertEquals(-3, $movement->balance_after);

        $stock = InventoryStock::where('product_variant_id', $this->variant->id)
            ->where('stock_location_id', $this->location->id)
            ->value('quantity');
        $this->assertEquals(-3, $stock);
    }
}
