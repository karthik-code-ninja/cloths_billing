<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-2xl border border-indigo-100 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Staff &amp; User Management</h1>
                    <p class="text-sm text-slate-500">Manage cashiers, store managers, salesperson attribution, discount limits, and logins</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="openCreateModal" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-lg shadow-indigo-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Add Staff Member</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Top Summary Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- 1. Total Staff -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Staff</div>
                <div class="text-xl font-black text-slate-900">{{ $totalStaff }} Users</div>
            </div>
        </div>

        <!-- 2. Active Staff -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Active Logins</div>
                <div class="text-xl font-black text-emerald-700">{{ $activeStaff }} Active</div>
            </div>
        </div>

        <!-- 3. POS Cashiers -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">POS Cashiers</div>
                <div class="text-xl font-black text-slate-900">{{ $totalCashiers }} Cashiers</div>
            </div>
        </div>

        <!-- 4. Managers & Admins -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
            <div class="w-11 h-11 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Managers &amp; Admins</div>
                <div class="text-xl font-black text-slate-900">{{ $totalManagers }} Staff</div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by staff name, email, or mobile..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
        </div>

        <div class="flex items-center space-x-3 overflow-x-auto pb-1 md:pb-0">
            <!-- Role Filter -->
            <select wire:model.live="roleFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                <option value="all">All Roles</option>
                <option value="admin">Owner / Admin</option>
                <option value="manager">Store Manager</option>
                <option value="cashier">POS Cashier</option>
                <option value="inventory_staff">Stock Incharge</option>
                <option value="accountant">Chief Accountant</option>
            </select>

            <!-- Branch Filter -->
            <select wire:model.live="branchFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                <option value="all">All Branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select wire:model.live="statusFilter" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Staff Users Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Staff Member</th>
                        <th class="py-3.5 px-4">Role &amp; Title</th>
                        <th class="py-3.5 px-4">Assigned Branch</th>
                        <th class="py-3.5 px-4">Contact Phone</th>
                        <th class="py-3.5 px-4">Checkout Permissions</th>
                        <th class="py-3.5 px-4 text-center">Login Access</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/75 transition">
                            <!-- Name & Email with Avatar -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-slate-700 to-slate-900 text-white flex items-center justify-center font-black text-xs shadow-sm">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm flex items-center space-x-1.5">
                                            <span>{{ $u->name }}</span>
                                            @if($u->id === auth()->id())
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">You</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-4">
                                @php
                                    $roleBadge = match($u->role) {
                                        'admin' => ['label' => 'Owner / Admin', 'class' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                                        'manager' => ['label' => 'Store Manager', 'class' => 'bg-blue-50 text-blue-800 border-blue-200'],
                                        'cashier' => ['label' => 'POS Cashier', 'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                        'inventory_staff' => ['label' => 'Stock Incharge', 'class' => 'bg-amber-50 text-amber-800 border-amber-200'],
                                        'accountant' => ['label' => 'Chief Accountant', 'class' => 'bg-slate-100 text-slate-800 border-slate-200'],
                                        default => ['label' => ucfirst($u->role), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border {{ $roleBadge['class'] }}">
                                    {{ $roleBadge['label'] }}
                                </span>
                            </td>

                            <!-- Branch -->
                            <td class="py-3.5 px-4 text-xs font-semibold text-slate-700">
                                <div>{{ $u->branch->name ?? 'Main Branch' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $u->branch->code ?? 'SLM01' }}</div>
                            </td>

                            <!-- Contact Phone -->
                            <td class="py-3.5 px-4 text-xs text-slate-600 font-mono">
                                {{ $u->phone ?: '—' }}
                            </td>

                            <!-- Checkout Permissions -->
                            <td class="py-3.5 px-4 text-xs space-y-1">
                                <div class="flex items-center space-x-1.5">
                                    <span class="font-bold text-slate-700">Max Discount:</span>
                                    <span class="font-bold text-indigo-600 font-mono">{{ (float)$u->max_discount_percentage }}%</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    @if($u->can_override_price)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Can override retail prices at checkout">Price Override</span>
                                    @endif
                                    @if($u->can_give_refund)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Can authorize customer cash refunds">Refunds</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Login Status Toggle -->
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $u->id }})" @if($u->id === auth()->id()) disabled title="Cannot deactivate self" @endif class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold transition {{ $u->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }} {{ $u->id === auth()->id() ? 'opacity-70 cursor-not-allowed' : 'hover:scale-105' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $u->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $u->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button type="button" wire:click="openResetPasswordModal({{ $u->id }})" class="p-1.5 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition" title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </button>
                                <button type="button" wire:click="editUser({{ $u->id }})" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition" title="Edit Staff Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                @if($u->id !== auth()->id())
                                    <button type="button" wire:click="deleteUser({{ $u->id }})" wire:confirm="Are you sure you want to remove or deactivate staff member '{{ $u->name }}'?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete or Deactivate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-base font-bold text-slate-700">No staff members found</p>
                                <p class="text-xs text-slate-400 mt-1">Try resetting your search query or add a new staff member.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- 1. CREATE / EDIT USER MODAL -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            {{ $isEditing ? 'Edit Staff Member' : 'Add New Staff Member' }}
                        </h3>
                        <p class="text-xs text-slate-500">Configure role, branch assignment, checkout permissions, and credentials</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveUser" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name &amp; Designation <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="e.g. Ramesh Kumar (Senior Cashier)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" placeholder="ramesh@nextdna.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Phone</label>
                            <input type="text" wire:model="phone" placeholder="9842712345" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('phone') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role / Function <span class="text-rose-500">*</span></label>
                            <select wire:model.live="role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="cashier">POS Cashier / Sales</option>
                                <option value="manager">Store Manager</option>
                                <option value="inventory_staff">Stock Incharge</option>
                                <option value="accountant">Chief Accountant</option>
                                <option value="admin">Owner / Administrator</option>
                            </select>
                            @error('role') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Branch Assignment -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Assigned Branch <span class="text-rose-500">*</span></label>
                            <select wire:model="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                                @endforeach
                            </select>
                            @error('branch_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Password @if(!$isEditing)<span class="text-rose-500">*</span>@else<span class="text-slate-400 font-normal text-[11px]">(Leave blank to keep)</span>@endif
                            </label>
                            <input type="password" wire:model="password" placeholder="Min. 6 characters" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password</label>
                            <input type="password" wire:model="password_confirmation" placeholder="Re-enter password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Checkout & Discount Permissions Box -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <div class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between">
                            <span>Checkout &amp; Store Permissions</span>
                            <span class="text-[10px] text-slate-400 font-normal">Controls daily retail cashier limits</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Max Discount %</label>
                                <div class="relative">
                                    <input type="number" step="0.5" min="0" max="100" wire:model="max_discount_percentage" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 font-bold text-xs">%</span>
                                </div>
                                @error('max_discount_percentage') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-3 sm:pt-4">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model="can_override_price" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                    <span class="text-xs font-bold text-slate-700">Override Price</span>
                                </label>
                            </div>

                            <div class="pt-3 sm:pt-4">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model="can_give_refund" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                    <span class="text-xs font-bold text-slate-700">Give Cash Refunds</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700">Account is Active &amp; Allowed to Log In</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition">
                            {{ $isEditing ? 'Update Staff Member' : 'Save Staff Member' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 2. QUICK RESET PASSWORD MODAL -->
    @if($showResetPasswordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full border border-slate-200 overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Reset Staff Password</h3>
                        <p class="text-xs text-slate-500">For: <strong>{{ $resetUserName }}</strong></p>
                    </div>
                    <button wire:click="$set('showResetPasswordModal', false)" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-200/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveResetPassword" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="newPassword" placeholder="Enter new password (min. 6 chars)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                        @error('newPassword') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" wire:click="$set('showResetPasswordModal', false)" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md shadow-amber-600/20 transition">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

