<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-2xl border border-blue-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Supplier / Vendor Master</h1>
                    <p class="text-sm text-slate-500">Manage fabric mills, garment manufacturers, GSTIN details, and credit terms</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="openCreateModal" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition shadow-lg shadow-blue-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Add Supplier</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, company, GSTIN, city, phone..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
        </div>

        <div class="flex items-center space-x-3">
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Supplier & Company</th>
                        <th class="py-3.5 px-4">GSTIN & State</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4 text-center">Credit Days</th>
                        <th class="py-3.5 px-4 text-right">Balance</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $sup)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $sup->name }}</div>
                                @if($sup->company_name)
                                    <div class="text-xs text-slate-500 font-medium">{{ $sup->company_name }}</div>
                                @endif
                                @if($sup->city)
                                    <span class="inline-flex items-center text-[10px] text-slate-400 mt-0.5">
                                        📍 {{ $sup->city }}, {{ $sup->state }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-xs font-bold text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-200 inline-block">
                                    {{ $sup->gstin ?? 'UNREGISTERED' }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">State Code: {{ $sup->state_code }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                @if($sup->phone)
                                    <div class="font-medium text-slate-800">📞 {{ $sup->phone }}</div>
                                @endif
                                @if($sup->email)
                                    <div class="text-slate-400 text-[11px]">{{ $sup->email }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700">
                                    {{ $sup->payment_terms_days }} days
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold">
                                <span class="{{ $sup->current_balance > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                                    ₹{{ number_format($sup->current_balance, 2) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $sup->id }})" class="focus:outline-none">
                                    @if($sup->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 hover:bg-slate-200 transition cursor-pointer">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <button wire:click="editSupplier({{ $sup->id }})" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Supplier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="deleteSupplier({{ $sup->id }})" wire:confirm="Are you sure you want to delete supplier '{{ $sup->name }}'?" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Supplier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p class="text-base font-bold text-slate-700">No suppliers registered</p>
                                <p class="text-xs text-slate-400 mt-1">Add your fabric suppliers, garment vendors, and job-workers.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

    <!-- Supplier Modal (Create / Edit) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $isEditing ? 'Edit Supplier' : 'Register New Supplier' }}
                        </h3>
                        <p class="text-xs text-slate-500">Configure vendor profile, GSTIN, and credit payment terms</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveSupplier" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Supplier Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Contact Person / Supplier Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="e.g. Ramesh Kumar, Tirupur Mills" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Company / Mill Name</label>
                            <input type="text" wire:model="company_name" placeholder="e.g. Tirupur Cotton Mills Pvt Ltd" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        </div>

                        <!-- GSTIN -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">GSTIN</label>
                            <input type="text" wire:model="gstin" placeholder="e.g. 33AABCT1234F1Z1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                            <input type="text" wire:model="phone" placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City / Town</label>
                            <input type="text" wire:model="city" placeholder="e.g. Tirupur, Surat, Salem" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        </div>

                        <!-- State -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">State & Code <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" wire:model="state" class="col-span-2 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white">
                                <input type="text" wire:model="state_code" placeholder="33" class="px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-center focus:bg-white">
                            </div>
                        </div>

                        <!-- Payment Terms -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Credit Payment Terms (Days)</label>
                            <input type="number" wire:model="payment_terms_days" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        </div>

                        <!-- Opening Balance -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Opening Balance (INR)</label>
                            <input type="number" step="0.01" wire:model="opening_balance" {{ $isEditing ? 'disabled' : '' }} class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="pt-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <span class="text-xs font-bold text-slate-700">Active Supplier (Eligible for Purchase Orders)</span>
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">
                            {{ $isEditing ? 'Save Changes' : 'Register Supplier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
