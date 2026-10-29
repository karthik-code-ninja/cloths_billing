<?php

namespace Tests\Feature;

use App\Livewire\BarcodeLabelPrinter;
use App\Livewire\ProductLookup;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Printer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\BarcodeService;
use App\Services\PrintService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class BarcodeAndProductLookupTest extends TestCase
{
    use DatabaseTransactions;

    protected BarcodeService $barcodeService;

    protected Branch $branch;

    protected StockLocation $location;

    protected User $user;

    protected Product $product;

    protected ProductVariant $variantM;

    protected ProductVariant $variantL;

    protected function setUp(): void
    {
        parent::setUp();
        $this->barcodeService = app(BarcodeService::class);

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
            ['name' => 'Main Sales Floor', 'code' => 'LOC-'.Str::random(4), 'is_default' => true]
        );

        $this->user = User::where('role', 'manager')->first() ?? User::create([
            'name' => 'Store Manager',
            'email' => 'mgr_bcode_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'manager',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'linen-shirts'],
            ['name' => 'Linen Shirts', 'hsn_code' => '6205', 'default_tax_rate' => 5.00]
        );

        $this->product = Product::firstOrCreate(
            ['article_code' => 'TEST-LINEN-01'],
            [
                'category_id' => $category->id,
                'name' => 'Pure Linen Casual Shirt',
                'hsn_code' => '6205',
                'tax_rate' => 5.00,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $this->variantM = ProductVariant::create([
            'product_id' => $this->product->id,
            'size' => 'M',
            'color' => 'Olive Green',
            'sku' => 'TEST-LIN-OLV-M-'.Str::random(4),
            'barcode' => $this->barcodeService->generateUniqueEan13(),
            'purchase_cost' => 600.00,
            'selling_price' => 1499.00,
            'mrp' => 1999.00,
            'is_active' => true,
        ]);

        $this->variantL = ProductVariant::create([
            'product_id' => $this->product->id,
            'size' => 'L',
            'color' => 'Olive Green',
            'sku' => 'TEST-LIN-OLV-L-'.Str::random(4),
            'barcode' => $this->barcodeService->generateUniqueEan13(),
            'purchase_cost' => 600.00,
            'selling_price' => 1499.00,
            'mrp' => 1999.00,
            'is_active' => true,
        ]);

        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantM->id, 'stock_location_id' => $this->location->id],
            ['quantity' => 12]
        );
        InventoryStock::updateOrCreate(
            ['product_variant_id' => $this->variantL->id, 'stock_location_id' => $this->location->id],
            ['quantity' => 8]
        );
    }

    public function test_barcode_service_generates_valid_ean13_with_correct_gs1_check_digit(): void
    {
        $code = $this->barcodeService->generateUniqueEan13('890', '2026');

        $this->assertEquals(13, strlen($code));
        $this->assertStringStartsWith('8902026', $code);
        $this->assertTrue($this->barcodeService->validateEan13($code));

        // Check digit calculation verification
        // Test known GS1 vector: 890123456789 -> Check digit should be 3
        $vector12 = '890123456789';
        $check = $this->barcodeService->calculateEan13CheckDigit($vector12);
        // Odd: 8+0+2+4+6+8 = 28 * 1 = 28
        // Even: 9+1+3+5+7+9 = 34 * 3 = 102
        // Total = 130 % 10 = 0 -> Check = 0
        $this->assertEquals((10 - ((28 + (34 * 3)) % 10)) % 10, $check);
    }

    public function test_barcode_service_generates_unique_code128_format(): void
    {
        $code1 = $this->barcodeService->generateUniqueCode128('NDNA');
        $code2 = $this->barcodeService->generateUniqueCode128('NDNA');

        $this->assertStringStartsWith('NDNA-', $code1);
        $this->assertStringStartsWith('NDNA-', $code2);
        $this->assertNotEquals($code1, $code2);
    }

    public function test_barcode_service_renders_valid_svg_markup(): void
    {
        $svg = $this->barcodeService->generateCode128Svg('NDNA-2610-0001', barWidth: 2, height: 40);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString('<rect', $svg);
        $this->assertStringContainsString('NDNA-2610-0001', $svg);
    }

    public function test_product_lookup_scans_barcode_and_fetches_variant_with_multi_location_stocks(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ProductLookup::class)
            ->set('barcodeInput', $this->variantM->barcode)
            ->call('handleBarcodeScan')
            ->assertSet('selectedVariant.id', $this->variantM->id)
            ->assertSee($this->variantM->product->name)
            ->assertSee($this->variantM->size)
            ->assertSee('₹1,499.00');
    }

    public function test_product_lookup_assigns_new_unique_barcode(): void
    {
        $this->actingAs($this->user);

        $oldBarcode = $this->variantM->barcode;

        Livewire::test(ProductLookup::class, ['id' => $this->variantM->id])
            ->call('generateUniqueBarcodeForVariant', 'EAN13');

        $this->variantM->refresh();
        $this->assertNotEquals($oldBarcode, $this->variantM->barcode);
        $this->assertEquals(13, strlen($this->variantM->barcode));
        $this->assertTrue($this->barcodeService->validateEan13($this->variantM->barcode));
    }

    public function test_barcode_label_printer_scans_barcode_and_adds_to_queue(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BarcodeLabelPrinter::class)
            ->call('clearQueue')
            ->set('scanInput', $this->variantM->barcode)
            ->call('handleScannerInput')
            ->assertCount('queue', 1);
    }

    public function test_barcode_label_printer_batch_adds_all_product_variants(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BarcodeLabelPrinter::class)
            ->call('clearQueue')
            ->call('addProductVariantsToQueue', $this->product->id)
            ->assertCount('queue', 2); // Variant M & Variant L
    }

    public function test_barcode_label_printer_generates_unique_barcodes_for_queued_items(): void
    {
        $this->actingAs($this->user);

        // Intentionally blank out barcodes
        $this->variantM->update(['barcode' => 'OLD-TEMP-1']);
        $this->variantL->update(['barcode' => 'OLD-TEMP-2']);

        Livewire::test(BarcodeLabelPrinter::class)
            ->call('clearQueue')
            ->call('addToQueue', $this->variantM->id, 2)
            ->call('addToQueue', $this->variantL->id, 2)
            ->call('generateUniqueBarcodesForQueue', 'EAN13');

        $this->variantM->refresh();
        $this->variantL->refresh();

        $this->assertEquals(13, strlen($this->variantM->barcode));
        $this->assertEquals(13, strlen($this->variantL->barcode));
        $this->assertTrue($this->barcodeService->validateEan13($this->variantM->barcode));
        $this->assertTrue($this->barcodeService->validateEan13($this->variantL->barcode));
        $this->assertNotEquals($this->variantM->barcode, $this->variantL->barcode);
    }

    public function test_barcode_label_printer_applies_2up_dual_column_preset(): void
    {
        $this->actingAs($this->user);

        Livewire::test(BarcodeLabelPrinter::class)
            ->call('setPreset', '2up_50x25')
            ->assertSet('templatePreset', '2up_50x25')
            ->assertSet('labelWidthMm', 50)
            ->assertSet('labelHeightMm', 25)
            ->assertSet('columns', 2)
            ->assertSet('gapMm', 3);
    }

    public function test_print_service_builds_2up_tspl_payload_for_tvs_lp46(): void
    {
        $printer = Printer::create([
            'branch_id' => $this->branch->id,
            'name' => 'TVS LP 46 Dlite 2-Up Test',
            'connection_type' => 'mock',
            'capability_profile' => 'tvs_lp46',
            'paper_width_mm' => 104,
            'columns' => 42,
            'dpi' => 203,
            'is_active' => true,
        ]);

        $printService = app(PrintService::class);
        $payload = $printService->buildBarcodeQueuePayload(
            [$this->variantM->id => 2],
            $printer,
            columns: 2,
            widthMm: 50,
            heightMm: 25
        );

        $this->assertStringContainsString('SIZE 104 mm, 25 mm', $payload);
        $this->assertStringContainsString('GAP 2 mm, 0 mm', $payload);
        $this->assertStringContainsString('TEXT 16,', $payload); // Left label
        $this->assertStringContainsString('TEXT 424,', $payload); // Right label
        $this->assertStringContainsString('PRINT 1,1', $payload);
    }
}
