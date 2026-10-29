<?php

namespace Tests\Feature;

use App\Livewire\PosBilling;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\ManagerOverride;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\BillingService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PosBillingTest extends TestCase
{
    use DatabaseTransactions;

    protected BillingService $billingService;

    protected Branch $branch;

    protected StockLocation $location;

    protected User $cashier;

    protected User $manager;

    protected Product $product;

    protected ProductVariant $variantM;

    protected ProductVariant $variantL;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Flagship Store',
            'code' => 'MAIN',
            'state_code' => '33',
            'city' => 'Salem',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->location = StockLocation::firstOrCreate(
            ['branch_id' => $this->branch->id, 'type' => 'retail'],
            ['name' => 'Main Sales Floor', 'is_default' => true]
        );

        $this->cashier = User::where('role', 'cashier')->first() ?? User::create([
            'name' => 'Anitha Cashier',
            'email' => 'cashier_test_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'max_discount_percentage' => 10.00,
            'can_override_price' => false,
            'is_active' => true,
        ]);

        $this->manager = User::where('role', 'manager')->first() ?? User::create([
            'name' => 'Karthik Manager',
            'email' => 'manager_test_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'manager',
            'branch_id' => $this->branch->id,
            'max_discount_percentage' => 50.00,
            'can_override_price' => true,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'mens-shirts'],
            ['name' => "Men's Shirts", 'hsn_code' => '6205', 'default_tax_rate' => 5.00]
        );

        $this->product = Product::firstOrCreate(
            ['article_code' => 'TEST-SHIRT-01'],
            [
                'category_id' => $category->id,
                'name' => 'Cotton Oxford Casual Shirt',
                'hsn_code' => '6205',
                'tax_rate' => 5.00,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $barcodeM = '890'.rand(1000000000, 9999999999);
        $this->variantM = ProductVariant::create([
            'product_id' => $this->product->id,
            'size' => 'M',
            'color' => 'Sky Blue',
            'sku' => 'TEST-OXF-BLU-M-'.Str::random(4),
            'barcode' => $barcodeM,
            'purchase_cost' => 500.00,
            'selling_price' => 999.00,
            'mrp' => 1299.00,
            'is_active' => true,
        ]);

        $barcodeL = '890'.rand(1000000000, 9999999999);
        $this->variantL = ProductVariant::create([
            'product_id' => $this->product->id,
            'size' => 'L',
            'color' => 'Sky Blue',
            'sku' => 'TEST-OXF-BLU-L-'.Str::random(4),
            'barcode' => $barcodeL,
            'purchase_cost' => 500.00,
            'selling_price' => 999.00,
            'mrp' => 1299.00,
            'is_active' => true,
        ]);

        // Seed initial stocks: Variant M = 10, Variant L = 15
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantM->id, 'stock_location_id' => $this->location->id],
            ['quantity' => 10]
        );
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantL->id, 'stock_location_id' => $this->location->id],
            ['quantity' => 15]
        );
    }

    public function test_checkout_deducts_only_specific_scanned_variant_stock(): void
    {
        $order = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 2,
                    'unit_price' => 999.00,
                    'discount_percent' => 0,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1998.00],
            ],
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('completed', $order->status);

        // Verify stock deduction: Variant M was 10, now 8; Variant L remains 15
        $stockM = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->location->id)
            ->value('quantity');
        $stockL = InventoryStock::where('product_variant_id', $this->variantL->id)
            ->where('stock_location_id', $this->location->id)
            ->value('quantity');

        $this->assertEquals(8, $stockM);
        $this->assertEquals(15, $stockL);

        // Verify stock movement ledger record
        $movement = StockMovement::where('product_variant_id', $this->variantM->id)
            ->where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('sale', $movement->movement_type);
        $this->assertEquals(-2, $movement->quantity);
        $this->assertEquals(8, $movement->balance_after);
    }

    public function test_checkout_idempotency_prevents_duplicate_billing_and_stock_deduction(): void
    {
        $idempotencyKey = 'IDEMP-TEST-'.Str::uuid();

        $checkoutData = [
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'idempotency_key' => $idempotencyKey,
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'unit_price' => 999.00,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 999.00],
            ],
        ];

        // 1st request
        $order1 = $this->billingService->checkout($checkoutData);

        // 2nd request with exact same idempotency_key
        $order2 = $this->billingService->checkout($checkoutData);

        // Should return the exact same Order instance
        $this->assertEquals($order1->id, $order2->id);
        $this->assertEquals($order1->invoice_number, $order2->invoice_number);

        // Stock must have only decremented once: 10 - 1 = 9
        $stockM = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->location->id)
            ->value('quantity');
        $this->assertEquals(9, $stockM);

        $orderCount = Order::where('idempotency_key', $idempotencyKey)->count();
        $this->assertEquals(1, $orderCount);
    }

    public function test_mixed_payments_reconciliation_matching_grand_total(): void
    {
        $customer = Customer::create([
            'name' => 'Suresh Kumar',
            'phone' => '98765'.rand(10000, 99999),
            'store_credit_balance' => 300.00,
            'state_code' => '33',
        ]);

        // Variant M selling price = ₹999.00
        $order = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'cashier_id' => $this->cashier->id,
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'unit_price' => 999.00,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 499.00],
                ['method' => 'upi', 'amount' => 300.00, 'reference' => 'UPI-TXN-98471829'],
                ['method' => 'store_credit', 'amount' => 200.00],
            ],
        ]);

        $this->assertEquals(999.00, $order->grand_total);
        $this->assertEquals(999.00, $order->paid_amount);
        $this->assertEquals(0.00, $order->balance_amount);
        $this->assertFalse($order->is_credit_sale);

        // Verify OrderPayment records
        $payments = OrderPayment::where('order_id', $order->id)->get();
        $this->assertCount(3, $payments);
        $this->assertEquals(999.00, $payments->sum('amount'));

        // Verify Customer Store Credit deducted: 300 - 200 = 100
        $customer->refresh();
        $this->assertEquals(100.00, $customer->store_credit_balance);
    }

    public function test_cashier_discount_above_limit_requires_manager_approval(): void
    {
        // Cashier has max_discount_percentage = 10.00. Attempt 20% discount.
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/exceeds cashier maximum permitted discount/');

        $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'unit_price' => 999.00,
                    'discount_percent' => 20.00, // Exceeds 10%
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 799.20],
            ],
        ]);
    }

    public function test_manager_override_allows_discount_above_cashier_limit(): void
    {
        // Create approved manager override
        $override = ManagerOverride::create([
            'manager_id' => $this->manager->id,
            'cashier_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'action_type' => 'discount_override',
            'reason' => 'Customer festival loyalty special concession',
            'requested_value' => '20.00',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Cashier submits 20% with approved override_id
        $order = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'override_id' => $override->id,
            'items' => [
                [
                    'variant_id' => $this->variantM->id,
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                    'discount_percent' => 20.00, // 20% of 1000 = 200 discount -> 800 net
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 800.00],
            ],
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(800.00, $order->grand_total);
        $this->assertEquals(200.00, $order->total_discount);
    }

    public function test_livewire_pos_billing_scan_and_checkout_flow(): void
    {
        $this->actingAs($this->cashier);

        Livewire::test(PosBilling::class)
            ->set('barcodeInput', $this->variantM->barcode)
            ->call('handleBarcodeScan')
            ->assertCount('cart', 1)
            ->call('openPaymentModal')
            ->assertSet('showPaymentModal', true)
            ->call('completeCheckout')
            ->assertSet('showReceiptModal', true)
            ->assertCount('cart', 0);

        // Verify stock decremented
        $stockM = InventoryStock::where('product_variant_id', $this->variantM->id)
            ->where('stock_location_id', $this->location->id)
            ->value('quantity');
        $this->assertEquals(9, $stockM);
    }
}
