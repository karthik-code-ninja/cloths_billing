<x-layouts.auth>
    <div class="relative overflow-hidden backdrop-blur-2xl bg-slate-900/80 border border-white/15 rounded-3xl p-7 sm:p-9 shadow-[0_30px_70px_-15px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.2)]">
        <!-- Top Glossy Specular Accent Line -->
        <div class="absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-transparent via-indigo-400 to-transparent"></div>
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Header: 3D Glossy Icon & Title -->
        <div class="text-center mb-7 relative z-10">
            <div class="relative w-16 h-16 mx-auto mb-4 group cursor-default">
                <div class="absolute -inset-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-3xl blur-md opacity-70 group-hover:opacity-100 transition duration-300"></div>
                <div class="relative w-16 h-16 bg-gradient-to-b from-indigo-500 via-indigo-600 to-indigo-800 rounded-2xl flex items-center justify-center font-black text-white text-3xl shadow-[0_10px_25px_rgba(0,0,0,0.5),inset_0_2px_2px_rgba(255,255,255,0.4)] border border-white/30 overflow-hidden">
                    <!-- Glossy top reflection sheen -->
                    <div class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/35 to-transparent"></div>
                    <span class="relative z-10 drop-shadow-md uppercase">{{ strtoupper(mb_substr(\App\Models\AppSetting::get('store_name', 'N'), 0, 1)) }}</span>
                </div>
            </div>

            <h2 class="text-2xl font-black tracking-tight text-white drop-shadow-sm">
                {{ \App\Models\AppSetting::get('store_name', 'NextDNA Retail POS') }}
            </h2>
            <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto leading-relaxed">
                {{ \App\Models\AppSetting::get('store_tagline', 'Clothing & Inventory Management') }}
            </p>

            <!-- Status Pill -->
            <div class="inline-flex items-center space-x-2 mt-3 px-3 py-1 rounded-full bg-slate-800/80 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.15)] text-[11px] text-slate-300">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-medium">Terminal Online • High-Speed POS</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-5 bg-rose-950/60 border border-rose-500/40 text-rose-200 text-xs rounded-2xl p-3.5 shadow-[inset_0_1px_1px_rgba(255,255,255,0.1)]">
                <div class="flex items-center space-x-2 font-bold mb-1">
                    <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>Authentication Notice</span>
                </div>
                @foreach ($errors->all() as $error)
                    <p class="pl-6 text-[11px]">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4 relative z-10">
            @csrf
            <div>
                <label for="email" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>Email Address</span>
                    <span class="text-[10px] text-slate-500 lowercase">staff terminal id</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email', 'admin@nextdna.com') }}" required autofocus
                           class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-950/70 hover:bg-slate-950/90 focus:bg-slate-950 border border-white/10 hover:border-white/20 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/20 text-white placeholder-slate-500 text-sm font-medium transition duration-200 shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Password</label>
                    <span class="text-[10px] text-indigo-400 font-mono">Encrypted</span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input id="password" type="password" name="password" value="Nextdna@123" required
                           class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-950/70 hover:bg-slate-950/90 focus:bg-slate-950 border border-white/10 hover:border-white/20 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/20 text-white placeholder-slate-500 text-sm font-medium transition duration-200 shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                <label class="flex items-center space-x-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    <span class="hover:text-slate-200 transition">Keep this terminal active</span>
                </label>
                <span class="text-[11px] text-slate-500">POS Shift Session</span>
            </div>

            <button type="submit" class="group relative overflow-hidden w-full py-3.5 px-5 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold rounded-2xl shadow-[0_12px_28px_-6px_rgba(99,102,241,0.55),inset_0_1px_1px_rgba(255,255,255,0.4)] border border-white/20 active:scale-[0.99] transition duration-200 flex items-center justify-center space-x-2 text-sm">
                <!-- Glossy specular top highlight -->
                <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>
                <span>Sign In to Terminal</span>
                <svg class="w-4 h-4 text-indigo-200 group-hover:translate-x-1 transition duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>

        <!-- Quick Demo Accounts Switcher -->
        <div class="mt-8 pt-6 border-t border-white/10 text-center relative z-10">
            <div class="flex items-center justify-center space-x-2 mb-3">
                <span class="h-px w-8 bg-white/10"></span>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Fast Role Switcher</p>
                <span class="h-px w-8 bg-white/10"></span>
            </div>
            
            <div class="grid grid-cols-2 gap-2.5 text-xs text-left">
                <button type="button" onclick="fillCredentials('admin@nextdna.com')" class="group p-2.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-indigo-400/50 backdrop-blur-md transition duration-200 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[11px] border border-indigo-500/30 group-hover:bg-indigo-500 group-hover:text-white transition">A</span>
                        <div>
                            <span class="font-bold text-slate-200 group-hover:text-white block text-[11px] leading-tight">Admin / Owner</span>
                            <span class="text-[9px] text-slate-400 group-hover:text-indigo-300">All Masters & POS</span>
                        </div>
                    </div>
                </button>
                <button type="button" onclick="fillCredentials('manager@nextdna.com')" class="group p-2.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-400/50 backdrop-blur-md transition duration-200 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-[11px] border border-purple-500/30 group-hover:bg-purple-500 group-hover:text-white transition">M</span>
                        <div>
                            <span class="font-bold text-slate-200 group-hover:text-white block text-[11px] leading-tight">Store Manager</span>
                            <span class="text-[9px] text-slate-400 group-hover:text-purple-300">Overrides & Shifts</span>
                        </div>
                    </div>
                </button>
                <button type="button" onclick="fillCredentials('cashier1@nextdna.com')" class="group p-2.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/50 backdrop-blur-md transition duration-200 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[11px] border border-emerald-500/30 group-hover:bg-emerald-500 group-hover:text-white transition">C</span>
                        <div>
                            <span class="font-bold text-slate-200 group-hover:text-white block text-[11px] leading-tight">Head Cashier</span>
                            <span class="text-[9px] text-slate-400 group-hover:text-emerald-300">Express Billing</span>
                        </div>
                    </div>
                </button>
                <button type="button" onclick="fillCredentials('accountant@nextdna.com')" class="group p-2.5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-blue-400/50 backdrop-blur-md transition duration-200 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-[11px] border border-blue-500/30 group-hover:bg-blue-500 group-hover:text-white transition">₹</span>
                        <div>
                            <span class="font-bold text-slate-200 group-hover:text-white block text-[11px] leading-tight">Accountant</span>
                            <span class="text-[9px] text-slate-400 group-hover:text-blue-300">GST & Financials</span>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(email) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = 'Nextdna@123';
                emailInput.classList.add('ring-2', 'ring-indigo-400');
                setTimeout(() => emailInput.classList.remove('ring-2', 'ring-indigo-400'), 400);
            }
        }
    </script>
</x-layouts.auth>
