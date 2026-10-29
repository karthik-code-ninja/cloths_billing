<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportsManager extends Component
{
    public string $reportType = 'daily_sales'; // daily_sales, payment_reconciliation, gross_profit, gst_tax, stock_valuation, top_selling, salesperson_performance, customer_dues

    public string $startDate = '';

    public string $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function render()
    {
        $data = match ($this->reportType) {
            'daily_sales' => $this->getDailySalesData(),
            'payment_reconciliation' => $this->getPaymentReconciliationData(),
            'gross_profit' => $this->getGrossProfitData(),
            'gst_tax' => $this->getGstTaxData(),
            'stock_valuation' => $this->getStockValuationData(),
            'top_selling' => $this->getTopSellingData(),
            'salesperson_performance' => $this->getSalespersonPerformanceData(),
            'customer_dues' => $this->getCustomerDuesData(),
            default => [],
        };

        return view('livewire.reports-manager', [
            'reportData' => $data,
        ]);
    }

    protected function getDailySalesData(): array
    {
        $orders = Order::whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(invoice_date) as date'),
                DB::raw('COUNT(id) as invoice_count'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(total_discount) as total_discount'),
                DB::raw('SUM(tax_total) as tax_total'),
                DB::raw('SUM(grand_total) as grand_total'),
                DB::raw('SUM(paid_amount) as paid_amount'),
                DB::raw('SUM(balance_amount) as credit_amount')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return [
            'rows' => $orders,
            'total_sales' => (float) $orders->sum('grand_total'),
            'total_invoices' => (int) $orders->sum('invoice_count'),
            'total_tax' => (float) $orders->sum('tax_total'),
        ];
    }

    protected function getPaymentReconciliationData(): array
    {
        $payments = OrderPayment::whereHas('order', function ($q) {
            $q->whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate])
                ->where('status', 'completed');
        })
            ->select('payment_method', DB::raw('COUNT(id) as txn_count'), DB::raw('SUM(amount) as total_collected'))
            ->groupBy('payment_method')
            ->get();

        return [
            'rows' => $payments,
            'grand_total_collected' => (float) $payments->sum('total_collected'),
        ];
    }

    protected function getGrossProfitData(): array
    {
        $items = OrderItem::whereHas('order', function ($q) {
            $q->whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate])
                ->where('status', 'completed');
        })
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(line_total) as revenue'),
                DB::raw('SUM(unit_cost * quantity) as total_cogs'),
                DB::raw('SUM(line_total - (unit_cost * quantity)) as gross_margin')
            )
            ->groupBy('product_name')
            ->get();

        $totalRevenue = (float) $items->sum('revenue');
        $totalCogs = (float) $items->sum('total_cogs');
        $totalGrossProfit = (float) $items->sum('gross_margin');
        $marginPercentage = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 2) : 0;

        return [
            'rows' => $items,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'total_gross_profit' => $totalGrossProfit,
            'margin_percentage' => $marginPercentage,
        ];
    }

    protected function getGstTaxData(): array
    {
        $items = OrderItem::whereHas('order', function ($q) {
            $q->whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate])
                ->where('status', 'completed');
        })
            ->select(
                'hsn_code',
                'tax_rate',
                DB::raw('SUM(taxable_amount) as taxable_value'),
                DB::raw('SUM(cgst_amount) as cgst'),
                DB::raw('SUM(sgst_amount) as sgst'),
                DB::raw('SUM(igst_amount) as igst'),
                DB::raw('SUM(total_tax) as total_tax'),
                DB::raw('SUM(line_total) as total_value')
            )
            ->groupBy('hsn_code', 'tax_rate')
            ->get();

        return [
            'rows' => $items,
            'total_taxable' => (float) $items->sum('taxable_value'),
            'total_cgst' => (float) $items->sum('cgst'),
            'total_sgst' => (float) $items->sum('sgst'),
            'total_igst' => (float) $items->sum('igst'),
            'grand_tax' => (float) $items->sum('total_tax'),
        ];
    }

    protected function getStockValuationData(): array
    {
        $variants = ProductVariant::with(['product', 'stocks'])
            ->where('is_active', true)
            ->get();

        $rows = $variants->map(function ($v) {
            $stock = $v->stock_on_hand;

            return [
                'name' => $v->fullName,
                'barcode' => $v->barcode,
                'stock' => $stock,
                'cost_price' => (float) $v->purchase_cost,
                'selling_price' => (float) $v->selling_price,
                'valuation_cost' => round($stock * (float) $v->purchase_cost, 2),
                'valuation_retail' => round($stock * (float) $v->selling_price, 2),
            ];
        });

        return [
            'rows' => $rows,
            'total_items' => $rows->sum('stock'),
            'total_valuation_cost' => (float) $rows->sum('valuation_cost'),
            'total_valuation_retail' => (float) $rows->sum('valuation_retail'),
        ];
    }

    protected function getTopSellingData(): array
    {
        $items = OrderItem::whereHas('order', function ($q) {
            $q->whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate])
                ->where('status', 'completed');
        })
            ->select(
                'product_name',
                'variant_details',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('AVG(unit_selling_price) as avg_price')
            )
            ->groupBy('product_name', 'variant_details')
            ->orderByDesc('units_sold')
            ->take(50)
            ->get();

        return [
            'rows' => $items,
            'total_units' => (int) $items->sum('units_sold'),
            'total_revenue' => (float) $items->sum('total_revenue'),
        ];
    }

    protected function getSalespersonPerformanceData(): array
    {
        $orders = Order::whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate])
            ->where('status', 'completed')
            ->with('salesperson')
            ->select(
                'salesperson_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(subtotal) as gross_sales'),
                DB::raw('SUM(total_discount) as total_discounts'),
                DB::raw('SUM(grand_total) as net_sales'),
                DB::raw('SUM(paid_amount) as collected_amount')
            )
            ->groupBy('salesperson_id')
            ->orderByDesc('net_sales')
            ->get();

        return [
            'rows' => $orders,
            'grand_orders' => (int) $orders->sum('total_orders'),
            'grand_net_sales' => (float) $orders->sum('net_sales'),
            'grand_discounts' => (float) $orders->sum('total_discounts'),
        ];
    }

    protected function getCustomerDuesData(): array
    {
        $customers = Customer::where('store_credit_balance', '>', 0)
            ->orWhereHas('orders', fn ($q) => $q->where('balance_amount', '>', 0))
            ->get();

        return [
            'rows' => $customers,
        ];
    }

    public function exportCsv()
    {
        $filename = "{$this->reportType}_{$this->startDate}_{$this->endDate}.csv";

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Report Type', strtoupper($this->reportType)]);
            fputcsv($handle, ['Date Range', "{$this->startDate} to {$this->endDate}"]);
            fputcsv($handle, []);

            if ($this->reportType === 'daily_sales') {
                fputcsv($handle, ['Date', 'Invoices', 'Subtotal', 'Discounts', 'Tax', 'Grand Total', 'Paid', 'Credit Balance']);
                foreach ($this->getDailySalesData()['rows'] as $r) {
                    fputcsv($handle, [$r->date, $r->invoice_count, $r->subtotal, $r->total_discount, $r->tax_total, $r->grand_total, $r->paid_amount, $r->credit_amount]);
                }
            } elseif ($this->reportType === 'gst_tax') {
                fputcsv($handle, ['HSN Code', 'GST Rate (%)', 'Taxable Value', 'CGST', 'SGST', 'IGST', 'Total Tax', 'Total Line Value']);
                foreach ($this->getGstTaxData()['rows'] as $r) {
                    fputcsv($handle, [$r->hsn_code, $r->tax_rate, $r->taxable_value, $r->cgst, $r->sgst, $r->igst, $r->total_tax, $r->total_value]);
                }
            } elseif ($this->reportType === 'payment_reconciliation') {
                fputcsv($handle, ['Payment Method', 'Transactions', 'Total Amount']);
                foreach ($this->getPaymentReconciliationData()['rows'] as $r) {
                    fputcsv($handle, [strtoupper($r->payment_method), $r->txn_count, $r->total_collected]);
                }
            } elseif ($this->reportType === 'top_selling') {
                fputcsv($handle, ['Product / Style Name', 'Variant (Size/Color)', 'Units Sold', 'Total Revenue', 'Avg Unit Price']);
                foreach ($this->getTopSellingData()['rows'] as $r) {
                    fputcsv($handle, [$r->product_name, $r->variant_details, $r->units_sold, $r->total_revenue, $r->avg_price]);
                }
            } elseif ($this->reportType === 'salesperson_performance') {
                fputcsv($handle, ['Salesperson', 'Orders Count', 'Gross Subtotal', 'Total Discounts', 'Net Revenue', 'Amount Collected']);
                foreach ($this->getSalespersonPerformanceData()['rows'] as $r) {
                    fputcsv($handle, [$r->salesperson?->name ?? 'Walk-in / Unassigned', $r->total_orders, $r->gross_sales, $r->total_discounts, $r->net_sales, $r->collected_amount]);
                }
            } else {
                fputcsv($handle, ['Report Data Exported Successfully']);
            }

            fclose($handle);
        }, $filename);
    }
}
