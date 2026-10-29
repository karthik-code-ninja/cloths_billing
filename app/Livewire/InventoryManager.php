<?php

namespace App\Livewire;

use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\InventoryService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InventoryManager extends Component
{
    use WithPagination;

    public string $tab = 'ledger'; // ledger, low_stock, adjustments, transfers

    public string $movementFilter = '';

    public string $search = '';

    // Stock Adjustment Modal
    public bool $showAdjustmentModal = false;

    public ?int $adjVariantId = null;

    public int $countedQuantity = 0;

    public string $adjReason = 'physical_count_discrepancy';

    public string $adjNotes = '';

    // Stock Transfer Modal
    public bool $showTransferModal = false;

    public ?int $transferVariantId = null;

    public ?int $fromLocationId = null;

    public ?int $toLocationId = null;

    public int $transferQuantity = 1;

    public function render()
    {
        $locations = StockLocation::all();
        $movements = collect();
        $lowStocks = collect();
        $adjustments = collect();
        $transfers = collect();

        if ($this->tab === 'ledger') {
            $movements = StockMovement::with(['variant', 'variant.product', 'location', 'user'])
                ->when($this->movementFilter, fn ($q) => $q->where('movement_type', $this->movementFilter))
                ->when($this->search, function ($q) {
                    $q->whereHas('variant', function ($vq) {
                        $vq->where('barcode', 'LIKE', "%{$this->search}%")
                            ->orWhere('sku', 'LIKE', "%{$this->search}%")
                            ->orWhereHas('product', fn ($pq) => $pq->where('name', 'LIKE', "%{$this->search}%"));
                    });
                })
                ->latest()
                ->paginate(15);
        } elseif ($this->tab === 'low_stock') {
            $lowStocks = ProductVariant::with(['product', 'stocks'])
                ->where('is_active', true)
                ->get()
                ->filter(fn ($v) => $v->stock_on_hand <= $v->low_stock_threshold);
        } elseif ($this->tab === 'adjustments') {
            $adjustments = StockAdjustment::with(['location', 'user', 'items', 'items.variant.product'])
                ->latest()
                ->paginate(10);
        } elseif ($this->tab === 'transfers') {
            $transfers = StockTransfer::with(['fromLocation', 'toLocation', 'user', 'items.variant.product'])
                ->latest()
                ->paginate(10);
        }

        $allVariants = ProductVariant::with('product')->get();

        return view('livewire.inventory-manager', [
            'movements' => $movements,
            'lowStocks' => $lowStocks,
            'adjustments' => $adjustments,
            'transfers' => $transfers,
            'locations' => $locations,
            'allVariants' => $allVariants,
        ]);
    }

    public function openAdjustmentModal(?int $variantId = null)
    {
        $this->adjVariantId = $variantId ?: (ProductVariant::first()->id ?? null);
        $variant = ProductVariant::find($this->adjVariantId);
        $this->countedQuantity = $variant?->stock_on_hand ?? 0;
        $this->showAdjustmentModal = true;
    }

    public function saveAdjustment()
    {
        $variant = ProductVariant::findOrFail($this->adjVariantId);
        $location = StockLocation::firstOrCreate(
            ['branch_id' => Auth::user()->branch_id ?: 1, 'type' => 'retail'],
            ['name' => 'Main Showroom Floor', 'code' => 'RETAIL-01']
        );

        $systemQty = $variant->stock_on_hand;
        $diff = $this->countedQuantity - $systemQty;

        if ($diff === 0) {
            session()->flash('error', 'Counted quantity equals current system stock. No adjustment needed.');

            return;
        }

        DB::transaction(function () use ($variant, $location, $systemQty, $diff) {
            $adjNum = 'ADJ-'.now()->format('ymd').'-'.rand(1000, 9999);

            $adj = StockAdjustment::create([
                'adjustment_number' => $adjNum,
                'stock_location_id' => $location->id,
                'user_id' => Auth::id(),
                'reason' => $this->adjReason,
                'status' => 'approved',
                'notes' => $this->adjNotes,
            ]);

            StockAdjustmentItem::create([
                'stock_adjustment_id' => $adj->id,
                'product_variant_id' => $variant->id,
                'system_quantity' => $systemQty,
                'counted_quantity' => $this->countedQuantity,
                'difference_quantity' => $diff,
                'unit_cost' => $variant->purchase_cost,
            ]);

            $invService = app(InventoryService::class);
            if ($diff > 0) {
                $invService->incrementStock(
                    $variant,
                    $diff,
                    $location->id,
                    'adjustment',
                    'StockAdjustment',
                    $adj->id,
                    "Stock Count Adjustment (#{$adjNum})",
                    Auth::id()
                );
            } else {
                $invService->decrementStock(
                    $variant,
                    abs($diff),
                    $location->id,
                    'adjustment',
                    'StockAdjustment',
                    $adj->id,
                    "Stock Count Adjustment (#{$adjNum})",
                    Auth::id()
                );
            }
        });

        $this->showAdjustmentModal = false;
        session()->flash('success', "Stock adjustment completed successfully (Diff: {$diff}).");
    }

    public function openTransferModal()
    {
        $locations = StockLocation::all();
        $this->fromLocationId = $locations->first()->id ?? null;
        $this->toLocationId = $locations->skip(1)->first()->id ?? $this->fromLocationId;
        $this->transferVariantId = ProductVariant::first()->id ?? null;
        $this->transferQuantity = 5;
        $this->showTransferModal = true;
    }

    public function saveTransfer()
    {
        if ($this->fromLocationId === $this->toLocationId) {
            session()->flash('error', 'Source and destination locations must be different.');

            return;
        }

        $variant = ProductVariant::findOrFail($this->transferVariantId);
        $fromLoc = StockLocation::findOrFail($this->fromLocationId);
        $toLoc = StockLocation::findOrFail($this->toLocationId);

        $invService = app(InventoryService::class);

        try {
            DB::transaction(function () use ($variant, $fromLoc, $toLoc, $invService) {
                $transferNum = 'TRF-'.now()->format('ymd').'-'.rand(1000, 9999);

                $transfer = StockTransfer::create([
                    'transfer_number' => $transferNum,
                    'from_location_id' => $fromLoc->id,
                    'to_location_id' => $toLoc->id,
                    'status' => 'received',
                    'dispatch_date' => now(),
                    'receipt_date' => now(),
                    'user_id' => Auth::id(),
                    'received_by_id' => Auth::id(),
                    'notes' => 'Direct location stock transfer',
                ]);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_variant_id' => $variant->id,
                    'quantity_sent' => $this->transferQuantity,
                    'quantity_received' => $this->transferQuantity,
                ]);

                // Decrement from source
                $invService->decrementStock(
                    $variant,
                    $this->transferQuantity,
                    $fromLoc->id,
                    'transfer_out',
                    'StockTransfer',
                    $transfer->id,
                    "Transfer to {$toLoc->name}",
                    Auth::id()
                );

                // Increment in destination
                $invService->incrementStock(
                    $variant,
                    $this->transferQuantity,
                    $toLoc->id,
                    'transfer_in',
                    'StockTransfer',
                    $transfer->id,
                    "Transfer from {$fromLoc->name}",
                    Auth::id()
                );
            });

            $this->showTransferModal = false;
            session()->flash('success', "Transferred {$this->transferQuantity} units of {$variant->fullName}.");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}
