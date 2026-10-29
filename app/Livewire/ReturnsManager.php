<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ReturnOrder;
use App\Services\PrintService;
use App\Services\ReturnService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ReturnsManager extends Component
{
    use WithPagination;

    public string $invoiceSearch = '';

    public ?Order $originalOrder = null;

    public array $returnSelections = []; // order_item_id => ['selected' => bool, 'quantity' => int, 'condition' => 'resellable']

    public string $refundMethod = 'cash'; // cash, store_credit, original_method

    public string $returnReason = 'Customer Return';

    // Exchange features
    public bool $isExchange = false;

    public array $exchangeItems = []; // list of new variants to exchange for

    public ?int $selectedExchangeVariantId = null;

    public int $exchangeQty = 1;

    // Completed return
    public ?ReturnOrder $completedReturn = null;

    public bool $showReturnReceiptModal = false;

    public function render()
    {
        $pastReturns = ReturnOrder::with(['originalOrder', 'customer', 'user', 'items.variant.product'])
            ->latest('return_date')
            ->paginate(10);

        $allVariants = ProductVariant::with('product')->where('is_active', true)->get();

        return view('livewire.returns-manager', [
            'pastReturns' => $pastReturns,
            'allVariants' => $allVariants,
        ]);
    }

    public function searchInvoice()
    {
        $query = trim($this->invoiceSearch);
        if (empty($query)) {
            return;
        }

        $order = Order::with(['items', 'items.variant', 'items.variant.product', 'customer', 'cashier'])
            ->where('invoice_number', $query)
            ->orWhere('id', $query)
            ->first();

        if ($order) {
            $this->originalOrder = $order;
            $this->returnSelections = [];
            foreach ($order->items as $item) {
                $this->returnSelections[$item->id] = [
                    'selected' => false,
                    'quantity' => 1,
                    'condition' => 'resellable',
                ];
            }
            $this->exchangeItems = [];
            $this->completedReturn = null;
            session()->flash('success', "Loaded Order #{$order->invoice_number}.");
        } else {
            session()->flash('error', "Invoice '{$query}' not found.");
        }
    }

    public function addExchangeItem()
    {
        if (! $this->selectedExchangeVariantId) {
            return;
        }

        $variant = ProductVariant::with('product')->find($this->selectedExchangeVariantId);
        if ($variant) {
            $this->exchangeItems[] = [
                'variant_id' => $variant->id,
                'name' => $variant->product->name,
                'size' => $variant->size,
                'color' => $variant->color,
                'price' => (float) $variant->selling_price,
                'quantity' => $this->exchangeQty,
                'total' => round((float) $variant->selling_price * $this->exchangeQty, 2),
            ];
            $this->exchangeQty = 1;
        }
    }

    public function removeExchangeItem(int $index)
    {
        unset($this->exchangeItems[$index]);
        $this->exchangeItems = array_values($this->exchangeItems);
    }

    public function processReturnOrExchange()
    {
        if (! $this->originalOrder) {
            return;
        }

        // Build return items array
        $itemsToReturn = [];
        foreach ($this->originalOrder->items as $orderItem) {
            $sel = $this->returnSelections[$orderItem->id] ?? null;
            if ($sel && ! empty($sel['selected'])) {
                $qty = (int) $sel['quantity'];
                if ($qty > $orderItem->returnable_quantity) {
                    session()->flash('error', "Cannot return {$qty} units of {$orderItem->product_name}. Maximum returnable: {$orderItem->returnable_quantity}.");

                    return;
                }

                $itemsToReturn[] = [
                    'order_item_id' => $orderItem->id,
                    'variant_id' => $orderItem->product_variant_id,
                    'quantity' => $qty,
                    'condition' => $sel['condition'] ?? 'resellable',
                ];
            }
        }

        if (empty($itemsToReturn)) {
            session()->flash('error', 'Please select at least one item to return.');

            return;
        }

        $returnService = app(ReturnService::class);
        $printService = app(PrintService::class);

        try {
            if ($this->isExchange && count($this->exchangeItems) > 0) {
                // Process as Exchange
                $result = $returnService->processExchange([
                    'return_data' => [
                        'original_order_id' => $this->originalOrder->id,
                        'branch_id' => $this->originalOrder->branch_id,
                        'user_id' => Auth::id(),
                        'reason' => $this->returnReason,
                        'items' => $itemsToReturn,
                    ],
                    'new_items' => collect($this->exchangeItems)->map(fn ($ei) => [
                        'variant_id' => $ei['variant_id'],
                        'quantity' => $ei['quantity'],
                        'unit_price' => $ei['price'],
                    ])->toArray(),
                ]);

                $this->completedReturn = $result['return_order'];
                $diff = $result['difference'];
                $msg = $diff > 0
                    ? "Exchange processed. Collected ₹{$diff} difference from customer."
                    : ($diff < 0 ? 'Exchange processed. Refunded ₹'.abs($diff).' difference.' : 'Even exchange processed.');
                session()->flash('success', $msg);
            } else {
                // Standard Return
                $returnOrder = $returnService->processReturn([
                    'original_order_id' => $this->originalOrder->id,
                    'branch_id' => $this->originalOrder->branch_id,
                    'user_id' => Auth::id(),
                    'refund_method' => $this->refundMethod,
                    'reason' => $this->returnReason,
                    'items' => $itemsToReturn,
                ]);

                $this->completedReturn = $returnOrder;
                session()->flash('success', "Return #{$returnOrder->return_number} processed. Refund: ₹{$returnOrder->total_refund_amount}.");
            }

            // Print return credit note
            $printService->printReturnReceipt($this->completedReturn);

            $this->showReturnReceiptModal = true;
            $this->originalOrder = null;
            $this->returnSelections = [];
            $this->exchangeItems = [];
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}
