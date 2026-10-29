<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Category Master</h1>
                    <p class="text-sm text-slate-500">Organize clothing departments, subcategories, HSN codes, and default GST rates</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('masters.brands') }}" class="px-4 py-2 text-sm font-semibold rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span>Brand Master</span>
            </a>
            <button wire:click="openCreateModal" class="px-4 py-2 text-sm font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, code, or HSN (e.g. 6109, Shirt)..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
        </div>

        <div class="flex items-center space-x-3 overflow-x-auto pb-1 md:pb-0">
            <!-- Parent Category Filter -->
            <select wire:model.live="parentFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                <option value="all">All Hierarchies</option>
                <option value="root">Root Categories Only</option>
                @foreach($parentCategories as $pCat)
                    <option value="{{ $pCat->id }}">Under: {{ $pCat->name }}</option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Category Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Category Name</th>
                        <th class="py-3.5 px-4">Parent Category</th>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">HSN Code</th>
                        <th class="py-3.5 px-4">GST Rate</th>
                        <th class="py-3.5 px-4 text-center">Products</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $category->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">slug: {{ $category->slug }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($category->parent)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $category->parent->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium italic">Root Level</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-600">
                                {{ $category->code ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-bold text-indigo-600">
                                {{ $category->hsn_code ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ number_format($category->default_tax_rate, 1) }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $category->products_count }} items
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $category->id }})" class="focus:outline-none">
                                    @if($category->is_active)
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
                                <button wire:click="editCategory({{ $category->id }})" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit Category">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="deleteCategory({{ $category->id }})" wire:confirm="Are you sure you want to delete category '{{ $category->name }}'?" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Category">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="text-base font-semibold text-slate-600">No categories found</p>
                                <p class="text-sm text-slate-400 mt-1">Try refining your search or add a new category.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- Category Modal (Create / Edit) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $isEditing ? 'Edit Category' : 'Create New Category' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="saveCategory" class="p-6 space-y-4">
                    <!-- Category Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="e.g. T-Shirts, Denim Jeans, Formal Shirts" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Parent Category -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Parent Category (Optional)</label>
                        <select wire:model="parent_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="">None (Top-Level / Root Department)</option>
                            @foreach($parentCategories as $pCat)
                                @if(!$isEditing || $pCat->id !== $categoryId)
                                    <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('parent_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Category Code -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category Code</label>
                            <input type="text" wire:model="code" placeholder="e.g. TSHIRT, PANT" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white font-mono">
                            @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- HSN Code -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">HSN Code (GST)</label>
                            <input type="text" wire:model="hsn_code" placeholder="e.g. 6109, 6203, 6205" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white font-mono">
                            <p class="text-[11px] text-slate-400 mt-0.5">T-Shirts: 6109 | Pants: 6203 | Shirts: 6205</p>
                            @error('hsn_code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Default GST Rate -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Default GST Rate (%) <span class="text-rose-500">*</span></label>
                        <div class="flex items-center space-x-3">
                            <label class="flex items-center space-x-1.5 text-xs text-slate-700 font-semibold cursor-pointer">
                                <input type="radio" wire:model="default_tax_rate" value="5" class="text-indigo-600 focus:ring-indigo-500">
                                <span>5% (Garments &lt; ₹1,000)</span>
                            </label>
                            <label class="flex items-center space-x-1.5 text-xs text-slate-700 font-semibold cursor-pointer">
                                <input type="radio" wire:model="default_tax_rate" value="12" class="text-indigo-600 focus:ring-indigo-500">
                                <span>12% (Garments &ge; ₹1,000)</span>
                            </label>
                            <label class="flex items-center space-x-1.5 text-xs text-slate-700 font-semibold cursor-pointer">
                                <input type="radio" wire:model="default_tax_rate" value="18" class="text-indigo-600 focus:ring-indigo-500">
                                <span>18%</span>
                            </label>
                        </div>
                        @error('default_tax_rate') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="pt-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Active (Visible in POS and Catalogue)</span>
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm">
                            {{ $isEditing ? 'Save Changes' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

