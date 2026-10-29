<?php

namespace Tests\Feature;

use App\Livewire\PosBilling;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\User;
use App\Services\TaxService;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class SaleReceiptPrintTest extends TestCase
{
    public function test_receipt_contains_sale_details_and_thanks_without_modal_controls(): void
    {
        $branch = new Branch([
            'name' => 'NextDNA Fashion Megastore',
            'address' => '124 Meyyanur Bypass Road, Salem',
            'phone' => '04272445566',
            'gstin' => '33AAAAA0000A1Z5',
            'state_code' => '33',
        ]);
        $order = new Order([
            'invoice_number' => 'INV-202610-SLM01-00007',
            'invoice_date' => '2026-10-29 12:50:00',
            'subtotal' => 449,
            'grand_total' => 449,
            'total_discount' => 0,
            'cgst_total' => 10.69,
            'sgst_total' => 10.69,
            'round_off_amount' => 0,
            'change_amount' => 0,
        ]);
        $order->setRelation('branch', $branch);
        $order->setRelation('cashier', new User(['name' => 'Senthil Nathan']));
        $order->setRelation('counter', null);
        $order->setRelation('customer', null);
        $order->setRelation('items', collect([new OrderItem([
            'product_name' => '1890 Flannel Shirt',
            'variant_details' => ['size' => 'L', 'color' => 'Standard'],
            'quantity' => 1,
            'unit_selling_price' => 449,
            'line_total' => 449,
        ])]));
        $order->setRelation('payments', collect([new OrderPayment([
            'payment_method' => 'cash',
            'amount' => 449,
        ])]));
        $component = new PosBilling;
        $component->branch = $branch;
        $component->showReceiptModal = true;
        $component->completedOrder = $order;
        $viewData = array_merge(
            get_object_vars($component),
            $component->calculateTotals(app(TaxService::class)),
            ['salespeople' => collect()],
        );

        $markup = view('livewire.pos-billing', $viewData)->render();
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$markup, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);
        $receipt = $xpath->query('//*[@id="thermal-receipt-print-area"]')->item(0);

        $this->assertNotNull($receipt);
        $this->assertStringContainsString('INV-202610-SLM01-00007', $receipt->textContent);
        $this->assertStringContainsString('1890 Flannel Shirt', $receipt->textContent);
        $this->assertStringContainsString('GRAND TOTAL:', $receipt->textContent);
        $this->assertStringContainsString('₹449.00', $receipt->textContent);
        $this->assertStringContainsString('Paid (CASH)', $receipt->textContent);
        $this->assertStringContainsString('Thank you for shopping with us!', $receipt->textContent);
        $this->assertSame(0, $receipt->getElementsByTagName('button')->length);
        $this->assertStringNotContainsString('Sale Completed', $markup);
        $this->assertStringContainsString('Thank you!</h3>', $markup);
    }
}
