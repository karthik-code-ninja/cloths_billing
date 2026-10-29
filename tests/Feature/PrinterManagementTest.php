<?php

namespace Tests\Feature;

use App\Livewire\PrinterSettingsManager;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\BillingService;
use App\Services\EscPosService;
use App\Services\PrintService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PrinterManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected PrintService $printService;

    protected EscPosService $escposService;

    protected BillingService $billingService;

    protected Branch $branch;

    protected User $cashier;

    protected Order $order;

    protected Printer $printer80;

    protected Printer $printer58;

    protected Printer $lanPrinterOffline;

    protected function setUp(): void
    {
        parent::setUp();
        $this->printService = app(PrintService::class);
        $this->escposService = app(EscPosService::class);
        $this->billingService = app(BillingService::class);

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Test Store',
            'code' => 'TEST',
            'state_code' => '33',
            'city' => 'Salem',
            'address' => '45 Car Street, Salem - 636001',
            'phone' => '9843012345',
            'gstin' => '33AAAAA0000A1Z5',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->cashier = User::where('role', 'cashier')->first() ?? User::create([
            'name' => 'Printer Test Cashier',
            'email' => 'cashier_prn_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->printer80 = Printer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Thermal 80mm Simulator',
            'connection_type' => 'mock',
            'paper_width_mm' => 80,
            'columns' => 42,
            'dpi' => 203,
            'has_cutter' => true,
            'has_cash_drawer' => true,
            'is_default_receipt' => true,
            'is_active' => true,
        ]);

        $this->printer58 = Printer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Thermal 58mm Simulator',
            'connection_type' => 'mock',
            'paper_width_mm' => 58,
            'columns' => 32,
            'dpi' => 203,
            'has_cutter' => false,
            'has_cash_drawer' => false,
            'is_default_receipt' => false,
            'is_active' => true,
        ]);

        $this->lanPrinterOffline = Printer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Unreachable LAN ESC/POS',
            'connection_type' => 'lan_escpos',
            'ip_address' => '192.0.2.1', // TEST-NET unreachable IP
            'port' => 9100,
            'paper_width_mm' => 80,
            'columns' => 42,
            'dpi' => 203,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 't-shirts'],
            ['name' => 'T-Shirts', 'hsn_code' => '6109', 'default_tax_rate' => 5.00]
        );

        $product = Product::firstOrCreate(
            ['article_code' => 'TEST-TSHIRT-01'],
            [
                'category_id' => $category->id,
                'name' => 'Round Neck Tee',
                'hsn_code' => '6109',
                'tax_rate' => 5.00,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'Black',
            'sku' => 'TEST-TEE-BLK-L-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'purchase_cost' => 250.00,
            'selling_price' => 599.00,
            'mrp' => 799.00,
            'is_active' => true,
        ]);

        $retailLocation = StockLocation::firstOrCreate(
            ['branch_id' => $this->branch->id, 'type' => 'retail'],
            ['name' => 'Retail Floor', 'is_default' => true]
        );
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $variant->id, 'stock_location_id' => $retailLocation->id],
            ['quantity' => 10]
        );

        $this->order = $this->billingService->checkout([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 599.00,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 599.00],
            ],
        ]);
    }

    public function test_escpos_payload_formatting_for_58mm_and_80mm_without_column_clipping(): void
    {
        // 80mm payload (42 cols)
        $payload80 = $this->printService->buildReceiptEscPosPayload($this->order, $this->printer80);
        $this->assertNotEmpty($payload80);
        $this->assertStringContainsStringIgnoringCase('NextDNA', $payload80);
        $this->assertStringContainsString('599.00', $payload80);

        // 58mm payload (32 cols)
        $payload58 = $this->printService->buildReceiptEscPosPayload($this->order, $this->printer58);
        $this->assertNotEmpty($payload58);
        $this->assertStringContainsStringIgnoringCase('NextDNA', $payload58);
        $this->assertStringContainsString('599.00', $payload58);

        // Verify two columns alignment fits within defined width
        $line42 = $this->escposService->formatTwoColumns('Grand Total', 'INR 599.00', 42);
        // Stripping trailing newline
        $cleanLine42 = rtrim($line42, "\n");
        $this->assertEquals(42, mb_strlen($cleanLine42));

        $line32 = $this->escposService->formatTwoColumns('Total', '599.00', 32);
        $cleanLine32 = rtrim($line32, "\n");
        $this->assertEquals(32, mb_strlen($cleanLine32));
    }

    public function test_print_order_receipt_creates_print_job_with_simulator_success(): void
    {
        $job = $this->printService->printOrderReceipt($this->order, $this->printer80);

        $this->assertInstanceOf(PrintJob::class, $job);
        $this->assertEquals($this->printer80->id, $job->printer_id);
        $this->assertEquals($this->order->id, $job->order_id);
        $this->assertEquals('success', $job->status);
        $this->assertEquals(1, $job->attempts);
        $this->assertFalse($job->is_reprint);
        $this->assertNotNull($job->printed_at);
        $this->assertNotEmpty($job->payload);
    }

    public function test_lan_printer_failure_preserves_sale_and_marks_job_failed(): void
    {
        // The sale already exists in DB
        $this->assertDatabaseHas('orders', ['id' => $this->order->id]);

        // Attempting to print to unreachable LAN printer
        $job = $this->printService->printOrderReceipt($this->order, $this->lanPrinterOffline);

        $this->assertEquals('failed', $job->status);
        $this->assertNotNull($job->error_message);

        // Sale is preserved in database! Printing failure MUST NOT undo a completed sale.
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'completed',
        ]);
    }

    public function test_reprint_marks_job_as_duplicate_reprint(): void
    {
        $job = $this->printService->printOrderReceipt($this->order, $this->printer80, isReprint: true);

        $this->assertTrue($job->is_reprint);
        $decodedPayload = base64_decode($job->payload);
        $this->assertStringContainsString('*** DUPLICATE / REPRINT ***', $decodedPayload);
    }

    public function test_test_print_diagnostic_generates_valid_payload(): void
    {
        $diagnosticJob = $this->printService->testPrint($this->printer80);

        $this->assertEquals('success', $diagnosticJob->status);
        $this->assertEquals('test_print', $diagnosticJob->job_type);
        $decoded = base64_decode($diagnosticJob->payload);
        $this->assertStringContainsString('NEXTDNA PRINTER DIAGNOSTICS', $decoded);
        $this->assertStringContainsString('Thermal Print Test Successful!', $decoded);
    }

    public function test_admin_can_safely_delete_printer_with_attached_print_jobs(): void
    {
        $this->actingAs($this->cashier);

        // Create a printer with print jobs attached
        $printer = Printer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Temporary Thermal Tester',
            'connection_type' => 'mock',
            'capability_profile' => 'generic_escpos',
            'paper_width_mm' => 80,
            'columns' => 42,
            'is_default_receipt' => false,
            'is_active' => true,
        ]);

        $job = PrintJob::create([
            'printer_id' => $printer->id,
            'order_id' => $this->order->id,
            'job_type' => 'receipt_80',
            'payload_version' => 'v1',
            'payload' => base64_encode('test payload'),
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('printers', ['id' => $printer->id]);
        $this->assertDatabaseHas('print_jobs', ['id' => $job->id]);

        // Delete printer via Livewire component
        Livewire::test(PrinterSettingsManager::class)
            ->call('deletePrinter', $printer->id)
            ->assertHasNoErrors()
            ->assertSee('removed successfully');

        // Verify printer and its dependent print jobs are safely deleted without FK violation
        $this->assertDatabaseMissing('printers', ['id' => $printer->id]);
        $this->assertDatabaseMissing('print_jobs', ['id' => $job->id]);
    }
}
