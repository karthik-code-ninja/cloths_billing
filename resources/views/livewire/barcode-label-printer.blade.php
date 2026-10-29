<div class="barcode-label-page max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6"
     x-data="{ alignment: JSON.parse(localStorage.getItem('barcode-label-alignment') || '{&quot;left&quot;:0,&quot;right&quot;:0,&quot;top&quot;:0}') }"
     x-effect="localStorage.setItem('barcode-label-alignment', JSON.stringify(alignment))"
     :style="{ '--label-left-offset': (Number(alignment.left) || 0) + 'mm', '--label-right-offset': (Number(alignment.right) || 0) + 'mm', '--label-top-offset': (Number(alignment.top) || 0) + 'mm' }">

    <!-- Screen Header & Fast Barcode Scan Bar (no-print) -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800 no-print">
        <div class="relative z-10 max-w-3xl">
            <div class="flex items-center space-x-2 text-indigo-400 text-xs font-mono font-semibold uppercase tracking-wider mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span>Garment Label Generator & Thermal Hangtag Printing</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Barcode Label & Hangtag Studio</h1>
            <p class="text-slate-300 text-sm mt-1">Generate guaranteed unique GS1 EAN-13 or Code-128 barcodes, customize garment hangtag dimensions, and print rolls or A4 sheets.</p>

            <!-- Scan to Queue Input -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <input type="text"
                           wire:model="scanInput"
                           wire:keydown.enter="handleScannerInput"
                           placeholder="Scan barcode with scanner to add directly to queue..."
                           autofocus
                           class="w-full pl-12 pr-28 py-3.5 bg-white text-slate-900 rounded-2xl shadow-inner text-sm font-mono font-medium focus:ring-4 focus:ring-indigo-500/50 outline-none border border-slate-200">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <span class="bg-indigo-50 text-indigo-700 text-xs font-mono font-bold px-2 py-1 rounded-md border border-indigo-200">SCAN TO QUEUE</span>
                    </div>
                </div>

                <!-- Text Search Input -->
                <div class="relative w-full sm:w-72">
                    <input type="text"
                           wire:model.live.debounce.300ms="searchQuery"
                           placeholder="Search garment name/article..."
                           class="w-full px-4 py-3.5 bg-slate-800/90 text-white rounded-2xl border border-slate-700 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-slate-400">

                    @if (!empty($searchResults))
                        <div class="absolute left-0 right-0 mt-2 bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 max-h-64 overflow-y-auto">
                            @foreach ($searchResults as $res)
                                <button type="button"
                                        wire:click="addToQueue({{ $res['id'] }}, 1)"
                                        class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 border-b border-slate-100 last:border-b-0 flex items-center justify-between text-xs transition">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $res['name'] }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $res['size'] }} / {{ $res['color'] }} • SKU: {{ $res['sku'] }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-indigo-600">₹{{ number_format($res['selling_price'], 2) }}</span>
                                        <span class="block text-[10px] text-slate-400">+ Add</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages (no-print) -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center space-x-2 no-print">
            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center space-x-2 no-print">
            <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Two-Column Studio Layout (no-print) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">

        <!-- Left Column: Print Queue & Template Settings (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Print Queue Card -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Labels Print Queue</h2>
                        <p class="text-xs text-slate-500">Selected garments ready for hangtag/sticker printing.</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-100">
                            {{ $totalLabelsCount }} Labels Total
                        </span>
                        @if (!empty($queue))
                            <button type="button" wire:click="clearQueue" class="text-xs font-semibold text-rose-600 hover:text-rose-800 p-1">Clear</button>
                        @endif
                    </div>
                </div>

                <!-- Batch Quantity Adjusters & Unique Barcode Generator -->
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                    <div class="flex items-center space-x-1.5 font-medium text-slate-600">
                        <span>Quantities:</span>
                        <button type="button" wire:click="matchAllCopiesToStock" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-800 rounded-lg border border-slate-200 font-semibold shadow-xs">Match Stock</button>
                        <button type="button" wire:click="setAllCopies(1)" class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-800 rounded-lg border border-slate-200 font-semibold shadow-xs">All 1</button>
                        <button type="button" wire:click="setAllCopies(5)" class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-800 rounded-lg border border-slate-200 font-semibold shadow-xs">All 5</button>
                    </div>

                    <!-- Generate Unique Barcodes for Queue -->
                    <div class="flex items-center space-x-1">
                        <button type="button"
                                wire:click="generateUniqueBarcodesForQueue('EAN13')"
                                class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-xs transition"
                                title="Assign new unique GS1 EAN-13 barcodes to all items in queue">
                            ⚡ Unique EAN-13
                        </button>
                        <button type="button"
                                wire:click="generateUniqueBarcodesForQueue('CODE128')"
                                class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold shadow-xs transition"
                                title="Assign new unique Code-128 barcodes">
                            Code-128
                        </button>
                    </div>
                </div>

                <!-- Queue Items Table -->
                @if (empty($queue))
                    <div class="py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <p class="text-sm font-semibold text-slate-500">Queue is empty</p>
                        <p class="text-xs">Scan a barcode or search above to add garments.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto pr-1">
                        @foreach ($queuedItems as $item)
                            @php
                                $variant = $item['model'];
                                $copies = $item['copies'];
                            @endphp
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-slate-900 truncate">{{ $variant->product->name }}</div>
                                    <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-slate-500 font-medium">
                                        <span class="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-bold">{{ $variant->size }}</span>
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded">{{ $variant->color }}</span>
                                        <span class="font-mono text-slate-400">Barcode: {{ $variant->barcode }}</span>
                                        <span class="text-slate-400">Stock: {{ $variant->stock_on_hand }}</span>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-700 mt-0.5">
                                        ₹{{ number_format($variant->selling_price, 2) }} <span class="line-through text-slate-400 text-[11px] ml-1">MRP ₹{{ number_format($variant->mrp, 2) }}</span>
                                    </div>
                                </div>

                                <!-- Copies Quantity Stepper -->
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                        <button type="button" wire:click="updateCopies({{ $variant->id }}, {{ $copies - 1 }})" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 font-bold">-</button>
                                        <input type="number"
                                               value="{{ $copies }}"
                                               wire:change="updateCopies({{ $variant->id }}, $event.target.value)"
                                               min="1"
                                               class="w-12 text-center py-1 text-xs font-bold bg-transparent outline-none">
                                        <button type="button" wire:click="updateCopies({{ $variant->id }}, {{ $copies + 1 }})" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 font-bold">+</button>
                                    </div>

                                    <button type="button" wire:click="removeFromQueue({{ $variant->id }})" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Label Customizer & Options Card -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-900">Garment Label Dimensions & Options</h3>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-lg {{ $columns === 2 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-100' }}">
                        {{ $columns === 2 ? '2-Across (Dual Roll)' : ($columns === 1 ? '1-Across (Single Roll)' : 'Multi-Across Sheet') }}
                    </span>
                </div>

                <!-- Preset Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-2">Label Size Preset</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <!-- 1. 2-Up Garment Sticker (TVS LP 46 Photo Match) -->
                        <button type="button"
                                wire:click="setPreset('2up_50x25')"
                                class="p-3 rounded-2xl border text-left transition relative overflow-hidden {{ $templatePreset === '2up_50x25' ? 'border-amber-500 bg-amber-50/70 text-amber-950 font-bold shadow-sm ring-2 ring-amber-400/40' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <div class="font-extrabold text-amber-900">2-Up Garment</div>
                                <span class="bg-amber-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md">TVSE LP46 DLite</span>
                            </div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">50 × 25 mm per sticker (2-Across)</div>
                            <div class="text-[10px] text-slate-400">100mm labels + centre gap • Dual column</div>
                        </button>

                        <!-- 2. 2-Up Compact -->
                        <button type="button"
                                wire:click="setPreset('2up_38x25')"
                                class="p-3 rounded-2xl border text-left transition {{ $templatePreset === '2up_38x25' ? 'border-amber-500 bg-amber-50/70 text-amber-950 font-bold shadow-sm ring-2 ring-amber-400/40' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="font-extrabold">2-Up Compact</div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">38 × 25 mm (2-Across)</div>
                            <div class="text-[10px] text-slate-400">80mm roll • Dual column</div>
                        </button>

                        <!-- 3. 1-Up Hangtag -->
                        <button type="button"
                                wire:click="setPreset('50x38')"
                                class="p-3 rounded-2xl border text-left transition {{ $templatePreset === '50x38' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-950 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="font-extrabold">Hangtag</div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">50 × 38 mm</div>
                            <div class="text-[10px] text-slate-400">Single tag with hole</div>
                        </button>

                        <!-- 4. 1-Up Single Sticker -->
                        <button type="button"
                                wire:click="setPreset('50x25')"
                                class="p-3 rounded-2xl border text-left transition {{ $templatePreset === '50x25' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-950 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="font-extrabold">1-Up Sticker</div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">50 × 25 mm</div>
                            <div class="text-[10px] text-slate-400">Single column roll</div>
                        </button>

                        <!-- 5. 1-Up Compact Tag -->
                        <button type="button"
                                wire:click="setPreset('38x25')"
                                class="p-3 rounded-2xl border text-left transition {{ $templatePreset === '38x25' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-950 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="font-extrabold">1-Up Compact</div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">38 × 25 mm</div>
                            <div class="text-[10px] text-slate-400">Single column roll</div>
                        </button>

                        <!-- 6. A4 Sheet Grid -->
                        <button type="button"
                                wire:click="setPreset('a4_24')"
                                class="p-3 rounded-2xl border text-left transition {{ $templatePreset === 'a4_24' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-950 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="font-extrabold">A4 Sheet</div>
                            <div class="text-[11px] text-slate-600 font-semibold mt-0.5">70 × 36 mm (24-Up)</div>
                            <div class="text-[10px] text-slate-400">Standard office printer</div>
                        </button>
                    </div>

                    <!-- Custom Dimensions Toggle & Controls -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs">
                        <label class="flex items-center space-x-1.5 font-bold text-slate-700">
                            <span>Columns:</span>
                            <select wire:model.live="columns" class="px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 text-xs font-bold">
                                <option value="2">2 Columns (2-Up Roll)</option>
                                <option value="1">1 Column (Single Roll)</option>
                                <option value="3">3 Columns (Sheet)</option>
                            </select>
                        </label>

                        <div class="flex items-center space-x-1 font-semibold text-slate-600">
                            <span>Width:</span>
                            <input type="number" wire:model.live="labelWidthMm" min="20" max="110" class="w-14 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center font-bold">
                            <span>mm</span>
                        </div>

                        <div class="flex items-center space-x-1 font-semibold text-slate-600">
                            <span>Height:</span>
                            <input type="number" wire:model.live="labelHeightMm" min="15" max="100" class="w-14 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center font-bold">
                            <span>mm</span>
                        </div>

                        <div class="flex items-center space-x-1 font-semibold text-slate-600">
                            <span>Gap:</span>
                            <input type="number" wire:model.live="gapMm" min="0" max="10" class="w-12 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center font-bold">
                            <span>mm</span>
                        </div>

                        <!-- Scale / Zoom Factor -->
                        <div class="flex items-center space-x-1.5 font-bold text-slate-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                            <span>Scale:</span>
                            <select wire:model.live="labelScalePercent" class="px-2 py-0.5 rounded border border-amber-300 bg-white text-xs font-black text-amber-900 outline-none">
                                <option value="85">85%</option>
                                <option value="95">95%</option>
                                <option value="100">100% (Standard)</option>

                            </select>
                        </div>

                        <!-- Orientation Override -->
                        <div class="flex items-center space-x-1.5 font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                            <span>Orientation:</span>
                            <select wire:model.live="labelOrientation" class="px-2 py-0.5 rounded border border-slate-300 bg-white text-xs font-bold text-slate-800 outline-none">
                                <option value="landscape">Landscape (Horizontal - 50mm)</option>
                                <option value="portrait">Portrait (Rotated 90°)</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if ($columns === 2)
                    <div class="mt-3 p-3 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="text-xs font-bold text-slate-700">Browser / PDF print alignment (mm)</div>
                        <div class="flex flex-wrap gap-3 text-xs text-slate-700">
                            <label>Left sticker X
                                <input type="number" x-model.number="alignment.left" min="-10" max="10" step="0.5" class="w-16 border border-slate-300 rounded px-2 py-1">
                            </label>
                            <label>Right sticker X
                                <input type="number" x-model.number="alignment.right" min="-10" max="10" step="0.5" class="w-16 border border-slate-300 rounded px-2 py-1">
                            </label>
                            <label>Both stickers Y
                                <input type="number" x-model.number="alignment.top" min="-10" max="10" step="0.5" class="w-16 border border-slate-300 rounded px-2 py-1">
                            </label>
                            <button type="button" @click="alignment = { left: 0, right: 0, top: 0 }" class="font-bold text-indigo-700">Reset alignment</button>
                        </div>
                        <p class="text-xs text-slate-500">Negative X moves left; positive X moves right. Positive Y moves down. Saved on this browser. Set Width and Height to one physical sticker, and Gap to the space between stickers.</p>
                    </div>
                @endif

                <!-- Fields Visibility Toggles -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-2">Display Elements on Label</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs text-slate-700 font-medium">
                        <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="showBrand" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Brand Name</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="showArticleCode" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Article Code</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="showSizeColor" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Size & Color</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="showMrp" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>MRP (Strike)</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="showSellingPrice" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Selling Price</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="showBarcodeText" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Barcode Text</span>
                        </label>
                    </div>
                </div>

                <!-- Target Printer Configuration -->
                <div class="pt-3 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Direct Thermal Hardware Printer</label>
                    <select wire:model="selectedPrinterId" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50">
                        <option value="">Browser Print (Use System / OS Printer Dialog)</option>
                        @foreach ($printers as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->name }} ({{ strtoupper($p->connection_type) }} • {{ $p->paper_width_mm }}mm)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        <!-- Right Column: Interactive Real-Time Label Preview & Print Controls (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Real-Time Visual Label Preview Card -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $columns === 2 ? 'Live 2-Up Roll Preview' : 'Live Label Preview' }}
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            {{ $columns === 2 ? 'Exact representation of 2-across stickers on thermal roll' : 'Exact representation of single thermal label' }}
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                        {{ $columns === 2 ? '2 × '.$labelWidthMm.' mm ('.(($labelWidthMm * 2) + $gapMm).'mm Roll)' : $labelWidthMm.' × '.$labelHeightMm.' mm' }}
                    </span>
                </div>

                @php
                    $previewItem1 = $queuedItems->first();
                    $previewItem2 = $queuedItems->skip(1)->first() ?? $previewItem1;
                @endphp

                @if ($previewItem1)
                    <!-- Physical Label Carrier Strip Simulation -->
                    <div class="p-4 sm:p-5 bg-gradient-to-b from-amber-50/60 to-slate-100 rounded-2xl border border-amber-200/50 flex flex-col items-center justify-center">
                        <div class="text-[10px] font-mono font-bold text-amber-800 uppercase tracking-widest mb-2 flex items-center space-x-1">
                            <span>▼ Carrier Roll Feed Direction ▼</span>
                        </div>

                        @if ($columns === 2)
                            <div style="max-width: 100%; overflow-x: auto;">
                                <div class="label-print-row-2up">
                                    @foreach (($printableRows[0] ?? []) as $previewLabel)
                                        @include('livewire.partials.garment-label', ['labelItem' => $previewLabel])
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- 1-UP SINGLE STICKER / HANGTAG PREVIEW -->
                            @php $pv = $previewItem1['model']; @endphp
                            <div class="bg-white border-2 border-slate-300 rounded-xl p-4 shadow-xl text-center space-y-2 relative overflow-hidden"
                                 style="width: 240px; min-height: 180px;">
                                @if ($templatePreset === '50x38')
                                    <div class="w-3.5 h-3.5 rounded-full border border-slate-300 bg-slate-100 mx-auto -mt-1 shadow-inner"></div>
                                @endif

                                @if ($showBrand)
                                    <div class="text-xs font-black tracking-wider uppercase text-slate-900">
                                        {{ $pv->product->brand->name ?? $customStoreName }}
                                    </div>
                                @endif

                                <div class="text-[11px] font-bold text-slate-800 leading-tight">
                                    {{ $pv->product->name }}
                                </div>

                                @if ($showSizeColor || $showArticleCode)
                                    <div class="flex items-center justify-center space-x-2 text-[10px] font-mono">
                                        @if ($showSizeColor)
                                            <span class="bg-slate-900 text-white font-extrabold px-1.5 py-0.5 rounded">SIZE: {{ $pv->size }}</span>
                                            <span class="text-slate-600">{{ $pv->color }}</span>
                                        @endif
                                        @if ($showArticleCode)
                                            <span class="text-slate-400">#{{ $pv->product->article_code }}</span>
                                        @endif
                                    </div>
                                @endif

                                <div class="py-1 px-2 bg-white flex items-center justify-center">
                                    {!! $previewItem1['barcode_svg'] !!}
                                </div>

                                @if ($showBarcodeText)
                                    <div class="text-[9px] font-mono font-bold text-slate-700">
                                        {{ $pv->barcode ?: $pv->sku }}
                                    </div>
                                @endif

                                <div class="border-t border-slate-200 pt-1.5">
                                    @if ($showMrp && $pv->mrp > $pv->selling_price)
                                        <div class="text-[10px] text-slate-400">
                                            MRP: <span class="line-through">₹{{ number_format($pv->mrp, 2) }}</span>
                                            <span class="text-emerald-600 font-bold ml-1">Save ₹{{ number_format($pv->mrp - $pv->selling_price, 2) }}</span>
                                        </div>
                                    @endif
                                    @if ($showSellingPrice)
                                        <div class="text-lg font-black text-slate-900 tracking-tight leading-none mt-0.5">
                                            ₹{{ number_format($pv->selling_price, 2) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-12 text-center text-slate-400">
                        <p class="text-xs">Add items to view realistic live label preview.</p>
                    </div>
                @endif
            </div>

            <!-- Print Execution Buttons -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-xl space-y-3">
                <h3 class="text-sm font-bold text-indigo-200 uppercase tracking-wider">Execute Print</h3>
                <p class="text-xs text-slate-300">
                    @if ($templatePreset === 'a4_24')
                        Select A4 paper, scale 100%, and disable headers and footers.
                    @else
                        Select your label printer, paper size {{ ($labelWidthMm * $columns) + ($columns === 2 ? $gapMm : 0) }} × {{ $labelHeightMm }} mm, margins None, scale 100%, and disable headers and footers.
                    @endif
                </p>

                <p class="text-xs font-bold text-indigo-100">
                    Print job: {{ $totalLabelsCount }} labels · {{ count($printableRows) }} {{ $templatePreset === 'a4_24' ? 'rows on A4' : (count($printableRows) === 1 ? 'print page' : 'print pages') }} · Set browser copies to 1.
                </p>

                <!-- 1. Browser Print Button (Opens Clean Print Media) -->
                <button type="button"
                        @if ($totalLabelsCount === 0) disabled @endif
                        @click="
                            const source = $root.querySelector('.print-only');
                            if (!source?.querySelector('.garment-label-card')) { return; }
                            const frame = document.createElement('iframe');
                            frame.title = 'Garment label print document';
                            frame.style.cssText = 'position: fixed; left: -10000px; top: 0; width: {{ ($labelWidthMm * $columns) + ($columns === 2 ? $gapMm : 0) }}mm; height: {{ $labelHeightMm }}mm; border: 0;';
                            const alignmentStyle = getComputedStyle($root);
                            frame.onload = async () => {
                                const printDocument = frame.contentDocument;
                                for (const variable of ['--label-left-offset', '--label-right-offset', '--label-top-offset']) {
                                    printDocument.body.style.setProperty(variable, alignmentStyle.getPropertyValue(variable));
                                }
                                await printDocument.fonts.ready;
                                frame.contentWindow.requestAnimationFrame(() => {
                                    frame.contentWindow.requestAnimationFrame(() => {
                                        frame.contentWindow.addEventListener('afterprint', () => frame.remove(), { once: true });
                                        frame.contentWindow.focus();
                                        frame.contentWindow.print();
                                    });
                                });
                            };
                            frame.srcdoc = '<!doctype html><meta charset=utf-8><title>Garment Labels</title><style>* { box-sizing: border-box; } html, body { display: block; width: auto; height: auto; min-height: 0; margin: 0; padding: 0; } body { font-family: Arial, sans-serif; } .label-print-container { margin: 0; padding: 0; }</style>' + source.innerHTML;
                            document.body.appendChild(frame);
                        "
                        class="w-full py-4 px-5 bg-indigo-500 hover:bg-indigo-400 text-white font-extrabold rounded-2xl shadow-lg shadow-indigo-500/30 transition flex items-center justify-center space-x-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Labels Now (Browser / PDF)</span>
                </button>

                <!-- 2. Direct Thermal Printer Driver Job -->
                <button type="button"
                        wire:click="dispatchThermalPrintJob"
                        class="w-full py-3 px-5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl border border-slate-700 transition flex items-center justify-center space-x-2 text-xs">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Send Raw to Thermal Label Printer (TSPL)</span>
                </button>
            </div>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- PRINTABLE SECTION: Rendered strictly during window.print()     -->
    <!-- Supports 2-Up rolls, 1-Up rolls, and A4 24-Up sheets           -->
    <!-- ============================================================== -->
    <div class="print-only">
        <style>
            .label-print-row-2up {
                display: flex;
                gap: {{ $gapMm }}mm;
                width: {{ ($labelWidthMm * 2) + $gapMm }}mm;
                height: {{ $labelHeightMm }}mm;
            }
            .label-print-row-2up > .garment-label-card {
                flex: 0 0 {{ $labelWidthMm }}mm;
                width: {{ $labelWidthMm }}mm;
                height: {{ $labelHeightMm }}mm;
                box-sizing: border-box;
                padding: 3mm 1.5mm 1mm;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                overflow: hidden;
                background: white;
                font-family: Arial, sans-serif;
            }
            .garment-label-content {
                width: {{ min(100, max(50, $labelScalePercent)) }}%;
                min-width: 0;
                display: grid;
                grid-template-rows: 3.2mm 3mm 6mm {{ $showBarcodeText ? '2.2mm' : '' }} 4.5mm;
                gap: 0.2mm;
                align-content: start;
                color: black;
                transform: translate(clamp(-10mm, var(--label-left-offset, 0mm), 10mm), clamp(-10mm, var(--label-top-offset, 0mm), 10mm));
            }
            .label-print-row-2up > .garment-label-card:nth-child(2) .garment-label-content {
                transform: translate(clamp(-10mm, var(--label-right-offset, 0mm), 10mm), clamp(-10mm, var(--label-top-offset, 0mm), 10mm));
            }
            .garment-label-content > * { flex-shrink: 0; min-width: 0; }
            .label-print-row-2up svg {
                width: 100%;
                height: 6mm;
                display: block;
            }
            @media print {
                @page {
                    @if ($templatePreset === 'a4_24')
                        size: A4 portrait;
                        margin: 8mm 6mm;
                    @elseif ($columns === 2)
                        size: {{ ($labelWidthMm * 2) + $gapMm }}mm {{ $labelHeightMm }}mm;
                        margin: 0;
                    @else
                        size: {{ $labelWidthMm }}mm {{ $labelHeightMm }}mm;
                        margin: 0;
                    @endif
                }
                html, body {
                    background: white !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                html, body, body > main, .barcode-label-page {
                    display: block !important;
                    width: auto !important;
                    max-width: none !important;
                    height: auto !important;
                    min-height: 0 !important;
                    overflow: visible !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                body:has(.barcode-label-page) > :not(main) {
                    display: none !important;
                }
                .barcode-label-page > .print-only {
                    margin: 0 !important;
                }
                .label-print-row-2up + .label-print-row-2up,
                .label-print-row-1up + .label-print-row-1up {
                    page-break-before: always !important;
                    break-before: page !important;
                }
                .label-print-container {
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }
                .label-print-row-2up {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: flex-start !important;
                    gap: {{ $gapMm }}mm !important;
                    align-items: stretch !important;
                    width: {{ ($labelWidthMm * 2) + $gapMm }}mm !important;
                    height: {{ $labelHeightMm }}mm !important;
                    max-height: {{ $labelHeightMm }}mm !important;
                    box-sizing: border-box !important;
                    overflow: hidden !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    page-break-after: auto !important;
                    break-after: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .label-print-row-1up {
                    display: block !important;
                    width: {{ $labelWidthMm }}mm !important;
                    height: {{ $labelHeightMm }}mm !important;
                    max-height: {{ $labelHeightMm }}mm !important;
                    box-sizing: border-box !important;
                    overflow: hidden !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    page-break-after: auto !important;
                    break-after: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .garment-label-card {
                    box-sizing: border-box !important;
                    overflow: hidden !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    padding: 1mm 1.5mm !important;
                    flex-shrink: 0 !important;
                    text-align: center !important;
                    font-family: Arial, sans-serif !important;
                    background: white !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    width: {{ $labelWidthMm }}mm !important;
                    height: {{ $labelHeightMm }}mm !important;
                    max-height: {{ $labelHeightMm }}mm !important;
                }
                .label-print-row-2up > .garment-label-card {
                    padding: 3mm 1.5mm 1mm !important;
                    flex-direction: row !important;
                    align-items: flex-start !important;
                    justify-content: center !important;
                }
                .garment-label-card svg {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: 6mm !important;
                    display: block !important;
                    margin: 0 auto !important;
                }
                .a4-sheet-grid {
                    display: flex !important;
                    flex-wrap: wrap !important;
                    gap: 2mm !important;
                    justify-content: flex-start !important;
                }
            }
        </style>

        <div class="label-print-container">
            @if ($templatePreset === 'a4_24')
                <div class="a4-sheet-grid">
                    @foreach ($printableRows as $row)
                        @foreach ($row as $item)
                            @php $pv = $item['model']; @endphp
                            <div class="garment-label-card" style="width: {{ $labelWidthMm }}mm; height: {{ $labelHeightMm }}mm; border: 0.2mm solid #cbd5e1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; line-height: 1;">
                                    @if ($showBrand)
                                        <span style="font-size: 6.5pt; font-weight: 900; text-transform: uppercase; color: #000;">{{ $pv->product->brand->name ?? $customStoreName }}</span>
                                    @else <span></span> @endif
                                    @if ($showSizeColor)
                                        <span style="font-size: 6pt; font-weight: 900; background: #000; color: #fff; padding: 0.5px 3px; border-radius: 2px;">{{ $pv->size }}</span>
                                    @endif
                                </div>
                                <div style="font-size: 6.5pt; font-weight: 700; color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: left; margin: 0.3mm 0;">
                                    {{ $pv->product->name }}
                                </div>
                                <div style="width: 100%; margin: 0.3mm 0; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                                    {!! $item['barcode_svg'] !!}
                                </div>
                                @if ($showBarcodeText)
                                    <div style="font-size: 6pt; font-family: monospace; font-weight: 700; color: #000; letter-spacing: 0.5px; text-align: center; line-height: 1;">
                                        {{ $pv->barcode ?: $pv->sku }}
                                    </div>
                                @endif
                                <div style="display: flex; justify-content: space-between; align-items: baseline; width: 100%; border-top: 0.2mm solid #000; padding-top: 0.4mm; line-height: 1;">
                                    <span style="font-size: 5.5pt; color: #333;">#{{ $pv->product->article_code }}</span>
                                    <div style="text-align: right; white-space: nowrap;">
                                        @if ($showMrp && $pv->mrp > $pv->selling_price)
                                            <span style="font-size: 5.5pt; color: #555; text-decoration: line-through; margin-right: 1mm;">₹{{ number_format($pv->mrp, 0) }}</span>
                                        @endif
                                        @if ($showSellingPrice)
                                            <span style="font-size: 8.5pt; font-weight: 900; color: #000;">₹{{ number_format($pv->selling_price, 0) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            @elseif ($columns === 2)
                <!-- 2-UP DUAL STICKER ROWS (Matches physical TVS LP 46 roll) -->
                @foreach ($printableRows as $row)
                    <div class="label-print-row-2up">
                        <!-- Left Sticker -->
                        @if (isset($row[0]))
                            @php $itemL = $row[0]; $pvL = $itemL['model']; @endphp
                            @include('livewire.partials.garment-label', ['labelItem' => $itemL])
                        @endif

                        <!-- Right Sticker (or placeholder if odd count) -->
                        @if (isset($row[1]))
                            @php $itemR = $row[1]; $pvR = $itemR['model']; @endphp
                            @include('livewire.partials.garment-label', ['labelItem' => $itemR])
                        @else
                            <div style="width: {{ $labelWidthMm }}mm; height: {{ $labelHeightMm }}mm; visibility: hidden;"></div>
                        @endif
                    </div>
                @endforeach
            @else
                <!-- 1-UP SINGLE COLUMN ROLL (e.g. 50x38mm Hangtag) -->
                @foreach ($printableRows as $row)
                    @foreach ($row as $item)
                        @php $pv = $item['model']; @endphp
                        <div class="label-print-row-1up">
                            <div class="garment-label-card" style="border: 0.2mm solid #cbd5e1;">
                                @if ($templatePreset === '50x38')
                                    <div style="width: 3mm; height: 3mm; border: 0.3mm solid #94a3b8; border-radius: 50%; margin: 0 auto;"></div>
                                @endif

                                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; line-height: 1;">
                                    @if ($showBrand)
                                        <span style="font-size: 9pt; font-weight: 900; text-transform: uppercase; color: #000; letter-spacing: 0.3px;">
                                            {{ $pv->product->brand->name ?? $customStoreName }}
                                        </span>
                                    @else <span></span> @endif
                                    @if ($showSizeColor)
                                        <span style="font-size: 9pt; font-weight: 900; background: #000; color: #fff; padding: 1.5px 5px; border-radius: 2px;">
                                            {{ $pv->size }}
                                        </span>
                                    @endif
                                </div>

                                <div style="font-size: 10.5pt; font-weight: 900; color: #000; margin: 0.4mm 0; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: left;">
                                    {{ $pv->product->name }}
                                </div>

                                <div style="width: 100%; margin: 0.4mm 0; display: flex; justify-content: center; align-items: center; height: 36px;">
                                    {!! $item['barcode_svg'] !!}
                                </div>

                                @if ($showBarcodeText)
                                    <div style="font-size: 8.5pt; font-family: monospace; font-weight: 900; color: #000; letter-spacing: 1.2px; text-align: center;">
                                        {{ $pv->barcode ?: $pv->sku }}
                                    </div>
                                @endif

                                <div style="border-top: 0.35mm solid #000; padding-top: 0.5mm; display: flex; justify-content: space-between; align-items: baseline;">
                                    <span style="font-size: 8pt; font-weight: 800; color: #000;">#{{ $pv->product->article_code }}</span>
                                    <div style="text-align: right; white-space: nowrap;">
                                        @if ($showMrp && $pv->mrp > $pv->selling_price)
                                            <span style="font-size: 8pt; font-weight: 700; color: #444; text-decoration: line-through; margin-right: 2mm;">
                                                ₹{{ number_format($pv->mrp, 0) }}
                                            </span>
                                        @endif
                                        @if ($showSellingPrice)
                                            <span style="font-size: 18pt; font-weight: 900; color: #000; line-height: 1;">
                                                ₹{{ number_format($pv->selling_price, 0) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @endif
        </div>
    </div>

</div>

