<?php

namespace Tests\Feature;

use App\Livewire\GeneralSettingsManager;
use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Printer;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PrintService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'NextDNA Flagship Store',
            'code' => 'MAIN',
            'state_code' => '33',
            'city' => 'Salem',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->admin = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin Setting Tester',
            'email' => 'admin_set_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'admin',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_settings_page_renders_with_saved_defaults(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(GeneralSettingsManager::class)
            ->assertSee('Dynamic General Settings')
            ->assertSee('Store Profile')
            ->assertSee('POS Billing Rules')
            ->assertSee('Thermal Receipts');
    }

    public function test_admin_can_update_and_persist_dynamic_settings(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(GeneralSettingsManager::class)
            ->set('store_name', 'NextDNA Couture Chennai')
            ->set('store_phone', '+91 94444 88888')
            ->set('gstin', '33BBBBB1111B2Z6')
            ->set('invoice_prefix', 'BILL')
            ->set('cashier_max_discount_percentage', 15.00)
            ->set('allow_negative_stock', true)
            ->set('receipt_header_title', 'NEXTDNA COUTURE')
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertSee('General settings saved');

        // Verify dynamic values in AppSetting
        $this->assertEquals('NextDNA Couture Chennai', AppSetting::get('store_name'));
        $this->assertEquals('+91 94444 88888', AppSetting::get('store_phone'));
        $this->assertEquals('33BBBBB1111B2Z6', AppSetting::get('gstin'));
        $this->assertEquals('BILL', AppSetting::get('invoice_prefix'));
        $this->assertEquals(15.00, AppSetting::get('cashier_max_discount_percentage'));
        $this->assertTrue(AppSetting::get('allow_negative_stock'));

        // Verify Branch synchronization
        $this->branch->refresh();
        $this->assertEquals('NextDNA Couture Chennai', $this->branch->name);
        $this->assertEquals('33BBBBB1111B2Z6', $this->branch->gstin);

        // Verify Audit Log entry was generated
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated_general_settings',
            'entity_type' => 'AppSetting',
        ]);
    }

    public function test_dynamic_invoice_prefix_reflected_in_sequential_invoice_number(): void
    {
        AppSetting::set('invoice_prefix', 'TXN');

        $billingService = app(BillingService::class);
        $invoiceNumber = $billingService->generateSequentialInvoiceNumber($this->branch);

        $this->assertStringStartsWith('TXN-'.now()->format('Ym'), $invoiceNumber);
    }

    public function test_dynamic_receipt_customization_reflected_in_thermal_payload(): void
    {
        AppSetting::set('receipt_header_title', 'ROYAL NEXTDNA APPARELS');
        AppSetting::set('receipt_footer_message', 'Items once sold can be exchanged within 30 days.');

        $printer = Printer::firstOrCreate(
            ['branch_id' => $this->branch->id, 'name' => 'Mock Receipt Printer'],
            ['connection_type' => 'mock', 'paper_width_mm' => 80, 'columns' => 42, 'is_active' => true]
        );

        $order = Order::firstOrCreate(
            ['invoice_number' => 'INV-TEST-001'],
            [
                'branch_id' => $this->branch->id,
                'cashier_id' => $this->admin->id,
                'subtotal' => 1000.00,
                'taxable_value' => 952.38,
                'tax_total' => 47.62,
                'grand_total' => 1000.00,
                'paid_amount' => 1000.00,
                'status' => 'completed',
                'invoice_date' => now(),
            ]
        );

        $printService = app(PrintService::class);
        $payload = $printService->buildReceiptEscPosPayload($order, $printer);

        $this->assertStringContainsString('ROYAL NEXTDNA APPARELS', $payload);
        $this->assertStringContainsString('exchanged within 30 days', $payload);
    }
}
