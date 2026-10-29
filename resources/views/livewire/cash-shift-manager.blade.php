<div class="p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Cash Register & Shifts</h1>
            <p class="text-xs text-slate-500 mt-1">Manage opening floats, cash drawer entries, petty cash expenses, and shift closures.</p>
        </div>
    </div>

    <!-- Active Shift Status Card -->
    @if($activeShift)
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-3xl p-6 shadow-xl border border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-800 gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-xl">
                        💰
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-lg font-extrabold text-white">Active Cash Shift (#{{ $activeShift->id }})</h2>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 animate-pulse">OPEN</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Counter: {{ $activeShift->counter->name }} • Opened by {{ $activeShift->cashier->name }} at {{ $activeShift->opened_at->format('d/m/Y h:i A') }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button wire:click="openDrawerModal('cash_in')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition">
                        + Cash In
                    </button>
                    <button wire:click="openDrawerModal('cash_out')" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition">
                        - Cash Out
                    </button>
                    <button wire:click="openExpenseModal" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 transition">
                        Store Expense
                    </button>
                    <button wire:click="openCloseModal" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/30 transition">
                        Close Shift
                    </button>
                </div>
            </div>

            <!-- Shift Live Numbers Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6">
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Opening Float</div>
                    <div class="text-xl font-mono font-extrabold text-slate-200 mt-1">₹{{ number_format($activeShift->opening_float, 2) }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Cash Sales (POS)</div>
                    <div class="text-xl font-mono font-extrabold text-emerald-400 mt-1">
                        +₹{{ number_format($activeShift->orderPayments()->where('payment_method', 'cash')->sum('amount'), 2) }}
                    </div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Cash Out / Expenses</div>
                    <div class="text-xl font-mono font-extrabold text-rose-400 mt-1">
                        -₹{{ number_format($activeShift->cashTransactions()->where('type', 'cash_out')->sum('amount') + $activeShift->expenses()->sum('amount'), 2) }}
                    </div>
                </div>
                <div class="bg-indigo-900/50 p-3 rounded-2xl border border-indigo-500/20">
                    <div class="text-[11px] font-bold text-indigo-300 uppercase tracking-wider">Expected Cash in Drawer</div>
                    <div class="text-2xl font-mono font-extrabold text-white mt-1">₹{{ number_format($expectedCash, 2) }}</div>
                </div>
            </div>
        </div>
    @else
        <!-- Open Shift Callout -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm text-center max-w-xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center font-bold text-2xl mx-auto">
                🔐
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-900">No Open Cash Shift</h3>
                <p class="text-xs text-slate-500 mt-1">Please open a shift with an initial cash drawer float to start billing.</p>
            </div>

            <div class="space-y-3 pt-2 text-left text-xs max-w-sm mx-auto">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Billing Counter</label>
                    <select wire:model="counterId" class="w-full px-3 py-2 rounded-xl border-slate-300">
                        @foreach($counters as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Opening Cash Float (₹)</label>
                    <input type="number" step="0.01" wire:model="openingFloat" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-sm">
                </div>
                <button wire:click="openShift" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition text-sm">
                    Open Shift & Start POS
                </button>
            </div>
        </div>
    @endif

    <!-- Past Shifts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Past Shift History & Cash Variance Audit</h3>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-4">Shift ID</th>
                    <th class="py-3 px-4">Counter & Cashier</th>
                    <th class="py-3 px-4">Opened At</th>
                    <th class="py-3 px-4">Closed At</th>
                    <th class="py-3 px-4 text-right">Expected Cash</th>
                    <th class="py-3 px-4 text-right">Counted Cash</th>
                    <th class="py-3 px-4 text-right">Variance</th>
                    <th class="py-3 px-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @forelse($shifts as $s)
                    <tr>
                        <td class="py-3 px-4 font-mono font-bold text-indigo-700">#{{ $s->id }}</td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $s->counter->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $s->cashier->name }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $s->opened_at->format('d/m/y H:i') }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $s->closed_at ? $s->closed_at->format('d/m/y H:i') : '-' }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₹{{ number_format($s->expected_closing_cash, 2) }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">{{ $s->counted_closing_cash !== null ? '₹'.number_format($s->counted_closing_cash, 2) : '-' }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold">
                            @if($s->variance !== null)
                                <span class="{{ $s->variance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $s->variance > 0 ? '+₹' : ($s->variance < 0 ? '-₹' : '₹') }}{{ number_format(abs($s->variance), 2) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase {{ $s->status === 'open' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $s->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 text-xs">No shifts recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 border-t border-slate-200">
            {{ $shifts->links() }}
        </div>
    </div>

    <!-- DRAWER MODAL (Cash In / Out) -->
    @if($showDrawerModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Record Drawer {{ $drawerType === 'cash_in' ? 'Cash In' : 'Cash Out' }}</h3>
                    <button wire:click="$set('showDrawerModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Amount (₹)</label>
                        <input type="number" step="0.01" wire:model="drawerAmount" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-sm">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Reason / Purpose</label>
                        <input type="text" wire:model="drawerReason" class="w-full px-3 py-2 rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showDrawerModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="saveDrawerTransaction" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- EXPENSE MODAL -->
    @if($showExpenseModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Record Store Expense</h3>
                    <button wire:click="$set('showExpenseModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Expense Category</label>
                        <select wire:model="expenseCategory" class="w-full px-3 py-2 rounded-xl border-slate-300">
                            <option value="tea_refreshments">Tea & Refreshments</option>
                            <option value="cleaning">Store Cleaning & Sanitation</option>
                            <option value="packaging">Packaging Materials</option>
                            <option value="repairs">Maintenance & Repairs</option>
                            <option value="misc">Miscellaneous</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Amount (₹)</label>
                        <input type="number" step="0.01" wire:model="expenseAmount" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-sm">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Description</label>
                        <input type="text" wire:model="expenseDescription" placeholder="Optional details..." class="w-full px-3 py-2 rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showExpenseModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="saveExpense" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">Save Expense</button>
                </div>
            </div>
        </div>
    @endif

    <!-- CLOSE SHIFT MODAL -->
    @if($showCloseModal && $activeShift)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Close Cash Register Shift</h3>
                    <button wire:click="$set('showCloseModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 space-y-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl flex justify-between items-center">
                        <span class="font-bold text-slate-600">Expected Closing Cash:</span>
                        <span class="font-mono font-extrabold text-base text-slate-900">₹{{ number_format($activeShift->calculateExpectedCash(), 2) }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Physically Counted Closing Cash (₹) *</label>
                        <input type="number" step="0.01" wire:model.live="countedCash" class="w-full px-3 py-2 rounded-xl border-slate-300 font-extrabold text-base text-indigo-700">
                    </div>

                    @php
                        $var = round($countedCash - $activeShift->calculateExpectedCash(), 2);
                    @endphp
                    <div class="flex justify-between items-center p-2.5 rounded-lg {{ $var >= 0 ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }} font-bold">
                        <span>Cash Variance:</span>
                        <span class="font-mono text-sm">{{ $var > 0 ? '+₹' : ($var < 0 ? '-₹' : '₹') }}{{ number_format(abs($var), 2) }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Closing Notes / Discrepancy Reason</label>
                        <textarea wire:model="closeNotes" rows="2" class="w-full px-3 py-2 rounded-xl border-slate-300" placeholder="Optional notes..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="$set('showCloseModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                    <button wire:click="closeShift" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow transition">
                        Confirm & Close Shift
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

