<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Size Group Master</h1>
                    <p class="text-sm text-slate-500">Configure sizing systems (Alpha, Waist numeric, Kids ages, Footwear) for rapid matrix creation</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="openCreateModal" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-amber-600 hover:bg-amber-700 text-white transition shadow-lg shadow-amber-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Add Size Group</span>
            </button>
        </div>
    </div>

    <!-- Feedback Message -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by size group name or code (e.g. ALPHA, Waist)..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition">
        </div>

        <div class="flex items-center space-x-3">
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Size Groups Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Size Group Name</th>
                        <th class="py-3.5 px-4">System Code</th>
                        <th class="py-3.5 px-4">Configured Sizes</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sizeGroups as $group)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $group->name }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ count($group->sizes ?? []) }} sizes configured</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-xs font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/80 uppercase">
                                    {{ $group->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1.5 max-w-xl">
                                    @foreach($group->sizes ?? [] as $s)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $s }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $group->id }})" class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold transition {{ $group->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $group->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $group->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" wire:click="editSizeGroup({{ $group->id }})" class="p-1.5 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition" title="Edit Group">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button type="button" wire:click="deleteSizeGroup({{ $group->id }})" wire:confirm="Are you sure you want to delete size group '{{ $group->name }}'?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete Group">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                <p class="text-base font-bold text-slate-700">No size groups found</p>
                                <p class="text-xs text-slate-400 mt-1">Add a new size group or load standard apparel presets.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sizeGroups->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $sizeGroups->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE / EDIT MODAL -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $isEditing ? 'Edit Size Group' : 'Create New Size Group' }}
                        </h3>
                        <p class="text-xs text-slate-500">Configure sizing sequence and presets for matrix generation</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveSizeGroup" class="p-6 space-y-4">
                    <!-- Quick Presets -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">⚡ Quick Apparel Presets</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="loadPreset('alpha')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition">
                                Alpha (XS - 3XL)
                            </button>
                            <button type="button" wire:click="loadPreset('waist')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200 hover:bg-indigo-100 transition">
                                Waist (28 - 40)
                            </button>
                            <button type="button" wire:click="loadPreset('kids')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition">
                                Kids (1Y - 12Y)
                            </button>
                            <button type="button" wire:click="loadPreset('footwear')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-800 border border-rose-200 hover:bg-rose-100 transition">
                                Footwear (6 - 11)
                            </button>
                            <button type="button" wire:click="loadPreset('free')" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-800 border border-slate-200 hover:bg-slate-200 transition">
                                Free Size
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Group Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Group Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="e.g. Standard Alpha (XS - XXL)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- System Code -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">System Code <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="code" placeholder="e.g. ALPHA, WAIST, KIDS" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('code') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Active Status</label>
                            <select wire:model="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Size Tags Builder -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Configured Sizes (In Sequence) <span class="text-rose-500">*</span></label>
                        
                        <!-- Tag Cloud Display -->
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl min-h-[56px] flex flex-wrap gap-2 items-center">
                            @forelse($sizes as $index => $sz)
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-white text-slate-800 border border-slate-200 shadow-sm">
                                    <span>{{ $sz }}</span>
                                    <button type="button" wire:click="removeSize({{ $index }})" class="ml-1.5 text-slate-400 hover:text-rose-600 focus:outline-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            @empty
                                <span class="text-xs text-slate-400">No sizes added yet. Add sizes below or click a quick preset.</span>
                            @endforelse
                        </div>
                        @error('sizes') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                        <!-- Input to add individual size -->
                        <div class="mt-2.5 flex items-center space-x-2">
                            <input type="text" wire:model="newSize" wire:keydown.enter.prevent="addSize" placeholder="Add size (e.g. 4XL or 42) & press Enter" class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">
                            <button type="button" wire:click="addSize" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-700 transition">
                                + Add Size
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md shadow-amber-600/20 transition">
                            {{ $isEditing ? 'Update Size Group' : 'Save Size Group' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
