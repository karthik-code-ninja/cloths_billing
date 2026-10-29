<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class ProductImportService
{
    /**
     * Import products from an XLSX, CSV, or JSON file.
     *
     * @return array{
     *     success: bool,
     *     total_rows: int,
     *     imported_products: int,
     *     imported_variants: int,
     *     total_stock_units: int,
     *     total_cost_value: float,
     *     categories_created: int,
     *     brands_created: int,
     *     errors: array<string>,
     *     message: string
     * }
     */
    public function importFromFile(string $filePath, ?int $userId = null, ?int $branchId = null): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rows = [];

        if ($extension === 'xlsx') {
            $rows = $this->parseXlsx($filePath);
        } elseif (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($filePath);
        } elseif ($extension === 'json') {
            $rows = json_decode(file_get_contents($filePath), true) ?: [];
        } else {
            throw new Exception("Unsupported file type: .{$extension}. Please provide .xlsx or .csv");
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'total_rows' => 0,
                'imported_products' => 0,
                'imported_variants' => 0,
                'total_stock_units' => 0,
                'total_cost_value' => 0.0,
                'categories_created' => 0,
                'brands_created' => 0,
                'errors' => ['File contains no readable product rows.'],
                'message' => 'No data rows found in file.',
            ];
        }

        $adminUser = $userId ? User::find($userId) : User::first();
        $targetUserId = $adminUser ? $adminUser->id : 1;
        $targetBranchId = $branchId ?: ($adminUser->branch_id ?? 1);

        $location = StockLocation::firstOrCreate(
            ['branch_id' => $targetBranchId, 'type' => 'retail'],
            ['name' => 'Main Showroom Floor', 'code' => 'RETAIL-01', 'is_active' => true]
        );

        $importedProducts = 0;
        $importedVariants = 0;
        $totalStockUnits = 0;
        $totalCostValue = 0.0;
        $categoriesCreated = 0;
        $brandsCreated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                try {
                    $rowNum = $index + 2; // account for header
                    $itemName = trim($row['name'] ?? $row['item_name'] ?? '');
                    $itemCode = trim((string) ($row['code'] ?? $row['item_code'] ?? $row['barcode'] ?? ''));
                    $rawCategory = trim($row['category'] ?? '');
                    $salePrice = (float) ($row['sale_price'] ?? $row['price'] ?? 0.0);
                    $purchaseCost = (float) ($row['purchase_price'] ?? $row['purchase_cost'] ?? $row['cost'] ?? 0.0);
                    $stockQty = (int) round((float) ($row['stock_qty'] ?? $row['current_stock_quantity'] ?? $row['stock'] ?? 0));
                    $minQty = (int) round((float) ($row['min_qty'] ?? $row['minimum_stock_quantity'] ?? 5));

                    if (empty($itemName) && empty($itemCode)) {
                        continue;
                    }

                    if (empty($itemName)) {
                        $itemName = 'Product '.$itemCode;
                    }

                    // 1. Resolve Category
                    $catResult = $this->resolveCategory($rawCategory, $itemName);
                    $category = $catResult['category'];
                    if ($catResult['created']) {
                        $categoriesCreated++;
                    }

                    // 2. Resolve Brand
                    $brandResult = $this->resolveBrand($itemName);
                    $brand = $brandResult['brand'];
                    if ($brandResult['created']) {
                        $brandsCreated++;
                    }

                    // 3. Determine Style / Article Code
                    $articleCode = $this->generateArticleCode($itemName, $itemCode);

                    // 4. Resolve Fabric, Fit, and Gender attributes
                    $attributes = $this->detectGarmentAttributes($itemName, $rawCategory);

                    // 5. Create or Find Product
                    $product = Product::firstOrCreate(
                        ['article_code' => $articleCode],
                        [
                            'name' => $itemName,
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                            'hsn_code' => $category->hsn_code ?? '6203',
                            'tax_rate' => $category->default_tax_rate,
                            'is_tax_inclusive' => true,
                            'gender' => $attributes['gender'],
                            'fit' => $attributes['fit'],
                            'fabric' => $attributes['fabric'],
                            'pattern' => $attributes['pattern'],
                            'min_selling_price' => $purchaseCost > 0 ? $purchaseCost * 1.10 : $salePrice * 0.85,
                            'is_active' => true,
                        ]
                    );

                    // Update product name/category if existed
                    if (! $product->wasRecentlyCreated) {
                        $product->update([
                            'name' => $itemName,
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                        ]);
                    } else {
                        $importedProducts++;
                    }

                    // 6. Calculate MRP (retail standard: sale price + markup rounded to .99 or 9)
                    $mrp = $salePrice > 0 ? (ceil(($salePrice * 1.35) / 10) * 10) - 1 : 0.0;
                    if ($mrp < $salePrice) {
                        $mrp = $salePrice;
                    }

                    // 7. Barcode & SKU
                    $barcode = ! empty($itemCode) ? $itemCode : app(BarcodeService::class)->generateUniqueEan13();
                    $sku = strtoupper(Str::slug($articleCode.'-'.($attributes['size'] ?? 'FS').'-'.rand(10, 99)));

                    // 8. Create or Update Variant
                    $variant = ProductVariant::where('barcode', $barcode)->first();

                    if (! $variant) {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'size' => $attributes['size'],
                            'color' => $attributes['color'],
                            'sku' => $sku,
                            'barcode' => $barcode,
                            'purchase_cost' => $purchaseCost,
                            'selling_price' => $salePrice,
                            'mrp' => $mrp,
                            'wholesale_price' => round($salePrice * 0.85, 2),
                            'low_stock_threshold' => max(2, $minQty),
                            'reorder_quantity' => 15,
                            'is_active' => true,
                        ]);
                        $importedVariants++;
                    } else {
                        $variant->update([
                            'product_id' => $product->id,
                            'purchase_cost' => $purchaseCost,
                            'selling_price' => $salePrice,
                            'mrp' => $mrp,
                            'wholesale_price' => round($salePrice * 0.85, 2),
                        ]);
                    }

                    // 9. Stock Allocation & Opening Movement Record
                    if ($stockQty > 0) {
                        $invStock = InventoryStock::firstOrCreate(
                            ['product_variant_id' => $variant->id, 'stock_location_id' => $location->id],
                            ['quantity' => 0]
                        );

                        // If new stock or replacing
                        $prevQty = $invStock->quantity;
                        $invStock->quantity = $stockQty;
                        $invStock->save();

                        // Record auditable opening movement
                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'stock_location_id' => $location->id,
                            'movement_type' => 'opening',
                            'quantity' => $stockQty,
                            'unit_cost' => $purchaseCost,
                            'balance_after' => $stockQty,
                            'remarks' => "Bulk Import Opening Stock (prev: {$prevQty})",
                            'user_id' => $targetUserId,
                        ]);

                        $totalStockUnits += $stockQty;
                        $totalCostValue += ($stockQty * $purchaseCost);
                    }
                } catch (Exception $rowEx) {
                    $errors[] = "Row {$rowNum} ('{$itemName}'): ".$rowEx->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'total_rows' => count($rows),
                'imported_products' => $importedProducts,
                'imported_variants' => $importedVariants,
                'total_stock_units' => $totalStockUnits,
                'total_cost_value' => round($totalCostValue, 2),
                'categories_created' => $categoriesCreated,
                'brands_created' => $brandsCreated,
                'errors' => $errors,
                'message' => "Successfully imported {$importedProducts} products and {$importedVariants} variants with {$totalStockUnits} stock units.",
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'total_rows' => count($rows),
                'imported_products' => 0,
                'imported_variants' => 0,
                'total_stock_units' => 0,
                'total_cost_value' => 0.0,
                'categories_created' => 0,
                'brands_created' => 0,
                'errors' => [$e->getMessage()],
                'message' => 'Import failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Parse native XLSX without external libraries using ZipArchive.
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            throw new Exception("Unable to open XLSX file: {$filePath}");
        }

        // Shared strings
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml) {
            $sTree = simplexml_load_string($stringsXml);
            if ($sTree && isset($sTree->si)) {
                foreach ($sTree->si as $si) {
                    $sharedStrings[] = (string) $si->t;
                }
            }
        }

        // Sheet 1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (! $sheetXml) {
            $zip->close();
            throw new Exception('Sheet1 not found in XLSX file');
        }

        $sheetTree = simplexml_load_string($sheetXml);
        $zip->close();

        if (! $sheetTree || ! isset($sheetTree->sheetData->row)) {
            return [];
        }

        $headers = [];
        $items = [];

        foreach ($sheetTree->sheetData->row as $row) {
            $rowNum = (int) $row['r'];

            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                preg_match('/[A-Z]+/', $ref, $matches);
                $col = $matches[0] ?? '';
                $t = (string) $c['t'];
                $val = (string) $c->v;

                if ($t === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }

                $cells[$col] = trim($val);
            }

            if ($rowNum === 1) {
                // Header row
                $headers = $cells;

                continue;
            }

            // Map cells to normalized keys
            // Column map: A: Name, B: Code, C: Category, D: HSN, E: Sale price, F: Purchase price, I: Stock Qty, J: Min Qty
            $items[] = [
                'name' => $cells['A'] ?? '',
                'code' => $cells['B'] ?? '',
                'category' => $cells['C'] ?? '',
                'hsn' => $cells['D'] ?? '',
                'sale_price' => $cells['E'] ?? '0',
                'purchase_price' => $cells['F'] ?? '0',
                'discount_type' => $cells['G'] ?? '',
                'discount' => $cells['H'] ?? '0',
                'stock_qty' => $cells['I'] ?? '0',
                'min_qty' => $cells['J'] ?? '0',
            ];
        }

        return $items;
    }

    /**
     * Parse standard CSV file.
     */
    public function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new Exception("Unable to open CSV file: {$filePath}");
        }

        $header = null;
        $items = [];

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            if (! $header) {
                $header = array_map(function ($h) {
                    return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $h)));
                }, $row);

                continue;
            }

            if (empty(array_filter($row))) {
                continue;
            }

            $mapped = [];
            foreach ($header as $idx => $key) {
                $mapped[$key] = $row[$idx] ?? '';
            }

            $items[] = [
                'name' => $mapped['item_name'] ?? $mapped['name'] ?? $mapped['product_name'] ?? $mapped['title'] ?? '',
                'code' => $mapped['item_code'] ?? $mapped['code'] ?? $mapped['barcode'] ?? $mapped['sku'] ?? '',
                'category' => $mapped['category'] ?? $mapped['category_name'] ?? '',
                'sale_price' => $mapped['sale_price'] ?? $mapped['price'] ?? $mapped['selling_price'] ?? '0',
                'purchase_price' => $mapped['purchase_price'] ?? $mapped['purchase_cost'] ?? $mapped['cost'] ?? '0',
                'stock_qty' => $mapped['current_stock_quantity'] ?? $mapped['stock_qty'] ?? $mapped['stock'] ?? $mapped['quantity'] ?? '0',
                'min_qty' => $mapped['minimum_stock_quantity'] ?? $mapped['min_qty'] ?? '5',
            ];
        }

        fclose($handle);

        return $items;
    }

    /**
     * Resolve and normalize Category with GST and HSN standards.
     *
     * @return array{category: Category, created: bool}
     */
    public function resolveCategory(string $rawCategory, string $productName): array
    {
        $combined = strtolower(trim($rawCategory.' '.$productName));

        // T-Shirts: HSN 6109
        if (preg_match('/\b(t-?shirt|tee|polos?)\b/i', $combined)) {
            $cat = Category::firstOrCreate(
                ['slug' => 't-shirts'],
                [
                    'name' => 'T-Shirts',
                    'code' => 'TSHIRT',
                    'hsn_code' => '6109',
                    'default_tax_rate' => 5.00,
                    'is_active' => true,
                ]
            );

            return ['category' => $cat, 'created' => $cat->wasRecentlyCreated];
        }

        // Pants / Trousers / Jeans: HSN 6203
        if (preg_match('/\b(pant|pand|jeans|denim|trouser|momfit|baggy|ankle)\b/i', $combined)) {
            $cat = Category::firstOrCreate(
                ['slug' => 'pants-and-trousers'],
                [
                    'name' => 'Pants & Trousers',
                    'code' => 'PANTS',
                    'hsn_code' => '6203',
                    'default_tax_rate' => 5.00,
                    'is_active' => true,
                ]
            );

            return ['category' => $cat, 'created' => $cat->wasRecentlyCreated];
        }

        // Shirts: HSN 6205
        if (preg_match('/\b(shirt|oxford|drill|checks|flannel)\b/i', $combined)) {
            $cat = Category::firstOrCreate(
                ['slug' => 'shirts'],
                [
                    'name' => 'Shirts',
                    'code' => 'SHIRTS',
                    'hsn_code' => '6205',
                    'default_tax_rate' => 5.00,
                    'is_active' => true,
                ]
            );

            return ['category' => $cat, 'created' => $cat->wasRecentlyCreated];
        }

        // Generic fallback or raw category
        $catName = ! empty($rawCategory) ? ucwords(strtolower($rawCategory)) : 'Men\'s Wear';
        $slug = Str::slug($catName);

        $cat = Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $catName,
                'code' => strtoupper(substr(str_replace('-', '', $slug), 0, 6)),
                'hsn_code' => '6203',
                'default_tax_rate' => 5.00,
                'is_active' => true,
            ]
        );

        return ['category' => $cat, 'created' => $cat->wasRecentlyCreated];
    }

    /**
     * Resolve Brand from Product Name.
     *
     * @return array{brand: Brand, created: bool}
     */
    public function resolveBrand(string $productName): array
    {
        $nameUpper = strtoupper($productName);

        $brandMap = [
            'NIKE' => ['name' => 'Nike', 'code' => 'NIKE'],
            'BALENCIAGE' => ['name' => 'Balenciaga', 'code' => 'BALEN'],
            'CHARLLY' => ['name' => 'Charly', 'code' => 'CHARLY'],
            'CHARLY' => ['name' => 'Charly', 'code' => 'CHARLY'],
            'ARMANI' => ['name' => 'Armani', 'code' => 'ARMANI'],
            'JEN' => ['name' => 'Jen Fashion', 'code' => 'JEN'],
            'SKP' => ['name' => 'SKP Denim', 'code' => 'SKP'],
            'WAH' => ['name' => 'Wah Apparel', 'code' => 'WAH'],
            'RMG' => ['name' => 'RMG Classics', 'code' => 'RMG'],
            'RFQ' => ['name' => 'RFQ Studio', 'code' => 'RFQ'],
            'NZF' => ['name' => 'NZF Checks', 'code' => 'NZF'],
            'ANS' => ['name' => 'ANS Casuals', 'code' => 'ANS'],
            'ARP' => ['name' => 'ARP Oxford', 'code' => 'ARP'],
            'GRV' => ['name' => 'GRV Korean', 'code' => 'GRV'],
            'KK' => ['name' => 'KK Baggy', 'code' => 'KK'],
            'AK' => ['name' => 'AK Trends', 'code' => 'AK'],
            'WD' => ['name' => 'WD Denim', 'code' => 'WD'],
            'OBA' => ['name' => 'Oba Fits', 'code' => 'OBA'],
            'HM' => ['name' => 'HM Jeans', 'code' => 'HM'],
            'V1S' => ['name' => 'V1S Linen', 'code' => 'V1S'],
            'AFFLICTION' => ['name' => 'Affliction', 'code' => 'AFFLIC'],
            'DIGIKAL' => ['name' => 'Digikal', 'code' => 'DIGI'],
            'FREEDOM' => ['name' => 'Freedom Casuals', 'code' => 'FREED'],
            'STAG' => ['name' => 'U Stag', 'code' => 'USTAG'],
        ];

        foreach ($brandMap as $keyword => $info) {
            if (str_contains($nameUpper, $keyword)) {
                $brand = Brand::firstOrCreate(
                    ['code' => $info['code']],
                    ['name' => $info['name'], 'is_active' => true]
                );

                return ['brand' => $brand, 'created' => $brand->wasRecentlyCreated];
            }
        }

        // Default brand: NextDNA Originals
        $defaultBrand = Brand::firstOrCreate(
            ['code' => 'NDNA'],
            ['name' => 'NextDNA Originals', 'is_active' => true]
        );

        return ['brand' => $defaultBrand, 'created' => $defaultBrand->wasRecentlyCreated];
    }

    /**
     * Generate concise article code from product name.
     */
    protected function generateArticleCode(string $name, string $itemCode): string
    {
        // If name starts with alphanumeric code like "1890", "255", "303", "RFQ16", "RMG31"
        if (preg_match('/^([A-Za-z0-9]+)/', $name, $matches)) {
            return strtoupper($matches[1]);
        }

        // Fallback to last 6 digits of barcode
        if (strlen($itemCode) >= 6) {
            return 'ART-'.substr($itemCode, -6);
        }

        return 'ART-'.Str::random(6);
    }

    /**
     * Detect fit, fabric, pattern, and gender from garment title.
     *
     * @return array{fit: string, fabric: string, pattern: string, gender: string, size: string, color: string}
     */
    protected function detectGarmentAttributes(string $name, string $category): array
    {
        $upper = strtoupper($name);

        // Fit
        $fit = 'Regular Fit';
        if (str_contains($upper, 'BAGGY')) {
            $fit = 'Baggy Fit';
        } elseif (str_contains($upper, 'SLIM')) {
            $fit = 'Slim Fit';
        } elseif (str_contains($upper, 'MOMFIT') || str_contains($upper, 'MOM FIT')) {
            $fit = 'Mom Fit';
        } elseif (str_contains($upper, 'STRAIGHT')) {
            $fit = 'Straight Fit';
        } elseif (str_contains($upper, 'BOXY')) {
            $fit = 'Boxy Fit';
        } elseif (str_contains($upper, 'ANKLE')) {
            $fit = 'Ankle Length';
        }

        // Fabric
        $fabric = '100% Cotton';
        if (str_contains($upper, 'LINEN')) {
            $fabric = 'Pure Linen';
        } elseif (str_contains($upper, 'DENIM') || str_contains($upper, 'JEANS')) {
            $fabric = 'Stretch Denim';
        } elseif (str_contains($upper, 'OXFORD')) {
            $fabric = 'Oxford Cotton';
        } elseif (str_contains($upper, 'FLANNEL')) {
            $fabric = 'Brushed Flannel';
        } elseif (str_contains($upper, 'LYOCELL')) {
            $fabric = 'Tencel Lyocell';
        } elseif (str_contains($upper, 'CODROUY') || str_contains($upper, 'CORDUROY')) {
            $fabric = 'Corduroy';
        }

        // Pattern
        $pattern = 'Solid';
        if (str_contains($upper, 'CHECKS')) {
            $pattern = 'Checks';
        } elseif (str_contains($upper, 'STRIPES') || str_contains($upper, 'STRIPET')) {
            $pattern = 'Stripes';
        } elseif (str_contains($upper, 'PRINT')) {
            $pattern = 'Graphic Print';
        } elseif (str_contains($upper, 'ACID')) {
            $pattern = 'Acid Wash';
        } elseif (str_contains($upper, 'WHISKER')) {
            $pattern = 'Whisker Wash';
        }

        // Size & Color defaults
        $size = '32';
        if (stripos($category, 'shirt') !== false || stripos($category, 't-shirt') !== false) {
            $size = 'L';
        }

        return [
            'fit' => $fit,
            'fabric' => $fabric,
            'pattern' => $pattern,
            'gender' => 'Men',
            'size' => $size,
            'color' => 'Standard',
        ];
    }
}
