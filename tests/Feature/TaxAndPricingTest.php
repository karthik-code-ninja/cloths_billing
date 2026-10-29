<?php

namespace Tests\Feature;

use App\Services\PricingService;
use App\Services\TaxService;
use Tests\TestCase;

class TaxAndPricingTest extends TestCase
{
    protected TaxService $taxService;

    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxService = app(TaxService::class);
        $this->pricingService = app(PricingService::class);
    }

    public function test_intra_state_gst_tax_inclusive_calculation_splits_cgst_and_sgst(): void
    {
        // For a ₹1000 tax-inclusive item at 5% GST
        // Taxable = 1000 / 1.05 = 952.38
        // Total Tax = 47.62
        // CGST = 23.81 (2.5%), SGST = 23.81 (2.5%)
        $result = $this->taxService->calculateTax(
            netPrice: 1000.00,
            taxRate: 5.00,
            isTaxInclusive: true,
            storeStateCode: '33',
            customerStateCode: '33'
        );

        $this->assertEquals(952.38, $result['taxable_amount']);
        $this->assertEquals(47.62, $result['total_tax']);
        $this->assertEquals(23.81, $result['cgst_amount']);
        $this->assertEquals(23.81, $result['sgst_amount']);
        $this->assertEquals(0.00, $result['igst_amount']);
        $this->assertFalse($result['is_interstate']);
        $this->assertEquals(1000.00, $result['line_total']);
    }

    public function test_inter_state_gst_tax_applies_full_igst(): void
    {
        // Store in Tamil Nadu (33), Customer in Karnataka (29)
        $result = $this->taxService->calculateTax(
            netPrice: 1000.00,
            taxRate: 5.00,
            isTaxInclusive: true,
            storeStateCode: '33',
            customerStateCode: '29'
        );

        $this->assertTrue($result['is_interstate']);
        $this->assertEquals(0.00, $result['cgst_amount']);
        $this->assertEquals(0.00, $result['sgst_amount']);
        $this->assertEquals(47.62, $result['igst_amount']);
        $this->assertEquals(5.00, $result['igst_rate']);
    }

    public function test_round_off_adjusts_to_nearest_rupee(): void
    {
        // ₹499.40 rounds to ₹499.00 with -0.40 diff
        $down = $this->taxService->calculateRoundOff(499.40);
        $this->assertEquals(499.00, $down['rounded_total']);
        $this->assertEquals(-0.40, $down['round_off_amount']);

        // ₹499.60 rounds to ₹500.00 with +0.40 diff
        $up = $this->taxService->calculateRoundOff(499.60);
        $this->assertEquals(500.00, $up['rounded_total']);
        $this->assertEquals(0.40, $up['round_off_amount']);
    }

    public function test_bill_discount_is_proportionally_allocated_without_penny_drift(): void
    {
        $items = [
            0 => ['subtotal' => 600.00],
            1 => ['subtotal' => 400.00],
        ];

        // Total bill discount = ₹100.
        // Item 0 (60%): ₹60
        // Item 1 (40%): ₹40
        $allocations = $this->pricingService->allocateBillDiscount($items, 100.00);

        $this->assertEquals(60.00, $allocations[0]);
        $this->assertEquals(40.00, $allocations[1]);
        $this->assertEquals(100.00, array_sum($allocations));
    }
}
