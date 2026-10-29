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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('cashier')->after('email')->index();
            $table->string('phone', 20)->nullable()->after('role');
            $table->unsignedBigInteger('branch_id')->nullable()->after('phone')->index();
            $table->boolean('is_active')->default(true)->after('branch_id')->index();
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->decimal('max_discount_percentage', 5, 2)->default(10.00)->after('is_active');
            $table->boolean('can_override_price')->default(false)->after('max_discount_percentage');
            $table->boolean('can_give_refund')->default(false)->after('can_override_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'branch_id',
                'is_active',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'max_discount_percentage',
                'can_override_price',
                'can_give_refund',
            ]);
        });
    }
};
