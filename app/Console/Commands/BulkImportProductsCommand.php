<?php

namespace App\Console\Commands;

use App\Services\ProductImportService;
use Illuminate\Console\Command;

class BulkImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:import-products {file? : Absolute path to XLSX or CSV product file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import apparel products from Vyapar Excel or CSV with automatic HSN, Category, Brand, and Stock initialization';

    /**
     * Execute the console command.
     */
    public function handle(ProductImportService $importService): int
    {
        $filePath = $this->argument('file');

        if (! $filePath) {
            $defaultPath = '/home/ubuntu/.gemini/antigravity/brain/a3baf179-4fb9-436d-a63c-995810d52cff/.user_uploaded/media_1793199416215_16d6fd2a.xlsx';
            if (file_exists($defaultPath)) {
                $filePath = $defaultPath;
            } else {
                $this->error('No file path provided and default Vyapar XLSX not found.');

                return self::FAILURE;
            }
        }

        $this->info("Starting bulk product import from: {$filePath}");

        try {
            $result = $importService->importFromFile($filePath);

            if (! $result['success']) {
                $this->error('Import failed: '.$result['message']);
                foreach ($result['errors'] as $err) {
                    $this->line(" - {$err}");
                }

                return self::FAILURE;
            }

            $this->newLine();
            $this->info('====================================================');
            $this->info('  NEXTDNA CLOTHING POS - BULK IMPORT COMPLETED');
            $this->info('====================================================');

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Rows Processed', $result['total_rows']],
                    ['New/Updated Products', $result['imported_products']],
                    ['New/Updated Variants', $result['imported_variants']],
                    ['Total Stock Units Added', $result['total_stock_units']],
                    ['Total Cost Valuation (INR)', '₹'.number_format($result['total_cost_value'], 2)],
                    ['New Categories Created', $result['categories_created']],
                    ['New Brands Created', $result['brands_created']],
                ]
            );

            if (! empty($result['errors'])) {
                $this->warn('Warnings / Row Errors ('.count($result['errors']).'):');
                foreach (array_slice($result['errors'], 0, 5) as $err) {
                    $this->line(" - {$err}");
                }
            }

            $this->newLine();
            $this->info('All products are now live in POS, Product Lookup (F3), and Barcode Label Studio!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An exception occurred during import: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
