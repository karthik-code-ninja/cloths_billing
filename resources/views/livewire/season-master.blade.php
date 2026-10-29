<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-violet-50 text-violet-600 rounded-2xl border border-violet-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Season & Collection Master</h1>
                    <p class="text-sm text-slate-500">Manage apparel fashion cycles (Summer Spring, Festive, Autumn Winter, Wedding Collections)</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="openCreateModal" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-violet-600 hover:bg-violet-700 text-white transition shadow-lg shadow-violet-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Add Season</span>
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
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by season name or code (e.g. Summer, SS26)..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white transition">
        </div>

        <div class="flex items-center space-x-3">
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-violet-500 text-slate-700">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Seasons Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Season / Collection Name</th>
                        <th class="py-3.5 px-4">Season Code</th>
                        <th class="py-3.5 px-4 text-center">Linked Products</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($seasons as $season)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $season->name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-xs font-bold text-violet-800 bg-violet-50 px-2.5 py-0.5 rounded border border-violet-200/80 uppercase">
                                    {{ $season->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $season->products_count }} styles
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $season->id }})" class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold transition {{ $season->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $season->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $season->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" wire:click="editSeason({{ $season->id }})" class="p-1.5 text-slate-400 hover:text-violet-600 rounded-lg hover:bg-violet-50 transition" title="Edit Season">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button type="button" wire:click="deleteSeason({{ $season->id }})" wire:confirm="Are you sure you want to delete season '{{ $season->name }}'?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete Season">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-base font-bold text-slate-700">No seasons or collections configured</p>
                                <p class="text-xs text-slate-400 mt-1">Add a new season or fashion cycle to categorize product releases.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($seasons->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $seasons->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE / EDIT MODAL -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $isEditing ? 'Edit Season / Collection' : 'Create Season / Collection' }}
                        </h3>
                        <p class="text-xs text-slate-500">Define fashion cycle and collection code</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveSeason" class="p-6 space-y-4">
                    <!-- Season Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Season Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="e.g. Summer Spring 2026, Diwali Festive" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Season Code -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Collection Code <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="code" placeholder="e.g. SS26, FEST26, AW26" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @error('code') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                        <select wire:model="is_active" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 rounded-xl shadow-md shadow-violet-600/20 transition">
                            {{ $isEditing ? 'Update Season' : 'Save Season' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
