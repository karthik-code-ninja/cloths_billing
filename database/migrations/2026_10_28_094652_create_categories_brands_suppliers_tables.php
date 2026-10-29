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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('code', 30)->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(5.00);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('company_name', 150)->nullable();
            $table->string('gstin', 25)->nullable()->index();
            $table->string('phone', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->default('Tamil Nadu');
            $table->string('state_code', 5)->default('33');
            $table->integer('payment_terms_days')->default(30);
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->decimal('current_balance', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('size_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->json('sizes'); // e.g. ["XS", "S", "M", "L", "XL", "XXL"]
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_groups');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
