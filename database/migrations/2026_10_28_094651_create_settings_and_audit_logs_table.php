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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->string('type', 30)->default('string');
            $table->string('group', 50)->default('general')->index();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        Schema::create('manager_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users');
            $table->foreignId('cashier_id')->constrained('users');
            $table->string('action_type', 50)->index(); // discount, refund, price_override, no_receipt_return, negative_stock
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('threshold_value', 12, 2)->nullable();
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('approved')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_overrides');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('app_settings');
    }
};
