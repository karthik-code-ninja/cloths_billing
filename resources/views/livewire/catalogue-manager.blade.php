<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Page Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white rounded-2xl shadow-md shadow-indigo-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clothing Catalogue & Variants</h1>
                    <p class="text-xs text-slate-500 font-medium">Manage apparel styles, size & color matrix, barcodes, and live inventory</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2.5">
            <button wire:click="$set('showImportModal', true)" class="px-4 py-2.5 bg-white/90 hover:bg-slate-50 border border-slate-300/80 text-slate-700 text-xs font-bold rounded-2xl shadow-xs hover:shadow transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Excel / CSV</span>
            </button>
            <button wire:click="openCreateModal" class="group relative overflow-hidden px-4.5 py-2.5 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-black rounded-2xl shadow-[0_8px_20px_-4px_rgba(79,70,229,0.5),inset_0_1px_1px_rgba(255,255,255,0.4)] border border-white/20 transition flex items-center space-x-2">
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
                <svg class="w-4 h-4 text-indigo-100 group-hover:rotate-90 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Add Product</span>
            </button>
        </div>
    </div>

    <!-- Top Summary Metric Cards with Glossy Depth -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Card 1: Total Styles -->
        <div class="bg-white/90 backdrop-blur-md p-4.5 rounded-3xl border border-slate-200/90 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05),inset_0_1px_1px_rgba(255,255,255,1)] hover:shadow-md transition flex items-center space-x-3.5">
            <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100/80 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Styles</p>
                <p class="text-xl font-black text-slate-900 leading-tight">{{ $totalProducts }} <span class="text-xs font-semibold text-slate-400 font-normal">articles</span></p>
            </div>
        </div>

        <!-- Card 2: Active Variants -->
        <div class="bg-white/90 backdrop-blur-md p-4.5 rounded-3xl border border-slate-200/90 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05),inset_0_1px_1px_rgba(255,255,255,1)] hover:shadow-md transition flex items-center space-x-3.5">
            <div class="p-3 rounded-2xl bg-cyan-50 text-cyan-600 border border-cyan-100/80 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">SKU Variants</p>
                <p class="text-xl font-black text-slate-900 leading-tight">{{ $totalVariants }} <span class="text-xs font-semibold text-slate-400 font-normal">sizes & clr</span></p>
            </div>
        </div>

        <!-- Card 3: Total Stock Units -->
        <div class="bg-white/90 backdrop-blur-md p-4.5 rounded-3xl border border-slate-200/90 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05),inset_0_1px_1px_rgba(255,255,255,1)] hover:shadow-md transition flex items-center space-x-3.5">
            <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Stock</p>
                <p class="text-xl font-black text-emerald-700 leading-tight">{{ number_format($totalStockUnits) }} <span class="text-xs font-semibold text-slate-400 font-normal">pcs on hand</span></p>
            </div>
        </div>

        <!-- Card 4: Inventory Valuation -->
        <div class="bg-white/90 backdrop-blur-md p-4.5 rounded-3xl border border-slate-200/90 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05),inset_0_1px_1px_rgba(255,255,255,1)] hover:shadow-md transition flex items-center space-x-3.5">
            <div class="p-3 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100/80 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cost Valuation</p>
                <p class="text-xl font-black text-slate-900 leading-tight">₹{{ number_format($totalValuation, 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Search Input -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search products by style name, article code, barcode, or SKU..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <!-- Filters -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-1 md:pb-0">
                <!-- Category Filter -->
                <select wire:model.live="selectedCategory" class="text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories ({{ $categories->count() }})</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <!-- Brand Filter -->
                <select wire:model.live="selectedBrand" class="text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Brands ({{ $brands->count() }})</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>

                <!-- Stock Level Filter -->
                <select wire:model.live="stockStatus" class="text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="all">All Stock Levels</option>
                    <option value="in_stock">In Stock (&gt; 5 pcs)</option>
                    <option value="low_stock">Low Stock (1-5 pcs)</option>
                    <option value="out_of_stock">Out of Stock (0 pcs)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-28">Article Code</th>
                        <th class="py-3.5 px-4">Product Details</th>
                        <th class="py-3.5 px-4 w-44">Category & Brand</th>
                        <th class="py-3.5 px-4 w-48">Variant & Price</th>
                        <th class="py-3.5 px-4 text-center w-36">Total Stock</th>
                        <th class="py-3.5 px-4 text-right w-44">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                        @php
                            $firstVar = $p->variants->first();
                            $hasMultiple = $p->variants->count() > 1;
                            $isExpanded = in_array($p->id, $expandedProducts);
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition group">
                            <!-- Article Code -->
                            <td class="py-3.5 px-4 align-top">
                                <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-mono font-black text-xs border border-indigo-100">
                                    {{ $p->article_code }}
                                </span>
                            </td>

                            <!-- Product Details -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition">{{ $p->name }}</div>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        {{ $p->fabric }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        {{ $p->fit }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-600 font-mono">
                                        HSN: {{ $p->hsn_code }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                        GST: {{ $p->tax_rate }}%
                                    </span>
                                </div>
                            </td>

                            <!-- Category & Brand -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-semibold text-slate-800 text-xs">{{ $p->category?->name ?? 'Unassigned' }}</div>
                                <div class="text-[11px] font-medium text-slate-400 flex items-center space-x-1 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    <span>{{ $p->brand?->name ?? 'NextDNA' }}</span>
                                </div>
                            </td>

                            <!-- Variant & Price -->
                            <td class="py-3.5 px-4 align-top">
                                @if($firstVar)
                                    <div class="flex items-baseline space-x-2">
                                        <span class="font-extrabold text-slate-900 text-sm">₹{{ number_format($firstVar->selling_price, 2) }}</span>
                                        @if($firstVar->mrp > $firstVar->selling_price)
                                            <span class="text-[11px] line-through text-slate-400">MRP ₹{{ number_format($firstVar->mrp, 0) }}</span>
                                        @endif
                                    </div>
                                    <div class="font-mono text-[11px] text-slate-500 mt-0.5 flex items-center space-x-1">
                                        <span class="font-semibold">{{ $firstVar->size }}</span>
                                        <span>/</span>
                                        <span>{{ $firstVar->color }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-slate-400 font-mono">{{ $firstVar->barcode }}</span>
                                    </div>
                                @endif

                                @if($hasMultiple)
                                    <button type="button" wire:click="toggleExpand({{ $p->id }})" class="mt-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center space-x-1 focus:outline-none">
                                        <span>{{ $isExpanded ? 'Hide' : 'Show all' }} {{ $p->variants->count() }} variants</span>
                                        <svg class="w-3 h-3 transition transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                @endif
                            </td>

                            <!-- Total Stock -->
                            <td class="py-3.5 px-4 align-top text-center">
                                @php
                                    $stock = $p->total_stock;
                                @endphp
                                @if($stock > 5)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        {{ $stock }} pcs
                                    </span>
                                @elseif($stock > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                        {{ $stock }} pcs (Low)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        Out of stock
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 align-top text-right space-x-1">
                                <a href="{{ route('barcode.labels', ['product_id' => $p->id]) }}" class="inline-flex p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Print Hangtags / Barcodes">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </a>
                                <button type="button" wire:click="openMatrixModal({{ $p->id }})" class="inline-flex p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Size-Colour Matrix Generator">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                </button>
                                <button type="button" wire:click="editProduct({{ $p->id }})" class="inline-flex p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit Product">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button type="button" wire:click="deleteProduct({{ $p->id }})" wire:confirm="Are you sure you want to delete '{{ $p->name }}'?" class="inline-flex p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Product">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Accordion Sub-Rows for Multiple Variants -->
                        @if($hasMultiple && $isExpanded)
                            <tr class="bg-indigo-50/40">
                                <td colspan="6" class="p-4 border-y border-indigo-100/60">
                                    <div class="space-y-2">
                                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-900">
                                            All Variants for Style: {{ $p->name }}
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                            @foreach($p->variants as $var)
                                                <div class="bg-white p-3 rounded-2xl border border-indigo-100 shadow-sm flex items-center justify-between text-xs">
                                                    <div>
                                                        <div class="font-bold text-slate-800">
                                                            <span>Size: {{ $var->size }}</span>
                                                            <span class="text-slate-300 mx-1">|</span>
                                                            <span>Clr: {{ $var->color }}</span>
                                                        </div>
                                                        <div class="font-mono text-[11px] text-slate-400 mt-0.5">{{ $var->barcode }}</div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="font-black text-slate-900">₹{{ number_format($var->selling_price, 2) }}</div>
                                                        <div class="text-[11px] font-semibold mt-0.5 {{ $var->stock_on_hand < $var->low_stock_threshold ? 'text-amber-600 font-bold' : 'text-emerald-700' }}">
                                                            {{ $var->stock_on_hand }} in stock
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-1 pl-2 border-l border-slate-100 ml-2">
                                                        <a href="{{ route('product.lookup', ['id' => $var->id]) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition" title="Scan & Fetch Details">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                                        </a>
                                                        <a href="{{ route('barcode.labels', ['variant_id' => $var->id]) }}" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="Print Hangtag / Sticker">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-base font-bold text-slate-700">No clothing items match your filters</p>
                                <p class="text-xs text-slate-400 mt-1">Try resetting your search query or upload new styles using Bulk Import.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- 1. CREATE / EDIT PRODUCT MODAL -->
    @if($showProductModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $editingProductId ? 'Edit Clothing Style' : 'Create New Clothing Style' }}
                        </h3>
                        <p class="text-xs text-slate-500">Configure garment article code, category, masters, HSN, and GST classification</p>
                    </div>
                    <button wire:click="$set('showProductModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveProduct" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Product Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Product / Style Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="e.g. 255 CRV Korean Baggy Jeans, Oxford Checks Shirt" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Article Code -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Article / Style Code <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="article_code" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('article_code') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category (with auto-fill of HSN & GST Rate) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Category <span class="text-rose-500">*</span></label>
                                <a href="{{ route('masters.categories') }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline font-semibold">+ Master</a>
                            </div>
                            <select wire:model.live="category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} (HSN: {{ $cat->hsn_code ?? '6203' }})</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Brand -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Brand</label>
                                <a href="{{ route('masters.brands') }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline font-semibold">+ Master</a>
                            </div>
                            <select wire:model="brand_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- No Brand / Unbranded --</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Season / Collection -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Season / Collection</label>
                                <a href="{{ route('masters.seasons') }}" target="_blank" class="text-[10px] text-violet-600 hover:underline font-semibold">+ Master</a>
                            </div>
                            <select wire:model="season_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Core Basics / Year-Round --</option>
                                @foreach($seasons as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Gender / Dept -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Gender / Dept</label>
                            <select wire:model="gender" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Men">Men</option>
                                <option value="Women">Women</option>
                                <option value="Kids">Kids</option>
                                <option value="Boys">Boys</option>
                                <option value="Girls">Girls</option>
                                <option value="Unisex">Unisex</option>
                            </select>
                        </div>

                        <!-- Pattern / Print -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pattern / Style</label>
                            <input type="text" wire:model="pattern" list="pattern-suggestions" placeholder="e.g. Solid, Checks, Stripes, Graphic" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <datalist id="pattern-suggestions">
                                <option value="Solid">
                                <option value="Checks">
                                <option value="Stripes">
                                <option value="Graphic Print">
                                <option value="Floral Print">
                                <option value="Acid Wash / Whisker">
                                <option value="Self Texture">
                                <option value="Embroidery">
                                <option value="Polka Dots">
                                <option value="Camouflage">
                            </datalist>
                        </div>

                        <!-- HSN Code -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">HSN Code <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="hsn_code" placeholder="e.g. 6109, 6203, 6205" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">6109 (T-Shirts), 6203 (Pants/Jeans), 6205 (Shirts)</span>
                            @error('hsn_code') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- GST Tax Rate -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">GST Rate (%) <span class="text-rose-500">*</span></label>
                            <select wire:model="tax_rate" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-indigo-700">
                                <option value="5.00">5.0% (Garments &lt; ₹1,000)</option>
                                <option value="12.00">12.0% (Garments &ge; ₹1,000)</option>
                                <option value="18.00">18.0% (Accessories &amp; Premium)</option>
                                <option value="0.00">0.0% (Exempted)</option>
                            </select>
                        </div>

                        <!-- Fabric -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Fabric</label>
                            <input type="text" wire:model="fabric" list="fabric-suggestions" placeholder="e.g. 100% Cotton, Pure Linen, Denim" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <datalist id="fabric-suggestions">
                                <option value="100% Cotton">
                                <option value="Pure Linen">
                                <option value="Stretch Denim">
                                <option value="Lycra Cotton">
                                <option value="Oxford Cotton">
                                <option value="Rayon Viscose">
                                <option value="French Terry">
                                <option value="Flannel">
                                <option value="Corduroy">
                                <option value="Poly-Cotton">
                                <option value="Silk Blend">
                            </datalist>
                        </div>

                        <!-- Fit -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Fit</label>
                            <input type="text" wire:model="fit" list="fit-suggestions" placeholder="e.g. Regular Fit, Baggy Fit, Mom Fit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <datalist id="fit-suggestions">
                                <option value="Regular Fit">
                                <option value="Slim Fit">
                                <option value="Baggy / Relaxed Fit">
                                <option value="Mom Fit">
                                <option value="Straight Fit">
                                <option value="Boxy Fit">
                                <option value="Oversized">
                                <option value="Ankle Length">
                                <option value="Bootcut">
                                <option value="Skinny Fit">
                            </datalist>
                        </div>

                        <!-- Min Selling Price & Tax Inclusive -->
                        <div class="sm:col-span-2 pt-2 flex items-center justify-between">
                            <div class="w-1/2 pr-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Min Selling Price (INR)</label>
                                <input type="number" step="0.01" wire:model="min_selling_price" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800">
                            </div>
                            <div class="w-1/2 pl-2 pt-5">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model="is_tax_inclusive" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                    <span class="text-xs font-bold text-slate-700">Tax Inclusive Pricing</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showProductModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition">
                            {{ $editingProductId ? 'Update Product' : 'Create & Generate Matrix' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 2. SIZE-AND-COLOUR MATRIX MODAL (1-Click Masters Integration) -->
    @if($showMatrixModal && $matrixProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Bulk Size &amp; Colour Matrix</h3>
                        <p class="text-xs text-slate-500">Quick variant generation for: <strong class="text-slate-800">{{ $matrixProduct->name }}</strong> ({{ $matrixProduct->article_code }})</p>
                    </div>
                    <button wire:click="$set('showMatrixModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    
                    <!-- 1. Size Selection with Master Presets -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">1. Select Garment Sizes</label>
                            <a href="{{ route('masters.sizes') }}" target="_blank" class="text-[10px] text-amber-600 hover:underline font-semibold">+ Size Master</a>
                        </div>

                        <!-- 1-Click Size Group Presets -->
                        <div class="flex flex-wrap gap-1.5 p-2 bg-slate-50 rounded-2xl border border-slate-200">
                            @foreach($sizeGroups as $sg)
                                <button type="button" wire:click="selectMatrixSizeGroup({{ $sg->id }})" class="px-2.5 py-1 text-xs font-bold rounded-xl transition {{ $selectedSizeGroupId === $sg->id ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-amber-50' }}">
                                    ⚡ {{ $sg->name }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Selected Size Chips Display -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($matrixSizes as $sz)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200 shadow-sm">
                                    <span>{{ $sz }}</span>
                                    <button type="button" wire:click="toggleMatrixSize('{{ $sz }}')" class="ml-1.5 text-amber-500 hover:text-rose-600">×</button>
                                </span>
                            @endforeach
                        </div>

                        <!-- Add Custom Size Input -->
                        <div class="flex items-center space-x-2 pt-1">
                            <input type="text" wire:model="customSizeInput" wire:keydown.enter.prevent="addCustomSize" placeholder="Add custom size (e.g. 42 or 4XL) &amp; press Enter" class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <button type="button" wire:click="addCustomSize" class="px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-700 transition">
                                + Add Size
                            </button>
                        </div>
                    </div>

                    <!-- 2. Colour Selection with Palette Presets -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">2. Select Garment Colours</label>

                        <!-- Quick Retail Colour Palette -->
                        @php
                            $presetColors = [
                                'Navy Blue' => '#1e3a8a',
                                'Jet Black' => '#0f172a',
                                'Crisp White' => '#ffffff',
                                'Olive Green' => '#3f6212',
                                'Wine Red' => '#881337',
                                'Charcoal Grey' => '#334155',
                                'Khaki Beige' => '#d97706',
                                'Maroon' => '#7f1d1d',
                                'Royal Blue' => '#1d4ed8',
                                'Lavender' => '#a855f7',
                            ];
                        @endphp
                        <div class="flex flex-wrap gap-1.5 p-2 bg-slate-50 rounded-2xl border border-slate-200">
                            @foreach($presetColors as $cName => $cHex)
                                @php $isActive = in_array($cName, $matrixColors); @endphp
                                <button type="button" wire:click="toggleMatrixColor('{{ $cName }}')" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-xl transition {{ $isActive ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-indigo-50' }}">
                                    <span class="w-2.5 h-2.5 rounded-full mr-1.5 border border-slate-300" style="background-color: {{ $cHex }};"></span>
                                    <span>{{ $cName }}</span>
                                </button>
                            @endforeach
                        </div>

                        <!-- Selected Colour Chips Display -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($matrixColors as $clr)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-900 border border-indigo-200 shadow-sm">
                                    <span>{{ $clr }}</span>
                                    <button type="button" wire:click="toggleMatrixColor('{{ $clr }}')" class="ml-1.5 text-indigo-500 hover:text-rose-600">×</button>
                                </span>
                            @endforeach
                        </div>

                        <!-- Add Custom Colour Input -->
                        <div class="flex items-center space-x-2 pt-1">
                            <input type="text" wire:model="customColorInput" wire:keydown.enter.prevent="addCustomColor" placeholder="Add custom shade (e.g. Sage Green) &amp; press Enter" class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="button" wire:click="addCustomColor" class="px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-700 transition">
                                + Add Colour
                            </button>
                        </div>
                    </div>

                    <!-- 3. Pricing & Initial Inventory -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">3. Pricing &amp; Opening Stock</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Cost (INR)</label>
                                <input type="number" step="0.01" wire:model="matrixCost" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Selling Price</label>
                                <input type="number" step="0.01" wire:model="matrixPrice" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-indigo-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">MRP (INR)</label>
                                <input type="number" step="0.01" wire:model="matrixMrp" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Stock / SKU (pcs)</label>
                                <input type="number" wire:model="matrixInitialStock" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-emerald-700">
                            </div>
                        </div>
                    </div>

                    <!-- Live Matrix Counter & Summary Banner -->
                    <div class="p-3.5 bg-gradient-to-r from-indigo-50 via-purple-50 to-amber-50 rounded-2xl border border-indigo-100/80 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2 text-indigo-900 font-bold">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Matrix Generator Summary:</span>
                        </div>
                        <div class="font-extrabold text-slate-900">
                            {{ count($matrixSizes) }} Sizes &times; {{ count($matrixColors) }} Colours = <span class="text-indigo-600 text-sm">{{ count($matrixSizes) * count($matrixColors) }} Variant SKUs</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showMatrixModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="generateMatrixVariants" class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
                            <span>Generate {{ count($matrixSizes) * count($matrixColors) }} Variants &amp; Barcodes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. CSV & EXCEL IMPORT MODAL -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Bulk Import Products</h3>
                        <p class="text-xs text-slate-500">Upload Vyapar Items Export (.xlsx) or standard CSV</p>
                    </div>
                    <button wire:click="$set('showImportModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="my-4 space-y-4">
                    <div class="p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-100 text-xs text-indigo-900">
                        <p class="font-bold">Automated Classification:</p>
                        <p class="mt-0.5 text-slate-600">Categories, Brands, HSN codes (6109, 6203, 6205), Barcodes, and Opening Stock ledger records will be populated automatically.</p>
                    </div>

                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-indigo-400 transition bg-slate-50/50">
                        <input type="file" wire:model="importFile" accept=".xlsx,.csv,.txt" class="w-full text-xs file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                        @error('importFile') <span class="text-rose-500 text-xs mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button wire:click="$set('showImportModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Cancel
                    </button>
                    <button wire:click="importCsv" wire:loading.attr="disabled" class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
                        <span wire:loading.remove wire:target="importCsv">Upload & Process Import</span>
                        <span wire:loading wire:target="importCsv">Processing Rows...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
