<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Counter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BranchAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branches
        $mainBranch = Branch::firstOrCreate(
            ['code' => 'SLM01'],
            [
                'name' => 'NextDNA Fashion Megastore - Salem',
                'address' => '124 Meyyanur Bypass Road, Near New Bus Stand',
                'city' => 'Salem',
                'state' => 'Tamil Nadu',
                'state_code' => '33',
                'pincode' => '636004',
                'phone' => '+91 427 2445566',
                'email' => 'salem@nextdna.com',
                'gstin' => '33AAAAA0000A1Z5',
                'is_main' => true,
                'is_active' => true,
            ]
        );

        $branch2 = Branch::firstOrCreate(
            ['code' => 'CBE01'],
            [
                'name' => 'NextDNA Retail - Coimbatore RS Puram',
                'address' => '45 DB Road, RS Puram',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'state_code' => '33',
                'pincode' => '641002',
                'phone' => '+91 422 2556677',
                'email' => 'cbe@nextdna.com',
                'gstin' => '33AAAAA0000A1Z5',
                'is_main' => false,
                'is_active' => true,
            ]
        );

        // 2. Counters
        Counter::firstOrCreate(
            ['branch_id' => $mainBranch->id, 'code' => 'CNT-01'],
            ['name' => 'Express Billing Counter 1', 'is_active' => true]
        );

        Counter::firstOrCreate(
            ['branch_id' => $mainBranch->id, 'code' => 'CNT-02'],
            ['name' => 'Main Billing Counter 2', 'is_active' => true]
        );

        // 3. Users with configured roles & permissions
        $password = Hash::make('Nextdna@123');

        User::firstOrCreate(
            ['email' => 'admin@nextdna.com'],
            [
                'name' => 'Senthil Nathan (Owner)',
                'password' => $password,
                'role' => 'admin',
                'phone' => '9842711223',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'max_discount_percentage' => 100.00,
                'can_override_price' => true,
                'can_give_refund' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@nextdna.com'],
            [
                'name' => 'Murugan Ramasamy (Store Manager)',
                'password' => $password,
                'role' => 'manager',
                'phone' => '9842722334',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'max_discount_percentage' => 30.00,
                'can_override_price' => true,
                'can_give_refund' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier1@nextdna.com'],
            [
                'name' => 'Kavitha Selvam (Head Cashier)',
                'password' => $password,
                'role' => 'cashier',
                'phone' => '9842733445',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'max_discount_percentage' => 10.00,
                'can_override_price' => false,
                'can_give_refund' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier2@nextdna.com'],
            [
                'name' => 'Dinesh Kumar (POS Cashier)',
                'password' => $password,
                'role' => 'cashier',
                'phone' => '9842744556',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'max_discount_percentage' => 10.00,
                'can_override_price' => false,
                'can_give_refund' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'inventory@nextdna.com'],
            [
                'name' => 'Praveen Chandran (Stock Incharge)',
                'password' => $password,
                'role' => 'inventory_staff',
                'phone' => '9842755667',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'max_discount_percentage' => 5.00,
                'can_override_price' => false,
                'can_give_refund' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'accountant@nextdna.com'],
            [
                'name' => 'Venkatesh Iyer (Chief Accountant)',
                'password' => $password,
                'role' => 'accountant',
                'phone' => '9842766778',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'max_discount_percentage' => 10.00,
                'can_override_price' => false,
                'can_give_refund' => false,
            ]
        );

        // 4. Default Application Settings
        $settings = [
            'store_name' => 'NextDNA Technologies - Retail POS',
            'store_phone' => '+91 427 2445566',
            'store_email' => 'support@nextdna.com',
            'store_gstin' => '33AAAAA0000A1Z5',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'store_state_code' => '33',
            'store_state_name' => 'Tamil Nadu',
            'allow_negative_stock' => '0',
            'manager_discount_limit' => '30.00',
            'cashier_discount_limit' => '10.00',
            'loyalty_points_per_100_inr' => '1',
            'loyalty_redemption_rate' => '1.00',
            'receipt_paper_width' => '80',
            'receipt_footer_message' => 'Thank you for shopping at NextDNA! Exchange within 15 days.',
            'enable_sound_effects' => '1',
            'barcode_symbology' => 'CODE128',
        ];

        foreach ($settings as $key => $val) {
            AppSetting::set($key, $val);
        }
    }
}
