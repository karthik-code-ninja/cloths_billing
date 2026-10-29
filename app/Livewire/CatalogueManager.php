<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Season;
use App\Models\SizeGroup;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Services\BarcodeService;
use App\Services\PrintService;
use App\Services\ProductImportService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CatalogueManager extends Component
{
    use WithFileUploads, WithPagination;

    // Filters
    public string $search = '';

    public string $selectedCategory = '';

    public string $selectedBrand = '';

    public string $stockStatus = 'all'; // all, in_stock, low_stock, out_of_stock

    public array $expandedProducts = [];

    // Create / Edit Product Form Modal
    public bool $showProductModal = false;

    public ?int $editingProductId = null;

    public string $name = '';

    public string $article_code = '';

    public int $category_id = 0;

    public ?int $brand_id = null;

    public ?int $season_id = null;

    public string $hsn_code = '6203';

    public float $tax_rate = 5.00;

    public bool $is_tax_inclusive = true;

    public string $gender = 'Men';

    public string $fit = 'Regular Fit';

    public string $fabric = '100% Cotton';

    public string $pattern = 'Solid';

    public float $min_selling_price = 0.0;

    public string $description = '';

    // Size-and-Colour Matrix Variant Creation
    public bool $showMatrixModal = false;

    public ?Product $matrixProduct = null;

    public array $matrixSizes = ['S', 'M', 'L', 'XL'];

    public array $matrixColors = ['Navy Blue', 'Black', 'Crisp White'];

    public string $customSizeInput = '';

    public string $customColorInput = '';

    public ?int $selectedSizeGroupId = null;

    public float $matrixCost = 450.00;

    public float $matrixPrice = 899.00;

    public float $matrixMrp = 1299.00;

    public int $matrixInitialStock = 20;

    // Barcode Label Print Modal
    public bool $showLabelModal = false;

    public ?ProductVariant $labelVariant = null;

    public int $labelCopies = 1;

    // Import / Export
    public bool $showImportModal = false;

    public $importFile = null;

    public array $importErrors = [];

    public int $importSuccessCount = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedBrand(): void
    {
        $this->resetPage();
    }

    public function updatingStockStatus(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $productId): void
    {
        if (in_array($productId, $this->expandedProducts)) {
            $this->expandedProducts = array_values(array_diff($this->expandedProducts, [$productId]));
        } else {
            $this->expandedProducts[] = $productId;
        }
    }

    public function render()
    {
        $query = Product::with(['category', 'brand', 'variants', 'variants.stocks'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'LIKE', "%{$this->search}%")
                        ->orWhere('article_code', 'LIKE', "%{$this->search}%")
                        ->orWhereHas('variants', fn ($vq) => $vq->where('barcode', 'LIKE', "%{$this->search}%")->orWhere('sku', 'LIKE', "%{$this->search}%"));
                });
            })
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
            ->when($this->selectedBrand, fn ($q) => $q->where('brand_id', $this->selectedBrand))
            ->when($this->stockStatus !== 'all', function ($q) {
                if ($this->stockStatus === 'in_stock') {
                    $q->whereHas('variants.stocks', fn ($sq) => $sq->where('quantity', '>', 5));
                } elseif ($this->stockStatus === 'low_stock') {
                    $q->whereHas('variants.stocks', fn ($sq) => $sq->where('quantity', '>', 0)->where('quantity', '<=', 5));
                } elseif ($this->stockStatus === 'out_of_stock') {
                    $q->whereDoesntHave('variants.stocks', fn ($sq) => $sq->where('quantity', '>', 0));
                }
            })
            ->latest();

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $seasons = Season::where('is_active', true)->orderBy('name')->get();
        $sizeGroups = SizeGroup::where('is_active', true)->orderBy('name')->get();

        // Metrics for summary cards
        $totalProducts = Product::count();
        $totalVariants = ProductVariant::count();
        $totalStockUnits = (int) InventoryStock::sum('quantity');
        $totalValuation = (float) ProductVariant::join('inventory_stocks', 'product_variants.id', '=', 'inventory_stocks.product_variant_id')
            ->sum(DB::raw('inventory_stocks.quantity * product_variants.purchase_cost'));

        return view('livewire.catalogue-manager', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'seasons' => $seasons,
            'sizeGroups' => $sizeGroups,
            'totalProducts' => $totalProducts,
            'totalVariants' => $totalVariants,
            'totalStockUnits' => $totalStockUnits,
            'totalValuation' => $totalValuation,
        ]);
    }

    public function updatedCategoryId($value): void
    {
        if ($value && $category = Category::find($value)) {
            if (! empty($category->hsn_code)) {
                $this->hsn_code = $category->hsn_code;
            }
            if ($category->default_tax_rate !== null) {
                $this->tax_rate = (float) $category->default_tax_rate;
            }
        }
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingProductId', 'name', 'article_code', 'description', 'min_selling_price',
        ]);
        $this->article_code = 'ART-'.now()->format('ymd').'-'.rand(100, 999);
        $firstCat = Category::where('is_active', true)->first();
        $this->category_id = $firstCat ? $firstCat->id : 1;
        $this->hsn_code = $firstCat?->hsn_code ?: '6203';
        $this->tax_rate = $firstCat && $firstCat->default_tax_rate !== null ? (float) $firstCat->default_tax_rate : 5.00;
        $this->brand_id = Brand::where('is_active', true)->first()?->id;
        $this->season_id = Season::where('is_active', true)->first()?->id;
        $this->gender = 'Men';
        $this->fit = 'Regular Fit';
        $this->fabric = '100% Cotton';
        $this->pattern = 'Solid';
        $this->is_tax_inclusive = true;
        $this->showProductModal = true;
    }

    public function editProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->article_code = $product->article_code;
        $this->category_id = $product->category_id;
        $this->brand_id = $product->brand_id;
        $this->season_id = $product->season_id;
        $this->hsn_code = $product->hsn_code;
        $this->tax_rate = (float) $product->tax_rate;
        $this->is_tax_inclusive = (bool) $product->is_tax_inclusive;
        $this->gender = $product->gender ?? 'Men';
        $this->fit = $product->fit ?? 'Regular Fit';
        $this->fabric = $product->fabric ?? '100% Cotton';
        $this->pattern = $product->pattern ?? 'Solid';
        $this->min_selling_price = (float) $product->min_selling_price;
        $this->description = $product->description ?? '';
        $this->showProductModal = true;
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::with(['variants', 'variants.stocks'])->findOrFail($productId);

        $hasOrders = DB::table('order_items')->whereIn('product_variant_id', $product->variants->pluck('id'))->exists();
        if ($hasOrders) {
            session()->flash('error', "Cannot delete product '{$product->name}' because sales orders are already attached to it.");

            return;
        }

        $productName = $product->name;
        DB::transaction(function () use ($product) {
            foreach ($product->variants as $variant) {
                $variant->stocks()->delete();
                $variant->stockMovements()->delete();
                $variant->delete();
            }
            $product->delete();
        });

        session()->flash('success', "Product '{$productName}' deleted successfully.");
    }

    public function saveProduct(): void
    {
        $this->validate([
            'name' => 'required|string|min:3',
            'article_code' => 'required|string|unique:products,article_code,'.$this->editingProductId,
            'category_id' => 'required|exists:categories,id',
            'hsn_code' => 'required|string',
            'tax_rate' => 'required|numeric|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'article_code' => $this->article_code,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'season_id' => $this->season_id,
            'hsn_code' => $this->hsn_code,
            'tax_rate' => $this->tax_rate,
            'is_tax_inclusive' => $this->is_tax_inclusive,
            'gender' => $this->gender,
            'fit' => $this->fit,
            'fabric' => $this->fabric,
            'pattern' => $this->pattern,
            'min_selling_price' => $this->min_selling_price,
            'description' => $this->description,
        ];

        if ($this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $product->update($data);
            session()->flash('success', "Product '{$product->name}' updated successfully.");
        } else {
            $product = Product::create($data);
            session()->flash('success', "Product '{$product->name}' created successfully.");

            // Open size-color matrix immediately for new product
            $this->openMatrixModal($product->id);
        }

        $this->showProductModal = false;
    }

    public function openMatrixModal(int $productId): void
    {
        $this->matrixProduct = Product::with('variants')->findOrFail($productId);
        $firstVar = $this->matrixProduct->variants->first();
        $this->matrixCost = $firstVar ? (float) $firstVar->purchase_cost : 450.00;
        $this->matrixPrice = $firstVar ? (float) $firstVar->selling_price : 899.00;
        $this->matrixMrp = $firstVar ? (float) $firstVar->mrp : 1299.00;
        $this->matrixSizes = ['S', 'M', 'L', 'XL'];
        $this->matrixColors = ['Navy Blue', 'Black', 'Crisp White'];
        $this->customSizeInput = '';
        $this->customColorInput = '';
        $this->selectedSizeGroupId = null;
        $this->showMatrixModal = true;
    }

    public function selectMatrixSizeGroup(int $id): void
    {
        $group = SizeGroup::find($id);
        if ($group && ! empty($group->sizes)) {
            $this->selectedSizeGroupId = $group->id;
            $this->matrixSizes = is_array($group->sizes) ? $group->sizes : [];
        }
    }

    public function toggleMatrixSize(string $size): void
    {
        if (in_array($size, $this->matrixSizes)) {
            if (count($this->matrixSizes) > 1) {
                $this->matrixSizes = array_values(array_diff($this->matrixSizes, [$size]));
            }
        } else {
            $this->matrixSizes[] = $size;
        }
    }

    public function addCustomSize(): void
    {
        $val = trim($this->customSizeInput);
        if ($val !== '' && ! in_array($val, $this->matrixSizes)) {
            $this->matrixSizes[] = $val;
            $this->customSizeInput = '';
        }
    }

    public function toggleMatrixColor(string $color): void
    {
        if (in_array($color, $this->matrixColors)) {
            if (count($this->matrixColors) > 1) {
                $this->matrixColors = array_values(array_diff($this->matrixColors, [$color]));
            }
        } else {
            $this->matrixColors[] = $color;
        }
    }

    public function addCustomColor(): void
    {
        $val = trim($this->customColorInput);
        if ($val !== '' && ! in_array($val, $this->matrixColors)) {
            $this->matrixColors[] = $val;
            $this->customColorInput = '';
        }
    }

    public function generateMatrixVariants(): void
    {
        if (! $this->matrixProduct) {
            return;
        }

        $location = StockLocation::firstOrCreate(
            ['branch_id' => Auth::user()->branch_id ?: 1, 'type' => 'retail'],
            ['name' => 'Main Showroom Floor', 'code' => 'RETAIL-01', 'is_active' => true]
        );

        $createdCount = 0;

        DB::transaction(function () use (&$createdCount, $location) {
            foreach ($this->matrixColors as $color) {
                foreach ($this->matrixSizes as $size) {
                    $color = trim($color);
                    $size = trim($size);
                    if (empty($color) || empty($size)) {
                        continue;
                    }

                    $sku = strtoupper(Str::slug($this->matrixProduct->article_code.'-'.$color.'-'.$size));
                    $barcode = app(BarcodeService::class)->generateUniqueEan13();

                    $variant = ProductVariant::firstOrCreate(
                        ['product_id' => $this->matrixProduct->id, 'size' => $size, 'color' => $color],
                        [
                            'sku' => $sku,
                            'barcode' => $barcode,
                            'purchase_cost' => $this->matrixCost,
                            'selling_price' => $this->matrixPrice,
                            'mrp' => $this->matrixMrp,
                            'wholesale_price' => $this->matrixPrice * 0.85,
                            'low_stock_threshold' => 5,
                            'reorder_quantity' => 15,
                            'is_active' => true,
                        ]
                    );

                    // Initialize stock if provided
                    if ($this->matrixInitialStock > 0) {
                        InventoryStock::firstOrCreate(
                            ['product_variant_id' => $variant->id, 'stock_location_id' => $location->id],
                            ['quantity' => $this->matrixInitialStock]
                        );

                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'stock_location_id' => $location->id,
                            'movement_type' => 'opening',
                            'quantity' => $this->matrixInitialStock,
                            'unit_cost' => $this->matrixCost,
                            'balance_after' => $this->matrixInitialStock,
                            'remarks' => 'Bulk Matrix Initial Entry',
                            'user_id' => Auth::id(),
                        ]);
                    }

                    $createdCount++;
                }
            }
        });

        $this->showMatrixModal = false;
        session()->flash('success', "Generated/Updated {$createdCount} size-and-colour matrix variants!");
    }

    public function openLabelPrintModal(int $variantId): void
    {
        $this->labelVariant = ProductVariant::with('product')->findOrFail($variantId);
        $this->labelCopies = 1;
        $this->showLabelModal = true;
    }

    public function printLabel(): void
    {
        if (! $this->labelVariant) {
            return;
        }

        $printService = app(PrintService::class);
        $printService->printBarcodeLabel($this->labelVariant, $this->labelCopies);

        $this->showLabelModal = false;
        session()->flash('success', "Barcode label print job dispatched ({$this->labelCopies} copies).");
    }

    /**
     * CSV & Excel XLSX Import with automatic category, brand, HSN, and stock ledger entry.
     */
    public function importCsv(): void
    {
        $this->validate([
            'importFile' => 'required|file|max:10240',
        ]);

        $path = $this->importFile->getRealPath();
        $originalExt = strtolower($this->importFile->getClientOriginalExtension());

        // Ensure temp file retains its extension for parser detection
        $tempPath = $path;
        if ($originalExt === 'xlsx' && ! str_ends_with($path, '.xlsx')) {
            $tempPath = $path.'.xlsx';
            copy($path, $tempPath);
        }

        try {
            $importService = app(ProductImportService::class);
            $result = $importService->importFromFile(
                $tempPath,
                Auth::id(),
                Auth::user()->branch_id ?: 1
            );

            if ($tempPath !== $path && file_exists($tempPath)) {
                unlink($tempPath);
            }

            if (! $result['success']) {
                $this->importErrors = $result['errors'];
                session()->flash('error', 'Import failed: '.$result['message']);

                return;
            }

            $this->importSuccessCount = $result['imported_variants'];
            $this->importErrors = $result['errors'];
            $this->showImportModal = false;

            session()->flash('success', "Import complete! Added/Updated {$result['imported_products']} products, {$result['imported_variants']} variants, and {$result['total_stock_units']} units into stock ledger.");
        } catch (Exception $e) {
            if ($tempPath !== $path && file_exists($tempPath)) {
                unlink($tempPath);
            }
            session()->flash('error', 'Import failed: '.$e->getMessage());
        }
    }
}
