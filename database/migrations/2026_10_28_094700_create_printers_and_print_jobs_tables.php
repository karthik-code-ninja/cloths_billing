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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
            $table->string('name', 100);
            $table->string('connection_type', 30)->default('browser')->index(); // browser, qz_tray, lan_escpos, mock
            $table->string('device_identifier', 150)->nullable(); // OS/QZ Tray printer name
            $table->string('ip_address', 45)->nullable();
            $table->integer('port')->default(9100);
            $table->string('capability_profile', 50)->default('generic_escpos'); // epson_standard, xprinter_80, xprinter_58, generic_escpos
            $table->integer('paper_width_mm')->default(80); // 58, 80
            $table->integer('columns')->default(42);
            $table->integer('dpi')->default(203);
            $table->integer('margins_mm')->default(0);
            $table->integer('feed_lines')->default(3);
            $table->boolean('has_cutter')->default(true);
            $table->boolean('has_cash_drawer')->default(true);
            $table->boolean('is_default_receipt')->default(false)->index();
            $table->boolean('is_default_label')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')->constrained('printers');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('job_type', 40)->index(); // receipt_58, receipt_80, invoice_a4, gift_receipt, return_receipt, exchange_receipt, barcode_label
            $table->string('payload_version', 10)->default('v1');
            $table->longText('payload')->nullable();
            $table->string('status', 30)->default('pending')->index(); // pending, submitted, success, failed
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->boolean('is_reprint')->default(false);
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('printers');
    }
};
