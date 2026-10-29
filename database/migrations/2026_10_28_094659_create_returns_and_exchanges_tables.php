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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            $table->foreignId('original_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('exchange_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users');
            $table->decimal('subtotal_returned', 12, 2);
            $table->decimal('tax_returned', 12, 2);
            $table->decimal('total_refund_amount', 12, 2);
            $table->string('refund_method', 30); // cash, store_credit, original_method, exchange_offset
            $table->string('reason', 150);
            $table->string('status', 30)->default('completed')->index();
            $table->boolean('is_no_receipt')->default(false);
            $table->timestamp('return_date');
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('original_order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity');
            $table->decimal('unit_refund_price', 12, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('tax_refund_amount', 12, 2);
            $table->decimal('line_refund_total', 12, 2);
            $table->string('condition', 30)->default('resellable')->index(); // resellable, damaged, defect
            $table->foreignId('routed_location_id')->constrained('stock_locations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
    }
};
