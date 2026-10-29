<?php

namespace Tests\Feature;

use App\Livewire\PosBilling;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Counter;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PosDiscountHandlingTest extends TestCase
{
    protected User $user;

    protected Branch $branch;

    protected Counter $counter;

    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::first() ?? Branch::create([
            'name' => 'Main Showroom',
            'code' => 'SHOW1',
            'is_main' => true,
            'state_code' => '33',
        ]);

        $this->counter = Counter::first() ?? Counter::create([
            'branch_id' => $this->branch->id,
            'name' => 'Counter 1',
            'code' => 'C1',
        ]);

        $this->user = User::where('role', 'admin')->first() ?? User::create([
            'name' => 'Admin User',
            'email' => 'admin_test_'.Str::random(5).'@nextdna.com',
            'password' => bcrypt('Nextdna@123'),
            'role' => 'admin',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'casual-shirts'],
            ['name' => 'Casual Shirts', 'hsn_code' => '6205', 'default_tax_rate' => 5.00]
        );

        $product = Product::firstOrCreate(
            ['article_code' => 'TEST-DISC-01'],
            [
                'category_id' => $category->id,
                'name' => 'Cotton Casual Shirt',
                'hsn_code' => '6205',
                'tax_rate' => 5.0,
                'is_tax_inclusive' => true,
                'is_active' => true,
            ]
        );

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DISC-SHIRT-'.Str::random(4),
            'barcode' => '890'.rand(1000000000, 9999999999),
            'size' => 'M',
            'color' => 'Navy Blue',
            'selling_price' => 1000.0,
            'mrp' => 1200.0,
            'stock_on_hand' => 10,
        ]);
    }

    public function test_discount_accepts_empty_string_numeric_and_switches_between_percent_and_amount(): void
    {
        $this->actingAs($this->user);

        Livewire::test(PosBilling::class)
            ->call('addVariantToCart', $this->variant)
            // 1. Enter bill discount amount (e.g. ₹100)
            ->set('billDiscountAmount', '100')
            ->assertSet('billDiscountAmount', '100')
            ->assertSet('billDiscountPercent', 0)
            ->assertSee('Applied Bill Discount')
            // 2. Clear discount amount (empty string) - must NOT throw PropertyNotFoundException or TypeError
            ->set('billDiscountAmount', '')
            ->assertOk()
            // 3. Enter bill discount percent (e.g. 10%)
            ->set('billDiscountPercent', '10')
            ->assertSet('billDiscountPercent', '10')
            ->assertSet('billDiscountAmount', 0)
            ->assertSee('Applied Bill Discount')
            // 4. Clear percent (empty string)
            ->set('billDiscountPercent', '')
            ->assertOk();
    }
}
