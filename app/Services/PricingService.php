<?php

namespace App\Services;

class PricingService
{
    public function __construct(
        protected TaxService $taxService
    ) {}

    /**
     * Calculate line item totals with discounts and taxes.
     *
     * @param array{
     *     unit_price: float,
     *     quantity: int,
     *     discount_percent?: float,
     *     discount_amount?: float,
     *     tax_rate?: float,
     *     is_tax_inclusive?: bool
     * } $item
     * @return array{
     *     gross_total: float,
     *     discount_amount: float,
     *     net_total: float,
     *     taxable_amount: float,
     *     tax_amount: float,
     *     tax_details: array
     * }
     */
    public function calculateItemLine(array $item, string $storeStateCode = '33', ?string $customerStateCode = '33'): array
    {
        $unitPrice = (float) $item['unit_price'];
        $quantity = (int) $item['quantity'];
        $grossTotal = round($unitPrice * $quantity, 2);

        $discountPercent = (float) ($item['discount_percent'] ?? 0);
        $discountAmount = (float) ($item['discount_amount'] ?? 0);

        if ($discountPercent > 0) {
            $calcDiscount = round(($grossTotal * $discountPercent) / 100, 2);
            $itemDiscount = min($grossTotal, $calcDiscount);
        } else {
            $itemDiscount = min($grossTotal, $discountAmount);
        }

        $netTotal = round($grossTotal - $itemDiscount, 2);
        $taxRate = (float) ($item['tax_rate'] ?? 5.0);
        $isInclusive = (bool) ($item['is_tax_inclusive'] ?? true);

        $taxDetails = $this->taxService->calculateTax(
            $netTotal,
            $taxRate,
            $isInclusive,
            $storeStateCode,
            $customerStateCode
        );

        return [
            'gross_total' => $grossTotal,
            'discount_amount' => $itemDiscount,
            'net_total' => $taxDetails['line_total'],
            'taxable_amount' => $taxDetails['taxable_amount'],
            'tax_amount' => $taxDetails['total_tax'],
            'tax_details' => $taxDetails,
        ];
    }

    /**
     * Distribute a bill-level discount proportionally across line items.
     * This ensures each returned item has a traceable, non-skewed cost basis.
     *
     * @param  array<int, array{subtotal: float}>  $items
     * @return array<int, float> array of allocated discounts keyed by item index
     */
    public function allocateBillDiscount(array $items, float $totalBillDiscount): array
    {
        $allocations = [];
        $totalItemsSubtotal = 0.0;

        foreach ($items as $item) {
            $totalItemsSubtotal += (float) ($item['net_subtotal'] ?? $item['subtotal'] ?? 0);
        }

        if ($totalItemsSubtotal <= 0 || $totalBillDiscount <= 0) {
            foreach ($items as $key => $item) {
                $allocations[$key] = 0.00;
            }

            return $allocations;
        }

        $allocatedSum = 0.0;
        $keys = array_keys($items);
        $lastKey = end($keys);

        foreach ($items as $key => $item) {
            if ($key === $lastKey) {
                // Give remainder to the last item to avoid penny drift
                $allocations[$key] = round($totalBillDiscount - $allocatedSum, 2);
            } else {
                $itemSubtotal = (float) ($item['net_subtotal'] ?? $item['subtotal'] ?? 0);
                $ratio = $itemSubtotal / $totalItemsSubtotal;
                $portion = round($totalBillDiscount * $ratio, 2);
                $allocations[$key] = $portion;
                $allocatedSum += $portion;
            }
        }

        return $allocations;
    }
}
