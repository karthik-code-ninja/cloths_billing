<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->index();
            $table->string('phone', 25)->nullable()->unique();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->default('Salem');
            $table->string('state', 100)->default('Tamil Nadu');
            $table->string('state_code', 5)->default('33');
            $table->string('gstin', 25)->nullable()->index();
            $table->string('customer_group', 30)->default('retail')->index(); // retail, wholesale, vip
            $table->integer('loyalty_points')->default(0);
            $table->decimal('store_credit_balance', 12, 2)->default(0.00);
            $table->integer('total_purchases_count')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->integer('points'); // + or -
            $table->string('type', 40)->index(); // earned_sale, redeemed_sale, reversed_return, manual_adjustment
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 50)->unique();
            $table->string('type', 40)->index(); // percentage_discount, fixed_discount, bogo, quantity_tier
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_cart_amount', 12, 2)->default(0.00);
            $table->integer('min_quantity')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('gift_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_code', 50)->unique();
            $table->decimal('initial_amount', 12, 2);
            $table->decimal('current_balance', 12, 2);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('expiry_date')->nullable();
            $table->string('status', 30)->default('active')->index(); // active, redeemed, expired, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_vouchers');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('customers');
    }
};
