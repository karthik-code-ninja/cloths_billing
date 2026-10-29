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
        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 30);
            $table->string('type', 30)->default('retail')->index(); // retail, warehouse, damaged
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['branch_id', 'code']);
        });

        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('stock_location_id')->constrained('stock_locations')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->timestamps();

            $table->unique(['product_variant_id', 'stock_location_id']);
            $table->index(['product_variant_id', 'quantity']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('stock_location_id')->constrained('stock_locations');
            $table->string('movement_type', 40)->index(); // opening, purchase, sale, return, exchange_in, exchange_out, adjustment, transfer_in, transfer_out, damaged
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('quantity'); // delta: + or -
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->integer('balance_after');
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->index(['product_variant_id', 'created_at']);
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            $table->foreignId('stock_location_id')->constrained('stock_locations');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users');
            $table->string('reason', 50)->index(); // physical_count_discrepancy, damage, theft, expired, other
            $table->string('status', 30)->default('approved')->index(); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('system_quantity');
            $table->integer('counted_quantity');
            $table->integer('difference_quantity');
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('from_location_id')->constrained('stock_locations');
            $table->foreignId('to_location_id')->constrained('stock_locations');
            $table->string('status', 30)->default('draft')->index(); // draft, dispatched, in_transit, received, cancelled
            $table->timestamp('dispatch_date')->nullable();
            $table->timestamp('receipt_date')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('received_by_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity_sent');
            $table->integer('quantity_received')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('stock_locations');
    }
};
