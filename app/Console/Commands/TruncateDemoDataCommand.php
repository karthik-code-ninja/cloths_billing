<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:truncate-demo-data {--with-masters : Also truncate categories, brands, suppliers, seasons, and size groups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate demo products, inventory stocks, sales orders, returns, cash shifts, and customers while preserving user accounts and store settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('Preparing to truncate demo data from retail database...');

        $tablesToTruncate = [
            // Sales, Invoices & Orders
            'order_items',
            'order_payments',
            'orders',
            'return_items',
            'returns',
            'held_orders',
            'manager_overrides',

            // Cash Drawer & Shifts
            'cash_transactions',
            'expenses',
            'cash_shifts',

            // Inventory & Stock Ledgers
            'stock_movements',
            'inventory_stocks',
            'stock_adjustment_items',
            'stock_adjustments',
            'stock_transfer_items',
            'stock_transfers',

            // Purchases & Shipments
            'purchase_order_items',
            'purchase_orders',
            'goods_received_notes',
            'supplier_payments',

            // Customers & Vouchers
            'loyalty_transactions',
            'gift_vouchers',
            'customers',

            // Products, Variants & Bundles
            'product_bundles',
            'product_variants',
            'products',

            // Audit & Print Logs
            'print_jobs',
            'audit_logs',
        ];

        if ($this->option('with-masters')) {
            $tablesToTruncate = array_merge($tablesToTruncate, [
                'categories',
                'brands',
                'suppliers',
                'seasons',
                'size_groups',
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        $truncatedCount = 0;
        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line(" <fg=green>✓</> Truncated table: <fg=cyan>{$table}</> ({$count} demo rows removed)");
                $truncatedCount++;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->newLine();
        $this->info("Successfully truncated {$truncatedCount} tables!");
        $this->info('User accounts preserved: '.DB::table('users')->count().' active staff accounts intact.');
        $this->info('Store infrastructure preserved: '.DB::table('branches')->count().' branch(es), '.DB::table('printers')->count().' printer(s).');

        return self::SUCCESS;
    }
}
