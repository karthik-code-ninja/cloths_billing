<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ReturnItem;
use App\Models\ReturnOrder;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected BillingService $billingService
    ) {}

    /**
     * Process an item return or refund safely.
     *
     * @param array{
     *     original_order_id?: int,
     *     branch_id: int,
     *     user_id: int,
     *     approved_by_id?: int,
     *     refund_method: string, // cash, store_credit, original_method, exchange_offset
     *     reason: string,
     *     is_no_receipt?: bool,
     *     items: array<int, array{
     *         order_item_id?: int,
     *         variant_id: int,
     *         quantity: int,
     *         condition: string, // resellable, damaged, defect
     *         refund_price?: float
     *     }>
     * } $data
     *
     * @throws Exception
     */
    public function processReturn(array $data): ReturnOrder
    {
        $user = User::findOrFail($data['user_id']);
        $isNoReceipt = (bool) ($data['is_no_receipt'] ?? false);
        $originalOrder = ! empty($data['original_order_id']) ? Order::with('items')->find($data['original_order_id']) : null;

        if ($isNoReceipt) {
            // No-receipt return requires manager permission or approval
            if (! $user->isManager()) {
                $hasApproval = ! empty($data['approved_by_id']) && User::where('id', $data['approved_by_id'])->whereIn('role', ['admin', 'manager'])->exists();
                if (! $hasApproval) {
                    throw new Exception('No-receipt return requires manager approval.');
                }
            }
        } elseif (! $originalOrder) {
            throw new Exception('Original order must be provided for a receipt-based return.');
        }

        if (empty($data['items'])) {
            throw new Exception('No items specified for return.');
        }

        return DB::transaction(function () use ($data, $user, $originalOrder, $isNoReceipt) {
            $subtotalReturned = 0.0;
            $taxReturned = 0.0;
            $totalRefundAmount = 0.0;
            $preparedReturnItems = [];

            $retailLocation = $this->inventoryService->getDefaultLocation($data['branch_id'], 'retail');
            $damagedLocation = $this->inventoryService->getDefaultLocation($data['branch_id'], 'damaged');

            foreach ($data['items'] as $itemData) {
                $variantId = (int) $itemData['variant_id'];
                $variant = ProductVariant::with('product')->findOrFail($variantId);
                $qty = (int) $itemData['quantity'];
                $condition = $itemData['condition'] ?? 'resellable';

                if ($qty <= 0) {
                    throw new Exception('Returned quantity must be greater than zero.');
                }

                $orderItem = null;
                if (! $isNoReceipt && ! empty($itemData['order_item_id'])) {
                    $orderItem = OrderItem::where('id', $itemData['order_item_id'])
                        ->where('order_id', $originalOrder->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $orderItem) {
                        throw new Exception("Order item ID {$itemData['order_item_id']} does not belong to Order #{$originalOrder->invoice_number}.");
                    }

                    // Enforce maximum return quantity constraint: cannot exceed sold minus previously returned
                    $availableToReturn = $orderItem->returnable_quantity;
                    if ($qty > $availableToReturn) {
                        throw new Exception(
                            "Cannot return {$qty} units of {$variant->fullName}. Eligible quantity remaining: {$availableToReturn}."
                        );
                    }

                    $unitRefundPrice = $orderItem->effective_unit_refund_price;
                    $taxRate = (float) $orderItem->tax_rate;
                } else {
                    // No receipt fallback price
                    $unitRefundPrice = isset($itemData['refund_price']) ? (float) $itemData['refund_price'] : (float) $variant->selling_price;
                    $taxRate = (float) ($variant->product->tax_rate ?? 5.0);
                }

                $lineRefundTotal = round($unitRefundPrice * $qty, 2);
                $taxPart = round(($lineRefundTotal * $taxRate) / (100 + $taxRate), 2);
                $taxablePart = round($lineRefundTotal - $taxPart, 2);

                $subtotalReturned += $taxablePart;
                $taxReturned += $taxPart;
                $totalRefundAmount += $lineRefundTotal;

                // Destination location based on condition
                $targetLocation = ($condition === 'resellable') ? $retailLocation : $damagedLocation;

                $preparedReturnItems[] = [
                    'order_item' => $orderItem,
                    'variant' => $variant,
                    'quantity' => $qty,
                    'unit_refund_price' => $unitRefundPrice,
                    'tax_rate' => $taxRate,
                    'tax_refund_amount' => $taxPart,
                    'line_refund_total' => $lineRefundTotal,
                    'condition' => $condition,
                    'target_location' => $targetLocation,
                ];
            }

            $returnNumber = 'RET-'.now()->format('Ym').'-'.str_pad((string) (ReturnOrder::count() + 1), 5, '0', STR_PAD_LEFT);
            $customerId = $originalOrder?->customer_id ?? $data['customer_id'] ?? null;

            $returnOrder = ReturnOrder::create([
                'return_number' => $returnNumber,
                'original_order_id' => $originalOrder?->id,
                'customer_id' => $customerId,
                'branch_id' => $data['branch_id'],
                'user_id' => $user->id,
                'approved_by_id' => $data['approved_by_id'] ?? null,
                'subtotal_returned' => round($subtotalReturned, 2),
                'tax_returned' => round($taxReturned, 2),
                'total_refund_amount' => round($totalRefundAmount, 2),
                'refund_method' => $data['refund_method'],
                'reason' => $data['reason'] ?? 'Customer Return',
                'status' => 'completed',
                'is_no_receipt' => $isNoReceipt,
                'return_date' => now(),
            ]);

            // Save line items, increment stock, and update original order item returned_quantity
            foreach ($preparedReturnItems as $item) {
                ReturnItem::create([
                    'return_id' => $returnOrder->id,
                    'original_order_item_id' => $item['order_item']?->id,
                    'product_variant_id' => $item['variant']->id,
                    'quantity' => $item['quantity'],
                    'unit_refund_price' => $item['unit_refund_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_refund_amount' => $item['tax_refund_amount'],
                    'line_refund_total' => $item['line_refund_total'],
                    'condition' => $item['condition'],
                    'routed_location_id' => $item['target_location']->id,
                ]);

                // Update original order item returned quantity
                if ($item['order_item']) {
                    $item['order_item']->increment('returned_quantity', $item['quantity']);
                }

                // Restore stock into appropriate location
                $movementType = ($item['condition'] === 'resellable') ? 'return' : 'damaged';
                $this->inventoryService->incrementStock(
                    $item['variant'],
                    $item['quantity'],
                    $item['target_location']->id,
                    $movementType,
                    'ReturnOrder',
                    $returnOrder->id,
                    "Return #{$returnOrder->return_number} ({$item['condition']})",
                    $user->id
                );
            }

            // If refund method is store credit, credit the customer's account
            if ($data['refund_method'] === 'store_credit' && $customerId) {
                $cust = Customer::find($customerId);
                $cust?->increment('store_credit_balance', $totalRefundAmount);
            }

            return $returnOrder->load(['items', 'originalOrder']);
        });
    }

    /**
     * Process an exchange: combines return of old items and billing of new items.
     *
     * @param array{
     *     return_data: array,
     *     new_items: array<int, array{variant_id: int, quantity: int, unit_price?: float}>,
     *     payment_method_for_diff?: string
     * } $data
     * @return array{return_order: ReturnOrder, exchange_order: ?Order, difference: float, action: string}
     */
    public function processExchange(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Process return with refund_method = 'exchange_offset'
            $returnData = $data['return_data'];
            $returnData['refund_method'] = 'exchange_offset';
            $returnOrder = $this->processReturn($returnData);

            $refundCredit = (float) $returnOrder->total_refund_amount;

            // 2. Prepare checkout data for new items
            $newItems = $data['new_items'];
            $branchId = $returnData['branch_id'];
            $userId = $returnData['user_id'];
            $customerId = $returnOrder->customer_id;

            // Calculate preliminary cost of new items
            $newItemsTotal = 0.0;
            foreach ($newItems as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $price = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $variant->selling_price;
                $newItemsTotal += round($price * (int) $item['quantity'], 2);
            }

            $diff = round($newItemsTotal - $refundCredit, 2);
            $exchangeOrder = null;

            if ($diff > 0) {
                // Customer owes money: offset exchange credit and collect remaining difference
                $checkoutData = [
                    'branch_id' => $branchId,
                    'cashier_id' => $userId,
                    'customer_id' => $customerId,
                    'items' => $newItems,
                    'payments' => [
                        [
                            'method' => 'store_credit', // represented as exchange credit offset
                            'amount' => $refundCredit,
                            'reference' => "Exchange offset from Return #{$returnOrder->return_number}",
                        ],
                        [
                            'method' => $data['payment_method_for_diff'] ?? 'cash',
                            'amount' => $diff,
                            'reference' => 'Exchange difference balance',
                        ],
                    ],
                    'notes' => "Exchange for Return #{$returnOrder->return_number}",
                ];

                // Temporarily grant virtual store credit to cover the offset
                if ($customerId) {
                    Customer::find($customerId)?->increment('store_credit_balance', $refundCredit);
                }

                $exchangeOrder = $this->billingService->checkout($checkoutData);
                $returnOrder->exchange_order_id = $exchangeOrder->id;
                $returnOrder->save();

                $action = 'collected_difference';
            } elseif ($diff < 0) {
                // Store owes refund: new items are cheaper than returned items
                $refundDifference = abs($diff);

                $checkoutData = [
                    'branch_id' => $branchId,
                    'cashier_id' => $userId,
                    'customer_id' => $customerId,
                    'items' => $newItems,
                    'payments' => [
                        [
                            'method' => 'cash',
                            'amount' => $newItemsTotal,
                            'reference' => "Full exchange offset from Return #{$returnOrder->return_number}",
                        ],
                    ],
                    'notes' => "Exchange for Return #{$returnOrder->return_number}",
                ];

                $exchangeOrder = $this->billingService->checkout($checkoutData);
                $returnOrder->exchange_order_id = $exchangeOrder->id;
                $returnOrder->save();

                // Credit the remainder to store credit
                if ($customerId) {
                    Customer::find($customerId)?->increment('store_credit_balance', $refundDifference);
                }

                $action = 'refunded_difference';
            } else {
                // Exact same price
                $checkoutData = [
                    'branch_id' => $branchId,
                    'cashier_id' => $userId,
                    'customer_id' => $customerId,
                    'items' => $newItems,
                    'payments' => [
                        [
                            'method' => 'cash',
                            'amount' => $newItemsTotal,
                            'reference' => "Even exchange for Return #{$returnOrder->return_number}",
                        ],
                    ],
                    'notes' => "Even exchange for Return #{$returnOrder->return_number}",
                ];

                $exchangeOrder = $this->billingService->checkout($checkoutData);
                $returnOrder->exchange_order_id = $exchangeOrder->id;
                $returnOrder->save();

                $action = 'even_exchange';
            }

            return [
                'return_order' => $returnOrder,
                'exchange_order' => $exchangeOrder,
                'difference' => $diff,
                'action' => $action,
            ];
        });
    }
}
