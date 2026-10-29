<div class="p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Inventory & Stock Ledger</h1>
            <p class="text-xs text-slate-500 mt-1">Real-time audit trail of all clothing stock movements, low-stock alerts, and adjustments.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button wire:click="openTransferModal" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-semibold rounded-xl shadow-sm transition">
                ⇄ Stock Transfer
            </button>
            <button wire:click="openAdjustmentModal" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition">
                + Adjust Stock
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex space-x-1 border-b border-slate-200 text-xs font-bold">
        <button wire:click="$set('tab', 'ledger')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'ledger' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Stock Movement Ledger
        </button>
        <button wire:click="$set('tab', 'low_stock')" class="px-4 py-2.5 border-b-2 transition flex items-center space-x-1.5 {{ $tab === 'low_stock' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span>Low Stock Alerts</span>
            @if(count($lowStocks) > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-700 font-extrabold">{{ count($lowStocks) }}</span>
            @endif
        </button>
        <button wire:click="$set('tab', 'adjustments')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'adjustments' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Physical Count Adjustments
        </button>
        <button wire:click="$set('tab', 'transfers')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'transfers' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Inter-Location Transfers
        </button>
    </div>

    <!-- TAB 1: STOCK MOVEMENT LEDGER -->
    @if($tab === 'ledger')
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by variant name, SKU, or barcode..." class="w-full text-xs rounded-xl border-slate-300 pl-8">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <select wire:model.live="movementFilter" class="text-xs rounded-xl border-slate-300">
                <option value="">All Movement Types</option>
                <option value="sale">Sales</option>
                <option value="return">Customer Returns</option>
                <option value="opening">Opening Stock</option>
                <option value="purchase">Purchases / GRN</option>
                <option value="adjustment">Adjustments</option>
                <option value="transfer_in">Transfers In</option>
                <option value="transfer_out">Transfers Out</option>
                <option value="damaged">Damaged Routing</option>
            </select>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Date / Time</th>
                        <th class="py-3 px-4">Item & Variant</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4 text-center">Change</th>
                        <th class="py-3 px-4 text-right">Balance After</th>
                        <th class="py-3 px-4">Remarks / By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $m->created_at->format('d/m/y H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $m->variant->product->name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $m->variant->size }} / {{ $m->variant->color }} ({{ $m->variant->barcode }})</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase
                                    @if(in_array($m->movement_type, ['sale', 'transfer_out'])) bg-rose-50 text-rose-700
                                    @elseif(in_array($m->movement_type, ['return', 'purchase', 'opening', 'transfer_in'])) bg-emerald-50 text-emerald-700
                                    @else bg-indigo-50 text-indigo-700 @endif">
                                    {{ str_replace('_', ' ', $m->movement_type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $m->location->name }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold">
                                <span class="{{ $m->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $m->quantity > 0 ? "+{$m->quantity}" : $m->quantity }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                                {{ $m->balance_after }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                <div>{{ $m->remarks ?: '-' }}</div>
                                <div class="text-[10px] text-slate-400">By: {{ $m->user->name ?? 'System' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">No stock movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $movements->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: LOW STOCK ALERTS -->
    @if($tab === 'low_stock')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-rose-50/60 border-b border-rose-100 text-[11px] font-bold text-rose-800 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Variant Name</th>
                        <th class="py-3 px-4">Barcode / SKU</th>
                        <th class="py-3 px-4 text-center">Threshold</th>
                        <th class="py-3 px-4 text-center">Current Stock</th>
                        <th class="py-3 px-4 text-center">Reorder Qty</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($lowStocks as $ls)
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $ls->product->name }} ({{ $ls->size }} / {{ $ls->color }})
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $ls->barcode }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-600">{{ $ls->low_stock_threshold }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 text-xs font-bold rounded bg-rose-100 text-rose-700">
                                    {{ $ls->stock_on_hand }} left
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-indigo-700 font-mono">{{ $ls->reorder_quantity }}</td>
                            <td class="py-3 px-4 text-right">
                                <button wire:click="openAdjustmentModal({{ $ls->id }})" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700">
                                    Adjust Stock
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-emerald-600 text-xs font-semibold">
                                ✓ Excellent! All clothing variants are well above their low-stock thresholds.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 3: ADJUSTMENTS -->
    @if($tab === 'adjustments')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Adjustment #</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4">Conducted By</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($adjustments as $adj)
                        <tr>
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">{{ $adj->adjustment_number }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $adj->location->name }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ str_replace('_', ' ', $adj->reason) }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $adj->user->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $adj->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">No physical count adjustments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- TAB 4: TRANSFERS -->
    @if($tab === 'transfers')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Transfer #</th>
                        <th class="py-3 px-4">From</th>
                        <th class="py-3 px-4">To</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Initiated By</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transfers as $trf)
                        <tr>
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">{{ $trf->transfer_number }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $trf->fromLocation->name }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $trf->toLocation->name }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase bg-emerald-50 text-emerald-700">
                                    {{ $trf->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $trf->user->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $trf->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">No stock transfers recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- ADJUSTMENT MODAL -->
    @if($showAdjustmentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Physical Stock Count Adjustment</h3>
                    <button wire:click="$set('showAdjustmentModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Select Product Variant</label>
                        <select wire:model.live="adjVariantId" class="w-full px-3 py-2 rounded-xl border-slate-300">
                            @foreach($allVariants as $v)
                                <option value="{{ $v->id }}">{{ $v->product->name }} ({{ $v->size }}/{{ $v->color }}) - Cur: {{ $v->stock_on_hand }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Physically Counted Quantity</label>
                        <input type="number" wire:model="countedQuantity" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-sm">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Adjustment Reason</label>
                        <select wire:model="adjReason" class="w-full px-3 py-2 rounded-xl border-slate-300">
                            <option value="physical_count_discrepancy">Physical Count Discrepancy</option>
                            <option value="damage">Damaged Goods</option>
                            <option value="theft">Loss / Shrinkage</option>
                            <option value="expired">Dead Stock</option>
                            <option value="other">Other Reason</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Notes / Auditor Remarks</label>
                        <textarea wire:model="adjNotes" rows="2" class="w-full px-3 py-2 rounded-xl border-slate-300" placeholder="Optional notes..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showAdjustmentModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="saveAdjustment" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
                        Post Stock Adjustment
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- TRANSFER MODAL -->
    @if($showTransferModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Inter-Location Stock Transfer</h3>
                    <button wire:click="$set('showTransferModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Select Item to Transfer</label>
                        <select wire:model="transferVariantId" class="w-full px-3 py-2 rounded-xl border-slate-300">
                            @foreach($allVariants as $v)
                                <option value="{{ $v->id }}">{{ $v->product->name }} ({{ $v->size }}/{{ $v->color }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">From Location</label>
                            <select wire:model="fromLocationId" class="w-full px-3 py-2 rounded-xl border-slate-300">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">To Location</label>
                            <select wire:model="toLocationId" class="w-full px-3 py-2 rounded-xl border-slate-300">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Transfer Quantity</label>
                        <input type="number" min="1" wire:model="transferQuantity" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-sm">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showTransferModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="saveTransfer" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                        Dispatch & Receive Transfer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

