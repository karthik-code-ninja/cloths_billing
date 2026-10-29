<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="border-b border-slate-200/80 pb-5">
        <div class="flex items-center space-x-3">
            <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-2xl border border-indigo-100 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">My Account Profile</h1>
                <p class="text-sm text-slate-500">Manage your retail staff profile details, contact information, and security credentials</p>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: User Summary Card -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-center">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-400 text-white flex items-center justify-center font-black text-2xl shadow-xl shadow-indigo-500/20 mb-4">
                    {{ substr($name, 0, 1) }}
                </div>
                <h2 class="text-lg font-black text-slate-900">{{ $name }}</h2>
                <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $email }}</p>

                <div class="mt-4 flex flex-wrap gap-2 justify-center">
                    @php
                        $roleBadge = match($role) {
                            'admin' => ['label' => 'Owner / Admin', 'class' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                            'manager' => ['label' => 'Store Manager', 'class' => 'bg-blue-50 text-blue-800 border-blue-200'],
                            'cashier' => ['label' => 'POS Cashier', 'class' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                            'inventory_staff' => ['label' => 'Stock Incharge', 'class' => 'bg-amber-50 text-amber-800 border-amber-200'],
                            'accountant' => ['label' => 'Chief Accountant', 'class' => 'bg-slate-100 text-slate-800 border-slate-200'],
                            default => ['label' => ucfirst($role), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold border {{ $roleBadge['class'] }}">
                        {{ $roleBadge['label'] }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $branchName }}
                    </span>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 text-left text-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Mobile Phone:</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $phone ?: 'Not set' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Account Created:</span>
                        <span class="font-bold text-slate-800">{{ $memberSince }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">POS Checkout Limit:</span>
                        <span class="font-bold text-indigo-600 font-mono">{{ (float)auth()->user()->max_discount_percentage }}% Disc</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Price Override:</span>
                        <span class="font-bold {{ auth()->user()->can_override_price ? 'text-emerald-700' : 'text-slate-500' }}">
                            {{ auth()->user()->can_override_price ? 'Permitted' : 'Requires Manager' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Card -->
            <div class="bg-indigo-900 text-white rounded-3xl p-6 shadow-xl shadow-indigo-950/20 relative overflow-hidden">
                <div class="relative z-10 space-y-3">
                    <div class="flex items-center space-x-2 text-indigo-300 text-xs font-extrabold uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Retail Workspace</span>
                    </div>
                    <h3 class="text-base font-bold">Fast POS Terminal</h3>
                    <p class="text-xs text-indigo-200">Jump directly to the touch-friendly barcode billing interface.</p>
                    <div class="pt-2">
                        <a href="{{ route('pos') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-white text-indigo-900 rounded-xl text-xs font-bold hover:bg-indigo-50 transition shadow-sm">
                            <span>Open POS Billing</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Edit Forms -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Profile Information -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Personal Information</h3>
                        <p class="text-xs text-slate-500">Update your name, primary email address, and mobile number</p>
                    </div>
                </div>

                @if(session('profile_success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('profile_success') }}</span>
                    </div>
                @endif

                <form wire:submit="updateProfile" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Mobile Phone -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile Phone Number</label>
                            <input type="text" wire:model="phone" placeholder="9842711223" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('phone') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
                            <span wire:loading.remove wire:target="updateProfile">Save Profile Changes</span>
                            <span wire:loading wire:target="updateProfile">Saving Changes...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Security & Password Change -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Security &amp; Change Password</h3>
                        <p class="text-xs text-slate-500">Ensure your account is using a secure password to protect store operations</p>
                    </div>
                </div>

                @if(session('password_success'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('password_success') }}</span>
                    </div>
                @endif

                <form wire:submit="updatePassword" class="space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Password <span class="text-rose-500">*</span></label>
                        <input type="password" wire:model="current_password" placeholder="Enter your current account password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('current_password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- New Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password <span class="text-rose-500">*</span></label>
                            <input type="password" wire:model="new_password" placeholder="Min. 8 characters" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('new_password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password <span class="text-rose-500">*</span></label>
                            <input type="password" wire:model="new_password_confirmation" placeholder="Re-enter new password" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 text-xs font-bold text-white bg-slate-900 hover:bg-black rounded-xl shadow-md transition flex items-center space-x-2">
                            <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                            <span wire:loading wire:target="updatePassword">Updating Password...</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>

