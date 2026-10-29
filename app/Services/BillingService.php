<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ManagerOverride;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\ProductVariant;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        protected TaxService $taxService,
        protected PricingService $pricingService,
        protected InventoryService $inventoryService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Complete a sale transaction atomically.
     *
     * @param array{
     *     branch_id: int,
     *     counter_id?: int,
     *     cash_shift_id?: int,
     *     customer_id?: int,
     *     cashier_id: int,
     *     salesperson_id?: int,
     *     items: array<int, array{variant_id: int, quantity: int, unit_price: float, discount_percent?: float, discount_amount?: float}>,
     *     payments: array<int, array{method: string, amount: float, reference?: string, voucher_code?: string}>,
     *     bill_discount_amount?: float,
     *     bill_discount_percent?: float,
     *     idempotency_key?: string,
     *     notes?: string,
     *     order_type?: string,
     *     override_id?: int
     * } $data
     *
     * @throws Exception
     */
    public function checkout(array $data): Order
    {
        // 1. Idempotency Check: prevent duplicate billing on network re-submission
        if (! empty($data['idempotency_key'])) {
            $existingOrder = Order::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existingOrder) {
                return $existingOrder;
            }
        }

        $cashier = User::findOrFail($data['cashier_id']);
        $branch = Branch::findOrFail($data['branch_id']);
        $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $orderType = $data['order_type'] ?? 'sale';

        if (empty($data['items'])) {
            throw new Exception('Cannot checkout an empty cart.');
        }

        // 2. Validate Discounter permissions
        $billDiscountAmount = (float) ($data['bill_discount_amount'] ?? 0.0);
        $billDiscountPercent = (float) ($data['bill_discount_percent'] ?? 0.0);

        // Pre-fetch variants to compute preliminary totals
        $variants = ProductVariant::with(['product', 'product.category'])->findMany(
            collect($data['items'])->pluck('variant_id')
        )->keyBy('id');

        $lineCalculations = [];
        $rawSubtotal = 0.0;
        $itemDiscountTotal = 0.0;

        foreach ($data['items'] as $index => $itemData) {
            $variantId = (int) $itemData['variant_id'];
            /** @var ProductVariant $variant */
            $variant = $variants->get($variantId);
            if (! $variant) {
                throw new Exception("Variant ID {$variantId} not found.");
            }

            $qty = (int) $itemData['quantity'];
            $unitPrice = isset($itemData['unit_price']) ? (float) $itemData['unit_price'] : (float) $variant->selling_price;
            $discPct = (float) ($itemData['discount_percent'] ?? 0);
            $discAmt = (float) ($itemData['discount_amount'] ?? 0);

            // Check if cashier discount exceeds limit
            if ($discPct > (float) $cashier->max_discount_percentage && ! $cashier->isManager()) {
                $hasOverride = ! empty($data['override_id']) && ManagerOverride::where('id', $data['override_id'])->where('status', 'approved')->exists();
                if (! $hasOverride) {
                    throw new Exception("Item discount ({$discPct}%) exceeds cashier maximum permitted discount ({$cashier->max_discount_percentage}%). Manager approval required.");
                }
            }

            $gross = round($unitPrice * $qty, 2);
            $calcDisc = ($discPct > 0) ? round(($gross * $discPct) / 100, 2) : min($gross, $discAmt);
            $netItem = round($gross - $calcDisc, 2);

            $rawSubtotal += $gross;
            $itemDiscountTotal += $calcDisc;

            $lineCalculations[$index] = [
                'variant' => $variant,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'gross' => $gross,
                'item_discount' => $calcDisc,
                'item_discount_percent' => $discPct,
                'net_subtotal' => $netItem,
                'tax_rate' => (float) ($variant->product->tax_rate ?? 5.0),
                'is_tax_inclusive' => (bool) ($variant->product->is_tax_inclusive ?? true),
                'hsn_code' => $variant->product->hsn_code ?? '6203',
            ];
        }

        // Apply bill-level discount
        if ($billDiscountPercent > 0) {
            $totalBillDiscount = round(($rawSubtotal * $billDiscountPercent) / 100, 2);
        } else {
            $totalBillDiscount = min($rawSubtotal - $itemDiscountTotal, $billDiscountAmount);
        }

        if ($totalBillDiscount > 0 && ! $cashier->isManager()) {
            $billDiscPct = ($rawSubtotal > 0) ? round(($totalBillDiscount / $rawSubtotal) * 100, 2) : 0;
            if ($billDiscPct > (float) $cashier->max_discount_percentage) {
                $hasOverride = ! empty($data['override_id']) && ManagerOverride::where('id', $data['override_id'])->where('status', 'approved')->exists();
                if (! $hasOverride) {
                    throw new Exception('Bill discount exceeds cashier maximum limit. Manager approval required.');
                }
            }
        }

        // Proportional distribution of bill discount
        $allocatedBillDiscounts = $this->pricingService->allocateBillDiscount($lineCalculations, $totalBillDiscount);

        // Calculate final line items, taxes, and order totals
        $finalLines = [];
        $taxableTotal = 0.0;
        $taxTotal = 0.0;
        $cgstTotal = 0.0;
        $sgstTotal = 0.0;
        $igstTotal = 0.0;
        $netSubtotalSum = 0.0;

        $storeState = $branch->state_code ?: '33';
        $customerState = $customer?->state_code ?: $storeState;

        foreach ($lineCalculations as $idx => $line) {
            $allocBillDisc = $allocatedBillDiscounts[$idx] ?? 0.0;
            $lineNetAfterAllDiscounts = max(0.0, round($line['net_subtotal'] - $allocBillDisc, 2));

            $taxInfo = $this->taxService->calculateTax(
                $lineNetAfterAllDiscounts,
                $line['tax_rate'],
                $line['is_tax_inclusive'],
                $storeState,
                $customerState
            );

            $taxableTotal += $taxInfo['taxable_amount'];
            $taxTotal += $taxInfo['total_tax'];
            $cgstTotal += $taxInfo['cgst_amount'];
            $sgstTotal += $taxInfo['sgst_amount'];
            $igstTotal += $taxInfo['igst_amount'];
            $netSubtotalSum += $taxInfo['line_total'];

            $finalLines[$idx] = array_merge($line, [
                'allocated_bill_discount' => $allocBillDisc,
                'tax_info' => $taxInfo,
            ]);
        }

        $roundOff = $this->taxService->calculateRoundOff($netSubtotalSum);
        $grandTotal = $roundOff['rounded_total'];
        $roundOffAmount = $roundOff['round_off_amount'];

        // 3. Process Payments
        $processedPayments = $this->paymentService->processPayments(
            $data['payments'] ?? [],
            $grandTotal,
            $customer
        );

        $totalPaid = $processedPayments['total_paid'];
        $balance = max(0.0, round($grandTotal - $totalPaid, 2));
        $change = max(0.0, round($totalPaid - $grandTotal, 2));
        $isCreditSale = ($balance > 0);

        // 4. ATOMIC DATABASE TRANSACTION
        return DB::transaction(function () use (
            $data,
            $cashier,
            $branch,
            $customer,
            $orderType,
            $rawSubtotal,
            $itemDiscountTotal,
            $totalBillDiscount,
            $taxableTotal,
            $taxTotal,
            $cgstTotal,
            $sgstTotal,
            $igstTotal,
            $roundOffAmount,
            $grandTotal,
            $totalPaid,
            $balance,
            $change,
            $isCreditSale,
            $finalLines,
            $processedPayments
        ) {
            $invoiceNumber = $this->generateSequentialInvoiceNumber($branch);

            // Snapshot customer details
            $customerSnapshot = $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'gstin' => $customer->gstin,
                'customer_group' => $customer->customer_group,
            ] : null;

            $taxBreakdown = [
                'cgst' => round($cgstTotal, 2),
                'sgst' => round($sgstTotal, 2),
                'igst' => round($igstTotal, 2),
                'total_tax' => round($taxTotal, 2),
            ];

            // Create Order
            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'order_type' => $orderType,
                'branch_id' => $branch->id,
                'counter_id' => $data['counter_id'] ?? null,
                'cash_shift_id' => $data['cash_shift_id'] ?? null,
                'customer_id' => $customer?->id,
                'cashier_id' => $cashier->id,
                'salesperson_id' => $data['salesperson_id'] ?? null,
                'status' => 'completed',
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'subtotal' => round($rawSubtotal, 2),
                'item_discount_total' => round($itemDiscountTotal, 2),
                'bill_discount_total' => round($totalBillDiscount, 2),
                'total_discount' => round($itemDiscountTotal + $totalBillDiscount, 2),
                'taxable_value' => round($taxableTotal, 2),
                'tax_total' => round($taxTotal, 2),
                'cgst_total' => round($cgstTotal, 2),
                'sgst_total' => round($sgstTotal, 2),
                'igst_total' => round($igstTotal, 2),
                'round_off_amount' => $roundOffAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => min($grandTotal, $totalPaid),
                'balance_amount' => $balance,
                'change_amount' => $change,
                'is_credit_sale' => $isCreditSale,
                'customer_snapshot' => $customerSnapshot,
                'tax_breakdown' => $taxBreakdown,
                'notes' => $data['notes'] ?? null,
                'invoice_date' => now(),
            ]);

            $defaultLocation = $this->inventoryService->getDefaultLocation($branch->id, 'retail');

            // Create Order Items and update stock
            foreach ($finalLines as $line) {
                /** @var ProductVariant $variant */
                $variant = $line['variant'];
                $taxInfo = $line['tax_info'];

                $variantSnapshot = [
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                ];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_details' => $variantSnapshot,
                    'hsn_code' => $line['hsn_code'],
                    'quantity' => $line['quantity'],
                    'unit_selling_price' => $line['unit_price'],
                    'mrp' => $variant->mrp,
                    'unit_cost' => $variant->purchase_cost,
                    'item_discount_amount' => $line['item_discount'],
                    'item_discount_percent' => $line['item_discount_percent'],
                    'allocated_bill_discount' => $line['allocated_bill_discount'],
                    'tax_rate' => $line['tax_rate'],
                    'is_tax_inclusive' => $line['is_tax_inclusive'],
                    'taxable_amount' => $taxInfo['taxable_amount'],
                    'cgst_amount' => $taxInfo['cgst_amount'],
                    'sgst_amount' => $taxInfo['sgst_amount'],
                    'igst_amount' => $taxInfo['igst_amount'],
                    'total_tax' => $taxInfo['total_tax'],
                    'line_total' => $taxInfo['line_total'],
                ]);

                // Deduct stock if this is a completed sale
                if ($orderType === 'sale') {
                    $this->inventoryService->decrementStock(
                        $variant,
                        $line['quantity'],
                        $defaultLocation->id,
                        'sale',
                        'Order',
                        $order->id,
                        "Sale on Invoice #{$order->invoice_number}",
                        $cashier->id
                    );
                }
            }

            // Create Order Payments
            foreach ($processedPayments['payments_data'] as $paymentRecord) {
                OrderPayment::create(array_merge($paymentRecord, [
                    'order_id' => $order->id,
                    'cash_shift_id' => $data['cash_shift_id'] ?? null,
                ]));
            }

            // Commit store credit & voucher usage
            $this->paymentService->commitDeductions($processedPayments, $customer);

            // Update customer totals & award loyalty
            if ($customer && $orderType === 'sale') {
                $customer->increment('total_purchases_count');
                $customer->increment('total_spent', $grandTotal);
                $this->paymentService->awardLoyaltyPoints($customer, $grandTotal, $order->id);
            }

            return $order->load(['items', 'payments', 'customer', 'cashier']);
        });
    }

    /**
     * Generate sequential invoice numbers with year/month prefix: e.g. INV-202610-00001
     * Protected against race conditions using table locking.
     */
    public function generateSequentialInvoiceNumber(Branch $branch): string
    {
        $invPrefix = strtoupper((string) AppSetting::get('invoice_prefix', 'INV'));
        $prefix = "{$invPrefix}-".now()->format('Ym');
        $branchCode = strtoupper($branch->code ?? 'MAIN');

        $latestOrder = Order::where('invoice_number', 'LIKE', "{$prefix}-{$branchCode}-%")
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        if ($latestOrder) {
            $parts = explode('-', $latestOrder->invoice_number);
            $seq = (int) end($parts);
            $nextSeq = str_pad((string) ($seq + 1), 5, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '00001';
        }

        return "{$prefix}-{$branchCode}-{$nextSeq}";
    }
}
