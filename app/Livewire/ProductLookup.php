<?php

namespace App\Livewire;

use App\Models\InventoryStock;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Services\BarcodeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProductLookup extends Component
{
    // Scanner & Search State
    public string $barcodeInput = '';

    public string $searchQuery = '';

    public array $searchResults = [];

    // Selected Variant Details
    public ?ProductVariant $selectedVariant = null;

    public array $siblingVariants = [];

    public ?string $barcodeSvg = null;

    // Quick Stock Adjustment Modal
    public bool $showAdjustmentModal = false;

    public int $adjustmentLocationId = 0;

    public string $adjustmentType = 'recount'; // recount, add, subtract

    public int $adjustmentQty = 0;

    public string $adjustmentReason = 'Physical stock count correction';

    public function mount(?int $id = null)
    {
        if ($id) {
            $this->selectVariant($id);
        } else {
            // Load default first active variant for display
            $first = ProductVariant::with(['product', 'product.brand', 'stocks'])->where('is_active', true)->first();
            if ($first) {
                $this->selectVariant($first->id);
            }
        }
    }

    public function render()
    {
        $locations = StockLocation::where('is_active', true)
            ->where('branch_id', Auth::user()->branch_id ?: 1)
            ->get();

        return view('livewire.product-lookup', [
            'locations' => $locations,
            'canViewCost' => Auth::user()->canViewCostPrices(),
        ]);
    }

    /**
     * Fast keyboard-wedge barcode scanner handler.
     */
    public function handleBarcodeScan()
    {
        $code = trim($this->barcodeInput);
        $this->barcodeInput = '';

        if (empty($code)) {
            return;
        }

        $variant = ProductVariant::with(['product', 'product.brand', 'product.category', 'stocks'])
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->orWhereHas('product', fn ($q) => $q->where('article_code', $code))
            ->first();

        if ($variant) {
            $this->selectVariant($variant->id);
            $this->dispatch('scan-success');
            session()->flash('success', "Found: {$variant->fullName} (Barcode: {$variant->barcode})");
        } else {
            $this->dispatch('scan-error');
            session()->flash('error', "No product found matching barcode or SKU '{$code}'.");
        }
    }

    /**
     * Live product catalogue search.
     */
    public function updatedSearchQuery()
    {
        $q = trim($this->searchQuery);
        if (strlen($q) < 2) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = ProductVariant::with(['product', 'product.brand', 'stocks'])
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('barcode', 'LIKE', "%{$q}%")
                    ->orWhere('sku', 'LIKE', "%{$q}%")
                    ->orWhere('size', 'LIKE', "%{$q}%")
                    ->orWhere('color', 'LIKE', "%{$q}%")
                    ->orWhereHas('product', function ($pq) use ($q) {
                        $pq->where('name', 'LIKE', "%{$q}%")
                            ->orWhere('article_code', 'LIKE', "%{$q}%");
                    });
            })
            ->take(10)
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->product->name,
                'article_code' => $v->product->article_code,
                'size' => $v->size,
                'color' => $v->color,
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'selling_price' => (float) $v->selling_price,
                'mrp' => (float) $v->mrp,
                'stock' => $v->stock_on_hand,
            ])
            ->toArray();
    }

    /**
     * Select and load a product variant.
     */
    public function selectVariant(int $variantId)
    {
        $this->selectedVariant = ProductVariant::with([
            'product',
            'product.brand',
            'product.category',
            'product.season',
            'stocks.location',
        ])->findOrFail($variantId);

        // Load all sibling variants (same product article) for quick size/color matrix navigation
        $this->siblingVariants = ProductVariant::where('product_id', $this->selectedVariant->product_id)
            ->where('is_active', true)
            ->with('stocks')
            ->get()
            ->toArray();

        // Generate vector SVG barcode preview
        $barcodeService = app(BarcodeService::class);
        $this->barcodeSvg = $barcodeService->generateCode128Svg(
            $this->selectedVariant->barcode ?: $this->selectedVariant->sku,
            barWidth: 2,
            height: 48,
            showText: true
        );

        $this->searchQuery = '';
        $this->searchResults = [];
    }

    /**
     * Generate a new unique GS1 EAN-13 or Code-128 barcode for this variant.
     */
    public function generateUniqueBarcodeForVariant(string $type = 'EAN13')
    {
        if (! $this->selectedVariant) {
            return;
        }

        $barcodeService = app(BarcodeService::class);

        if ($type === 'EAN13') {
            $newBarcode = $barcodeService->generateUniqueEan13();
        } else {
            $newBarcode = $barcodeService->generateUniqueCode128();
        }

        $this->selectedVariant->barcode = $newBarcode;
        $this->selectedVariant->save();

        $this->selectVariant($this->selectedVariant->id);
        session()->flash('success', "Assigned new unique {$type} barcode: {$newBarcode}");
    }

    /**
     * Open Quick Stock Adjustment Modal.
     */
    public function openAdjustmentModal(int $locationId = 0)
    {
        $defaultLoc = StockLocation::where('branch_id', Auth::user()->branch_id ?: 1)->first();
        $this->adjustmentLocationId = $locationId ?: ($defaultLoc->id ?? 1);
        $currentStock = InventoryStock::where('product_variant_id', $this->selectedVariant->id)
            ->where('stock_location_id', $this->adjustmentLocationId)
            ->value('quantity') ?? 0;

        $this->adjustmentQty = $currentStock;
        $this->adjustmentType = 'recount';
        $this->showAdjustmentModal = true;
    }

    /**
     * Save Stock Adjustment.
     */
    public function saveStockAdjustment()
    {
        $this->validate([
            'adjustmentLocationId' => 'required|exists:stock_locations,id',
            'adjustmentQty' => 'required|integer',
            'adjustmentReason' => 'required|string|min:3',
        ]);

        $location = StockLocation::findOrFail($this->adjustmentLocationId);
        $stockRecord = InventoryStock::firstOrCreate(
            ['product_variant_id' => $this->selectedVariant->id, 'stock_location_id' => $location->id],
            ['quantity' => 0]
        );

        $oldQty = $stockRecord->quantity;
        $newQty = match ($this->adjustmentType) {
            'recount' => max(0, $this->adjustmentQty),
            'add' => $oldQty + max(1, $this->adjustmentQty),
            'subtract' => max(0, $oldQty - max(1, $this->adjustmentQty)),
            default => $oldQty,
        };

        $diff = $newQty - $oldQty;

        if ($diff === 0) {
            $this->showAdjustmentModal = false;

            return;
        }

        $stockRecord->quantity = $newQty;
        $stockRecord->save();

        StockMovement::create([
            'product_variant_id' => $this->selectedVariant->id,
            'stock_location_id' => $location->id,
            'movement_type' => 'adjustment',
            'quantity' => $diff,
            'unit_cost' => $this->selectedVariant->purchase_cost,
            'balance_after' => $newQty,
            'remarks' => "Quick Scan Adjustment: {$this->adjustmentReason}",
            'user_id' => Auth::id(),
        ]);

        $this->showAdjustmentModal = false;
        $this->selectVariant($this->selectedVariant->id);
        session()->flash('success', "Stock adjusted on '{$location->name}': {$oldQty} → {$newQty} units.");
    }
}
