<div class="p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Purchases & Supplier Invoices</h1>
            <p class="text-xs text-slate-500 mt-1">Manage supplier orders, GRN receipts, and accounts payable balances.</p>
        </div>
        <button wire:click="openCreatePoModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition">
            + New Purchase Order
        </button>
    </div>

    <!-- Tabs -->
    <div class="flex space-x-1 border-b border-slate-200 text-xs font-bold">
        <button wire:click="$set('tab', 'orders')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'orders' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Purchase Orders
        </button>
        <button wire:click="$set('tab', 'grn')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'grn' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Goods Receipts (GRN)
        </button>
        <button wire:click="$set('tab', 'suppliers')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'suppliers' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Supplier Balances
        </button>
        <button wire:click="$set('tab', 'payments')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'payments' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Payment Records
        </button>
    </div>

    <!-- TAB 1: PURCHASE ORDERS -->
    @if($tab === 'orders')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">PO Number</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Order Date</th>
                        <th class="py-3 px-4 text-right">Grand Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">{{ $po->po_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $po->supplier->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $po->order_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">₹{{ number_format($po->grand_total, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase
                                    {{ $po->status === 'received' ? 'bg-emerald-50 text-emerald-700' : ($po->status === 'partial_received' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ str_replace('_', ' ', $po->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($po->status !== 'received')
                                    <button wire:click="openReceiveGrnModal({{ $po->id }})" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700">
                                        Receive GRN
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">No purchase orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: GOODS RECEIPTS (GRN) -->
    @if($tab === 'grn')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">GRN Number</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Supplier Inv #</th>
                        <th class="py-3 px-4">Received Date</th>
                        <th class="py-3 px-4 text-right">Total Amount</th>
                        <th class="py-3 px-4 text-center">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($grns as $g)
                        <tr>
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">{{ $g->grn_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $g->supplier->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $g->supplier_invoice_number ?: '-' }}</td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $g->received_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">₹{{ number_format($g->total_amount, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase
                                    {{ $g->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $g->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">No goods received notes recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $grns->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: SUPPLIER BALANCES -->
    @if($tab === 'suppliers')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Supplier Name</th>
                        <th class="py-3 px-4">GSTIN</th>
                        <th class="py-3 px-4">City / State</th>
                        <th class="py-3 px-4 text-right">Current Payable Balance</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($suppliers as $s)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $s->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $s->company_name }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $s->gstin ?: 'Unregistered' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $s->city }}, {{ $s->state }}</td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900">₹{{ number_format($s->current_balance, 2) }}</td>
                            <td class="py-3 px-4 text-right">
                                <button wire:click="openPaymentModal({{ $s->id }})" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700">
                                    Record Payment
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">No suppliers configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 4: PAYMENTS -->
    @if($tab === 'payments')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Payment #</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Method & Ref</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Amount Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($payments as $pay)
                        <tr>
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">{{ $pay->payment_number }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $pay->supplier->name }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-700">
                                {{ strtoupper($pay->payment_method) }} ({{ $pay->reference_number ?: 'N/A' }})
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $pay->payment_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-600">₹{{ number_format($pay->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">No supplier payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $payments->links() }}
            </div>
        </div>
    @endif

    <!-- CREATE PO MODAL -->
    @if($showPoModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Create Purchase Order</h3>
                    <button wire:click="$set('showPoModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Select Supplier</label>
                            <select wire:model="selectedSupplierId" class="w-full px-3 py-2 rounded-xl border-slate-300">
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Expected Delivery Date</label>
                            <input type="date" wire:model="deliveryDate" class="w-full px-3 py-2 rounded-xl border-slate-300">
                        </div>
                    </div>

                    <!-- Line items -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-slate-700">Order Items</span>
                            <button wire:click="addPoItem" class="text-indigo-600 hover:underline font-bold">+ Add Line</button>
                        </div>

                        <div class="space-y-2 max-h-56 overflow-y-auto">
                            @foreach($poItems as $pIdx => $pItem)
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-2">
                                    <select wire:model="poItems.{{ $pIdx }}.variant_id" class="flex-1 text-xs rounded-lg border-slate-300 py-1.5">
                                        @foreach($variants as $var)
                                            <option value="{{ $var->id }}">{{ $var->product->name }} ({{ $var->size }}/{{ $var->color }})</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" wire:model="poItems.{{ $pIdx }}.quantity" placeholder="Qty" class="w-16 px-2 py-1.5 text-xs rounded-lg border-slate-300">
                                    <input type="number" step="0.01" wire:model="poItems.{{ $pIdx }}.unit_cost" placeholder="Cost" class="w-20 px-2 py-1.5 text-xs rounded-lg border-slate-300 font-mono">
                                    @if(count($poItems) > 1)
                                        <button wire:click="removePoItem({{ $pIdx }})" class="text-rose-500 hover:text-rose-700 p-1">&times;</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showPoModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="savePurchaseOrder" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">Save Purchase Order</button>
                </div>
            </div>
        </div>
    @endif

    <!-- RECEIVE GRN MODAL -->
    @if($showGrnModal && $grnPo)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Receive Goods (GRN)</h3>
                        <p class="text-xs text-slate-500">For PO: {{ $grnPo->po_number }} • {{ $grnPo->supplier->name }}</p>
                    </div>
                    <button wire:click="$set('showGrnModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Supplier Invoice Number</label>
                        <input type="text" wire:model="supplierInvoiceNumber" class="w-full px-3 py-2 rounded-xl border-slate-300 font-mono">
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <label class="block font-bold text-slate-700 mb-2">Quantities Received:</label>
                        <div class="space-y-2 max-h-56 overflow-y-auto">
                            @foreach($grnPo->items as $item)
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $item->variant->product->name }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $item->variant->size }} / {{ $item->variant->color }} (Ordered: {{ $item->quantity_ordered }}, Recv: {{ $item->quantity_received }})</div>
                                    </div>
                                    <input type="number" min="0" wire:model="receivedQuantities.{{ $item->id }}" class="w-20 px-2 py-1 text-center font-bold text-xs rounded-lg border-slate-300">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showGrnModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="processGrn" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                        Receive & Update Stock
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- RECORD PAYMENT MODAL -->
    @if($showPaymentModal && $paymentSupplier)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Record Supplier Payment</h3>
                    <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">{{ $paymentSupplier->name }}</div>
                        <div class="text-slate-500">Current Payable: ₹{{ number_format($paymentSupplier->current_balance, 2) }}</div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Payment Amount (₹)</label>
                        <input type="number" step="0.01" wire:model="paymentAmount" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full px-3 py-2 rounded-xl border-slate-300">
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Reference / UTR Number</label>
                        <input type="text" wire:model="paymentRef" class="w-full px-3 py-2 rounded-xl border-slate-300 font-mono">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showPaymentModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="saveSupplierPayment" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">Save Payment</button>
                </div>
            </div>
        </div>
    @endif
</div>

