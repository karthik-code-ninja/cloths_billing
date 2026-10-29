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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_total', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->string('status', 30)->default('ordered')->index(); // draft, ordered, partial_received, received, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(5.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 50)->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('stock_location_id')->constrained('stock_locations');
            $table->string('supplier_invoice_number', 50)->nullable()->index();
            $table->date('invoice_date')->nullable();
            $table->date('received_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->string('payment_status', 30)->default('unpaid')->index(); // unpaid, partial, paid
            $table->foreignId('user_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('goods_received_note_id')->nullable()->constrained('goods_received_notes')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('bank_transfer'); // cash, bank_transfer, upi, cheque
            $table->string('reference_number', 100)->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('goods_received_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
