<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-indigo-600 text-xs font-mono font-semibold uppercase tracking-wider mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Store Administration & Policy Config</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dynamic General Settings</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Configure company profile, GST rules, POS limits, thermal receipts, and barcode presets dynamically.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button"
                    wire:click="saveSettings"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save All Settings</span>
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center space-x-2 animate-in fade-in">
            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
            <span class="font-bold block">Please correct the following errors:</span>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Settings Navigation Tabs -->
    <div class="flex space-x-2 border-b border-slate-200 text-xs font-bold overflow-x-auto pb-1">
        <button type="button"
                wire:click="$set('activeTab', 'store')"
                class="px-4 py-3 rounded-t-xl transition flex items-center space-x-2 whitespace-nowrap {{ $activeTab === 'store' ? 'bg-white text-indigo-600 border-t-2 border-indigo-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <span>🏪</span>
            <span>Store Profile</span>
        </button>
        <button type="button"
                wire:click="$set('activeTab', 'tax')"
                class="px-4 py-3 rounded-t-xl transition flex items-center space-x-2 whitespace-nowrap {{ $activeTab === 'tax' ? 'bg-white text-indigo-600 border-t-2 border-indigo-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <span>📑</span>
            <span>Taxation & GST</span>
        </button>
        <button type="button"
                wire:click="$set('activeTab', 'pos')"
                class="px-4 py-3 rounded-t-xl transition flex items-center space-x-2 whitespace-nowrap {{ $activeTab === 'pos' ? 'bg-white text-indigo-600 border-t-2 border-indigo-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <span>💳</span>
            <span>POS Billing Rules</span>
        </button>
        <button type="button"
                wire:click="$set('activeTab', 'thermal')"
                class="px-4 py-3 rounded-t-xl transition flex items-center space-x-2 whitespace-nowrap {{ $activeTab === 'thermal' ? 'bg-white text-indigo-600 border-t-2 border-indigo-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <span>🖨️</span>
            <span>Thermal Receipts</span>
        </button>
        <button type="button"
                wire:click="$set('activeTab', 'barcode')"
                class="px-4 py-3 rounded-t-xl transition flex items-center space-x-2 whitespace-nowrap {{ $activeTab === 'barcode' ? 'bg-white text-indigo-600 border-t-2 border-indigo-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <span>🏷️</span>
            <span>Barcodes & Labels</span>
        </button>
        <button type="button"
                wire:click="$set('activeTab', 'localization')"
                class="px-4 py-3 rounded-t-xl transition flex items-center space-x-2 whitespace-nowrap {{ $activeTab === 'localization' ? 'bg-white text-indigo-600 border-t-2 border-indigo-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <span>🌐</span>
            <span>Localization</span>
        </button>
    </div>

    <!-- Active Tab Panels -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200">

        <!-- TAB 1: STORE PROFILE -->
        @if ($activeTab === 'store')
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Store & Company Identity</h2>
                    <p class="text-xs text-slate-500">Appears on invoices, thermal receipt slips, window titles, and reports.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Store / Brand Name *</label>
                        <input type="text" wire:model="store_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-indigo-500">
                        <span class="text-[11px] text-indigo-600 font-medium mt-1 block">Displayed on the top header, header branch pill, thermal receipts, hangtag labels, and invoices.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Tagline / Subtitle</label>
                        <input type="text" wire:model="store_tagline" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Contact Phone / Mobile</label>
                        <input type="text" wire:model="store_phone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Support Email</label>
                        <input type="email" wire:model="store_email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Physical Store Address</label>
                        <input type="text" wire:model="store_address" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">City</label>
                        <input type="text" wire:model="store_city" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">State</label>
                        <input type="text" wire:model="store_state" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Pin Code</label>
                        <input type="text" wire:model="store_pincode" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">State GST Code (e.g. 33 for TN) *</label>
                        <input type="text" wire:model="store_state_code" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold focus:ring-2 focus:ring-indigo-500" placeholder="33">
                        <span class="text-[11px] text-slate-400 mt-1 block">Used to determine Intra-state (CGST+SGST) vs Inter-state (IGST).</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 2: TAXATION & GST -->
        @if ($activeTab === 'tax')
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Taxation & Invoice Standards (GST)</h2>
                    <p class="text-xs text-slate-500">Configure Indian GSTIN, sequential numbering prefixes, and default apparel tax rates.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Store GSTIN Number</label>
                        <input type="text" wire:model="gstin" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold uppercase focus:ring-2 focus:ring-indigo-500" placeholder="33AAAAA0000A1Z5">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Default Garment Tax Rate (%)</label>
                        <input type="number" step="0.25" wire:model="default_tax_rate" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Invoice Number Prefix *</label>
                        <input type="text" wire:model="invoice_prefix" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold uppercase focus:ring-2 focus:ring-indigo-500" placeholder="INV">
                        <span class="text-[11px] text-slate-400 mt-1 block">Produces invoice numbers like: INV-202610-MAIN-00001</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Return / Credit Note Prefix *</label>
                        <input type="text" wire:model="return_prefix" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold uppercase focus:ring-2 focus:ring-indigo-500" placeholder="RET">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Default Apparel HSN Code</label>
                        <input type="text" wire:model="hsn_default_apparel" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500" placeholder="6203">
                    </div>

                    <div class="sm:col-span-2 pt-2">
                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="tax_inclusive_by_default" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Selling Prices are Tax-Inclusive by Default</span>
                                <span class="text-[11px] text-slate-500">When enabled, tag price (e.g. ₹999) includes GST. Tax is extracted during billing without adding extra onto total.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 3: POS BILLING RULES -->
        @if ($activeTab === 'pos')
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">POS Billing & Cashier Controls</h2>
                    <p class="text-xs text-slate-500">Enforce cashier discount limits, negative stock policies, and round-offs.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Cashier Max Permitted Discount (%)</label>
                        <input type="number" step="0.5" wire:model="cashier_max_discount_percentage" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-indigo-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">Discounts exceeding this require manager PIN override.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Default Checkout Payment Method</label>
                        <select wire:model="default_payment_method" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="cash">Cash (Auto-selected)</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="card">Credit / Debit Card</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 space-y-3 pt-2">
                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="require_manager_override_for_discount" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Strict Manager Authorization on High Discounts</span>
                                <span class="text-[11px] text-slate-500">Block cashier checkouts whenever total discount exceeds their limit until a manager logs in to approve.</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="allow_negative_stock" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Allow Negative Stock Checkout</span>
                                <span class="text-[11px] text-slate-500">Permit cashiers to bill items even when physical stock count is 0 in the database (useful for busy holiday rush).</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="enable_round_off" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Round-Off Bill Grand Total to Nearest Rupee</span>
                                <span class="text-[11px] text-slate-500">Automatically rounds paise amounts (e.g. ₹499.40 → ₹499.00; ₹499.60 → ₹500.00).</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="enable_walkin_customer" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Enable Walk-In Billing without Mandatory Customer Info</span>
                                <span class="text-[11px] text-slate-500">Allows fast one-click checkout for walk-in retail shoppers who do not wish to provide phone numbers.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 4: THERMAL RECEIPTS -->
        @if ($activeTab === 'thermal')
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Thermal Receipt Templates & Formatting</h2>
                    <p class="text-xs text-slate-500">Customize header, footer, return policy message, and printed tax breakdowns.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Receipt Header Store Title</label>
                        <input type="text" wire:model="receipt_header_title" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-indigo-500" placeholder="NEXTDNA RETAIL MEGASTORE">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Default Thermal Paper Width</label>
                        <select wire:model="receipt_paper_width" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold">
                            <option value="80">80 mm (42 Columns - Standard POS)</option>
                            <option value="58">58 mm (32 Columns - Compact POS)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Receipt Footer & Return Policy Message</label>
                        <textarea wire:model="receipt_footer_message" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                        <span class="text-[11px] text-slate-400 mt-1 block">Printed at the bottom of customer receipts above the paper cut line.</span>
                    </div>

                    <div class="sm:col-span-2 space-y-3 pt-2">
                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="receipt_show_tax_breakdown" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Show CGST & SGST Split on Thermal Receipts</span>
                                <span class="text-[11px] text-slate-500">Prints individual CGST and SGST amounts below the subtotal.</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="receipt_show_savings" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Highlight "Total Savings" on Receipt</span>
                                <span class="text-[11px] text-slate-500">Shows customer how much money they saved compared to original MRP.</span>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                            <input type="checkbox" wire:model="receipt_show_powered_by" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-800 block text-xs">Show "Powered by NextDNA Technologies"</span>
                                <span class="text-[11px] text-slate-500">Appends software branding line at the bottom.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 5: BARCODES & LABELS -->
        @if ($activeTab === 'barcode')
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Barcode & Hangtag Defaults</h2>
                    <p class="text-xs text-slate-500">Configure GS1 country codes, auto-generation standards, and label presets.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Default Barcode Generation Standard</label>
                        <select wire:model="barcode_default_type" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold">
                            <option value="EAN13">GS1 EAN-13 (Standard Retail Barcode)</option>
                            <option value="CODE128">Code-128 (Alphanumeric Sequential)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">GS1 Country Prefix (e.g. 890 for India)</label>
                        <input type="text" wire:model="barcode_country_prefix" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold focus:ring-2 focus:ring-indigo-500" maxlength="3">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Manufacturer / Store Prefix Code (4 digits)</label>
                        <input type="text" wire:model="barcode_mfg_code" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500" maxlength="4">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Brand Name on Garment Hangtags</label>
                        <input type="text" wire:model="barcode_label_store_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="NextDNA Retail">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Default Hangtag Dimensions</label>
                        <select wire:model="barcode_label_default_preset" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold">
                            <option value="50x38">Garment Hangtag (50 mm × 38 mm)</option>
                            <option value="50x25">Standard Adhesive Sticker (50 mm × 25 mm)</option>
                            <option value="38x25">Compact Accessory Tag (38 mm × 25 mm)</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAB 6: LOCALIZATION -->
        @if ($activeTab === 'localization')
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Regional & System Localization</h2>
                    <p class="text-xs text-slate-500">Configure Indian currency formats, date formats, and timezones.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Currency Symbol</label>
                        <input type="text" wire:model="currency_symbol" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-indigo-500" placeholder="₹">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Currency Code</label>
                        <input type="text" wire:model="currency_code" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold uppercase focus:ring-2 focus:ring-indigo-500" placeholder="INR">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Date Format</label>
                        <select wire:model="date_format" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono">
                            <option value="d/m/Y">DD/MM/YYYY (Indian Standard - e.g. 28/10/2026)</option>
                            <option value="d-m-Y">DD-MM-YYYY (e.g. 28-10-2026)</option>
                            <option value="Y-m-d">YYYY-MM-DD (ISO Format - e.g. 2026-10-28)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">System Timezone</label>
                        <select wire:model="timezone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="Asia/Kolkata">Asia/Kolkata (IST - Indian Standard Time)</option>
                            <option value="UTC">UTC</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Interface Language</label>
                        <select wire:model="app_language" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold">
                            <option value="en">English (Default)</option>
                            <option value="ta">Tamil / தமிழ் (Regional)</option>
                        </select>
                    </div>
                </div>
            </div>
        @endif

        <!-- Bottom Save Button -->
        <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Settings are dynamically cached and applied immediately.</span>
            <button type="button"
                    wire:click="saveSettings"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/30 text-xs flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save All Settings</span>
            </button>
        </div>

    </div>

</div>

