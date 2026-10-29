<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\CashShift;
use App\Models\Counter;
use App\Models\Customer;
use App\Models\GiftVoucher;
use App\Models\HeldOrder;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PrintService;
use App\Services\TaxService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PosBilling extends Component
{
    // Terminal & Session State
    public ?Branch $branch = null;

    public ?Counter $counter = null;

    public ?CashShift $activeShift = null;

    // Barcode & Search
    public string $barcodeInput = '';

    public string $searchQuery = '';

    public array $searchResults = [];

    public bool $showSearchModal = false;

    // Cart Items
    public array $cart = [];

    public $billDiscountPercent = 0.0;

    public $billDiscountAmount = 0.0;

    public ?int $salespersonId = null;

    // Customer
    public ?Customer $customer = null;

    public string $customerSearch = '';

    public array $customerSearchResults = [];

    public bool $showCustomerModal = false;

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerEmail = '';

    // Held Carts
    public array $heldCarts = [];

    public bool $showHeldCartsModal = false;

    public string $holdCartLabel = '';

    // Checkout & Payment
    public bool $showPaymentModal = false;

    public array $payments = [
        ['method' => 'cash', 'amount' => 0.0, 'reference' => ''],
    ];

    public string $voucherCode = '';

    public ?int $overrideId = null;

    public bool $isProcessingCheckout = false;

    // Completed Sale & Receipt Modal
    public ?Order $completedOrder = null;

    public bool $showReceiptModal = false;

    public string $receiptViewMode = '80mm'; // 80mm, 58mm, A4

    public ?string $escposBase64 = null;

    public function mount()
    {
        $user = Auth::user();
        $this->branch = $user->branch ?: Branch::where('is_main', true)->first() ?: Branch::first();
        $this->counter = Counter::where('branch_id', $this->branch->id)->first();
        $this->activeShift = CashShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        $this->loadHeldCarts();
    }

    public function render()
    {
        $taxService = app(TaxService::class);
        $calculations = $this->calculateTotals($taxService);

        $salespeople = User::where('is_active', true)->get();

        return view('livewire.pos-billing', array_merge($calculations, [
            'salespeople' => $salespeople,
        ]));
    }

    /**
     * Compute current cart totals, discounts, taxes, and round off.
     */
    public function calculateTotals(TaxService $taxService): array
    {
        $rawSubtotal = 0.0;
        $itemDiscountTotal = 0.0;

        foreach ($this->cart as $item) {
            $gross = round((float) $item['unit_price'] * (int) $item['quantity'], 2);
            $rawSubtotal += $gross;

            $discPct = (float) ($item['discount_percent'] ?? 0);
            $discAmt = (float) ($item['discount_amount'] ?? 0);
            $itemDisc = ($discPct > 0) ? round(($gross * $discPct) / 100, 2) : min($gross, $discAmt);
            $itemDiscountTotal += $itemDisc;
        }

        // Bill-level discount
        $discountPercent = is_numeric($this->billDiscountPercent) ? max(0.0, min(100.0, (float) $this->billDiscountPercent)) : 0.0;
        $discountAmount = is_numeric($this->billDiscountAmount) ? max(0.0, (float) $this->billDiscountAmount) : 0.0;

        $netAfterItemDisc = max(0.0, $rawSubtotal - $itemDiscountTotal);
        if ($discountPercent > 0) {
            $totalBillDiscount = round(($netAfterItemDisc * $discountPercent) / 100, 2);
        } else {
            $totalBillDiscount = min($netAfterItemDisc, $discountAmount);
        }

        $taxableValue = 0.0;
        $taxTotal = 0.0;
        $cgstTotal = 0.0;
        $sgstTotal = 0.0;
        $igstTotal = 0.0;

        $storeState = $this->branch->state_code ?: '33';
        $customerState = $this->customer?->state_code ?: $storeState;

        // Proportional allocation of bill discount to compute taxes accurately
        foreach ($this->cart as $item) {
            $gross = round((float) $item['unit_price'] * (int) $item['quantity'], 2);
            $discPct = (float) ($item['discount_percent'] ?? 0);
            $discAmt = (float) ($item['discount_amount'] ?? 0);
            $itemDisc = ($discPct > 0) ? round(($gross * $discPct) / 100, 2) : min($gross, $discAmt);
            $net = $gross - $itemDisc;

            $ratio = ($netAfterItemDisc > 0) ? ($net / $netAfterItemDisc) : 0;
            $lineBillDisc = round($totalBillDiscount * $ratio, 2);
            $lineNet = max(0.0, $net - $lineBillDisc);

            $taxInfo = $taxService->calculateTax(
                $lineNet,
                (float) ($item['tax_rate'] ?? 5.0),
                (bool) ($item['is_tax_inclusive'] ?? true),
                $storeState,
                $customerState
            );

            $taxableValue += $taxInfo['taxable_amount'];
            $taxTotal += $taxInfo['total_tax'];
            $cgstTotal += $taxInfo['cgst_amount'];
            $sgstTotal += $taxInfo['sgst_amount'];
            $igstTotal += $taxInfo['igst_amount'];
        }

        $subtotalWithTax = $taxableValue + $taxTotal;
        $roundOff = $taxService->calculateRoundOff($subtotalWithTax);

        return [
            'subtotal' => round($rawSubtotal, 2),
            'item_discount_total' => round($itemDiscountTotal, 2),
            'bill_discount_total' => round($totalBillDiscount, 2),
            'total_discount' => round($itemDiscountTotal + $totalBillDiscount, 2),
            'taxable_value' => round($taxableValue, 2),
            'tax_total' => round($taxTotal, 2),
            'cgst_total' => round($cgstTotal, 2),
            'sgst_total' => round($sgstTotal, 2),
            'igst_total' => round($igstTotal, 2),
            'round_off_amount' => $roundOff['round_off_amount'],
            'grand_total' => $roundOff['rounded_total'],
            'total_items_count' => count($this->cart),
            'total_quantity_count' => (int) collect($this->cart)->sum('quantity'),
        ];
    }

    /**
     * Handle fast keyboard-wedge barcode scanner input.
     */
    public function handleBarcodeScan()
    {
        $code = trim($this->barcodeInput);
        $this->barcodeInput = '';

        if (empty($code)) {
            return;
        }

        $variant = ProductVariant::with(['product', 'stocks'])
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->orWhereHas('product', fn ($q) => $q->where('article_code', $code))
            ->first();

        if ($variant) {
            $this->addVariantToCart($variant);
            $this->dispatch('scan-success');
        } else {
            $this->dispatch('scan-error');
            session()->flash('error', "Barcode or SKU '{$code}' not found.");
        }
    }

    /**
     * Search product catalogue live.
     */
    public function updatedSearchQuery()
    {
        $query = trim($this->searchQuery);
        if (strlen($query) < 2) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = ProductVariant::with(['product', 'product.brand', 'stocks'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('barcode', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%")
                    ->orWhere('size', 'LIKE', "%{$query}%")
                    ->orWhere('color', 'LIKE', "%{$query}%")
                    ->orWhereHas('product', function ($pq) use ($query) {
                        $pq->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('article_code', 'LIKE', "%{$query}%");
                    });
            })
            ->take(12)
            ->get()
            ->map(function ($v) {
                return [
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
                    'brand' => $v->product->brand?->name ?? 'NextDNA',
                ];
            })
            ->toArray();
    }

    public function selectSearchResult(int $variantId)
    {
        $variant = ProductVariant::with(['product', 'stocks'])->find($variantId);
        if ($variant) {
            $this->addVariantToCart($variant);
            $this->searchQuery = '';
            $this->searchResults = [];
            $this->showSearchModal = false;
            $this->dispatch('scan-success');
        }
    }

    /**
     * Add or increment variant in cart.
     */
    public function addVariantToCart(ProductVariant $variant, int $qty = 1)
    {
        $existingIndex = collect($this->cart)->search(fn ($item) => $item['variant_id'] === $variant->id);

        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['quantity'] += $qty;
            $this->cart[$existingIndex]['line_total'] = round($this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['unit_price'], 2);
        } else {
            $this->cart[] = [
                'variant_id' => $variant->id,
                'name' => $variant->product->name,
                'size' => $variant->size,
                'color' => $variant->color,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'unit_price' => (float) $variant->selling_price,
                'mrp' => (float) $variant->mrp,
                'quantity' => $qty,
                'stock' => $variant->stock_on_hand,
                'tax_rate' => (float) ($variant->product->tax_rate ?? 5.0),
                'is_tax_inclusive' => (bool) ($variant->product->is_tax_inclusive ?? true),
                'discount_percent' => 0.0,
                'discount_amount' => 0.0,
                'line_total' => round((float) $variant->selling_price * $qty, 2),
            ];
        }
    }

    public function incrementQuantity(int $index)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity']++;
            $this->cart[$index]['line_total'] = round($this->cart[$index]['quantity'] * $this->cart[$index]['unit_price'], 2);
        }
    }

    public function decrementQuantity(int $index)
    {
        if (isset($this->cart[$index])) {
            if ($this->cart[$index]['quantity'] > 1) {
                $this->cart[$index]['quantity']--;
                $this->cart[$index]['line_total'] = round($this->cart[$index]['quantity'] * $this->cart[$index]['unit_price'], 2);
            } else {
                $this->removeItem($index);
            }
        }
    }

    public function updateQuantity(int $index, $qty)
    {
        $q = max(1, (int) $qty);
        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity'] = $q;
            $this->cart[$index]['line_total'] = round($q * $this->cart[$index]['unit_price'], 2);
        }
    }

    public function removeItem(int $index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->billDiscountPercent = 0.0;
        $this->billDiscountAmount = 0.0;
        $this->customer = null;
        $this->overrideId = null;
    }

    /**
     * Customer lookup live.
     */
    public function updatedCustomerSearch()
    {
        $q = trim($this->customerSearch);
        if (strlen($q) < 2) {
            $this->customerSearchResults = [];

            return;
        }

        $this->customerSearchResults = Customer::where('phone', 'LIKE', "%{$q}%")
            ->orWhere('name', 'LIKE', "%{$q}%")
            ->take(5)
            ->get()
            ->toArray();
    }

    public function updatedBillDiscountPercent($value): void
    {
        if (is_numeric($value) && (float) $value > 0) {
            $this->billDiscountAmount = 0;
        }
    }

    public function updatedBillDiscountAmount($value): void
    {
        if (is_numeric($value) && (float) $value > 0) {
            $this->billDiscountPercent = 0;
        }
    }

    public function selectCustomer(int $id)
    {
        $this->customer = Customer::find($id);
        $this->customerSearch = '';
        $this->customerSearchResults = [];
        $this->showCustomerModal = false;
    }

    public function removeCustomer()
    {
        $this->customer = null;
    }

    public function createCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|min:2',
            'newCustomerPhone' => 'required|string|min:10',
        ]);

        $customer = Customer::create([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail ?: null,
            'city' => $this->branch->city ?? 'Salem',
            'state' => 'Tamil Nadu',
            'state_code' => '33',
            'customer_group' => 'retail',
        ]);

        $this->customer = $customer;
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->showCustomerModal = false;
        session()->flash('success', "Customer '{$customer->name}' created and selected.");
    }

    /**
     * Hold and Resume Carts.
     */
    public function holdCurrentCart()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cannot hold an empty cart.');

            return;
        }

        $label = $this->holdCartLabel ?: ('Cart #'.(count($this->heldCarts) + 1).' - '.now()->format('h:i A'));

        HeldOrder::create([
            'branch_id' => $this->branch->id,
            'counter_id' => $this->counter?->id,
            'user_id' => Auth::id(),
            'hold_label' => $label,
            'customer_id' => $this->customer?->id,
            'cart_data' => [
                'cart' => $this->cart,
                'bill_discount_percent' => is_numeric($this->billDiscountPercent) ? (float) $this->billDiscountPercent : 0.0,
                'bill_discount_amount' => is_numeric($this->billDiscountAmount) ? (float) $this->billDiscountAmount : 0.0,
            ],
            'held_at' => now(),
        ]);

        $this->clearCart();
        $this->holdCartLabel = '';
        $this->loadHeldCarts();
        session()->flash('success', "Cart held: '{$label}'.");
    }

    public function resumeHeldCart(int $heldId)
    {
        $held = HeldOrder::find($heldId);
        if ($held) {
            $this->cart = $held->cart_data['cart'] ?? [];
            $this->billDiscountPercent = (float) ($held->cart_data['bill_discount_percent'] ?? 0);
            $this->billDiscountAmount = (float) ($held->cart_data['bill_discount_amount'] ?? 0);
            $this->customer = $held->customer_id ? Customer::find($held->customer_id) : null;
            $held->delete();

            $this->loadHeldCarts();
            $this->showHeldCartsModal = false;
            session()->flash('success', "Resumed held cart: '{$held->hold_label}'.");
        }
    }

    public function loadHeldCarts()
    {
        $this->heldCarts = HeldOrder::where('branch_id', $this->branch->id)
            ->latest('held_at')
            ->get()
            ->toArray();
    }

    /**
     * Checkout Modal: setup default payment = grand total.
     */
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty. Scan items first.');

            return;
        }

        $taxService = app(TaxService::class);
        $totals = $this->calculateTotals($taxService);

        $this->payments = [
            ['method' => 'cash', 'amount' => $totals['grand_total'], 'reference' => ''],
        ];

        $this->showPaymentModal = true;
    }

    public function addPaymentRow(string $method = 'upi')
    {
        $this->payments[] = [
            'method' => $method,
            'amount' => 0.0,
            'reference' => '',
        ];
    }

    public function removePaymentRow(int $index)
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
    }

    public function applyGiftVoucher()
    {
        $code = trim($this->voucherCode);
        $voucher = GiftVoucher::where('voucher_code', $code)->first();

        if (! $voucher || ! $voucher->isUsable()) {
            session()->flash('error', "Voucher '{$code}' is invalid or expired.");

            return;
        }

        $this->payments[] = [
            'method' => 'gift_voucher',
            'amount' => (float) $voucher->current_balance,
            'reference' => $code,
            'voucher_code' => $code,
        ];
        $this->voucherCode = '';
        session()->flash('success', "Voucher '{$code}' applied (Balance: ₹{$voucher->current_balance}).");
    }

    /**
     * Complete checkout and generate thermal print receipt.
     */
    public function completeCheckout()
    {
        if ($this->isProcessingCheckout) {
            return;
        }

        $this->isProcessingCheckout = true;

        try {
            $billingService = app(BillingService::class);
            $printService = app(PrintService::class);

            $idempotencyKey = 'IDEMP-'.Auth::id().'-'.Str::uuid();

            $order = $billingService->checkout([
                'branch_id' => $this->branch->id,
                'counter_id' => $this->counter?->id,
                'cash_shift_id' => $this->activeShift?->id,
                'customer_id' => $this->customer?->id,
                'cashier_id' => Auth::id(),
                'salesperson_id' => $this->salespersonId,
                'items' => collect($this->cart)->map(fn ($item) => [
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                ])->toArray(),
                'payments' => $this->payments,
                'bill_discount_percent' => is_numeric($this->billDiscountPercent) ? (float) $this->billDiscountPercent : 0.0,
                'bill_discount_amount' => is_numeric($this->billDiscountAmount) ? (float) $this->billDiscountAmount : 0.0,
                'idempotency_key' => $idempotencyKey,
                'override_id' => $this->overrideId,
            ]);

            // Dispatch print job
            $printJob = $printService->printOrderReceipt($order);
            $this->escposBase64 = $printJob->payload;

            $this->completedOrder = $order;
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;
            $this->clearCart();

            $this->dispatch('scan-success');
            session()->flash('success', "Sale completed successfully! Invoice: {$order->invoice_number}");
        } catch (Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('scan-error');
        } finally {
            $this->isProcessingCheckout = false;
        }
    }

    public function printAgain()
    {
        if ($this->completedOrder) {
            $printService = app(PrintService::class);
            $printService->printOrderReceipt($this->completedOrder, null, true);
            session()->flash('success', 'Reprint job dispatched.');
        }
    }
}
