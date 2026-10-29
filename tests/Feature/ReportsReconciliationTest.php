<?php

namespace Tests\Feature;

use App\Livewire\ReportsManager;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsReconciliationTest extends TestCase
{
    use DatabaseTransactions;

    protected BillingService $billingService;

    protected Branch $branch;

    protected User $cashier;

    protected ProductVariant $variantA;

    protected ProductVariant $variantB;

    protected Order $order1;

    protected Order $order2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = app(BillingService::class);

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Salem Store',
            'code' => 'SLM1',
            'state_code' => '33',
            'city' => 'Salem',
            'is_main' => true,
            'is_active' => true,
        ]);

        $retailLocation = StockLocation::firstOrCreate(
            ['branch_id' => $this->branch->id, 'type' => 'retail'],
            ['name' => 'Main Sales Floor', 'is_default' => true]
        );

        $this->cashier = User::where('role', 'cashier')->first() ?? User::create([
            'name' => 'Report Cashier',
            'email' => 'cashier_rep_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'casual-shirts'],
            ['name' => 'Casual Shirts', 'hsn_code' => '6205', 'default_tax_rate' => 5.00]
        );

        $product = Product::firstOrCreate(
            ['article_code' => 'REP-SHIRT-01'],
            [
                'category_id' => $category->id,
                'name' => 'Classic Cotton Shirt',
                'hsn_code' => '6205',
                'tax_rate' => 5.00,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $this->variantA = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'White',
            'sku' => 'REP-SHT-WHT-M-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'purchase_cost' => 500.00,
            'selling_price' => 1000.00,
            'mrp' => 1299.00,
            'is_active' => true,
        ]);

        $this->variantB = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'White',
            'sku' => 'REP-SHT-WHT-L-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'purchase_cost' => 250.00,
            'selling_price' => 500.00,
            'mrp' => 699.00,
            'is_active' => true,
        ]);

        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantA->id, 'stock_location_id' => $retailLocation->id],
            ['quantity' => 20]
        );
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantB->id, 'stock_location_id' => $retailLocation->id],
            ['quantity' => 20]
        );

        // Order 1: 1 unit of Variant A (₹1000.00) paid via Cash
        $this->order1 = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'items' => [
                [
                    'variant_id' => $this->variantA->id,
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1000.00],
            ],
        ]);

        // Order 2: 1 unit of Variant B (₹500.00) paid via UPI
        $this->order2 = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'items' => [
                [
                    'variant_id' => $this->variantB->id,
                    'quantity' => 1,
                    'unit_price' => 500.00,
                ],
            ],
            'payments' => [
                ['method' => 'upi', 'amount' => 500.00, 'reference' => 'UPI-REP-TEST-123'],
            ],
        ]);
    }

    public function test_daily_sales_report_reconciles_order_totals_and_taxes(): void
    {
        $today = now()->toDateString();

        $component = Livewire::test(ReportsManager::class)
            ->set('reportType', 'daily_sales')
            ->set('startDate', $today)
            ->set('endDate', $today);

        $viewData = $component->viewData('reportData');

        $this->assertGreaterThanOrEqual(1500.00, $viewData['total_sales']);
        $this->assertGreaterThanOrEqual(2, $viewData['total_invoices']);
        $this->assertGreaterThanOrEqual(71.43, $viewData['total_tax']);
    }

    public function test_payment_reconciliation_groups_by_payment_method(): void
    {
        $today = now()->toDateString();

        $component = Livewire::test(ReportsManager::class)
            ->set('reportType', 'payment_reconciliation')
            ->set('startDate', $today)
            ->set('endDate', $today);

        $viewData = $component->viewData('reportData');
        $rows = collect($viewData['rows'])->keyBy('payment_method');

        $this->assertTrue($rows->has('cash'));
        $this->assertTrue($rows->has('upi'));
        $this->assertGreaterThanOrEqual(1000.00, (float) $rows['cash']->total_collected);
        $this->assertGreaterThanOrEqual(500.00, (float) $rows['upi']->total_collected);
    }

    public function test_gross_profit_report_calculates_cogs_and_margin(): void
    {
        $today = now()->toDateString();

        $component = Livewire::test(ReportsManager::class)
            ->set('reportType', 'gross_profit')
            ->set('startDate', $today)
            ->set('endDate', $today);

        $viewData = $component->viewData('reportData');

        $this->assertGreaterThanOrEqual(1500.00, $viewData['total_revenue']);
        $this->assertGreaterThanOrEqual(750.00, $viewData['total_cogs']); // 500 + 250
        $this->assertGreaterThanOrEqual(750.00, $viewData['total_gross_profit']); // 1500 - 750
        $this->assertGreaterThan(0, $viewData['margin_percentage']);
    }

    public function test_gst_tax_report_hsn_breakdown(): void
    {
        $today = now()->toDateString();

        $component = Livewire::test(ReportsManager::class)
            ->set('reportType', 'gst_tax')
            ->set('startDate', $today)
            ->set('endDate', $today);

        $viewData = $component->viewData('reportData');
        $rows = collect($viewData['rows']);

        $hsnRow = $rows->firstWhere('hsn_code', '6205');
        $this->assertNotNull($hsnRow);
        $this->assertEquals(5.00, (float) $hsnRow->tax_rate);
        $this->assertGreaterThanOrEqual(1428.57, (float) $hsnRow->taxable_value);
        $this->assertGreaterThanOrEqual(71.43, (float) $hsnRow->total_tax);
    }

    public function test_report_csv_export_returns_streamed_download(): void
    {
        $today = now()->toDateString();

        Livewire::test(ReportsManager::class)
            ->set('reportType', 'daily_sales')
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->call('exportCsv')
            ->assertFileDownloaded("daily_sales_{$today}_{$today}.csv");
    }
}
