<?php

namespace App\Livewire;

use App\Models\Printer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\BarcodeService;
use App\Services\PrintService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BarcodeLabelPrinter extends Component
{
    // Fast Scanner & Search
    public string $scanInput = '';

    public string $searchQuery = '';

    public array $searchResults = [];

    // Print Queue: array of ['variant_id' => int, 'copies' => int]
    public array $queue = [];

    // Label Template & Sizing Preset
    public string $templatePreset = '2up_50x25'; // 2up_50x25 (TVS LP 46 2-across), 2up_38x25, 50x38 (Hangtag), 50x25, 38x25, a4_24, custom

    public int $labelWidthMm = 50;

    public int $labelHeightMm = 25;

    public int $columns = 2;

    public int $gapMm = 3;

    public int $rowGapMm = 3;

    // Print Scale & Orientation Customizers
    public int $labelScalePercent = 100; // 85% to 135% zoom

    public string $labelOrientation = 'landscape'; // 'landscape' (standard 50mm horizontal) or 'portrait' (rotated 90deg)

    // Custom Label Fields Toggle
    public bool $showBrand = true;

    public bool $showArticleCode = true;

    public bool $showSizeColor = true;

    public bool $showMrp = true;

    public bool $showSellingPrice = true;

    public bool $showBarcodeText = true;

    public bool $showStoreName = true;

    public bool $showDateHsn = true;

    public string $customStoreName = 'NextDNA Retail';

    // Target Printer
    public ?int $selectedPrinterId = null;

    public function mount(?int $variant_id = null, ?int $product_id = null)
    {
        if ($variant_id) {
            $this->addToQueue($variant_id);
        } elseif ($product_id) {
            $this->addProductVariantsToQueue($product_id);
        } else {
            // Default load first 3 active variants so the user immediately sees a live preview
            $initialVariants = ProductVariant::where('is_active', true)->take(3)->pluck('id');
            foreach ($initialVariants as $id) {
                $this->addToQueue($id, 2);
            }
        }

        $defaultPrinter = Printer::where('branch_id', Auth::user()->branch_id ?: 1)
            ->where('is_default_label', true)
            ->first();
        $this->selectedPrinterId = $defaultPrinter?->id;
    }

    public function render()
    {
        $barcodeService = app(BarcodeService::class);

        // Fetch variant details for all queued items
        $variantIds = array_keys($this->queue);
        $barcodeHeight = $this->labelHeightMm <= 25 ? 32 : ($this->labelHeightMm <= 38 ? 38 : 48);

        $variants = ProductVariant::with(['product', 'product.brand', 'product.category', 'stocks'])
            ->whereIn('id', $variantIds)
            ->get()
            ->map(function ($variant) use ($barcodeService, $barcodeHeight) {
                $copies = $this->queue[$variant->id] ?? 1;
                $svg = $barcodeService->generateCode128Svg(
                    $variant->barcode ?: $variant->sku,
                    barWidth: 2,
                    height: $barcodeHeight,
                    showText: false,
                    preserveAspectRatio: 'none'
                );

                return [
                    'model' => $variant,
                    'copies' => $copies,
                    'barcode_svg' => $svg,
                ];
            });

        // Expand queued items into flat individual printable label instances
        $flatLabels = [];
        foreach ($variants as $item) {
            for ($c = 0; $c < $item['copies']; $c++) {
                $flatLabels[] = $item;
            }
        }

        // Chunk labels into rows according to column count (e.g. 2 for 2-Up roll, 1 for single hangtag)
        $chunkSize = max(1, $this->columns);
        $printableRows = array_chunk($flatLabels, $chunkSize);

        $printers = Printer::where('is_active', true)
            ->where('branch_id', Auth::user()->branch_id ?: 1)
            ->get();

        return view('livewire.barcode-label-printer', [
            'queuedItems' => $variants,
            'printableRows' => $printableRows,
            'totalLabelsCount' => count($flatLabels),
            'printers' => $printers,
        ]);
    }

    /**
     * Fast scanner handler: scan any barcode/SKU to immediately fetch product and add to queue.
     */
    public function handleScannerInput()
    {
        $code = trim($this->scanInput);
        $this->scanInput = '';

        if (empty($code)) {
            return;
        }

        $variant = ProductVariant::with('product')
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->orWhereHas('product', fn ($q) => $q->where('article_code', $code))
            ->first();

        if ($variant) {
            // Default copies to stock on hand or 1
            $copies = max(1, $variant->stock_on_hand);
            $this->addToQueue($variant->id, $copies);
            $this->dispatch('scan-success');
            session()->flash('success', "Fetched & Added: {$variant->fullName} ({$copies} copies)");
        } else {
            $this->dispatch('scan-error');
            session()->flash('error', "No variant found matching barcode or SKU '{$code}'.");
        }
    }

    /**
     * Autocomplete search.
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
            ->take(8)
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
     * Add single variant to print queue.
     */
    public function addToQueue(int $variantId, int $copies = 1)
    {
        if (isset($this->queue[$variantId])) {
            $this->queue[$variantId] += $copies;
        } else {
            $this->queue[$variantId] = max(1, $copies);
        }

        $this->searchQuery = '';
        $this->searchResults = [];
    }

    /**
     * Add all variants of a product (all sizes/colors) to queue.
     */
    public function addProductVariantsToQueue(int $productId)
    {
        $variants = ProductVariant::where('product_id', $productId)->where('is_active', true)->get();
        foreach ($variants as $v) {
            $this->addToQueue($v->id, max(1, $v->stock_on_hand));
        }

        session()->flash('success', "Added all {$variants->count()} variants of product to label print queue.");
    }

    public function updateCopies(int $variantId, int $copies)
    {
        if ($copies <= 0) {
            $this->removeFromQueue($variantId);
        } else {
            $this->queue[$variantId] = $copies;
        }
    }

    public function removeFromQueue(int $variantId)
    {
        unset($this->queue[$variantId]);
    }

    public function clearQueue()
    {
        $this->queue = [];
    }

    /**
     * Set all queued copies to match exact physical stock on hand.
     */
    public function matchAllCopiesToStock()
    {
        $variants = ProductVariant::whereIn('id', array_keys($this->queue))->get();
        foreach ($variants as $v) {
            $this->queue[$v->id] = max(1, $v->stock_on_hand);
        }

        session()->flash('success', 'Matched label print quantities to current stock on hand.');
    }

    /**
     * Set all queued copies to uniform quantity (e.g. 1, 5, 10).
     */
    public function setAllCopies(int $count = 1)
    {
        foreach ($this->queue as $id => $val) {
            $this->queue[$id] = max(1, $count);
        }
    }

    /**
     * Generate unique EAN-13 or Code-128 barcodes for all items in queue.
     */
    public function generateUniqueBarcodesForQueue(string $type = 'EAN13')
    {
        $barcodeService = app(BarcodeService::class);
        $updated = 0;

        foreach (array_keys($this->queue) as $variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                $newCode = ($type === 'EAN13')
                    ? $barcodeService->generateUniqueEan13()
                    : $barcodeService->generateUniqueCode128();

                $variant->barcode = $newCode;
                $variant->save();
                $updated++;
            }
        }

        session()->flash('success', "Generated and saved unique {$type} barcodes for {$updated} variants!");
    }

    /**
     * Set template preset explicitly from UI buttons.
     */
    public function setPreset(string $preset): void
    {
        $this->templatePreset = $preset;
        $this->applyPreset($preset);
    }

    /**
     * Update template preset dimensions when wire:model changes.
     */
    public function updatedTemplatePreset(string $preset): void
    {
        $this->applyPreset($preset);
    }

    /**
     * Apply dimensions and column settings for a given preset.
     */
    protected function applyPreset(string $preset): void
    {
        match ($preset) {
            '2up_50x25' => [
                $this->labelWidthMm = 50,
                $this->labelHeightMm = 25,
                $this->columns = 2,
                $this->gapMm = 3,
            ],
            '2up_50x30' => [
                $this->labelWidthMm = 50,
                $this->labelHeightMm = 30,
                $this->columns = 2,
                $this->gapMm = 3,
            ],
            '2up_38x25' => [
                $this->labelWidthMm = 38,
                $this->labelHeightMm = 25,
                $this->columns = 2,
                $this->gapMm = 3,
            ],
            '50x38' => [
                $this->labelWidthMm = 50,
                $this->labelHeightMm = 38,
                $this->columns = 1,
                $this->gapMm = 0,
            ],
            '50x25' => [
                $this->labelWidthMm = 50,
                $this->labelHeightMm = 25,
                $this->columns = 1,
                $this->gapMm = 0,
            ],
            '38x25' => [
                $this->labelWidthMm = 38,
                $this->labelHeightMm = 25,
                $this->columns = 1,
                $this->gapMm = 0,
            ],
            'a4_24' => [
                $this->labelWidthMm = 70,
                $this->labelHeightMm = 36,
                $this->columns = 3,
                $this->gapMm = 2,
            ],
            'custom' => null,
            default => null,
        };
    }

    /**
     * Dispatch thermal print job for all queued labels to target printer.
     */
    public function dispatchThermalPrintJob()
    {
        if (empty($this->queue)) {
            session()->flash('error', 'Label print queue is empty.');

            return;
        }

        $printService = app(PrintService::class);
        $printer = $this->selectedPrinterId ? Printer::find($this->selectedPrinterId) : null;
        $totalSent = array_sum($this->queue);

        $printService->printBarcodeQueue(
            $this->queue,
            $printer,
            $this->columns,
            $this->labelWidthMm,
            $this->labelHeightMm
        );

        session()->flash('success', "Successfully dispatched {$totalSent} thermal barcode labels ({$this->columns}-Across layout) to printer!");
    }
}
