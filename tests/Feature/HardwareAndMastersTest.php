<?php

namespace Tests\Feature;

use App\Livewire\BrandMaster;
use App\Livewire\CatalogueManager;
use App\Livewire\CategoryMaster;
use App\Livewire\PosBilling;
use App\Livewire\ProductLookup;
use App\Livewire\SeasonMaster;
use App\Livewire\SizeGroupMaster;
use App\Livewire\SupplierMaster;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Printer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Season;
use App\Models\SizeGroup;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PrintService;
use App\Services\ProductImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class HardwareAndMastersTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin Tester',
            'email' => 'admin_test_'.Str::random(6).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_category_master_renders_and_lists_categories(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CategoryMaster::class)
            ->assertStatus(200)
            ->assertSee('Category Master')
            ->assertSee('Men&#039;s Wear', false);
    }

    public function test_admin_can_create_and_edit_category(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CategoryMaster::class)
            ->call('openCreateModal')
            ->set('name', 'Ethnic Sherwanis')
            ->set('code', 'SHERW')
            ->set('hsn_code', '5208')
            ->set('default_tax_rate', 12.0)
            ->call('saveCategory')
            ->assertHasNoErrors();

        $cat = Category::where('name', 'Ethnic Sherwanis')->first();
        $this->assertNotNull($cat);
        $this->assertEquals('5208', $cat->hsn_code);
        $this->assertEquals(12.0, (float) $cat->default_tax_rate);

        // Edit
        Livewire::test(CategoryMaster::class)
            ->call('editCategory', $cat->id)
            ->set('hsn_code', '6203')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertEquals('6203', $cat->fresh()->hsn_code);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $this->actingAs($this->admin);

        $cat = Category::firstOrCreate(
            ['slug' => 'winter-jackets'],
            ['name' => 'Winter Jackets', 'hsn_code' => '6201', 'default_tax_rate' => 12.0, 'is_active' => true]
        );

        $initialStatus = $cat->is_active;

        Livewire::test(CategoryMaster::class)
            ->call('toggleStatus', $cat->id);

        $this->assertEquals(! $initialStatus, $cat->fresh()->is_active);
    }

    public function test_cannot_delete_category_with_linked_products(): void
    {
        $this->actingAs($this->admin);

        $cat = Category::has('products')->first();
        $this->assertNotNull($cat);

        Livewire::test(CategoryMaster::class)
            ->call('deleteCategory', $cat->id);

        // Must still exist in database
        $this->assertNotNull(Category::find($cat->id));
    }

    public function test_brand_master_renders_and_lists_brands(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(BrandMaster::class)
            ->assertStatus(200)
            ->assertSee('Brand Master')
            ->assertSee('Raymond Classic');
    }

    public function test_admin_can_create_and_edit_brand(): void
    {
        $this->actingAs($this->admin);

        $code = 'ZARA'.rand(10, 99);

        Livewire::test(BrandMaster::class)
            ->call('openCreateModal')
            ->set('name', 'Zara Man')
            ->set('code', $code)
            ->call('saveBrand')
            ->assertHasNoErrors();

        $brand = Brand::where('code', $code)->first();
        $this->assertNotNull($brand);
        $this->assertEquals('Zara Man', $brand->name);

        // Edit
        Livewire::test(BrandMaster::class)
            ->call('editBrand', $brand->id)
            ->set('name', 'Zara Man Collection')
            ->call('saveBrand')
            ->assertHasNoErrors();

        $this->assertEquals('Zara Man Collection', $brand->fresh()->name);
    }

    public function test_bulk_product_import_service_with_vyapar_dataset(): void
    {
        $filePath = '/home/ubuntu/.gemini/antigravity/brain/a3baf179-4fb9-436d-a63c-995810d52cff/.user_uploaded/media_1793199416215_16d6fd2a.xlsx';

        if (! file_exists($filePath)) {
            $this->markTestSkipped('Vyapar XLSX file not found on path.');
        }

        $importService = app(ProductImportService::class);
        $result = $importService->importFromFile($filePath, $this->admin->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(45, $result['total_rows']);
        $this->assertGreaterThanOrEqual(40, $result['imported_variants']);
        $this->assertGreaterThan(0, $result['total_stock_units']);

        // Check specific items: 1890 (barcode 38620681324) and NIKE (barcode 38626101741)
        $variant1890 = ProductVariant::where('barcode', '38620681324')->first();
        $this->assertNotNull($variant1890);
        $this->assertEquals(449.00, (float) $variant1890->selling_price);
        $this->assertEquals(295.00, (float) $variant1890->purchase_cost);
        $this->assertEquals(6, $variant1890->stock_on_hand);

        $variantNike = ProductVariant::where('barcode', '38626101741')->first();
        $this->assertNotNull($variantNike);
        $this->assertEquals('Nike', $variantNike->product->brand->name);
        $this->assertEquals(499.00, (float) $variantNike->selling_price);
    }

    public function test_tvs_bs_l100_plus_barcode_scanner_lookup_in_pos(): void
    {
        $this->actingAs($this->admin);

        // Ensure item exists
        $variant = ProductVariant::where('barcode', '38620681324')->first();
        if (! $variant) {
            $cat = Category::first();
            $brand = Brand::first();
            $product = Product::create([
                'name' => '1890 T-Shirt',
                'article_code' => '1890',
                'category_id' => $cat->id,
                'brand_id' => $brand->id,
                'is_active' => true,
            ]);
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'barcode' => '38620681324',
                'sku' => '1890-L-BLK-TEST',
                'size' => 'L',
                'color' => 'Standard',
                'selling_price' => 449.00,
                'purchase_cost' => 295.00,
                'mrp' => 699.00,
                'is_active' => true,
            ]);
        }

        // Test scanner wedge in POS Billing
        Livewire::test(PosBilling::class)
            ->set('barcodeInput', '38620681324')
            ->call('handleBarcodeScan')
            ->assertSee('1890')
            ->assertSee('449');
    }

    public function test_tvs_bs_l100_plus_barcode_scanner_in_product_lookup(): void
    {
        $this->actingAs($this->admin);

        // Ensure item exists
        $variant = ProductVariant::where('barcode', '38620681324')->first();
        if (! $variant) {
            $cat = Category::first();
            $brand = Brand::first();
            $product = Product::create([
                'name' => '1890 T-Shirt',
                'article_code' => '1890',
                'category_id' => $cat->id,
                'brand_id' => $brand->id,
                'is_active' => true,
            ]);
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'barcode' => '38620681324',
                'sku' => '1890-L-BLK-TEST2',
                'size' => 'L',
                'color' => 'Standard',
                'selling_price' => 449.00,
                'purchase_cost' => 295.00,
                'mrp' => 699.00,
                'is_active' => true,
            ]);
        }

        Livewire::test(ProductLookup::class)
            ->set('barcodeInput', '38620681324')
            ->call('handleBarcodeScan')
            ->assertSee('1890')
            ->assertSee('449');
    }

    public function test_tvs_rp_3200_lite_thermal_receipt_printer_payload(): void
    {
        $branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Retail',
            'code' => 'SLM01',
            'state_code' => '33',
            'is_main' => true,
            'is_active' => true,
        ]);

        $tvsPrinter = Printer::firstOrCreate(
            ['name' => 'TVS RP 3200 LITE Feature Test'],
            [
                'branch_id' => $branch->id,
                'connection_type' => 'browser',
                'capability_profile' => 'tvs_rp3200',
                'paper_width_mm' => 80,
                'columns' => 42,
                'dpi' => 203,
                'has_cutter' => true,
                'has_cash_drawer' => true,
                'is_default_receipt' => true,
                'is_active' => true,
            ]
        );

        $customer = Customer::first() ?? Customer::create([
            'name' => 'Walk-in Customer',
            'phone' => '9876543210',
        ]);

        $order = Order::create([
            'invoice_number' => 'INV-TEST-TVS',
            'order_type' => 'pos',
            'branch_id' => $branch->id,
            'cashier_id' => $this->admin->id,
            'customer_id' => $customer->id,
            'subtotal' => 1000.00,
            'taxable_value' => 1000.00,
            'tax_total' => 50.00,
            'cgst_total' => 25.00,
            'sgst_total' => 25.00,
            'grand_total' => 1050.00,
            'paid_amount' => 1050.00,
            'status' => 'completed',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 1050.00,
            'is_verified' => true,
            'received_at' => now(),
        ]);

        $printService = app(PrintService::class);
        $payload = $printService->buildReceiptEscPosPayload($order, $tvsPrinter);

        // Verify ESC/POS commands
        $this->assertNotEmpty($payload);
        // Check cash drawer kick pulse is generated for cash sale
        $this->assertStringContainsString("\x1B\x70\x00\x19\xFA", $payload);
        // Check partial cut command is present
        $this->assertStringContainsString("\x1D\x56", $payload);
    }

    public function test_tvs_lp_46_dlite_tspl_hangtag_label_generation(): void
    {
        $variant = ProductVariant::with('product')->first();
        $this->assertNotNull($variant);

        $labelPrinter = Printer::firstOrCreate(
            ['name' => 'TVS LP 46 Dlite Feature Test'],
            [
                'branch_id' => 1,
                'connection_type' => 'browser',
                'capability_profile' => 'tvs_lp46',
                'paper_width_mm' => 50,
                'columns' => 32,
                'dpi' => 203,
                'has_cutter' => false,
                'has_cash_drawer' => false,
                'is_default_label' => true,
                'is_active' => true,
            ]
        );

        $printService = app(PrintService::class);
        $tsplPayload = $printService->buildBarcodeLabelPayload($variant, 2, $labelPrinter);

        // Assert TSPL structure calibrated for TVS LP 46 Dlite
        $this->assertStringContainsString('SIZE 50 mm, 38 mm', $tsplPayload);
        $this->assertStringContainsString('BARCODE 20,110,"128"', $tsplPayload);
        $this->assertStringContainsString($variant->barcode, $tsplPayload);
        $this->assertStringContainsString('PRINT 2,1', $tsplPayload);
    }

    public function test_supplier_master_crud_and_status(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SupplierMaster::class)
            ->assertStatus(200)
            ->assertSee('Supplier / Vendor Master')
            ->call('openCreateModal')
            ->set('name', 'Erode Weaving Mills')
            ->set('company_name', 'Erode Textiles Group')
            ->set('gstin', '33AABCE9999Z1Z5')
            ->set('city', 'Erode')
            ->set('state', 'Tamil Nadu')
            ->set('state_code', '33')
            ->set('payment_terms_days', 45)
            ->call('saveSupplier')
            ->assertHasNoErrors();

        $sup = Supplier::where('name', 'Erode Weaving Mills')->first();
        $this->assertNotNull($sup);
        $this->assertEquals('33AABCE9999Z1Z5', $sup->gstin);
        $this->assertEquals(45, $sup->payment_terms_days);

        // Edit
        Livewire::test(SupplierMaster::class)
            ->call('editSupplier', $sup->id)
            ->set('payment_terms_days', 60)
            ->call('saveSupplier')
            ->assertHasNoErrors();

        $this->assertEquals(60, $sup->fresh()->payment_terms_days);

        // Toggle Status
        Livewire::test(SupplierMaster::class)
            ->call('toggleStatus', $sup->id);

        $this->assertFalse((bool) $sup->fresh()->is_active);
    }

    public function test_size_group_master_crud_and_presets(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SizeGroupMaster::class)
            ->assertStatus(200)
            ->assertSee('Size Group Master')
            ->call('openCreateModal')
            ->call('loadPreset', 'alpha')
            ->set('name', 'Custom Test Alpha')
            ->set('code', 'TEST_ALPHA_'.rand(10, 99))
            ->call('addSize')
            ->call('saveSizeGroup')
            ->assertHasNoErrors();

        $sg = SizeGroup::where('name', 'Custom Test Alpha')->first();
        $this->assertNotNull($sg);
        $this->assertContains('XS', $sg->sizes);
        $this->assertContains('XXL', $sg->sizes);

        // Edit & Add custom size
        Livewire::test(SizeGroupMaster::class)
            ->call('editSizeGroup', $sg->id)
            ->set('newSize', '4XL')
            ->call('addSize')
            ->call('saveSizeGroup')
            ->assertHasNoErrors();

        $this->assertContains('4XL', $sg->fresh()->sizes);
    }

    public function test_season_master_crud_and_validation(): void
    {
        $this->actingAs($this->admin);

        $code = 'PONGAL_'.rand(10, 99);

        Livewire::test(SeasonMaster::class)
            ->assertStatus(200)
            ->assertSee('Collection Master')
            ->call('openCreateModal')
            ->set('name', 'Pongal Festive Collection')
            ->set('code', $code)
            ->call('saveSeason')
            ->assertHasNoErrors();

        $season = Season::where('code', $code)->first();
        $this->assertNotNull($season);
        $this->assertEquals('Pongal Festive Collection', $season->name);

        // Toggle status
        Livewire::test(SeasonMaster::class)
            ->call('toggleStatus', $season->id);

        $this->assertFalse((bool) $season->fresh()->is_active);
    }

    public function test_catalogue_manager_category_autofill_and_matrix_generation(): void
    {
        $this->actingAs($this->admin);

        // Ensure category exists with specific HSN and tax rate
        $category = Category::firstOrCreate(
            ['slug' => 'linen-shirts'],
            ['name' => 'Linen Shirts', 'hsn_code' => '6205', 'default_tax_rate' => 12.00, 'is_active' => true]
        );

        $artCode = 'ART-TEST-'.rand(1000, 9999);

        // Test category selection auto-fills HSN & GST rate
        $component = Livewire::test(CatalogueManager::class)
            ->call('openCreateModal')
            ->set('category_id', $category->id);

        $this->assertEquals('6205', $component->get('hsn_code'));
        $this->assertEquals(12.00, (float) $component->get('tax_rate'));

        // Save product
        $component->set('name', 'Premium Irish Linen Shirt')
            ->set('article_code', $artCode)
            ->set('fabric', '100% Pure Linen')
            ->set('fit', 'Slim Fit')
            ->set('pattern', 'Checks')
            ->call('saveProduct')
            ->assertHasNoErrors();

        $product = Product::where('article_code', $artCode)->first();
        $this->assertNotNull($product);
        $this->assertEquals('100% Pure Linen', $product->fabric);
        $this->assertEquals('Slim Fit', $product->fit);
        $this->assertEquals('Checks', $product->pattern);

        // Test matrix generation
        $sizeGroup = SizeGroup::first();
        $this->assertNotNull($sizeGroup);

        Livewire::test(CatalogueManager::class)
            ->call('openMatrixModal', $product->id)
            ->call('selectMatrixSizeGroup', $sizeGroup->id)
            ->set('matrixColors', ['Navy Blue', 'Olive Green'])
            ->set('matrixCost', 550.00)
            ->set('matrixPrice', 1199.00)
            ->set('matrixMrp', 1599.00)
            ->set('matrixInitialStock', 10)
            ->call('generateMatrixVariants')
            ->assertHasNoErrors();

        // Check variants created
        $expectedVariantCount = count($sizeGroup->sizes) * 2;
        $variants = ProductVariant::where('product_id', $product->id)->get();
        $this->assertCount($expectedVariantCount, $variants);

        // Check stock created
        $firstVariant = $variants->first();
        $this->assertEquals(10, $firstVariant->stock_on_hand);
        $this->assertEquals(1199.00, (float) $firstVariant->selling_price);
    }
}
