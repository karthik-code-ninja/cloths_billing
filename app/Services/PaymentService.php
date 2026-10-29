<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\GiftVoucher;
use App\Models\LoyaltyTransaction;
use Exception;

class PaymentService
{
    /**
     * Process and validate a payment breakdown.
     *
     * @param  array<int, array{method: string, amount: float, reference?: string, voucher_code?: string}>  $payments
     * @return array{
     *     total_paid: float,
     *     payments_data: array,
     *     store_credit_used: float,
     *     voucher_deductions: array
     * }
     *
     * @throws Exception
     */
    public function processPayments(array $payments, float $payableAmount, ?Customer $customer = null): array
    {
        $totalPaid = 0.0;
        $paymentsData = [];
        $storeCreditUsed = 0.0;
        $voucherDeductions = [];

        foreach ($payments as $payment) {
            $method = $payment['method'];
            $amount = round((float) $payment['amount'], 2);

            if ($amount <= 0) {
                continue;
            }

            if ($method === 'store_credit') {
                if (! $customer) {
                    throw new Exception('Store credit can only be used by registered customers.');
                }
                if ($customer->store_credit_balance < $amount) {
                    throw new Exception(
                        "Insufficient store credit balance. Available: ₹{$customer->store_credit_balance}, Requested: ₹{$amount}"
                    );
                }
                $storeCreditUsed += $amount;
            } elseif ($method === 'gift_voucher') {
                $code = trim($payment['voucher_code'] ?? $payment['reference'] ?? '');
                $voucher = GiftVoucher::where('voucher_code', $code)->first();

                if (! $voucher || ! $voucher->isUsable()) {
                    throw new Exception("Gift voucher '{$code}' is invalid or expired.");
                }
                if ($voucher->current_balance < $amount) {
                    throw new Exception(
                        "Gift voucher '{$code}' has insufficient balance. Available: ₹{$voucher->current_balance}, Requested: ₹{$amount}"
                    );
                }
                $voucherDeductions[] = [
                    'voucher' => $voucher,
                    'amount' => $amount,
                ];
            }

            $totalPaid = round($totalPaid + $amount, 2);
            $paymentsData[] = [
                'payment_method' => $method,
                'amount' => $amount,
                'reference_number' => $payment['reference'] ?? null,
                'is_verified' => false, // manual unless gateway integration confirms
                'received_at' => now(),
            ];
        }

        return [
            'total_paid' => $totalPaid,
            'payments_data' => $paymentsData,
            'store_credit_used' => $storeCreditUsed,
            'voucher_deductions' => $voucherDeductions,
        ];
    }

    /**
     * Commit customer balance and voucher deductions within an ongoing transaction.
     */
    public function commitDeductions(array $processedPayments, ?Customer $customer = null): void
    {
        if ($customer && $processedPayments['store_credit_used'] > 0) {
            $customer->decrement('store_credit_balance', $processedPayments['store_credit_used']);
        }

        foreach ($processedPayments['voucher_deductions'] as $item) {
            /** @var GiftVoucher $voucher */
            $voucher = $item['voucher'];
            $amount = $item['amount'];

            $newBal = round($voucher->current_balance - $amount, 2);
            $voucher->current_balance = $newBal;
            if ($newBal <= 0) {
                $voucher->status = 'redeemed';
            }
            $voucher->save();
        }
    }

    /**
     * Award loyalty points to customer on sale.
     * Default rule: 1 point per ₹100 spent.
     */
    public function awardLoyaltyPoints(Customer $customer, float $grandTotal, int $orderId): int
    {
        $pointsEarned = (int) floor($grandTotal / 100);

        if ($pointsEarned > 0) {
            $customer->increment('loyalty_points', $pointsEarned);

            LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'points' => $pointsEarned,
                'type' => 'earned_sale',
                'reference_type' => 'Order',
                'reference_id' => $orderId,
                'notes' => "Points earned on Order #{$orderId}",
            ]);
        }

        return $pointsEarned;
    }
}
