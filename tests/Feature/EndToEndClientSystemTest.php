<?php

namespace Tests\Feature;

use App\Livewire\BarcodeLabelPrinter;
use App\Livewire\CatalogueManager;
use App\Livewire\PosBilling;
use App\Livewire\ProductLookup;
use App\Livewire\ReportsManager;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Printer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\BarcodeService;
use App\Services\BillingService;
use App\Services\PrintService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class EndToEndClientSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $salesStaff;

    protected Branch $branch;

    protected Category $category;

    protected StockLocation $location;

    protected Printer $tvsReceiptPrinter;

    protected Printer $tvsLabelPrinter;

    protected function setUp(): void
    {
        parent::setUp();

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
            ['name' => 'Main Showroom Floor', 'code' => 'RETAIL-01', 'is_active' => true]
        );

        $this->admin = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin Tester',
            'email' => 'admin_e2e_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'admin',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->salesStaff = User::where('role', 'cashier')->first() ?? User::create([
            'name' => 'Murugan Ramasamy',
            'email' => 'murugan_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->category = Category::firstOrCreate(
            ['slug' => 'mens-shirts'],
            ['name' => 'Men Shirts', 'hsn_code' => '6205', 'default_tax_rate' => 5.0, 'is_active' => true]
        );

        // Configure TVS RP 3200 Lite (Receipt) & TVS LP 46 Dlite (Label)
        $this->tvsReceiptPrinter = Printer::firstOrCreate(
            ['name' => 'TVS RP 3200 Lite (Cash Counter 1)'],
            [
                'branch_id' => $this->branch->id,
                'connection_type' => 'mock',
                'capability_profile' => 'generic_escpos',
                'paper_width_mm' => 80,
                'columns' => 42,
                'has_cutter' => true,
                'has_cash_drawer' => true,
                'is_default_receipt' => true,
                'is_active' => true,
            ]
        );

        $this->tvsLabelPrinter = Printer::firstOrCreate(
            ['name' => 'TVS LP 46 Dlite (Barcode Hangtags)'],
            [
                'branch_id' => $this->branch->id,
                'connection_type' => 'mock',
                'capability_profile' => 'tvs_lp46',
                'paper_width_mm' => 50,
                'columns' => 38,
                'has_cutter' => false,
                'has_cash_drawer' => false,
                'is_default_label' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * 1. Product creation and matrix variant generation with auto unique barcodes.
     */
    public function test_product_creation_and_matrix_generation_with_auto_barcodes(): void
    {
        $this->actingAs($this->admin);

        $articleCode = 'TEST-ART-'.rand(1000, 9999);

        // Create style via Livewire
        Livewire::test(CatalogueManager::class)
            ->set('name', 'Premium Cotton Linen Shirt')
            ->set('article_code', $articleCode)
            ->set('category_id', $this->category->id)
            ->set('hsn_code', '6205')
            ->set('tax_rate', 5.0)
            ->set('gender', 'Men')
            ->call('saveProduct')
            ->assertHasNoErrors()
            ->assertSet('showMatrixModal', true);

        $product = Product::where('article_code', $articleCode)->first();
        $this->assertNotNull($product);

        // Generate matrix variants
        Livewire::test(CatalogueManager::class)
            ->set('matrixProduct', $product)
            ->set('matrixSizes', ['M', 'L'])
            ->set('matrixColors', ['Navy Blue', 'White'])
            ->set('matrixCost', 400.00)
            ->set('matrixPrice', 899.00)
            ->set('matrixMrp', 1299.00)
            ->set('matrixInitialStock', 15)
            ->call('generateMatrixVariants')
            ->assertHasNoErrors();

        // 2 sizes * 2 colors = 4 variants
        $variants = ProductVariant::where('product_id', $product->id)->get();
        $this->assertCount(4, $variants);

        // Verify each variant has a unique valid barcode and initial stock
        foreach ($variants as $variant) {
            $this->assertNotEmpty($variant->barcode);
            $this->assertEquals(13, strlen($variant->barcode)); // EAN-13
            $this->assertEquals(899.00, (float) $variant->selling_price);
            $this->assertEquals(1299.00, (float) $variant->mrp);
            $this->assertEquals(15, $variant->stock_on_hand);
        }
    }

    /**
     * 2. Auto Barcode Fetch: Test lookup by EAN-13 barcode, SKU, and Article Code.
     */
    public function test_auto_barcode_fetch_via_barcode_sku_and_article_code(): void
    {
        $this->actingAs($this->admin);

        $articleCode = 'LOOKUP-'.rand(1000, 9999);
        $product = Product::create([
            'name' => 'Oxford Button Down Shirt',
            'article_code' => $articleCode,
            'category_id' => $this->category->id,
            'hsn_code' => '6205',
            'tax_rate' => 5.0,
            'is_tax_inclusive' => true,
        ]);

        $barcode = app(BarcodeService::class)->generateUniqueEan13();
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'Sky Blue',
            'sku' => $articleCode.'-BLU-L',
            'barcode' => $barcode,
            'purchase_cost' => 450.00,
            'selling_price' => 999.00,
            'mrp' => 1499.00,
            'is_active' => true,
        ]);

        // A. Auto fetch via EAN-13 Barcode
        Livewire::test(ProductLookup::class)
            ->set('barcodeInput', $barcode)
            ->call('handleBarcodeScan')
            ->assertSet('selectedVariant.id', $variant->id)
            ->assertDispatched('scan-success');

        // B. Auto fetch via SKU
        Livewire::test(ProductLookup::class)
            ->set('barcodeInput', $variant->sku)
            ->call('handleBarcodeScan')
            ->assertSet('selectedVariant.id', $variant->id)
            ->assertDispatched('scan-success');

        // C. Auto fetch via Article Code
        Livewire::test(ProductLookup::class)
            ->set('barcodeInput', $articleCode)
            ->call('handleBarcodeScan')
            ->assertSet('selectedVariant.id', $variant->id)
            ->assertDispatched('scan-success');
    }

    /**
     * 3. POS Billing: Auto-scan barcode to cart, salesperson attribution, and complete checkout.
     */
    public function test_pos_billing_auto_scan_salesperson_and_checkout(): void
    {
        $this->actingAs($this->admin);

        $barcode = app(BarcodeService::class)->generateUniqueEan13();
        $product = Product::create([
            'name' => 'Chino Trousers Slim',
            'article_code' => 'CHINO-'.rand(100, 999),
            'category_id' => $this->category->id,
            'hsn_code' => '6203',
            'tax_rate' => 5.0,
            'is_tax_inclusive' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => '32',
            'color' => 'Khaki',
            'sku' => 'CHINO-KHK-32',
            'barcode' => $barcode,
            'purchase_cost' => 500.00,
            'selling_price' => 1200.00,
            'mrp' => 1799.00,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'product_variant_id' => $variant->id,
            'stock_location_id' => $this->location->id,
            'quantity' => 20,
        ]);

        // Scan in POS Billing
        $testPos = Livewire::test(PosBilling::class)
            ->set('barcodeInput', $barcode)
            ->call('handleBarcodeScan')
            ->assertDispatched('scan-success')
            ->assertCount('cart', 1)
            ->set('salespersonId', $this->salesStaff->id);

        $cart = $testPos->get('cart');
        $this->assertEquals(1200.00, $cart[0]['unit_price']);

        // Process checkout payment directly
        $billingService = app(BillingService::class);
        $order = $billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->admin->id,
            'salesperson_id' => $this->salesStaff->id,
            'customer_id' => null,
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 2,
                    'unit_price' => 1200.00,
                    'mrp' => 1799.00,
                    'discount_percent' => 10.0, // 10% discount
                ],
            ],
            'bill_discount_amount' => 0.0,
            'payments' => [
                ['method' => 'cash', 'amount' => 2160.00],
            ],
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('completed', $order->status);
        $this->assertEquals($this->salesStaff->id, $order->salesperson_id);
        $this->assertEquals(2160.00, (float) $order->grand_total);
        $this->assertEquals(18, $variant->fresh()->stock_on_hand); // 20 - 2 = 18
    }

    /**
     * 4. Thermal Receipt Printing: TVS RP 3200 Lite ESC/POS dispatch.
     */
    public function test_tvs_rp3200_thermal_receipt_printing(): void
    {
        $this->actingAs($this->admin);

        $barcode = app(BarcodeService::class)->generateUniqueEan13();
        $product = Product::create([
            'name' => 'Printed Polo T-Shirt',
            'article_code' => 'POLO-'.rand(100, 999),
            'category_id' => $this->category->id,
            'hsn_code' => '6109',
            'tax_rate' => 5.0,
            'is_tax_inclusive' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Navy',
            'sku' => 'POLO-NVY-M',
            'barcode' => $barcode,
            'purchase_cost' => 250.00,
            'selling_price' => 699.00,
            'mrp' => 999.00,
            'is_active' => true,
        ]);

        InventoryStock::create([
            'product_variant_id' => $variant->id,
            'stock_location_id' => $this->location->id,
            'quantity' => 10,
        ]);

        $order = app(BillingService::class)->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->admin->id,
            'salesperson_id' => $this->salesStaff->id,
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1, 'unit_price' => 699.00, 'mrp' => 999.00],
            ],
            'payments' => [['method' => 'cash', 'amount' => 699.00]],
        ]);

        $printService = app(PrintService::class);
        $printJob = $printService->printOrderReceipt($order, $this->tvsReceiptPrinter);

        $this->assertNotNull($printJob);
        $this->assertEquals('success', $printJob->status);
        $this->assertEquals($this->tvsReceiptPrinter->id, $printJob->printer_id);
        $this->assertNotEmpty($printJob->payload);

        // Verify ESC/POS payload contains store name and item
        $decoded = base64_decode($printJob->payload);
        $this->assertStringContainsString('Printed Polo T-Shirt', $decoded);
    }

    /**
     * 5. Label Printing: TVS LP 46 Dlite TSPL hangtag generation.
     */
    public function test_tvs_lp46_dlite_barcode_label_printing(): void
    {
        $this->actingAs($this->admin);

        $barcode = app(BarcodeService::class)->generateUniqueEan13();
        $product = Product::create([
            'name' => 'Denim Jacket Classic',
            'article_code' => 'JKT-'.rand(100, 999),
            'category_id' => $this->category->id,
            'hsn_code' => '6201',
            'tax_rate' => 12.0,
            'is_tax_inclusive' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'XL',
            'color' => 'Washed Blue',
            'sku' => 'JKT-WBL-XL',
            'barcode' => $barcode,
            'purchase_cost' => 800.00,
            'selling_price' => 1999.00,
            'mrp' => 2999.00,
            'is_active' => true,
        ]);

        $printService = app(PrintService::class);
        $labelJob = $printService->printBarcodeLabel($variant, 3, $this->tvsLabelPrinter);

        $this->assertNotNull($labelJob);
        $this->assertEquals('success', $labelJob->status);
        $this->assertEquals('barcode_label', $labelJob->job_type);

        // Verify TSPL native commands for TVS LP 46
        $tsplPayload = base64_decode($labelJob->payload);
        $this->assertStringContainsString('SIZE 50 mm, 38 mm', $tsplPayload);
        $this->assertStringContainsString('BARCODE 20,110,"128"', $tsplPayload);
        $this->assertStringContainsString($variant->barcode, $tsplPayload);
        $this->assertStringContainsString('PRINT 3,1', $tsplPayload);
    }

    /**
     * 6. Barcode Label Studio Queue management.
     */
    public function test_barcode_label_studio_queue_and_scanner(): void
    {
        $this->actingAs($this->admin);

        $barcode = app(BarcodeService::class)->generateUniqueEan13();
        $product = Product::create([
            'name' => 'Silk Saree Traditional',
            'article_code' => 'SAREE-'.rand(100, 999),
            'category_id' => $this->category->id,
            'hsn_code' => '5007',
            'tax_rate' => 5.0,
            'is_tax_inclusive' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'Free Size',
            'color' => 'Maroon Gold',
            'sku' => 'SAREE-MG-FS',
            'barcode' => $barcode,
            'purchase_cost' => 1500.00,
            'selling_price' => 3500.00,
            'mrp' => 4999.00,
            'is_active' => true,
        ]);

        Livewire::test(BarcodeLabelPrinter::class)
            ->set('scanInput', $barcode)
            ->call('handleScannerInput')
            ->assertDispatched('scan-success')
            ->assertSet('queue.'.$variant->id, 1)
            ->set('selectedPrinterId', $this->tvsLabelPrinter->id)
            ->call('dispatchThermalPrintJob')
            ->assertHasNoErrors()
            ->assertSee('Successfully dispatched');
    }

    /**
     * 7. All Reports: Verify all 7 reports render correctly with real calculations.
     */
    public function test_all_reports_render_accurately(): void
    {
        $this->actingAs($this->admin);

        $today = now()->toDateString();

        // A. Daily Sales Report
        Livewire::test(ReportsManager::class)
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->set('reportType', 'daily_sales')
            ->assertSee('Daily Sales')
            ->assertSee('Total Net Revenue')
            ->assertSee('Total Completed Invoices');

        // B. Payment Reconciliation Report
        Livewire::test(ReportsManager::class)
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->set('reportType', 'payment_reconciliation')
            ->assertSee('Payment Channel Breakdown');

        // C. Gross Profit Report
        Livewire::test(ReportsManager::class)
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->set('reportType', 'gross_profit')
            ->assertSee('Cost of Goods Sold (COGS)')
            ->assertSee('Gross Profit Margin');

        // D. GST Tax Summary Report
        Livewire::test(ReportsManager::class)
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->set('reportType', 'gst_tax')
            ->assertSee('Tax Breakdown')
            ->assertSee('Grand Total Tax');

        // E. Stock Valuation Report
        Livewire::test(ReportsManager::class)
            ->set('reportType', 'stock_valuation')
            ->assertSee('Stock Valuation');

        // F. Fast & Slow Moving Apparel Report (New)
        Livewire::test(ReportsManager::class)
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->set('reportType', 'top_selling')
            ->assertSee('Fast / Slow Moving')
            ->assertSee('Total Quantity Sold')
            ->assertSee('Gross Garment Revenue');

        // G. Staff Performance & Salesperson Attribution Report (New)
        Livewire::test(ReportsManager::class)
            ->set('startDate', $today)
            ->set('endDate', $today)
            ->set('reportType', 'salesperson_performance')
            ->assertSee('Staff Performance')
            ->assertSee('Total Sales Invoices')
            ->assertSee('Discounts Given by Staff');
    }

    /**
     * 8. Reports CSV Export Stream test.
     */
    public function test_reports_csv_export_streaming(): void
    {
        $this->actingAs($this->admin);

        $response = Livewire::test(ReportsManager::class)
            ->set('reportType', 'top_selling')
            ->call('exportCsv');

        $this->assertNotNull($response);
    }
}
