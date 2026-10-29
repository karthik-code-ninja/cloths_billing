<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Brand Master</h1>
                    <p class="text-sm text-slate-500">Manage apparel manufacturer brands, labels, brand codes, and active statuses</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('masters.categories') }}" class="px-4 py-2 text-sm font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Category Master</span>
            </a>
            <button wire:click="openCreateModal" class="px-4 py-2 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Brand</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search brands by name or code (e.g. Nike, RAYM)..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
        </div>

        <div class="flex items-center space-x-3">
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Brand Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Brand Name</th>
                        <th class="py-3.5 px-4">Brand Code</th>
                        <th class="py-3.5 px-4 text-center">Linked Products</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-xs">
                                        {{ substr($brand->name, 0, 2) }}
                                    </div>
                                    <div class="font-bold text-slate-900">{{ $brand->name }}</div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-600">
                                <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $brand->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $brand->products_count }} items
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $brand->id }})" class="focus:outline-none">
                                    @if($brand->is_active)
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
                                <button wire:click="editBrand({{ $brand->id }})" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Edit Brand">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="deleteBrand({{ $brand->id }})" wire:confirm="Are you sure you want to delete brand '{{ $brand->name }}'?" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Brand">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <p class="text-base font-semibold text-slate-600">No brands found</p>
                                <p class="text-sm text-slate-400 mt-1">Add your apparel brands to organize products and reports.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $brands->links() }}
            </div>
        @endif
    </div>

    <!-- Brand Modal (Create / Edit) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $isEditing ? 'Edit Brand' : 'Create New Brand' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="saveBrand" class="p-6 space-y-4">
                    <!-- Brand Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Brand Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.live.debounce.300ms="name" placeholder="e.g. Nike, Balenciaga, Raymond, NextDNA" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Brand Code -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Brand Code (Unique) <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="code" placeholder="e.g. NIKE, BALEN, RAYM" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-mono">
                        <p class="text-[11px] text-slate-400 mt-0.5">Short unique identifier for barcodes, hangtags, and SKUs.</p>
                        @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="pt-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-slate-700">Active (Visible in POS and Catalogue)</span>
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                            {{ $isEditing ? 'Save Changes' : 'Create Brand' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

