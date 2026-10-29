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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('seasons')->nullOnDelete();
            $table->string('name', 150)->index();
            $table->string('article_code', 50)->unique();
            $table->string('hsn_code', 20)->default('6203')->index();
            $table->decimal('tax_rate', 5, 2)->default(5.00);
            $table->boolean('is_tax_inclusive')->default(true);
            $table->text('description')->nullable();
            $table->string('gender', 30)->default('Unisex')->index();
            $table->string('fit', 50)->nullable();
            $table->string('fabric', 100)->nullable();
            $table->string('pattern', 100)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->decimal('min_selling_price', 12, 2)->default(0.00);
            $table->boolean('is_bundle')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('size', 30)->index();
            $table->string('color', 50)->index();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->unique();
            $table->decimal('purchase_cost', 12, 2)->default(0.00);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->decimal('mrp', 12, 2)->default(0.00);
            $table->decimal('wholesale_price', 12, 2)->default(0.00);
            $table->integer('low_stock_threshold')->default(5);
            $table->integer('reorder_quantity')->default(10);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['product_id', 'size', 'color']);
        });

        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
