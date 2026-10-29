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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->default('Salem');
            $table->string('state', 100)->default('Tamil Nadu');
            $table->string('state_code', 5)->default('33');
            $table->string('pincode', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('gstin', 25)->nullable()->index();
            $table->boolean('is_main')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 30);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['branch_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counters');
        Schema::dropIfExists('branches');
    }
};
