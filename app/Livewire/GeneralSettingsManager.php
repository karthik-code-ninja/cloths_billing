<?php

namespace App\Livewire;

use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GeneralSettingsManager extends Component
{
    public string $activeTab = 'store'; // store, tax, pos, thermal, barcode, localization

    // Store & Company Profile
    public string $store_name = '';

    public string $store_tagline = '';

    public string $store_phone = '';

    public string $store_email = '';

    public string $store_address = '';

    public string $store_city = '';

    public string $store_state = '';

    public string $store_pincode = '';

    public string $store_state_code = '33';

    // Tax & Invoicing (GST)
    public string $gstin = '';

    public float $default_tax_rate = 5.00;

    public bool $tax_inclusive_by_default = true;

    public string $invoice_prefix = 'INV';

    public string $return_prefix = 'RET';

    public string $hsn_default_apparel = '6203';

    // POS & Checkout Rules
    public float $cashier_max_discount_percentage = 10.00;

    public bool $require_manager_override_for_discount = true;

    public bool $allow_negative_stock = false;

    public bool $enable_walkin_customer = true;

    public bool $enable_round_off = true;

    public string $default_payment_method = 'cash';

    public bool $enable_loyalty_program = true;

    public float $loyalty_points_per_100_inr = 1.00;

    public float $loyalty_point_redemption_value = 1.00;

    // Thermal Receipt & Printing
    public string $receipt_header_title = '';

    public string $receipt_footer_message = '';

    public int $receipt_paper_width = 80;

    public bool $receipt_show_tax_breakdown = true;

    public bool $receipt_show_powered_by = true;

    public bool $receipt_show_savings = true;

    // Barcode & Hangtag Labels
    public string $barcode_default_type = 'EAN13';

    public string $barcode_country_prefix = '890';

    public string $barcode_mfg_code = '2026';

    public string $barcode_label_store_name = '';

    public string $barcode_label_default_preset = '50x38';

    // Localization & System
    public string $currency_symbol = '₹';

    public string $currency_code = 'INR';

    public string $date_format = 'd/m/Y';

    public string $timezone = 'Asia/Kolkata';

    public string $app_language = 'en';

    public function mount()
    {
        $this->loadSettings();
    }

    public function render()
    {
        return view('livewire.general-settings-manager');
    }

    /**
     * Load settings from database with sensible retail fallbacks.
     */
    public function loadSettings()
    {
        $branch = Branch::where('is_main', true)->first() ?: Branch::first();

        // Store Profile
        $this->store_name = (string) AppSetting::get('store_name', $branch?->name ?: 'NextDNA Retail Megastore');
        $this->store_tagline = (string) AppSetting::get('store_tagline', 'Clothing, Apparel & Fashion Store');
        $this->store_phone = (string) AppSetting::get('store_phone', $branch?->phone ?: '+91 98430 12345');
        $this->store_email = (string) AppSetting::get('store_email', 'contact@nextdna.com');
        $this->store_address = (string) AppSetting::get('store_address', $branch?->address ?: '45 Car Street, Near New Bus Stand');
        $this->store_city = (string) AppSetting::get('store_city', $branch?->city ?: 'Salem');
        $this->store_state = (string) AppSetting::get('store_state', 'Tamil Nadu');
        $this->store_pincode = (string) AppSetting::get('store_pincode', '636001');
        $this->store_state_code = (string) AppSetting::get('store_state_code', $branch?->state_code ?: '33');

        // Tax & GST
        $this->gstin = (string) AppSetting::get('gstin', $branch?->gstin ?: '33AAAAA0000A1Z5');
        $this->default_tax_rate = (float) AppSetting::get('default_tax_rate', 5.00);
        $this->tax_inclusive_by_default = (bool) AppSetting::get('tax_inclusive_by_default', true);
        $this->invoice_prefix = (string) AppSetting::get('invoice_prefix', 'INV');
        $this->return_prefix = (string) AppSetting::get('return_prefix', 'RET');
        $this->hsn_default_apparel = (string) AppSetting::get('hsn_default_apparel', '6203');

        // POS & Rules
        $this->cashier_max_discount_percentage = (float) AppSetting::get('cashier_max_discount_percentage', 10.00);
        $this->require_manager_override_for_discount = (bool) AppSetting::get('require_manager_override_for_discount', true);
        $this->allow_negative_stock = (bool) AppSetting::get('allow_negative_stock', false);
        $this->enable_walkin_customer = (bool) AppSetting::get('enable_walkin_customer', true);
        $this->enable_round_off = (bool) AppSetting::get('enable_round_off', true);
        $this->default_payment_method = (string) AppSetting::get('default_payment_method', 'cash');
        $this->enable_loyalty_program = (bool) AppSetting::get('enable_loyalty_program', true);
        $this->loyalty_points_per_100_inr = (float) AppSetting::get('loyalty_points_per_100_inr', 1.00);
        $this->loyalty_point_redemption_value = (float) AppSetting::get('loyalty_point_redemption_value', 1.00);

        // Thermal Receipt
        $this->receipt_header_title = (string) AppSetting::get('receipt_header_title', $this->store_name);
        $this->receipt_footer_message = (string) AppSetting::get('receipt_footer_message', 'Exchange within 15 days with tags & original bill. Thank you for shopping with us!');
        $this->receipt_paper_width = (int) AppSetting::get('receipt_paper_width', 80);
        $this->receipt_show_tax_breakdown = (bool) AppSetting::get('receipt_show_tax_breakdown', true);
        $this->receipt_show_powered_by = (bool) AppSetting::get('receipt_show_powered_by', true);
        $this->receipt_show_savings = (bool) AppSetting::get('receipt_show_savings', true);

        // Barcode Defaults
        $this->barcode_default_type = (string) AppSetting::get('barcode_default_type', 'EAN13');
        $this->barcode_country_prefix = (string) AppSetting::get('barcode_country_prefix', '890');
        $this->barcode_mfg_code = (string) AppSetting::get('barcode_mfg_code', '2026');
        $this->barcode_label_store_name = (string) AppSetting::get('barcode_label_store_name', 'NextDNA Retail');
        $this->barcode_label_default_preset = (string) AppSetting::get('barcode_label_default_preset', '50x38');

        // Localization
        $this->currency_symbol = (string) AppSetting::get('currency_symbol', '₹');
        $this->currency_code = (string) AppSetting::get('currency_code', 'INR');
        $this->date_format = (string) AppSetting::get('date_format', 'd/m/Y');
        $this->timezone = (string) AppSetting::get('timezone', 'Asia/Kolkata');
        $this->app_language = (string) AppSetting::get('app_language', 'en');
    }

    /**
     * Save settings dynamically and persist to DB and main branch.
     */
    public function saveSettings()
    {
        $this->validate([
            'store_name' => 'required|string|min:2|max:150',
            'store_phone' => 'nullable|string|max:30',
            'store_email' => 'nullable|email|max:100',
            'store_state_code' => 'required|string|max:5',
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'invoice_prefix' => 'required|string|min:1|max:10',
            'cashier_max_discount_percentage' => 'required|numeric|min:0|max:100',
            'barcode_country_prefix' => 'required|string|size:3',
        ]);

        // Save Store Group
        AppSetting::set('store_name', $this->store_name, 'string', 'store');
        AppSetting::set('store_tagline', $this->store_tagline, 'string', 'store');
        AppSetting::set('store_phone', $this->store_phone, 'string', 'store');
        AppSetting::set('store_email', $this->store_email, 'string', 'store');
        AppSetting::set('store_address', $this->store_address, 'string', 'store');
        AppSetting::set('store_city', $this->store_city, 'string', 'store');
        AppSetting::set('store_state', $this->store_state, 'string', 'store');
        AppSetting::set('store_pincode', $this->store_pincode, 'string', 'store');
        AppSetting::set('store_state_code', $this->store_state_code, 'string', 'store');

        // Save Tax Group
        AppSetting::set('gstin', strtoupper(trim($this->gstin)), 'string', 'tax');
        AppSetting::set('default_tax_rate', $this->default_tax_rate, 'decimal', 'tax');
        AppSetting::set('tax_inclusive_by_default', $this->tax_inclusive_by_default, 'boolean', 'tax');
        AppSetting::set('invoice_prefix', strtoupper(trim($this->invoice_prefix)), 'string', 'tax');
        AppSetting::set('return_prefix', strtoupper(trim($this->return_prefix)), 'string', 'tax');
        AppSetting::set('hsn_default_apparel', trim($this->hsn_default_apparel), 'string', 'tax');

        // Save POS Group
        AppSetting::set('cashier_max_discount_percentage', $this->cashier_max_discount_percentage, 'decimal', 'pos');
        AppSetting::set('require_manager_override_for_discount', $this->require_manager_override_for_discount, 'boolean', 'pos');
        AppSetting::set('allow_negative_stock', $this->allow_negative_stock, 'boolean', 'pos');
        AppSetting::set('enable_walkin_customer', $this->enable_walkin_customer, 'boolean', 'pos');
        AppSetting::set('enable_round_off', $this->enable_round_off, 'boolean', 'pos');
        AppSetting::set('default_payment_method', $this->default_payment_method, 'string', 'pos');
        AppSetting::set('enable_loyalty_program', $this->enable_loyalty_program, 'boolean', 'pos');
        AppSetting::set('loyalty_points_per_100_inr', $this->loyalty_points_per_100_inr, 'decimal', 'pos');
        AppSetting::set('loyalty_point_redemption_value', $this->loyalty_point_redemption_value, 'decimal', 'pos');

        // Save Thermal Group
        AppSetting::set('receipt_header_title', $this->receipt_header_title ?: $this->store_name, 'string', 'thermal');
        AppSetting::set('receipt_footer_message', $this->receipt_footer_message, 'string', 'thermal');
        AppSetting::set('receipt_paper_width', $this->receipt_paper_width, 'integer', 'thermal');
        AppSetting::set('receipt_show_tax_breakdown', $this->receipt_show_tax_breakdown, 'boolean', 'thermal');
        AppSetting::set('receipt_show_powered_by', $this->receipt_show_powered_by, 'boolean', 'thermal');
        AppSetting::set('receipt_show_savings', $this->receipt_show_savings, 'boolean', 'thermal');

        // Save Barcode Group
        AppSetting::set('barcode_default_type', $this->barcode_default_type, 'string', 'barcode');
        AppSetting::set('barcode_country_prefix', $this->barcode_country_prefix, 'string', 'barcode');
        AppSetting::set('barcode_mfg_code', $this->barcode_mfg_code, 'string', 'barcode');
        AppSetting::set('barcode_label_store_name', $this->barcode_label_store_name ?: $this->store_name, 'string', 'barcode');
        AppSetting::set('barcode_label_default_preset', $this->barcode_label_default_preset, 'string', 'barcode');

        // Save Localization Group
        AppSetting::set('currency_symbol', $this->currency_symbol, 'string', 'localization');
        AppSetting::set('currency_code', $this->currency_code, 'string', 'localization');
        AppSetting::set('date_format', $this->date_format, 'string', 'localization');
        AppSetting::set('timezone', $this->timezone, 'string', 'localization');
        AppSetting::set('app_language', $this->app_language, 'string', 'localization');

        // Also synchronize main Branch record
        $mainBranch = Branch::where('is_main', true)->first() ?: Branch::first();
        if ($mainBranch) {
            $mainBranch->update([
                'name' => $this->store_name,
                'phone' => $this->store_phone,
                'address' => $this->store_address,
                'city' => $this->store_city,
                'state_code' => $this->store_state_code,
                'gstin' => strtoupper(trim($this->gstin)),
            ]);
        }

        // Record Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated_general_settings',
            'entity_type' => 'AppSetting',
            'entity_id' => null,
            'new_values' => [
                'store_name' => $this->store_name,
                'gstin' => $this->gstin,
                'allow_negative_stock' => $this->allow_negative_stock,
                'cashier_max_discount' => $this->cashier_max_discount_percentage,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        session()->flash('success', 'General settings saved and applied dynamically across the system!');
    }
}
