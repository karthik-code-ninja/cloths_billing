<div class="p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Financial & Retail Reports</h1>
            <p class="text-xs text-slate-500 mt-1">Daily sales summaries, payment audits, gross profit, stock valuation, and GST accountant tax exports.</p>
        </div>
        <button wire:click="exportCsv" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center space-x-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export CSV</span>
        </button>
    </div>

    <!-- Filter Controls Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Report Type Tabs -->
        <div class="flex flex-wrap gap-1.5 text-xs font-bold">
            <button wire:click="$set('reportType', 'daily_sales')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'daily_sales' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Daily Sales
            </button>
            <button wire:click="$set('reportType', 'payment_reconciliation')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'payment_reconciliation' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Payment Reconcile
            </button>
            <button wire:click="$set('reportType', 'gross_profit')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'gross_profit' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Gross Profit
            </button>
            <button wire:click="$set('reportType', 'gst_tax')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'gst_tax' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                GST Tax Summary
            </button>
            <button wire:click="$set('reportType', 'stock_valuation')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'stock_valuation' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Stock Valuation
            </button>
            <button wire:click="$set('reportType', 'top_selling')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'top_selling' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Fast / Slow Moving
            </button>
            <button wire:click="$set('reportType', 'salesperson_performance')" class="px-3 py-1.5 rounded-lg transition {{ $reportType === 'salesperson_performance' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Staff Performance
            </button>
        </div>

        <!-- Date Range Pickers -->
        <div class="flex items-center space-x-2 text-xs">
            <input type="date" wire:model.live="startDate" class="px-3 py-1.5 rounded-xl border-slate-300 text-xs">
            <span class="text-slate-400">to</span>
            <input type="date" wire:model.live="endDate" class="px-3 py-1.5 rounded-xl border-slate-300 text-xs">
        </div>
    </div>

    <!-- REPORT 1: DAILY SALES -->
    @if($reportType === 'daily_sales')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Net Revenue</div>
                <div class="text-2xl font-mono font-extrabold text-slate-900 mt-1">₹{{ number_format($reportData['total_sales'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Completed Invoices</div>
                <div class="text-2xl font-mono font-extrabold text-indigo-600 mt-1">{{ $reportData['total_invoices'] ?? 0 }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tax Collected</div>
                <div class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">₹{{ number_format($reportData['total_tax'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-center">Invoices</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-right">Discounts</th>
                        <th class="py-3 px-4 text-right">Tax</th>
                        <th class="py-3 px-4 text-right">Grand Total</th>
                        <th class="py-3 px-4 text-right">Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $r)
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-center">{{ $r->invoice_count }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($r->subtotal, 2) }}</td>
                            <td class="py-3 px-4 text-right text-rose-600">-₹{{ number_format($r->total_discount, 2) }}</td>
                            <td class="py-3 px-4 text-right text-slate-500">₹{{ number_format($r->tax_total, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">₹{{ number_format($r->grand_total, 2) }}</td>
                            <td class="py-3 px-4 text-right text-emerald-600 font-bold">₹{{ number_format($r->paid_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">No sales recorded for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- REPORT 2: PAYMENT RECONCILIATION -->
    @if($reportType === 'payment_reconciliation')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-900">Payment Channel Breakdown</h3>
                <span class="text-xs font-mono font-bold text-indigo-700">Total: ₹{{ number_format($reportData['grand_total_collected'] ?? 0, 2) }}</span>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4 text-center">Transactions</th>
                        <th class="py-3 px-4 text-right">Total Amount Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $pm)
                        <tr>
                            <td class="py-3 px-4 font-bold uppercase text-slate-800">{{ str_replace('_', ' ', $pm->payment_method) }}</td>
                            <td class="py-3 px-4 text-center text-slate-600">{{ $pm->txn_count }}</td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600">₹{{ number_format($pm->total_collected, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-400 text-xs">No payments recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- REPORT 3: GROSS PROFIT -->
    @if($reportType === 'gross_profit')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Revenue</div>
                <div class="text-xl font-mono font-extrabold text-slate-900 mt-1">₹{{ number_format($reportData['total_revenue'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cost of Goods Sold (COGS)</div>
                <div class="text-xl font-mono font-extrabold text-rose-600 mt-1">₹{{ number_format($reportData['total_cogs'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gross Profit Margin</div>
                <div class="text-xl font-mono font-extrabold text-emerald-600 mt-1">₹{{ number_format($reportData['total_gross_profit'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Profit Margin %</div>
                <div class="text-xl font-mono font-extrabold text-indigo-600 mt-1">{{ $reportData['margin_percentage'] ?? 0 }}%</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Product Name</th>
                        <th class="py-3 px-4 text-center">Units Sold</th>
                        <th class="py-3 px-4 text-right">Revenue</th>
                        <th class="py-3 px-4 text-right">Cost (COGS)</th>
                        <th class="py-3 px-4 text-right">Gross Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $row)
                        <tr>
                            <td class="py-3 px-4 font-sans font-bold text-slate-800">{{ $row->product_name }}</td>
                            <td class="py-3 px-4 text-center">{{ $row->units_sold }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">₹{{ number_format($row->revenue, 2) }}</td>
                            <td class="py-3 px-4 text-right text-slate-500">₹{{ number_format($row->total_cogs, 2) }}</td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600">₹{{ number_format($row->gross_margin, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">No product sales in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- REPORT 4: GST TAX SUMMARY -->
    @if($reportType === 'gst_tax')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">HSN & GST Tax Breakdown</h3>
                    <p class="text-xs text-slate-500">Accountant export format for GST return filing (GSTR-1)</p>
                </div>
                <div class="text-right font-mono font-bold text-xs text-emerald-600">
                    Grand Total Tax: ₹{{ number_format($reportData['grand_tax'] ?? 0, 2) }}
                </div>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">HSN Code</th>
                        <th class="py-3 px-4 text-center">Tax Rate</th>
                        <th class="py-3 px-4 text-right">Taxable Value</th>
                        <th class="py-3 px-4 text-right">CGST (2.5%)</th>
                        <th class="py-3 px-4 text-right">SGST (2.5%)</th>
                        <th class="py-3 px-4 text-right">IGST</th>
                        <th class="py-3 px-4 text-right">Total Tax</th>
                        <th class="py-3 px-4 text-right">Total Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $gt)
                        <tr>
                            <td class="py-3 px-4 font-bold text-indigo-700">{{ $gt->hsn_code }}</td>
                            <td class="py-3 px-4 text-center">{{ $gt->tax_rate }}%</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($gt->taxable_value, 2) }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($gt->cgst, 2) }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($gt->sgst, 2) }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($gt->igst, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600">₹{{ number_format($gt->total_tax, 2) }}</td>
                            <td class="py-3 px-4 text-right font-extrabold text-slate-900">₹{{ number_format($gt->total_value, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">No transactions in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- REPORT 5: STOCK VALUATION -->
    @if($reportType === 'stock_valuation')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Stock on Hand</div>
                <div class="text-2xl font-mono font-extrabold text-slate-900 mt-1">{{ number_format($reportData['total_items'] ?? 0) }} pcs</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Valuation (at Purchase Cost)</div>
                <div class="text-2xl font-mono font-extrabold text-indigo-600 mt-1">₹{{ number_format($reportData['total_valuation_cost'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Valuation (at Retail Selling Price)</div>
                <div class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">₹{{ number_format($reportData['total_valuation_retail'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Variant Name</th>
                        <th class="py-3 px-4">Barcode</th>
                        <th class="py-3 px-4 text-center">Stock</th>
                        <th class="py-3 px-4 text-right">Cost Price</th>
                        <th class="py-3 px-4 text-right">Selling Price</th>
                        <th class="py-3 px-4 text-right">Total Cost Value</th>
                        <th class="py-3 px-4 text-right">Total Retail Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $sv)
                        <tr>
                            <td class="py-3 px-4 font-sans font-bold text-slate-800">{{ $sv['name'] }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $sv['barcode'] }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $sv['stock'] }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($sv['cost_price'], 2) }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($sv['selling_price'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-indigo-700">₹{{ number_format($sv['valuation_cost'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600">₹{{ number_format($sv['valuation_retail'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">No active variants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- REPORT 6: TOP SELLING (FAST & SLOW MOVING APPAREL) -->
    @if($reportType === 'top_selling')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Quantity Sold</div>
                <div class="text-2xl font-mono font-extrabold text-indigo-600 mt-1">{{ number_format($reportData['total_units'] ?? 0) }} pcs</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gross Garment Revenue</div>
                <div class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">₹{{ number_format($reportData['total_revenue'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Rank</th>
                        <th class="py-3 px-4">Garment / Style Name</th>
                        <th class="py-3 px-4">Variant Details</th>
                        <th class="py-3 px-4 text-center">Units Sold</th>
                        <th class="py-3 px-4 text-right">Avg Unit Price</th>
                        <th class="py-3 px-4 text-right">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $index => $item)
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-500">#{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-sans font-bold text-slate-800">{{ $item->product_name }}</td>
                            <td class="py-3 px-4 font-sans text-slate-600">{{ $item->variant_details ?: 'Standard' }}</td>
                            <td class="py-3 px-4 text-center font-bold text-indigo-700">{{ $item->units_sold }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">₹{{ number_format($item->avg_price, 2) }}</td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600">₹{{ number_format($item->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">No product sales found for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- REPORT 7: SALESPERSON PERFORMANCE & COMMISSION ATTRIBUTION -->
    @if($reportType === 'salesperson_performance')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Sales Invoices</div>
                <div class="text-2xl font-mono font-extrabold text-indigo-600 mt-1">{{ number_format($reportData['grand_orders'] ?? 0) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Net Revenue</div>
                <div class="text-2xl font-mono font-extrabold text-emerald-600 mt-1">₹{{ number_format($reportData['grand_net_sales'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Discounts Given by Staff</div>
                <div class="text-2xl font-mono font-extrabold text-rose-600 mt-1">₹{{ number_format($reportData['grand_discounts'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Salesperson</th>
                        <th class="py-3 px-4 text-center">Orders Count</th>
                        <th class="py-3 px-4 text-right">Gross Subtotal</th>
                        <th class="py-3 px-4 text-right">Discounts</th>
                        <th class="py-3 px-4 text-right">Net Sales</th>
                        <th class="py-3 px-4 text-right">Amount Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($reportData['rows'] ?? [] as $sp)
                        <tr>
                            <td class="py-3 px-4 font-sans font-bold text-slate-800">
                                {{ $sp->salesperson?->name ?? 'Walk-in / Unassigned' }}
                                @if($sp->salesperson)
                                    <span class="text-[10px] text-slate-400 font-normal block font-sans">{{ $sp->salesperson->role }} ({{ $sp->salesperson->phone ?: $sp->salesperson->email }})</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-indigo-700">{{ $sp->total_orders }}</td>
                            <td class="py-3 px-4 text-right">₹{{ number_format($sp->gross_sales, 2) }}</td>
                            <td class="py-3 px-4 text-right text-rose-600">-₹{{ number_format($sp->total_discounts, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">₹{{ number_format($sp->net_sales, 2) }}</td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600">₹{{ number_format($sp->collected_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">No sales attribution records found for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

