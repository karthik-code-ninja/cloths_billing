<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\Printer;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('code', 'SLM01')->first() ?? Branch::first();
        if (! $branch) {
            return;
        }

        $c1 = Counter::where('branch_id', $branch->id)->where('code', 'CNT-01')->first();
        $c2 = Counter::where('branch_id', $branch->id)->where('code', 'CNT-02')->first();

        // 1. Counter 1 Thermal 80mm Printer (Browser printing)
        Printer::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Counter 1 - Thermal 80mm'],
            [
                'counter_id' => $c1?->id,
                'connection_type' => 'browser',
                'device_identifier' => 'EPSON_TM_T82III',
                'capability_profile' => 'epson_standard',
                'paper_width_mm' => 80,
                'columns' => 42,
                'dpi' => 203,
                'margins_mm' => 0,
                'feed_lines' => 3,
                'has_cutter' => true,
                'has_cash_drawer' => true,
                'is_default_receipt' => true,
                'is_default_label' => false,
                'is_active' => true,
            ]
        );

        // 2. Counter 2 Compact 58mm Printer
        Printer::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Counter 2 - Compact 58mm'],
            [
                'counter_id' => $c2?->id,
                'connection_type' => 'browser',
                'device_identifier' => 'XP_58_SERIES',
                'capability_profile' => 'xprinter_58',
                'paper_width_mm' => 58,
                'columns' => 32,
                'dpi' => 203,
                'margins_mm' => 0,
                'feed_lines' => 3,
                'has_cutter' => false,
                'has_cash_drawer' => true,
                'is_default_receipt' => false,
                'is_default_label' => false,
                'is_active' => true,
            ]
        );

        // 3. Warehouse Barcode Label Printer
        Printer::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Warehouse Barcode Hangtag Printer'],
            [
                'counter_id' => null,
                'connection_type' => 'browser',
                'device_identifier' => 'TSC_TE244_LABEL',
                'capability_profile' => 'generic_escpos',
                'paper_width_mm' => 58,
                'columns' => 32,
                'dpi' => 203,
                'margins_mm' => 2,
                'feed_lines' => 1,
                'has_cutter' => true,
                'has_cash_drawer' => false,
                'is_default_receipt' => false,
                'is_default_label' => true,
                'is_active' => true,
            ]
        );

        // 4. TVS Electronics RP 3200 LITE (Hardware Receipt Printer)
        Printer::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'TVS RP 3200 LITE (Counter 1)'],
            [
                'counter_id' => $c1?->id,
                'connection_type' => 'browser',
                'device_identifier' => 'TVS_RP3200_LITE',
                'capability_profile' => 'tvs_rp3200',
                'paper_width_mm' => 80,
                'columns' => 42,
                'dpi' => 203,
                'margins_mm' => 0,
                'feed_lines' => 3,
                'has_cutter' => true,
                'has_cash_drawer' => true,
                'is_default_receipt' => true,
                'is_default_label' => false,
                'is_active' => true,
            ]
        );

        // 5. TVS Electronics LP 46 Dlite (Barcode & Hangtag Printer)
        Printer::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'TVS LP 46 Dlite (Barcode & Hangtags)'],
            [
                'counter_id' => null,
                'connection_type' => 'browser',
                'device_identifier' => 'TVS_LP46_DLITE',
                'capability_profile' => 'tvs_lp46',
                'paper_width_mm' => 50,
                'columns' => 32,
                'dpi' => 203,
                'margins_mm' => 1,
                'feed_lines' => 1,
                'has_cutter' => false,
                'has_cash_drawer' => false,
                'is_default_receipt' => false,
                'is_default_label' => true,
                'is_active' => true,
            ]
        );

        // 6. Automated Testing Mock Simulator Printer
        Printer::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Virtual Mock Simulator Printer'],
            [
                'counter_id' => null,
                'connection_type' => 'mock',
                'device_identifier' => 'VIRTUAL_SIMULATOR',
                'capability_profile' => 'generic_escpos',
                'paper_width_mm' => 80,
                'columns' => 42,
                'dpi' => 203,
                'has_cutter' => true,
                'has_cash_drawer' => true,
                'is_default_receipt' => false,
                'is_default_label' => false,
                'is_active' => true,
            ]
        );
    }
}
