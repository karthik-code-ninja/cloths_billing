<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\GiftVoucher;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class CustomerAndPromotionSeeder extends Seeder
{
    public function run(): void
    {
        // Customers
        $c1 = Customer::firstOrCreate(
            ['phone' => '9876543210'],
            [
                'name' => 'Ramesh Kumar',
                'email' => 'ramesh.kumar@gmail.com',
                'address' => '15 Gandhi Road, Fairlands',
                'city' => 'Salem',
                'state' => 'Tamil Nadu',
                'state_code' => '33',
                'customer_group' => 'retail',
                'loyalty_points' => 350,
                'store_credit_balance' => 500.00,
                'total_purchases_count' => 8,
                'total_spent' => 12450.00,
                'is_active' => true,
            ]
        );

        $c2 = Customer::firstOrCreate(
            ['phone' => '9876543211'],
            [
                'name' => 'Priya Sundaram',
                'email' => 'priya.sundar@yahoo.com',
                'address' => '78 Perur Main Road',
                'city' => 'Coimbatore',
                'state' => 'Tamil Nadu',
                'state_code' => '33',
                'customer_group' => 'retail',
                'loyalty_points' => 180,
                'store_credit_balance' => 0.00,
                'total_purchases_count' => 3,
                'total_spent' => 4500.00,
                'is_active' => true,
            ]
        );

        $c3 = Customer::firstOrCreate(
            ['phone' => '9876543212'],
            [
                'name' => 'Karthik Raja Textiles (Wholesale)',
                'email' => 'karthik.raja@outlook.com',
                'address' => '22 Anna Salai, T Nagar',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'state_code' => '33',
                'gstin' => '33ABCDE1234F1Z9',
                'customer_group' => 'wholesale',
                'loyalty_points' => 1200,
                'store_credit_balance' => 2500.00,
                'total_purchases_count' => 14,
                'total_spent' => 85000.00,
                'is_active' => true,
            ]
        );

        // Promotions
        Promotion::firstOrCreate(
            ['code' => 'DIWALI10'],
            [
                'name' => 'Diwali Festive 10% Discount',
                'type' => 'percentage_discount',
                'discount_value' => 10.00,
                'min_cart_amount' => 1500.00,
                'min_quantity' => 1,
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(60)->toDateString(),
                'is_stackable' => false,
                'is_active' => true,
            ]
        );

        Promotion::firstOrCreate(
            ['code' => 'FLAT500'],
            [
                'name' => 'Mega Shopping Flat ₹500 Off',
                'type' => 'fixed_discount',
                'discount_value' => 500.00,
                'min_cart_amount' => 3000.00,
                'min_quantity' => 2,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'is_stackable' => false,
                'is_active' => true,
            ]
        );

        // Gift Vouchers
        GiftVoucher::firstOrCreate(
            ['voucher_code' => 'GIFT-2026-1000'],
            [
                'initial_amount' => 1000.00,
                'current_balance' => 1000.00,
                'customer_id' => $c1->id,
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'status' => 'active',
            ]
        );

        GiftVoucher::firstOrCreate(
            ['voucher_code' => 'GIFT-2026-500'],
            [
                'initial_amount' => 500.00,
                'current_balance' => 500.00,
                'customer_id' => $c2->id,
                'expiry_date' => now()->addMonths(3)->toDateString(),
                'status' => 'active',
            ]
        );
    }
}
