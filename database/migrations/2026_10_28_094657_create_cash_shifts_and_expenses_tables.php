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
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('counter_id')->constrained('counters');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_float', 12, 2)->default(0.00);
            $table->decimal('expected_closing_cash', 12, 2)->default(0.00);
            $table->decimal('counted_closing_cash', 12, 2)->nullable();
            $table->decimal('variance', 12, 2)->nullable();
            $table->string('status', 30)->default('open')->index(); // open, closed
            $table->text('notes')->nullable();
            $table->foreignId('closed_by_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_shift_id')->constrained('cash_shifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type', 30)->index(); // cash_in, cash_out, expense
            $table->decimal('amount', 12, 2);
            $table->string('reason', 150);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();
            $table->string('category', 50)->index(); // tea_refreshments, cleaning, repairs, utilities, packaging, travel, misc
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->text('description');
            $table->string('receipt_number', 50)->nullable();
            $table->date('expense_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cash_shifts');
    }
};
