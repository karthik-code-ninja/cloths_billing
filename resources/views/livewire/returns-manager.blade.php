<div class="p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Returns & Size/Colour Exchange</h1>
            <p class="text-xs text-slate-500 mt-1">Look up original invoices, process partial returns, and execute exchanges with price difference settlement.</p>
        </div>
    </div>

    <!-- Invoice Lookup Box -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Look Up Original Invoice</label>
        <div class="flex items-center space-x-2 max-w-xl">
            <input type="text" 
                   wire:model="invoiceSearch" 
                   wire:keydown.enter="searchInvoice"
                   placeholder="Scan invoice barcode or type invoice number (e.g. INV-202610-SLM01-00001)..." 
                   class="flex-1 px-4 py-2.5 rounded-xl border-slate-300 font-mono text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-500">
            <button wire:click="searchInvoice" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition">
                Find Invoice
            </button>
        </div>
    </div>

    <!-- Active Return / Exchange Form -->
    @if($originalOrder)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-100 gap-3">
                <div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 font-mono">Invoice: {{ $originalOrder->invoice_number }}</span>
                    <span class="text-xs text-slate-500 ml-2">Date: {{ $originalOrder->invoice_date->format('d/m/Y h:i A') }}</span>
                    <div class="text-xs text-slate-600 mt-1">Customer: <strong class="text-slate-800">{{ $originalOrder->getCustomerName() }}</strong> | Cashier: {{ $originalOrder->cashier->name }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-500">Original Total:</div>
                    <div class="text-xl font-mono font-extrabold text-slate-900">₹{{ number_format($originalOrder->grand_total, 2) }}</div>
                </div>
            </div>

            <!-- Items Table -->
            <div>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Select Items to Return</h3>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">Select</th>
                                <th class="py-2.5 px-3">Item Details</th>
                                <th class="py-2.5 px-3 text-center">Sold Qty</th>
                                <th class="py-2.5 px-3 text-center">Prev. Returned</th>
                                <th class="py-2.5 px-3 text-center">Returnable</th>
                                <th class="py-2.5 px-3 text-center">Return Qty</th>
                                <th class="py-2.5 px-3">Condition / Route</th>
                                <th class="py-2.5 px-3 text-right">Refund Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($originalOrder->items as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-3 text-center">
                                        <input type="checkbox" 
                                               wire:model.live="returnSelections.{{ $item->id }}.selected" 
                                               {{ $item->returnable_quantity <= 0 ? 'disabled' : '' }}
                                               class="rounded text-brand-600 focus:ring-brand-500">
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-800">{{ $item->product_name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $item->variant_details['size'] ?? '' }} / {{ $item->variant_details['color'] ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold text-slate-700">{{ $item->quantity }}</td>
                                    <td class="py-3 px-3 text-center font-bold text-rose-600">{{ $item->returned_quantity }}</td>
                                    <td class="py-3 px-3 text-center font-extrabold text-emerald-600">{{ $item->returnable_quantity }}</td>
                                    <td class="py-3 px-3 text-center">
                                        @if($item->returnable_quantity > 0)
                                            <input type="number" 
                                                   min="1" 
                                                   max="{{ $item->returnable_quantity }}" 
                                                   wire:model.live="returnSelections.{{ $item->id }}.quantity" 
                                                   class="w-16 px-2 py-1 text-center font-bold text-xs rounded-lg border-slate-300">
                                        @else
                                            <span class="text-[10px] text-slate-400">All Returned</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <select wire:model="returnSelections.{{ $item->id }}.condition" class="text-xs rounded-lg border-slate-300 py-1">
                                            <option value="resellable">Resellable (Showroom)</option>
                                            <option value="damaged">Damaged (Defect Rack)</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                        ₹{{ number_format($item->effective_unit_refund_price, 2) }} / pc
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Return Options -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-slate-100 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Return Reason</label>
                    <input type="text" wire:model="returnReason" placeholder="e.g. Size mismatch, defective stitching..." class="w-full px-3 py-2 rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Refund Method</label>
                    <select wire:model="refundMethod" class="w-full px-3 py-2 rounded-xl border-slate-300">
                        <option value="cash">Cash Refund</option>
                        <option value="store_credit">Store Credit (Customer Account)</option>
                        <option value="original_method">Original Payment Method</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Exchange Mode</label>
                    <label class="flex items-center space-x-2 mt-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="isExchange" class="rounded text-brand-600 focus:ring-brand-500">
                        <span class="font-bold text-slate-800">Exchange for another size / colour / item</span>
                    </label>
                </div>
            </div>

            <!-- EXCHANGE REPLACEMENT ITEMS SECTION -->
            @if($isExchange)
                <div class="p-4 bg-indigo-50/60 rounded-2xl border border-indigo-200 space-y-3">
                    <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Select Replacement Items</h4>
                    <div class="flex items-center space-x-2 text-xs">
                        <select wire:model="selectedExchangeVariantId" class="flex-1 rounded-xl border-slate-300 py-1.5 bg-white text-xs">
                            <option value="">-- Choose Replacement Variant --</option>
                            @foreach($allVariants as $var)
                                <option value="{{ $var->id }}">{{ $var->product->name }} ({{ $var->size }}/{{ $var->color }}) - ₹{{ number_format($var->selling_price, 2) }}</option>
                            @endforeach
                        </select>
                        <input type="number" min="1" wire:model="exchangeQty" class="w-16 rounded-xl border-slate-300 py-1.5 text-xs text-center font-bold bg-white">
                        <button wire:click="addExchangeItem" class="px-4 py-1.5 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow">
                            + Add to Exchange
                        </button>
                    </div>

                    @if(count($exchangeItems) > 0)
                        <div class="space-y-1.5 pt-2">
                            @foreach($exchangeItems as $eIdx => $eItem)
                                <div class="p-2 bg-white rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-bold text-slate-800">{{ $eItem['name'] }}</span>
                                        <span class="text-slate-500">({{ $eItem['size'] }} / {{ $eItem['color'] }})</span>
                                        <span class="text-slate-400">Qty: {{ $eItem['quantity'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="font-mono font-bold text-slate-900">₹{{ number_format($eItem['total'], 2) }}</span>
                                        <button wire:click="removeExchangeItem({{ $eIdx }})" class="text-rose-500 font-bold">&times;</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Submit Return Button -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                <button wire:click="$set('originalOrder', null)" class="px-4 py-2.5 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">
                    Cancel
                </button>
                <button wire:click="processReturnOrExchange" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition">
                    {{ $isExchange ? 'Complete Exchange & Print Credit Note' : 'Process Return & Issue Refund' }}
                </button>
            </div>
        </div>
    @endif

    <!-- PAST RETURNS AUDIT TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Past Returns & Credit Notes Audit</h3>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-4">Return Number</th>
                    <th class="py-3 px-4">Original Inv</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Refund Method</th>
                    <th class="py-3 px-4">Processed By</th>
                    <th class="py-3 px-4 text-right">Refund Amount</th>
                    <th class="py-3 px-4 text-center">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($pastReturns as $ret)
                    <tr>
                        <td class="py-3 px-4 font-mono font-bold text-indigo-700">{{ $ret->return_number }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $ret->originalOrder->invoice_number ?? 'No-Receipt' }}</td>
                        <td class="py-3 px-4 text-slate-800 font-semibold">{{ $ret->customer->name ?? 'Walk-in' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase bg-slate-100 text-slate-700">
                                {{ str_replace('_', ' ', $ret->refund_method) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $ret->user->name }}</td>
                        <td class="py-3 px-4 text-right font-mono font-extrabold text-rose-600">₹{{ number_format($ret->total_refund_amount, 2) }}</td>
                        <td class="py-3 px-4 text-center font-mono text-slate-400">{{ $ret->return_date->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 text-xs">No returns processed yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 border-t border-slate-200">
            {{ $pastReturns->links() }}
        </div>
    </div>
</div>

