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
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropForeign(['printer_id']);
            $table->foreign('printer_id')
                ->references('id')
                ->on('printers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropForeign(['printer_id']);
            $table->foreign('printer_id')
                ->references('id')
                ->on('printers');
        });
    }
};
