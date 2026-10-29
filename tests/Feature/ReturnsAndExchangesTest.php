<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnOrder;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\BillingService;
use App\Services\ReturnService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReturnsAndExchangesTest extends TestCase
{
    use DatabaseTransactions;

    protected BillingService $billingService;

    protected ReturnService $returnService;

    protected Branch $branch;

    protected StockLocation $retailLocation;

    protected StockLocation $damagedLocation;

    protected User $cashier;

    protected User $manager;

    protected Customer $customer;

    protected ProductVariant $variantM;

    protected ProductVariant $variantL;

    protected Order $order;

    protected OrderItem $orderItemM;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);
        $this->returnService = app(ReturnService::class);

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Salem Store',
            'code' => 'SLM1',
            'state_code' => '33',
            'city' => 'Salem',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->retailLocation = StockLocation::firstOrCreate(
            ['branch_id' => $this->branch->id, 'type' => 'retail'],
            ['name' => 'Retail Floor', 'is_default' => true]
        );

        $this->damagedLocation = StockLocation::firstOrCreate(
            ['branch_id' => $this->branch->id, 'type' => 'damaged'],
            ['name' => 'Damaged / Defect Room']
        );

        $this->cashier = User::where('role', 'cashier')->first() ?? User::create([
            'name' => 'Cashier User',
            'email' => 'cashier_ret_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'max_discount_percentage' => 10.00,
            'is_active' => true,
        ]);

        $this->manager = User::where('role', 'manager')->first() ?? User::create([
            'name' => 'Manager User',
            'email' => 'mgr_ret_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'manager',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Ravi Varma',
            'phone' => '9843'.rand(100000, 999999),
            'store_credit_balance' => 0.00,
            'state_code' => '33',
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'polo-tshirts'],
            ['name' => 'Polo T-Shirts', 'hsn_code' => '6105', 'default_tax_rate' => 5.00]
        );

        $product = Product::firstOrCreate(
            ['article_code' => 'TEST-POLO-01'],
            [
                'category_id' => $category->id,
                'name' => 'Premium Pique Polo T-Shirt',
                'hsn_code' => '6105',
                'tax_rate' => 5.00,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $this->variantM = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Navy',
            'sku' => 'TEST-POLO-NAV-M-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'purchase_cost' => 400.00,
            'selling_price' => 799.00,
            'mrp' => 999.00,
            'is_active' => true,
        ]);

        $this->variantL = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'Navy',
            'sku' => 'TEST-POLO-NAV-L-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'purchase_cost' => 450.00,
            'selling_price' => 899.00,
            'mrp' => 1099.00,
            'is_active' => true,
        ]);

        // Seed stock: Variant M = 10, Variant L = 10
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantM->id, 'stock_location_id' => $this->retailLocation->id],
            ['quantity' => 10]
        );
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantL->id, 'stock_location_id' => $this->retailLocation->id],
            ['quantity' => 10]
        );

        // Perform initial sale of 2 units of Variant M
        $this->order = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 2,
                    'unit_price' => 799.00,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1598.00],
            ],
        ]);

        $this->orderItemM = $this->order->items->first();
    }

    public function test_return_cannot_exceed_sold_quantity(): void
    {
        // Customer bought 2 units. Attempting to return 3 units must fail.
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Eligible quantity remaining: 2/');

        $this->returnService->processReturn([
            'original_order_id' => $this->order->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashier->id,
            'refund_method' => 'cash',
            'reason' => 'Wrong size',
            'items' => [
                [
                    'order_item_id' => $this->orderItemM->id,
                    'variant_id' => $this->variantM->id,
                    'quantity' => 3, // Exceeds 2
                    'condition' => 'resellable',
                ],
            ],
        ]);
    }

    public function test_resellable_return_restores_stock_to_retail_location(): void
    {
        // Initial stock after selling 2 from 10 is 8
        $stockBefore = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->retailLocation->id)
            ->value('quantity');
        $this->assertEquals(8, $stockBefore);

        // Return 1 unit as resellable
        $returnOrder = $this->returnService->processReturn([
            'original_order_id' => $this->order->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashier->id,
            'refund_method' => 'cash',
            'reason' => 'Customer changed mind',
            'items' => [
                [
                    'order_item_id' => $this->orderItemM->id,
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'condition' => 'resellable',
                ],
            ],
        ]);

        $this->assertInstanceOf(ReturnOrder::class, $returnOrder);
        $this->assertEquals('completed', $returnOrder->status);
        $this->assertEquals(799.00, $returnOrder->total_refund_amount);

        // Retail stock restored from 8 to 9
        $stockAfter = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->retailLocation->id)
            ->value('quantity');
        $this->assertEquals(9, $stockAfter);

        // Original order item returned_quantity updated to 1
        $this->orderItemM->refresh();
        $this->assertEquals(1, $this->orderItemM->returned_quantity);
        $this->assertEquals(1, $this->orderItemM->returnable_quantity);
    }

    public function test_damaged_return_routes_stock_to_damaged_location(): void
    {
        // Return 1 unit as damaged
        $returnOrder = $this->returnService->processReturn([
            'original_order_id' => $this->order->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashier->id,
            'refund_method' => 'cash',
            'reason' => 'Loose stitch discovered',
            'items' => [
                [
                    'order_item_id' => $this->orderItemM->id,
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'condition' => 'damaged',
                ],
            ],
        ]);

        // Retail stock must NOT be increased
        $retailStock = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->retailLocation->id)
            ->value('quantity');
        $this->assertEquals(8, $retailStock);

        // Damaged location stock must be increased by 1
        $damagedStock = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->damagedLocation->id)
            ->value('quantity');
        $this->assertEquals(1, $damagedStock);
    }

    public function test_store_credit_refund_increases_customer_balance(): void
    {
        $this->assertEquals(0.00, $this->customer->store_credit_balance);

        $this->returnService->processReturn([
            'original_order_id' => $this->order->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashier->id,
            'refund_method' => 'store_credit',
            'reason' => 'Customer preferred store credit',
            'items' => [
                [
                    'order_item_id' => $this->orderItemM->id,
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'condition' => 'resellable',
                ],
            ],
        ]);

        $this->customer->refresh();
        $this->assertEquals(799.00, $this->customer->store_credit_balance);
    }

    public function test_no_receipt_return_requires_manager_permission(): void
    {
        // Cashier attempts no-receipt return without manager approval -> fails
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/No-receipt return requires manager approval/');

        $this->returnService->processReturn([
            'branch_id' => $this->branch->id,
            'user_id' => $this->cashier->id,
            'is_no_receipt' => true,
            'refund_method' => 'cash',
            'reason' => 'Customer lost bill',
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'condition' => 'resellable',
                    'refund_price' => 700.00,
                ],
            ],
        ]);
    }

    public function test_exchange_process_calculates_difference_and_updates_stocks(): void
    {
        // Return 1 unit of Variant M (₹799.00) and exchange for 1 unit of Variant L (₹899.00)
        // Customer owes difference: 899 - 799 = ₹100.00
        $result = $this->returnService->processExchange([
            'return_data' => [
                'original_order_id' => $this->order->id,
                'branch_id' => $this->branch->id,
                'user_id' => $this->cashier->id,
                'reason' => 'Size exchange from M to L',
                'items' => [
                    [
                        'order_item_id' => $this->orderItemM->id,
                        'variant_id' => $this->variantM->id,
                        'quantity' => 1,
                        'condition' => 'resellable',
                    ],
                ],
            ],
            'new_items' => [
                [
                    'variant_id' => $this->variantL->id,
                    'quantity' => 1,
                    'unit_price' => 899.00,
                ],
            ],
            'payment_method_for_diff' => 'cash',
        ]);

        $this->assertEquals(100.00, $result['difference']);
        $this->assertEquals('collected_difference', $result['action']);
        $this->assertInstanceOf(Order::class, $result['exchange_order']);
        $this->assertEquals(899.00, $result['exchange_order']->grand_total);

        // Variant M stock returned: 8 + 1 = 9
        $stockM = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->retailLocation->id)
            ->value('quantity');
        $this->assertEquals(9, $stockM);

        // Variant L stock sold in exchange: 10 - 1 = 9
        $stockL = InventoryStock::where('product_variant_id', $this->variantL->id)
            ->where('stock_location_id', $this->retailLocation->id)
            ->value('quantity');
        $this->assertEquals(9, $stockL);
    }
}
