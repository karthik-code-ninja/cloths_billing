<div class="pos-billing-page flex-1 flex flex-col lg:flex-row h-full overflow-hidden"
     x-data="{
        initShortcuts() {
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    $wire.set('showSearchModal', true);
                } else if (e.key === 'F3') {
                    e.preventDefault();
                    document.getElementById('barcode-scanner-input')?.focus();
                } else if (e.key === 'F7') {
                    e.preventDefault();
                    $wire.set('showCustomerModal', true);
                } else if (e.key === 'F8') {
                    e.preventDefault();
                    $wire.set('showHeldCartsModal', true);
                } else if (e.key === 'F9') {
                    e.preventDefault();
                    $wire.openPaymentModal();
                }
            });
        }
     }"
     x-init="initShortcuts()">

    <!-- LEFT: Scanner & Line Items (65% width on desktop) -->
    <div class="flex-1 flex flex-col bg-slate-50 border-r border-slate-200 overflow-hidden">
        
        <!-- Top Action Bar: Barcode Input & Search -->
        <!-- Top Action Bar: Barcode Input & Search with Glossy Touch -->
        <div class="p-3 bg-white/95 backdrop-blur-md border-b border-slate-200/90 flex items-center space-x-3 shadow-xs z-10">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-500">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <input id="barcode-scanner-input" 
                       type="text" 
                       wire:model="barcodeInput" 
                       wire:keydown.enter="handleBarcodeScan" 
                       placeholder="Scan barcode or type SKU / article code and press Enter (F3)..." 
                       autofocus
                       autocomplete="off"
                       class="w-full pl-11 pr-24 py-2.5 bg-slate-50 hover:bg-slate-100/60 focus:bg-white border border-slate-250 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 rounded-2xl text-sm font-mono placeholder:text-slate-400 transition shadow-[inset_0_1px_2px_rgba(0,0,0,0.05)]">
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[10px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 my-2 mr-2 rounded-md border border-slate-200">
                    ↵ Enter
                </span>
            </div>

            <!-- Search Modal Trigger Button with Glossy Pill -->
            <button wire:click="$set('showSearchModal', true)" 
                    class="px-4 py-2.5 bg-gradient-to-r from-indigo-50 via-indigo-100/80 to-violet-50 hover:from-indigo-100 hover:to-violet-100 text-indigo-700 font-bold rounded-2xl text-xs flex items-center space-x-1.5 border border-indigo-200/90 shadow-xs transition hover:scale-[1.02] active:scale-[0.98]">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Lookup (F2)</span>
            </button>

            <!-- Held Carts Button with Glossy Pill -->
            <button wire:click="$set('showHeldCartsModal', true)" 
                    class="relative px-4 py-2.5 bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-bold rounded-2xl text-xs flex items-center space-x-1.5 border border-slate-250 shadow-xs transition hover:scale-[1.02] active:scale-[0.98]">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <span>Held (F8)</span>
                @if(count($heldCarts) > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-amber-500 text-white font-extrabold text-[10px] w-5 h-5 rounded-full flex items-center justify-center shadow-md animate-pulse">
                        {{ count($heldCarts) }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Cart Items Table Area -->
        <div class="flex-1 overflow-y-auto p-3">
            @if(count($cart) === 0)
                <div class="h-full flex flex-col items-center justify-center text-slate-400 py-16">
                    <div class="w-20 h-20 rounded-2xl bg-slate-200/60 flex items-center justify-center text-slate-400 mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-600">Cart is empty</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm text-center">Use your barcode scanner or press <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono text-slate-600">F2</kbd> to search clothing items.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100/75 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Item Details</th>
                                <th class="py-2.5 px-3 text-right">Price</th>
                                <th class="py-2.5 px-3 text-center">Quantity</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                                <th class="py-2.5 px-2 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($cart as $index => $item)
                                <tr class="hover:bg-indigo-50/30 transition">
                                    <td class="py-3 px-3 text-xs font-mono text-slate-400">{{ $index + 1 }}</td>
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-800 text-sm leading-tight">{{ $item['name'] }}</div>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                Size: {{ $item['size'] }}
                                            </span>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Color: {{ $item['color'] }}
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-400">
                                                {{ $item['barcode'] }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="font-bold text-slate-800">₹{{ number_format($item['unit_price'], 2) }}</div>
                                        @if($item['mrp'] > $item['unit_price'])
                                            <div class="text-[11px] text-slate-400 line-through">MRP ₹{{ number_format($item['mrp'], 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="inline-flex items-center border border-slate-200 rounded-lg bg-slate-50">
                                            <button wire:click="decrementQuantity({{ $index }})" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 font-bold transition rounded-l-lg">-</button>
                                            <input type="number" 
                                                   value="{{ $item['quantity'] }}" 
                                                   wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                   class="w-12 text-center text-xs font-bold bg-transparent border-0 focus:ring-0 p-1">
                                            <button wire:click="incrementQuantity({{ $index }})" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 font-bold transition rounded-r-lg">+</button>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <div class="font-extrabold text-slate-900">₹{{ number_format($item['line_total'], 2) }}</div>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <button wire:click="removeItem({{ $index }})" class="text-slate-400 hover:text-rose-600 p-1 rounded transition" title="Remove line">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Cart Bottom Quick Action Bar -->
        <div class="p-3 bg-white border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center space-x-4">
                <span>Items: <strong class="text-slate-800">{{ $total_items_count }}</strong></span>
                <span>Qty: <strong class="text-slate-800">{{ $total_quantity_count }}</strong></span>
            </div>
            <div class="flex items-center space-x-2">
                <button wire:click="clearCart" 
                        onclick="return confirm('Clear entire cart?') || event.stopImmediatePropagation()"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-lg font-semibold transition">
                    Clear Cart
                </button>
                <button wire:click="holdCurrentCart" 
                        class="px-3 py-1.5 bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-600 rounded-lg font-semibold transition">
                    Hold Cart (F8)
                </button>
            </div>
        </div>
    </div>

    <!-- RIGHT: Billing & Payment Summary (35% width on desktop) -->
    <div class="w-full lg:w-[380px] xl:w-[420px] bg-white flex flex-col shadow-lg z-20 border-l border-slate-200">
        
        <!-- Cashier & Register Header -->
        <div class="p-3.5 bg-slate-900 text-white border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-2.5 h-2.5 rounded-full {{ $activeShift ? 'bg-emerald-400' : 'bg-rose-400' }}"></div>
                <div>
                    <div class="text-xs font-bold leading-tight">{{ $counter->name ?? 'Counter 1' }}</div>
                    <div class="text-[10px] text-slate-400">{{ $activeShift ? 'Shift Active (#'.$activeShift->id.')' : 'No Active Shift' }}</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[11px] font-mono text-slate-300">{{ now()->format('d/m/Y') }}</div>
                <div class="text-[10px] text-slate-400">{{ now()->format('h:i A') }}</div>
            </div>
        </div>

        <!-- Customer Card -->
        <div class="p-3 border-b border-slate-200 bg-slate-50/70">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Customer Details</span>
                @if($customer)
                    <button wire:click="removeCustomer" class="text-[11px] text-rose-600 hover:underline">Change</button>
                @else
                    <button wire:click="$set('showCustomerModal', true)" class="text-[11px] text-brand-600 font-bold hover:underline">
                        + Select Customer (F7)
                    </button>
                @endif
            </div>

            @if($customer)
                <div class="bg-white p-2.5 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-800">{{ $customer->name }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">{{ $customer->phone }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] text-slate-400">Store Credit</div>
                        <div class="text-xs font-bold text-emerald-600">₹{{ number_format($customer->store_credit_balance, 2) }}</div>
                        <div class="text-[10px] text-indigo-600">★ {{ $customer->loyalty_points }} pts</div>
                    </div>
                </div>
            @else
                <div class="bg-white p-2.5 rounded-xl border border-slate-200 flex items-center justify-between text-slate-500 text-xs">
                    <span class="italic text-slate-400">Walk-in Customer</span>
                    <button wire:click="$set('showCustomerModal', true)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-[11px] text-slate-700">
                        Add Details
                    </button>
                </div>
            @endif
        </div>

        <!-- Salesperson Attribution -->
        <div class="px-3.5 py-2 border-b border-slate-200 flex items-center justify-between text-xs">
            <label for="salesperson-select" class="text-slate-500 font-medium">Salesperson Attribution:</label>
            <select id="salesperson-select" wire:model="salespersonId" class="text-xs rounded-lg border-slate-300 py-1 pl-2 pr-6 bg-slate-50">
                <option value="">-- None / Walk-in --</option>
                @foreach($salespeople as $sp)
                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Bill Calculation Summary -->
        <div class="flex-1 p-4 space-y-2.5 overflow-y-auto">
            <div class="flex justify-between text-xs text-slate-600">
                <span>Gross Subtotal</span>
                <span class="font-mono">₹{{ number_format($subtotal, 2) }}</span>
            </div>

            @if($item_discount_total > 0)
                <div class="flex justify-between text-xs text-rose-600">
                    <span>Item Discounts</span>
                    <span class="font-mono">-₹{{ number_format($item_discount_total, 2) }}</span>
                </div>
            @endif

            <!-- Bill Discount Row -->
            <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                <span>Bill Discount (% / ₹):</span>
                <div class="flex items-center space-x-1.5">
                    <div class="relative w-16">
                        <input type="number" 
                               wire:model.live.debounce.300ms="billDiscountPercent" 
                               min="0"
                               max="100"
                               step="any"
                               placeholder="%" 
                               class="w-full text-right text-xs p-1 rounded border-slate-300 font-mono">
                        <span class="absolute inset-y-0 right-1 flex items-center pointer-events-none text-slate-400 text-[10px]">%</span>
                    </div>
                    <div class="relative w-20">
                        <input type="number" 
                               wire:model.live.debounce.300ms="billDiscountAmount" 
                               min="0"
                               step="any"
                               placeholder="₹" 
                               class="w-full text-right text-xs p-1 rounded border-slate-300 font-mono">
                        <span class="absolute inset-y-0 right-1 flex items-center pointer-events-none text-slate-400 text-[10px]">₹</span>
                    </div>
                </div>
            </div>

            @if($bill_discount_total > 0)
                <div class="flex justify-between text-xs text-rose-600">
                    <span>Applied Bill Discount</span>
                    <span class="font-mono">-₹{{ number_format($bill_discount_total, 2) }}</span>
                </div>
            @endif

            <div class="border-t border-slate-200 my-2"></div>

            <div class="flex justify-between text-xs text-slate-500">
                <span>Taxable Value</span>
                <span class="font-mono">₹{{ number_format($taxable_value, 2) }}</span>
            </div>

            @if($cgst_total > 0 || $sgst_total > 0)
                <div class="flex justify-between text-xs text-slate-500">
                    <span>CGST (2.5%)</span>
                    <span class="font-mono">₹{{ number_format($cgst_total, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-500">
                    <span>SGST (2.5%)</span>
                    <span class="font-mono">₹{{ number_format($sgst_total, 2) }}</span>
                </div>
            @endif

            @if($igst_total > 0)
                <div class="flex justify-between text-xs text-slate-500">
                    <span>IGST</span>
                    <span class="font-mono">₹{{ number_format($igst_total, 2) }}</span>
                </div>
            @endif

            @if(abs($round_off_amount) > 0)
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Round Off Adjustment</span>
                    <span class="font-mono">{{ $round_off_amount > 0 ? '+₹' : '-₹' }}{{ number_format(abs($round_off_amount), 2) }}</span>
                </div>
            @endif

            <!-- GRAND TOTAL CALLOUT WITH GLOSSY FINISH -->
            <div class="relative overflow-hidden rounded-2xl p-4 mt-3 bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 text-white shadow-xl shadow-indigo-950/40 border border-indigo-500/30">
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-indigo-400 to-transparent"></div>
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/15 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative flex items-baseline justify-between">
                    <div>
                        <div class="text-[11px] font-black uppercase tracking-wider text-indigo-300">Total Payable</div>
                        <div class="text-[10px] text-indigo-200/70">Incl. all taxes & rounding</div>
                    </div>
                    <div class="text-3xl font-black text-white tracking-tight font-mono drop-shadow-md">
                        ₹{{ number_format($grand_total, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Trigger Button with Glossy Emerald Reflection -->
        <div class="p-4 bg-white border-t border-slate-200">
            <button wire:click="openPaymentModal" 
                    {{ count($cart) === 0 ? 'disabled' : '' }}
                    class="group relative overflow-hidden w-full py-3.5 px-4 bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 hover:from-emerald-400 hover:to-teal-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-base rounded-2xl shadow-xl shadow-emerald-600/35 border border-emerald-400/40 flex items-center justify-center space-x-2 transition duration-200 active:scale-[0.99]">
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                <svg class="w-5 h-5 text-emerald-100 group-hover:scale-110 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="tracking-wide">Checkout & Pay (F9)</span>
            </button>
        </div>
    </div>

    <!-- 1. SEARCH PRODUCTS MODAL (F2) -->
    @if($showSearchModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full p-6 border border-slate-200 flex flex-col max-h-[85vh]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Clothing Catalogue Lookup</h3>
                    <button wire:click="$set('showSearchModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 relative">
                    <input type="text" 
                           wire:model.live.debounce.250ms="searchQuery" 
                           placeholder="Type product name, article code, size, color, or brand..." 
                           class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:bg-white"
                           autofocus>
                </div>

                <!-- Results list -->
                <div class="flex-1 overflow-y-auto mt-4 divide-y divide-slate-100">
                    @forelse($searchResults as $res)
                        <div wire:click="selectSearchResult({{ $res['id'] }})" class="p-3 hover:bg-indigo-50/60 rounded-xl cursor-pointer flex items-center justify-between transition">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">{{ $res['name'] }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700">Size: {{ $res['size'] }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-50 text-indigo-700">Color: {{ $res['color'] }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $res['brand'] }} | SKU: {{ $res['sku'] }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900 text-base">₹{{ number_format($res['selling_price'], 2) }}</div>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $res['stock'] > 5 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    Stock: {{ $res['stock'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        @if(strlen($searchQuery) >= 2)
                            <div class="py-8 text-center text-slate-400 text-xs">No matching clothing items found.</div>
                        @else
                            <div class="py-8 text-center text-slate-400 text-xs">Type at least 2 characters to search...</div>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- 2. CUSTOMER LOOKUP & ADD MODAL (F7) -->
    @if($showCustomerModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Select or Add Customer</h3>
                    <button wire:click="$set('showCustomerModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <!-- Existing search -->
                <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Search Existing Customer</label>
                    <input type="text" 
                           wire:model.live.debounce.250ms="customerSearch" 
                           placeholder="Search by phone number or name..." 
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm">
                    
                    @if(count($customerSearchResults) > 0)
                        <div class="mt-2 divide-y divide-slate-100 border border-slate-200 rounded-xl max-h-48 overflow-y-auto bg-white">
                            @foreach($customerSearchResults as $c)
                                <div wire:click="selectCustomer({{ $c['id'] }})" class="p-2.5 hover:bg-indigo-50 cursor-pointer flex justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $c['name'] }}</div>
                                        <div class="text-slate-500 font-mono">{{ $c['phone'] }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-emerald-600 font-bold">Credit: ₹{{ number_format($c['store_credit_balance'], 2) }}</div>
                                        <div class="text-indigo-600">Points: {{ $c['loyalty_points'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Quick Add Form -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Or Quick Create New</h4>
                    <div class="space-y-3">
                        <input type="text" wire:model="newCustomerName" placeholder="Customer Full Name *" class="w-full px-3 py-2 text-sm rounded-xl border-slate-300">
                        <input type="text" wire:model="newCustomerPhone" placeholder="Mobile Phone (10 digits) *" class="w-full px-3 py-2 text-sm rounded-xl border-slate-300">
                        <input type="email" wire:model="newCustomerEmail" placeholder="Email Address (Optional)" class="w-full px-3 py-2 text-sm rounded-xl border-slate-300">
                        <button wire:click="createCustomer" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow transition">
                            Save & Select Customer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. HELD CARTS MODAL (F8) -->
    @if($showHeldCartsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Held Carts (Parked)</h3>
                    <button wire:click="$set('showHeldCartsModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="mt-4 divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    @forelse($heldCarts as $hc)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">{{ $hc['hold_label'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($hc['held_at'])->diffForHumans() }}</div>
                            </div>
                            <button wire:click="resumeHeldCart({{ $hc['id'] }})" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs rounded-lg transition">
                                Resume Cart
                            </button>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">No held carts available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- 4. CHECKOUT & PAYMENT MODAL (F9) -->
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Payment Breakdown</h3>
                        <p class="text-xs text-slate-500">Payable Amount: <strong class="text-slate-800">₹{{ number_format($grand_total, 2) }}</strong></p>
                    </div>
                    <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <!-- Payment Rows -->
                <div class="mt-4 space-y-3">
                    @php $sumPaid = 0.0; @endphp
                    @foreach($payments as $pIdx => $pmt)
                        @php $sumPaid += (float) ($pmt['amount'] ?? 0); @endphp
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-3">
                            <select wire:model="payments.{{ $pIdx }}.method" class="text-xs font-semibold rounded-lg border-slate-300 py-1.5 bg-white">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI (Manual)</option>
                                <option value="card">Card (POS Terminal)</option>
                                <option value="store_credit" {{ !$customer ? 'disabled' : '' }}>Store Credit</option>
                                <option value="gift_voucher">Gift Voucher</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>

                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-2.5 flex items-center text-xs text-slate-400 font-bold">₹</span>
                                <input type="number" 
                                       step="0.01" 
                                       wire:model.live="payments.{{ $pIdx }}.amount" 
                                       class="w-full pl-6 pr-3 py-1.5 text-sm font-bold text-slate-800 rounded-lg border-slate-300 bg-white">
                            </div>

                            <input type="text" 
                                   wire:model="payments.{{ $pIdx }}.reference" 
                                   placeholder="Ref / Txn ID" 
                                   class="w-28 py-1.5 px-2 text-xs rounded-lg border-slate-300 bg-white">

                            @if(count($payments) > 1)
                                <button wire:click="removePaymentRow({{ $pIdx }})" class="text-rose-500 hover:text-rose-700 p-1">&times;</button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Add Split Payment / Voucher -->
                <div class="mt-3 flex items-center justify-between">
                    <button wire:click="addPaymentRow('upi')" class="text-xs text-indigo-600 hover:underline font-bold">
                        + Add Split Payment Method
                    </button>

                    <!-- Gift Voucher Input -->
                    <div class="flex items-center space-x-1.5">
                        <input type="text" wire:model="voucherCode" placeholder="Gift Voucher Code" class="px-2 py-1 text-xs rounded-lg border-slate-300">
                        <button wire:click="applyGiftVoucher" class="px-2.5 py-1 bg-slate-800 text-white rounded-lg text-xs font-semibold">Apply</button>
                    </div>
                </div>

                <!-- Payment Summary & Quick Cash Pills -->
                <div class="mt-5 p-4 bg-slate-100/70 rounded-xl space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-600">Total Tendered:</span>
                        <span class="font-bold font-mono text-slate-800">₹{{ number_format($sumPaid, 2) }}</span>
                    </div>

                    @php 
                        $diff = round($sumPaid - $grand_total, 2);
                    @endphp

                    @if($diff >= 0)
                        <div class="flex justify-between text-xs text-emerald-700 font-bold">
                            <span>Change Due to Customer:</span>
                            <span class="font-mono">₹{{ number_format($diff, 2) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between text-xs text-amber-700 font-bold">
                            <span>Balance Due (Credit Sale):</span>
                            <span class="font-mono">₹{{ number_format(abs($diff), 2) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button wire:click="$set('showPaymentModal', false)" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button wire:click="completeCheckout" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 flex items-center space-x-2 transition">
                        <span wire:loading.remove>Confirm & Print Receipt (↵)</span>
                        <span wire:loading class="flex items-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Processing...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- 5. COMPLETED SALE & THERMAL RECEIPT MODAL -->
    @if($showReceiptModal && $completedOrder)
        <div class="sale-receipt-modal fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="receipt-dialog bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200">
                <div class="receipt-controls flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">✓</div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Thank you!</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ $completedOrder->invoice_number }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showReceiptModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <!-- Receipt Paper View Switcher -->
                <div class="receipt-controls flex items-center justify-center space-x-2 my-4">
                    <button wire:click="$set('receiptViewMode', '80mm')" class="px-3 py-1 text-xs font-semibold rounded-lg {{ $receiptViewMode === '80mm' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">80mm Thermal</button>
                    <button wire:click="$set('receiptViewMode', '58mm')" class="px-3 py-1 text-xs font-semibold rounded-lg {{ $receiptViewMode === '58mm' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">58mm Compact</button>
                    <button wire:click="$set('receiptViewMode', 'A4')" class="px-3 py-1 text-xs font-semibold rounded-lg {{ $receiptViewMode === 'A4' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">A4 Tax Invoice</button>
                </div>

                <style>
                    #thermal-receipt-print-area {
                        color: #000;
                        font-family: 'Courier New', monospace;
                        font-size: 12px;
                        line-height: 1.4;
                        font-weight: 700;
                    }
                    #thermal-receipt-print-area * {
                        color: #000 !important;
                        border-color: #000 !important;
                    }
                    #thermal-receipt-print-area [class*="text-[9px]"],
                    #thermal-receipt-print-area [class*="text-[10px]"] {
                        font-size: 11px;
                    }
                    #thermal-receipt-print-area .font-bold {
                        font-weight: 800;
                    }
                    #thermal-receipt-print-area .font-extrabold {
                        font-weight: 900;
                    }
                    @media print {
                        @page { size: auto; margin: 3mm; }
                        body > :not(main),
                        .pos-billing-page > :not(.sale-receipt-modal),
                        .sale-receipt-modal .receipt-controls {
                            display: none !important;
                        }
                        html, body, body > main, .pos-billing-page,
                        .sale-receipt-modal, .receipt-dialog, .receipt-paper-viewport {
                            position: static !important;
                            display: block !important;
                            width: auto !important;
                            max-width: none !important;
                            height: auto !important;
                            min-height: 0 !important;
                            max-height: none !important;
                            overflow: visible !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            border: 0 !important;
                            border-radius: 0 !important;
                            box-shadow: none !important;
                            background: #fff !important;
                            backdrop-filter: none !important;
                            transform: none !important;
                        }
                        #thermal-receipt-print-area {
                            box-sizing: border-box !important;
                            width: {{ $receiptViewMode === '58mm' ? '50mm' : ($receiptViewMode === 'A4' ? '100%' : '72mm') }} !important;
                            max-width: 100% !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            box-shadow: none !important;
                            print-color-adjust: exact;
                            -webkit-print-color-adjust: exact;
                        }
                        #thermal-receipt-print-area .flex > :last-child {
                            flex-shrink: 0;
                        }
                        #thermal-receipt-print-area .receipt-item {
                            break-inside: avoid;
                        }
                    }
                </style>

                <!-- Thermal Paper Slip Container -->
                <div class="receipt-paper-viewport bg-slate-50 p-4 rounded-xl border border-slate-200 max-h-96 overflow-y-auto font-mono text-xs shadow-inner">
                    <div id="thermal-receipt-print-area" class="bg-white p-4 shadow-sm mx-auto {{ $receiptViewMode === '58mm' ? 'max-w-[280px]' : ($receiptViewMode === 'A4' ? 'max-w-full' : 'max-w-[340px]') }}">
                        <!-- Receipt Content -->
                        <div class="text-center pb-2 border-b border-dashed border-slate-300">
                            <h2 class="font-extrabold text-sm uppercase">{{ $completedOrder->branch->name }}</h2>
                            <p class="text-[10px] text-slate-500">{{ $completedOrder->branch->address }}</p>
                            <p class="text-[10px] text-slate-500">Tel: {{ $completedOrder->branch->phone }}</p>
                            <p class="text-[10px] text-slate-500">GSTIN: {{ $completedOrder->branch->gstin }}</p>
                        </div>

                        <div class="py-2 border-b border-dashed border-slate-300 text-[11px] space-y-1">
                            <div>Inv: {{ $completedOrder->invoice_number }}</div>
                            <div>Date: {{ $completedOrder->invoice_date->format('d/m/y H:i') }}</div>
                            <div>Cashier: {{ $completedOrder->cashier->name }}</div>
                            <div>Counter: {{ $completedOrder->counter->name ?? 'Main' }}</div>
                            <div>Cust: {{ $completedOrder->customer->name ?? 'Walk-in' }}</div>
                        </div>

                        <!-- Items list -->
                        <div class="py-2 border-b border-dashed border-slate-300">
                            <div class="flex justify-between font-bold text-[10px] uppercase pb-1">
                                <span>Item</span>
                                <span>Amount</span>
                            </div>
                            @foreach($completedOrder->items as $item)
                                <div class="receipt-item py-1">
                                    <div class="font-bold text-[11px] leading-tight">{{ $item->product_name }}</div>
                                    <div class="flex justify-between text-[10px] text-slate-500">
                                        <span>{{ $item->variant_details['size'] ?? '' }}/{{ $item->variant_details['color'] ?? '' }} ({{ $item->quantity }} x {{ number_format($item->unit_selling_price, 2) }})</span>
                                        <span class="font-bold text-slate-800">₹{{ number_format($item->line_total, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Totals -->
                        <div class="py-2 border-b border-dashed border-slate-300 space-y-1 text-[11px]">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>₹{{ number_format($completedOrder->subtotal, 2) }}</span>
                            </div>
                            @if($completedOrder->total_discount > 0)
                                <div class="flex justify-between text-rose-600">
                                    <span>Discount:</span>
                                    <span>-₹{{ number_format($completedOrder->total_discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-[10px] text-slate-500">
                                <span>CGST: ₹{{ number_format($completedOrder->cgst_total, 2) }} | SGST: ₹{{ number_format($completedOrder->sgst_total, 2) }}</span>
                            </div>
                            @if(abs($completedOrder->round_off_amount) > 0)
                                <div class="flex justify-between text-[10px] text-slate-500">
                                    <span>Round Off:</span>
                                    <span>{{ $completedOrder->round_off_amount > 0 ? '+' : '' }}₹{{ number_format($completedOrder->round_off_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between font-extrabold text-sm pt-1 border-t border-slate-200">
                                <span>GRAND TOTAL:</span>
                                <span>₹{{ number_format($completedOrder->grand_total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Payments -->
                        <div class="py-2 border-b border-dashed border-slate-300 text-[10px] space-y-0.5">
                            @foreach($completedOrder->payments as $pmt)
                                <div class="flex justify-between">
                                    <span>Paid ({{ strtoupper($pmt->payment_method) }}):</span>
                                    <span>₹{{ number_format($pmt->amount, 2) }}</span>
                                </div>
                            @endforeach
                            @if($completedOrder->change_amount > 0)
                                <div class="flex justify-between font-bold text-emerald-600">
                                    <span>Change Given:</span>
                                    <span>₹{{ number_format($completedOrder->change_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-center pt-2 text-[10px] text-slate-500">
                            <p>Exchange within 15 days with tags & bill.</p>
                            <p class="font-bold mt-0.5">Thank you for shopping with us!</p>
                            <p class="text-[9px] text-slate-400 mt-1">Powered by NextDNA Technologies</p>
                        </div>
                    </div>
                </div>

                <!-- Print Actions -->
                <div class="receipt-controls mt-5 flex items-center justify-between">
                    <button wire:click="printAgain" class="px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Dispatch LAN / Bridge Reprint
                    </button>
                    <div class="flex space-x-2">
                        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Print Thermal Browser (Ctrl+P)
                        </button>
                        <button wire:click="$set('showReceiptModal', false)" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                            New Sale (Esc)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

