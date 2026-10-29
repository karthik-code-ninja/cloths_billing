<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Top Banner: Barcode Scan & Quick Fetch Header -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center space-x-2 text-indigo-400 text-xs font-mono font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Instant Barcode Scanner & Price Checker (F3)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Scan & Fetch Garment Details</h1>
            <p class="text-slate-300 text-sm mt-1">Scan any garment hangtag barcode or type SKU to instantly retrieve prices, sizes, and multi-location stocks.</p>

            <!-- Keyboard-Wedge Fast Input Bar -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1" x-data="{ focused: true }">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <input type="text"
                           wire:model="barcodeInput"
                           wire:keydown.enter="handleBarcodeScan"
                           placeholder="Scan Barcode / SKU with scanner or press Enter..."
                           autofocus
                           x-ref="scanInput"
                           class="w-full pl-12 pr-28 py-3.5 bg-white text-slate-900 rounded-2xl shadow-inner text-base font-mono font-medium focus:ring-4 focus:ring-indigo-500/50 outline-none transition placeholder-slate-400 border border-slate-200">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center space-x-1.5">
                        <span class="bg-indigo-50 text-indigo-700 text-xs font-mono font-bold px-2 py-1 rounded-md border border-indigo-200">SCAN</span>
                    </div>
                </div>

                <!-- Text Search Fallback -->
                <div class="relative w-full sm:w-72">
                    <input type="text"
                           wire:model.live.debounce.300ms="searchQuery"
                           placeholder="Or search by name..."
                           class="w-full px-4 py-3.5 bg-slate-800/90 text-white rounded-2xl border border-slate-700 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-slate-400">
                    
                    @if (!empty($searchResults))
                        <div class="absolute left-0 right-0 mt-2 bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 max-h-72 overflow-y-auto">
                            @foreach ($searchResults as $res)
                                <button type="button"
                                        wire:click="selectVariant({{ $res['id'] }})"
                                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b border-slate-100 last:border-b-0 flex items-center justify-between transition">
                                    <div>
                                        <div class="font-bold text-sm text-slate-900">{{ $res['name'] }}</div>
                                        <div class="text-xs text-slate-500 flex items-center space-x-2 mt-0.5">
                                            <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">{{ $res['size'] }} / {{ $res['color'] }}</span>
                                            <span>SKU: {{ $res['sku'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-sm text-indigo-600">₹{{ number_format($res['selling_price'], 2) }}</div>
                                        <div class="text-[11px] text-slate-400">Stock: {{ $res['stock'] }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center space-x-2">
            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center space-x-2">
            <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($selectedVariant)
        <!-- Main Product Profile Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left 8 Columns: Core Product Specifications & Live Barcode -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Product Identity Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100">
                                {{ $selectedVariant->product->category->name ?? 'Apparel' }}
                            </span>
                            <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg">
                                Brand: {{ $selectedVariant->product->brand->name ?? 'NextDNA' }}
                            </span>
                            <span class="px-3 py-1 bg-amber-50 text-amber-800 text-xs font-mono font-medium rounded-lg border border-amber-200">
                                Art: {{ $selectedVariant->product->article_code }}
                            </span>
                        </div>

                        <!-- Active status -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedVariant->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $selectedVariant->is_active ? 'Active in Catalogue' : 'Inactive' }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $selectedVariant->product->name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-slate-600 font-medium">
                            <span class="inline-flex items-center px-3 py-1 rounded-xl bg-slate-900 text-white font-bold text-sm">
                                Size: {{ $selectedVariant->size }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-xl bg-slate-100 text-slate-800 font-semibold text-sm">
                                Color: {{ $selectedVariant->color }}
                            </span>
                            <span class="text-xs font-mono text-slate-400">SKU: {{ $selectedVariant->sku }}</span>
                        </div>
                    </div>

                    <!-- Pricing Banner -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Selling Price</span>
                            <div class="text-2xl font-black text-emerald-600 mt-1">₹{{ number_format($selectedVariant->selling_price, 2) }}</div>
                            <span class="text-[11px] text-slate-400">Tax {{ $selectedVariant->product->is_tax_inclusive ? 'Inclusive' : 'Exclusive' }}</span>
                        </div>

                        <div>
                            <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">MRP</span>
                            <div class="text-2xl font-extrabold text-slate-700 mt-1 line-through decoration-rose-500">₹{{ number_format($selectedVariant->mrp, 2) }}</div>
                            @if ($selectedVariant->mrp > $selectedVariant->selling_price)
                                <span class="text-[11px] font-bold text-emerald-600">Save ₹{{ number_format($selectedVariant->mrp - $selectedVariant->selling_price, 2) }}</span>
                            @endif
                        </div>

                        @if ($canViewCost)
                            <div>
                                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Purchase Cost</span>
                                <div class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($selectedVariant->purchase_cost, 2) }}</div>
                                @php
                                    $margin = $selectedVariant->selling_price - $selectedVariant->purchase_cost;
                                    $marginPct = $selectedVariant->selling_price > 0 ? round(($margin / $selectedVariant->selling_price) * 100, 1) : 0;
                                @endphp
                                <span class="text-[11px] font-semibold text-indigo-600">Margin: {{ $marginPct }}% (₹{{ number_format($margin, 2) }})</span>
                            </div>
                        @else
                            <div>
                                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">Min Selling Price</span>
                                <div class="text-xl font-bold text-slate-800 mt-1">₹{{ number_format($selectedVariant->product->min_selling_price ?: $selectedVariant->selling_price, 2) }}</div>
                                <span class="text-[11px] text-slate-400">Restricted discount limit</span>
                            </div>
                        @endif

                        <div>
                            <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider block">GST & HSN</span>
                            <div class="text-xl font-bold text-slate-800 mt-1">{{ $selectedVariant->product->tax_rate }}% GST</div>
                            <span class="text-[11px] font-mono text-slate-500">HSN: {{ $selectedVariant->product->hsn_code ?? '6203' }}</span>
                        </div>
                    </div>

                    <!-- Unique Barcode & Vector SVG Card -->
                    <div class="p-5 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="space-y-2 text-center sm:text-left flex-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Vector Barcode Preview</span>
                            <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm inline-block">
                                {!! $barcodeSvg !!}
                            </div>
                            <div class="text-xs font-mono text-slate-500">
                                Barcode Number: <span class="font-bold text-slate-800">{{ $selectedVariant->barcode }}</span>
                            </div>
                        </div>

                        <!-- Barcode Generator Actions -->
                        <div class="flex flex-col gap-2 w-full sm:w-auto">
                            <button type="button"
                                    wire:click="generateUniqueBarcodeForVariant('EAN13')"
                                    class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Generate Unique EAN-13 (890...)</span>
                            </button>
                            <button type="button"
                                    wire:click="generateUniqueBarcodeForVariant('CODE128')"
                                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                <span>Generate Unique Code-128</span>
                            </button>
                        </div>
                    </div>

                    <!-- Garment Attributes Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-2">
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block font-medium">Fit</span>
                            <span class="font-bold text-slate-800">{{ $selectedVariant->product->fit ?? 'Regular Fit' }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block font-medium">Fabric</span>
                            <span class="font-bold text-slate-800">{{ $selectedVariant->product->fabric ?? '100% Cotton' }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block font-medium">Pattern</span>
                            <span class="font-bold text-slate-800">{{ $selectedVariant->product->pattern ?? 'Solid' }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 block font-medium">Gender</span>
                            <span class="font-bold text-slate-800">{{ $selectedVariant->product->gender ?? 'Unisex' }}</span>
                        </div>
                    </div>

                    <!-- Sibling Variants Quick Navigation (Other sizes & colors of this article) -->
                    @if (!empty($siblingVariants))
                        <div class="pt-4 border-t border-slate-100">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">All Sizes & Colors for Article #{{ $selectedVariant->product->article_code }}</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($siblingVariants as $sib)
                                    @php
                                        $isSelected = $sib['id'] === $selectedVariant->id;
                                        $sibStock = collect($sib['stocks'])->sum('quantity');
                                    @endphp
                                    <button type="button"
                                            wire:click="selectVariant({{ $sib['id'] }})"
                                            class="px-3 py-2 rounded-xl text-xs font-semibold transition border flex items-center space-x-2 {{ $isSelected ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200' }}">
                                        <span>{{ $sib['size'] }} / {{ $sib['color'] }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] {{ $isSelected ? 'bg-indigo-800 text-indigo-100' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $sibStock }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right 4 Columns: Live Multi-Location Stock & Quick Actions -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Multi-Location Stock Breakdown Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-extrabold text-slate-900">Stock on Hand</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $selectedVariant->stock_on_hand > 5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            Total: {{ $selectedVariant->stock_on_hand }} units
                        </span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($locations as $loc)
                            @php
                                $locStock = $selectedVariant->stocks->firstWhere('stock_location_id', $loc->id)?->quantity ?? 0;
                            @endphp
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">{{ $loc->name }}</span>
                                    <span class="text-[11px] text-slate-400 capitalize">{{ $loc->type }} Area</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-black {{ $locStock > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                        {{ $locStock }}
                                    </span>
                                    <button type="button"
                                            wire:click="openAdjustmentModal({{ $loc->id }})"
                                            class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-white transition"
                                            title="Quick Adjust Stock">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button"
                            wire:click="openAdjustmentModal(0)"
                            class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Adjust Stock Count</span>
                    </button>
                </div>

                <!-- Instant Action Shortcuts -->
                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-xl space-y-4">
                    <h3 class="text-sm font-bold text-indigo-200 uppercase tracking-wider">Fast Retail Operations</h3>
                    
                    <a href="{{ route('barcode.labels', ['variant_id' => $selectedVariant->id]) }}"
                       class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/30 transition flex items-center justify-center space-x-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Barcode Labels</span>
                    </a>

                    <a href="{{ route('pos') }}"
                       class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center space-x-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Open POS Billing</span>
                    </a>

                    <a href="{{ route('catalogue') }}"
                       class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl transition flex items-center justify-center space-x-2 text-xs">
                        <span>View in Catalogue</span>
                    </a>
                </div>

            </div>

        </div>
    @endif

    <!-- Quick Stock Adjustment Modal -->
    @if ($showAdjustmentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-900">Quick Stock Adjustment</h3>
                    <button type="button" wire:click="$set('showAdjustmentModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Target Location</label>
                        <select wire:model="adjustmentLocationId" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                            @foreach ($locations as $l)
                                <option value="{{ $l->id }}">{{ $l->name }} ({{ $l->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Adjustment Type</label>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <button type="button" wire:click="$set('adjustmentType', 'recount')" class="py-2 rounded-lg font-bold border {{ $adjustmentType === 'recount' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-700' }}">Set Exact</button>
                            <button type="button" wire:click="$set('adjustmentType', 'add')" class="py-2 rounded-lg font-bold border {{ $adjustmentType === 'add' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700' }}">+ Add</button>
                            <button type="button" wire:click="$set('adjustmentType', 'subtract')" class="py-2 rounded-lg font-bold border {{ $adjustmentType === 'subtract' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-700' }}">- Subtract</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                            {{ $adjustmentType === 'recount' ? 'Actual Count' : 'Quantity to change' }}
                        </label>
                        <input type="number" wire:model="adjustmentQty" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-base font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Reason / Note</label>
                        <input type="text" wire:model="adjustmentReason" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm" placeholder="Physical count recount...">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="$set('showAdjustmentModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="button" wire:click="saveStockAdjustment" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md">Apply Adjustment</button>
                </div>
            </div>
        </div>
    @endif

</div>

