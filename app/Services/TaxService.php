<?php

namespace App\Services;

class TaxService
{
    public const DEFAULT_STATE_CODE = '33'; // Tamil Nadu

    /**
     * Calculate Indian GST details for an item.
     * Uses bcmath to avoid floating-point inaccuracies.
     *
     * @return array{
     *     taxable_amount: float,
     *     tax_rate: float,
     *     is_interstate: bool,
     *     cgst_rate: float,
     *     cgst_amount: float,
     *     sgst_rate: float,
     *     sgst_amount: float,
     *     igst_rate: float,
     *     igst_amount: float,
     *     total_tax: float,
     *     line_total: float
     * }
     */
    public function calculateTax(
        float $netPrice,
        float $taxRate,
        bool $isTaxInclusive = true,
        string $storeStateCode = self::DEFAULT_STATE_CODE,
        ?string $customerStateCode = self::DEFAULT_STATE_CODE
    ): array {
        $customerState = ! empty($customerStateCode) ? $customerStateCode : $storeStateCode;
        $isInterstate = ($storeStateCode !== $customerState);

        $priceStr = number_format($netPrice, 4, '.', '');
        $rateStr = number_format($taxRate, 4, '.', '');

        if ($taxRate <= 0) {
            $val = round($netPrice, 2);

            return [
                'taxable_amount' => $val,
                'tax_rate' => 0.0,
                'is_interstate' => $isInterstate,
                'cgst_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_rate' => 0.0,
                'sgst_amount' => 0.0,
                'igst_rate' => 0.0,
                'igst_amount' => 0.0,
                'total_tax' => 0.0,
                'line_total' => $val,
            ];
        }

        if ($isTaxInclusive) {
            // Precise integer paise calculation
            // 1 INR = 100 Paise, taxRate in basis points (5.00% = 500)
            $netPricePaise = (int) round($netPrice * 100);
            $taxRateBps = (int) round($taxRate * 100);

            // taxablePaise = (netPricePaise * 10000) / (10000 + taxRateBps)
            $taxablePaise = (int) round(($netPricePaise * 10000) / (10000 + $taxRateBps));
            $taxable = round($taxablePaise / 100, 2);

            $totalTaxPaise = $netPricePaise - $taxablePaise;
            $totalTax = round($totalTaxPaise / 100, 2);
            $lineTotal = round($netPricePaise / 100, 2);
        } else {
            $netPricePaise = (int) round($netPrice * 100);
            $taxRateBps = (int) round($taxRate * 100);

            $taxPaise = (int) round(($netPricePaise * $taxRateBps) / 10000);
            $taxable = round($netPricePaise / 100, 2);
            $totalTax = round($taxPaise / 100, 2);
            $lineTotal = round(($netPricePaise + $taxPaise) / 100, 2);
        }

        if ($isInterstate) {
            $cgstRate = 0.0;
            $cgstAmount = 0.0;
            $sgstRate = 0.0;
            $sgstAmount = 0.0;
            $igstRate = $taxRate;
            $igstAmount = $totalTax;
        } else {
            $halfRate = round($taxRate / 2, 2);
            $cgstRate = $halfRate;
            $sgstRate = $halfRate;

            // Split total tax evenly; allocate penny rounding to SGST if odd
            $cgstAmount = round($totalTax / 2, 2);
            $sgstAmount = round($totalTax - $cgstAmount, 2);
            $igstRate = 0.0;
            $igstAmount = 0.0;
        }

        return [
            'taxable_amount' => $taxable,
            'tax_rate' => round($taxRate, 2),
            'is_interstate' => $isInterstate,
            'cgst_rate' => $cgstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_rate' => $sgstRate,
            'sgst_amount' => $sgstAmount,
            'igst_rate' => $igstRate,
            'igst_amount' => $igstAmount,
            'total_tax' => $totalTax,
            'line_total' => $lineTotal,
        ];
    }

    /**
     * Compute invoice rounding adjustment to nearest rupee.
     * e.g., 499.40 => rounded 499.00, diff -0.40
     *       499.60 => rounded 500.00, diff +0.40
     */
    public function calculateRoundOff(float $amount): array
    {
        $rounded = round($amount);
        $diff = round($rounded - $amount, 2);

        return [
            'rounded_total' => (float) $rounded,
            'round_off_amount' => $diff,
        ];
    }
}
