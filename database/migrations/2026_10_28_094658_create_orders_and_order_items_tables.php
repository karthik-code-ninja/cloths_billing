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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->string('order_type', 30)->default('sale')->index(); // sale, quotation, estimate
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
            $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('completed')->index(); // completed, held, cancelled
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('item_discount_total', 12, 2)->default(0.00);
            $table->decimal('bill_discount_total', 12, 2)->default(0.00);
            $table->decimal('total_discount', 12, 2)->default(0.00);
            $table->decimal('taxable_value', 12, 2);
            $table->decimal('tax_total', 12, 2);
            $table->decimal('cgst_total', 12, 2)->default(0.00);
            $table->decimal('sgst_total', 12, 2)->default(0.00);
            $table->decimal('igst_total', 12, 2)->default(0.00);
            $table->decimal('round_off_amount', 8, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('balance_amount', 12, 2)->default(0.00);
            $table->decimal('change_amount', 12, 2)->default(0.00);
            $table->boolean('is_credit_sale')->default(false);
            $table->json('customer_snapshot')->nullable();
            $table->json('tax_breakdown')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('invoice_date');
            $table->timestamps();

            $table->index(['branch_id', 'invoice_date']);
            $table->index(['customer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->string('product_name', 150);
            $table->json('variant_details'); // size, color, sku, barcode
            $table->string('hsn_code', 20)->default('6203');
            $table->integer('quantity');
            $table->decimal('unit_selling_price', 12, 2);
            $table->decimal('mrp', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('item_discount_amount', 12, 2)->default(0.00);
            $table->decimal('item_discount_percent', 5, 2)->default(0.00);
            $table->decimal('allocated_bill_discount', 12, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(5.00);
            $table->boolean('is_tax_inclusive')->default(true);
            $table->decimal('taxable_amount', 12, 2);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_tax', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->integer('returned_quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();
            $table->string('payment_method', 30)->index(); // cash, upi, card, bank_transfer, store_credit, gift_voucher
            $table->decimal('amount', 12, 2);
            $table->string('reference_number', 100)->nullable();
            $table->boolean('is_verified')->default(false); // only true when real payment gateway confirms
            $table->timestamp('received_at');
            $table->timestamps();
        });

        Schema::create('held_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('hold_label', 100);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->json('cart_data');
            $table->timestamp('held_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('held_orders');
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
