<?php

namespace App\Livewire;

use App\Models\GoodsReceivedNote;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLocation;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PurchasesManager extends Component
{
    use WithPagination;

    public string $tab = 'orders'; // orders, grn, suppliers, payments

    // Create PO Modal
    public bool $showPoModal = false;

    public ?int $selectedSupplierId = null;

    public string $orderDate = '';

    public string $deliveryDate = '';

    public array $poItems = [];

    // Create GRN Modal
    public bool $showGrnModal = false;

    public ?PurchaseOrder $grnPo = null;

    public string $supplierInvoiceNumber = '';

    public string $invoiceDate = '';

    public array $receivedQuantities = [];

    // Supplier Payment Modal
    public bool $showPaymentModal = false;

    public ?Supplier $paymentSupplier = null;

    public float $paymentAmount = 0.0;

    public string $paymentMethod = 'bank_transfer';

    public string $paymentRef = '';

    public function mount()
    {
        $this->orderDate = now()->toDateString();
        $this->deliveryDate = now()->addDays(7)->toDateString();
    }

    public function render()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $variants = ProductVariant::with('product')->get();
        $locations = StockLocation::all();

        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'items.variant.product'])->latest()->paginate(10);
        $grns = GoodsReceivedNote::with(['supplier', 'purchaseOrder'])->latest()->paginate(10);
        $payments = SupplierPayment::with(['supplier', 'goodsReceivedNote'])->latest()->paginate(10);

        return view('livewire.purchases-manager', [
            'purchaseOrders' => $purchaseOrders,
            'grns' => $grns,
            'suppliers' => $suppliers,
            'variants' => $variants,
            'locations' => $locations,
            'payments' => $payments,
        ]);
    }

    public function openCreatePoModal()
    {
        $this->selectedSupplierId = Supplier::first()->id ?? null;
        $v1 = ProductVariant::first();
        $this->poItems = [
            ['variant_id' => $v1->id ?? 1, 'quantity' => 20, 'unit_cost' => (float) ($v1->purchase_cost ?? 400), 'tax_rate' => 5.0],
        ];
        $this->showPoModal = true;
    }

    public function addPoItem()
    {
        $v = ProductVariant::first();
        $this->poItems[] = [
            'variant_id' => $v->id ?? 1,
            'quantity' => 10,
            'unit_cost' => (float) ($v->purchase_cost ?? 400),
            'tax_rate' => 5.0,
        ];
    }

    public function removePoItem(int $index)
    {
        unset($this->poItems[$index]);
        $this->poItems = array_values($this->poItems);
    }

    public function savePurchaseOrder()
    {
        $this->validate([
            'selectedSupplierId' => 'required|exists:suppliers,id',
            'poItems' => 'required|array|min:1',
        ]);

        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($this->poItems as $item) {
            $lineSub = round((float) $item['unit_cost'] * (int) $item['quantity'], 2);
            $lineTax = round(($lineSub * (float) $item['tax_rate']) / 100, 2);
            $subtotal += $lineSub;
            $taxTotal += $lineTax;
        }

        $grandTotal = round($subtotal + $taxTotal, 2);
        $poNumber = 'PO-'.now()->format('ymd').'-'.rand(100, 999);

        DB::transaction(function () use ($poNumber, $subtotal, $taxTotal, $grandTotal) {
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $this->selectedSupplierId,
                'branch_id' => Auth::user()->branch_id ?: 1,
                'order_date' => $this->orderDate ?: now(),
                'expected_delivery_date' => $this->deliveryDate ?: now()->addDays(7),
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'status' => 'ordered',
            ]);

            foreach ($this->poItems as $item) {
                $lineSub = round((float) $item['unit_cost'] * (int) $item['quantity'], 2);
                $lineTax = round(($lineSub * (float) $item['tax_rate']) / 100, 2);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity_ordered' => $item['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $lineTax,
                    'line_total' => $lineSub + $lineTax,
                ]);
            }
        });

        $this->showPoModal = false;
        session()->flash('success', "Purchase Order {$poNumber} created successfully!");
    }

    public function openReceiveGrnModal(int $poId)
    {
        $this->grnPo = PurchaseOrder::with(['items', 'items.variant.product'])->findOrFail($poId);
        $this->supplierInvoiceNumber = 'INV-SUP-'.rand(1000, 9999);
        $this->invoiceDate = now()->toDateString();
        $this->receivedQuantities = [];

        foreach ($this->grnPo->items as $item) {
            $remaining = max(0, $item->quantity_ordered - $item->quantity_received);
            $this->receivedQuantities[$item->id] = $remaining;
        }

        $this->showGrnModal = true;
    }

    public function processGrn()
    {
        if (! $this->grnPo) {
            return;
        }

        $location = StockLocation::firstOrCreate(
            ['branch_id' => Auth::user()->branch_id ?: 1, 'type' => 'retail'],
            ['name' => 'Main Showroom Floor', 'code' => 'RETAIL-01']
        );

        $invService = app(InventoryService::class);

        DB::transaction(function () use ($location, $invService) {
            $grnNumber = 'GRN-'.now()->format('ymd').'-'.rand(100, 999);
            $totalReceivedValue = 0.0;

            foreach ($this->grnPo->items as $item) {
                $recvQty = (int) ($this->receivedQuantities[$item->id] ?? 0);
                if ($recvQty > 0) {
                    $item->increment('quantity_received', $recvQty);
                    $lineTotal = round($recvQty * (float) $item->unit_cost * (1 + (float) $item->tax_rate / 100), 2);
                    $totalReceivedValue += $lineTotal;

                    // Increment inventory stock
                    $invService->incrementStock(
                        $item->variant,
                        $recvQty,
                        $location->id,
                        'purchase',
                        'GoodsReceivedNote',
                        null,
                        "Received on {$grnNumber} (PO: {$this->grnPo->po_number})",
                        Auth::id()
                    );
                }
            }

            $grn = GoodsReceivedNote::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $this->grnPo->id,
                'supplier_id' => $this->grnPo->supplier_id,
                'branch_id' => $this->grnPo->branch_id,
                'stock_location_id' => $location->id,
                'supplier_invoice_number' => $this->supplierInvoiceNumber,
                'invoice_date' => $this->invoiceDate ?: now(),
                'received_date' => now(),
                'total_amount' => $totalReceivedValue,
                'paid_amount' => 0.00,
                'payment_status' => 'unpaid',
                'user_id' => Auth::id(),
            ]);

            // Update supplier balance
            $this->grnPo->supplier->increment('current_balance', $totalReceivedValue);

            // Update PO status
            $allDone = $this->grnPo->items()->whereRaw('quantity_received < quantity_ordered')->doesntExist();
            $this->grnPo->update(['status' => $allDone ? 'received' : 'partial_received']);
        });

        $this->showGrnModal = false;
        session()->flash('success', 'Goods Receipt Note posted and variant stock updated!');
    }

    public function openPaymentModal(int $supplierId)
    {
        $this->paymentSupplier = Supplier::findOrFail($supplierId);
        $this->paymentAmount = (float) $this->paymentSupplier->current_balance;
        $this->paymentMethod = 'bank_transfer';
        $this->paymentRef = 'NEFT-'.rand(100000, 999999);
        $this->showPaymentModal = true;
    }

    public function saveSupplierPayment()
    {
        if (! $this->paymentSupplier || $this->paymentAmount <= 0) {
            return;
        }

        DB::transaction(function () {
            $paymentNum = 'PAY-'.now()->format('ymd').'-'.rand(100, 999);

            SupplierPayment::create([
                'payment_number' => $paymentNum,
                'supplier_id' => $this->paymentSupplier->id,
                'amount' => $this->paymentAmount,
                'payment_method' => $this->paymentMethod,
                'reference_number' => $this->paymentRef,
                'payment_date' => now(),
                'user_id' => Auth::id(),
            ]);

            $this->paymentSupplier->decrement('current_balance', $this->paymentAmount);
        });

        $this->showPaymentModal = false;
        session()->flash('success', "Payment of ₹{$this->paymentAmount} recorded for {$this->paymentSupplier->name}.");
    }
}
