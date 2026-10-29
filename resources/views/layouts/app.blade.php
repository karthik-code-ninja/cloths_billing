<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \App\Models\AppSetting::get('store_name', config('app.name', 'NextDNA POS')) }} - Clothing Retail Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS with CDN & Build Fallback -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        pos: {
                            sidebar: '#0f172a',
                            surface: '#ffffff',
                            subtle: '#f8fafc',
                            border: '#e2e8f0',
                            accent: '#2563eb',
                            success: '#059669',
                            warning: '#d97706',
                            danger: '#dc2626',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Menlo', 'Monaco', 'Courier New', 'monospace'],
                    }
                }
            }
        }
    </script>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; padding: 0 !important; margin: 0 !important; }
        }
        @media screen {
            .print-only { display: none !important; }
        }

        /* Professional Software Glossy Design System */
        .glossy-panel {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(226, 232, 240, 0.8), inset 0 1px 1px 0 rgba(255, 255, 255, 1);
        }

        .glossy-dark {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.9) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.6), inset 0 1px 1px 0 rgba(255, 255, 255, 0.18);
        }

        .glossy-btn {
            background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.35);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glossy-btn:hover {
            background: linear-gradient(180deg, #4f46e5 0%, #4338ca 100%);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.45);
        }

        /* Custom Modern Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.7);
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#f8fafc] flex flex-col selection:bg-indigo-500 selection:text-white relative min-h-screen">

    <!-- Ambient background sheen for professional SaaS look -->
    <div class="fixed top-0 inset-x-0 h-96 bg-gradient-to-b from-indigo-500/5 via-violet-500/3 to-transparent pointer-events-none -z-10"></div>

    <!-- Top Navigation Header with Glossy Dark Glass & Specular Edge -->
    <header class="bg-slate-950/90 backdrop-blur-xl text-white shadow-[0_8px_30px_rgba(0,0,0,0.45)] z-30 no-print sticky top-0 border-b border-white/10 relative">
        <!-- Top Specular Highlight Line -->
        <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-indigo-500/60 to-transparent pointer-events-none"></div>

        <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            
            <!-- Left: Brand Logo & Title -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('pos') }}" class="flex items-center space-x-3 group">
                    <!-- 3D Glossy App Icon -->
                    <div class="relative w-10 h-10 rounded-2xl bg-gradient-to-b from-indigo-500 via-indigo-600 to-indigo-800 flex items-center justify-center font-black text-xl text-white shadow-[0_4px_16px_rgba(99,102,241,0.5),inset_0_1px_1px_rgba(255,255,255,0.4)] border border-white/25 group-hover:scale-105 transition duration-200 uppercase overflow-hidden">
                        <div class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/30 to-transparent"></div>
                        <span class="relative z-10">{{ strtoupper(mb_substr(\App\Models\AppSetting::get('store_name', 'N'), 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-black tracking-tight text-base sm:text-lg text-white group-hover:text-indigo-200 transition">{{ \App\Models\AppSetting::get('store_name', 'NextDNA') }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-gradient-to-r from-indigo-500/25 to-violet-500/25 text-indigo-300 rounded-md border border-indigo-400/35 uppercase tracking-wider shadow-[inset_0_1px_0_rgba(255,255,255,0.12)]">Retail POS</span>
                        </div>
                        <p class="text-[11px] text-slate-400 hidden xl:block leading-none">{{ \App\Models\AppSetting::get('store_tagline', 'Clothing & Inventory Management') }}</p>
                    </div>
                </a>

                <!-- Grouped Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-1.5 pl-4 border-l border-white/10">
                    
                    <!-- 1. Highlighted POS Billing Button with Glossy Sheen -->
                    <a href="{{ route('pos') }}" class="relative overflow-hidden px-4 py-1.5 text-xs font-black rounded-xl transition-all flex items-center space-x-1.5 shadow-[0_4px_14px_rgba(99,102,241,0.45),inset_0_1px_1px_rgba(255,255,255,0.4)] border border-white/25 active:scale-[0.98] {{ request()->routeIs('pos*') ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white' : 'bg-slate-900/80 text-indigo-300 hover:text-white hover:bg-slate-800' }}">
                        <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
                        <svg class="w-4 h-4 text-amber-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>POS Billing</span>
                    </a>

                    <!-- 2. Operations Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-3 py-1.5 text-xs font-semibold rounded-xl transition-all flex items-center space-x-1.5 backdrop-blur-md {{ request()->routeIs('product.lookup*', 'barcode.labels*', 'cash-shifts*') ? 'bg-gradient-to-r from-indigo-600/90 to-violet-600/90 text-white font-bold border border-white/25 shadow-[0_4px_12px_rgba(99,102,241,0.35),inset_0_1px_1px_rgba(255,255,255,0.25)]' : 'text-slate-300 hover:text-white bg-slate-900/60 hover:bg-slate-800/80 border border-white/10 hover:border-white/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            <span>Operations</span>
                            <svg class="w-3 h-3 text-slate-400 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="origin-top-left absolute left-0 mt-2 w-56 rounded-2xl shadow-[0_25px_60px_-10px_rgba(0,0,0,0.8),inset_0_1px_1px_rgba(255,255,255,0.15)] bg-slate-950/95 backdrop-blur-2xl border border-white/15 py-1.5 text-slate-200 z-50 divide-y divide-white/5">
                            <div class="p-1">
                                <a href="{{ route('product.lookup') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center justify-between transition {{ request()->routeIs('product.lookup*') ? 'text-indigo-400 bg-white/10 font-bold' : '' }}">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>Scan & Fetch Item</span>
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-400 bg-slate-900 border border-white/10 px-1 py-0.5 rounded">F3</span>
                                </a>
                                <a href="{{ route('barcode.labels') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('barcode.labels*') ? 'text-emerald-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span>Barcode & Hangtag Studio</span>
                                </a>
                            </div>
                            <div class="p-1">
                                <a href="{{ route('cash-shifts') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('cash-shifts*') ? 'text-amber-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Cash Shifts & Drawer</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Catalogue & Inventory Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-3 py-1.5 text-xs font-semibold rounded-xl transition-all flex items-center space-x-1.5 backdrop-blur-md {{ request()->routeIs('catalogue*', 'inventory*', 'purchases*', 'returns*') ? 'bg-gradient-to-r from-indigo-600/90 to-violet-600/90 text-white font-bold border border-white/25 shadow-[0_4px_12px_rgba(99,102,241,0.35),inset_0_1px_1px_rgba(255,255,255,0.25)]' : 'text-slate-300 hover:text-white bg-slate-900/60 hover:bg-slate-800/80 border border-white/10 hover:border-white/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]' }}">
                            <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Catalogue & Stock</span>
                            <svg class="w-3 h-3 text-slate-400 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="origin-top-left absolute left-0 mt-2 w-56 rounded-2xl shadow-[0_25px_60px_-10px_rgba(0,0,0,0.8),inset_0_1px_1px_rgba(255,255,255,0.15)] bg-slate-950/95 backdrop-blur-2xl border border-white/15 py-1.5 text-slate-200 z-50 divide-y divide-white/5">
                            <div class="p-1">
                                <a href="{{ route('catalogue') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('catalogue*') ? 'text-indigo-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Products & Variants</span>
                                </a>
                                <a href="{{ route('inventory') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('inventory*') ? 'text-cyan-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <span>Stock Ledger & Movements</span>
                                </a>
                            </div>
                            <div class="p-1">
                                <a href="{{ route('purchases') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('purchases*') ? 'text-emerald-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Supplier Purchases</span>
                                </a>
                                <a href="{{ route('returns') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('returns*') ? 'text-rose-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a4 4 0 004 4h8m0 0l-3-3m3 3l-3 3"/></svg>
                                    <span>Returns & Exchange</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Masters Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-3 py-1.5 text-xs font-semibold rounded-xl transition-all flex items-center space-x-1.5 backdrop-blur-md {{ request()->routeIs('masters.*') ? 'bg-gradient-to-r from-indigo-600/90 to-violet-600/90 text-white font-bold border border-white/25 shadow-[0_4px_12px_rgba(99,102,241,0.35),inset_0_1px_1px_rgba(255,255,255,0.25)]' : 'text-slate-300 hover:text-white bg-slate-900/60 hover:bg-slate-800/80 border border-white/10 hover:border-white/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Masters</span>
                            <svg class="w-3 h-3 text-slate-400 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="origin-top-left absolute left-0 mt-2 w-56 rounded-2xl shadow-[0_25px_60px_-10px_rgba(0,0,0,0.8),inset_0_1px_1px_rgba(255,255,255,0.15)] bg-slate-950/95 backdrop-blur-2xl border border-white/15 p-1 text-slate-200 z-50 divide-y divide-white/5">
                            <div class="p-1">
                                <a href="{{ route('masters.categories') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('masters.categories*') ? 'text-indigo-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>Category Master</span>
                                </a>
                                <a href="{{ route('masters.brands') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('masters.brands*') ? 'text-emerald-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span>Brand Master</span>
                                </a>
                                <a href="{{ route('masters.suppliers') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('masters.suppliers*') ? 'text-blue-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Supplier / Vendor</span>
                                </a>
                            </div>
                            <div class="p-1">
                                <a href="{{ route('masters.sizes') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('masters.sizes*') ? 'text-amber-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                    <span>Size Group Master</span>
                                </a>
                                <a href="{{ route('masters.seasons') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('masters.seasons*') ? 'text-violet-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Season & Collections</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Reports Link -->
                    <a href="{{ route('reports') }}" class="px-3 py-1.5 text-xs font-semibold rounded-xl transition-all flex items-center space-x-1.5 backdrop-blur-md {{ request()->routeIs('reports*') ? 'bg-gradient-to-r from-indigo-600/90 to-violet-600/90 text-white font-bold border border-white/25 shadow-[0_4px_12px_rgba(99,102,241,0.35),inset_0_1px_1px_rgba(255,255,255,0.25)]' : 'text-slate-300 hover:text-white bg-slate-900/60 hover:bg-slate-800/80 border border-white/10 hover:border-white/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]' }}">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Reports</span>
                    </a>

                    <!-- 6. Settings Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-3 py-1.5 text-xs font-semibold rounded-xl transition-all flex items-center space-x-1.5 backdrop-blur-md {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-indigo-600/90 to-violet-600/90 text-white font-bold border border-white/25 shadow-[0_4px_12px_rgba(99,102,241,0.35),inset_0_1px_1px_rgba(255,255,255,0.25)]' : 'text-slate-300 hover:text-white bg-slate-900/60 hover:bg-slate-800/80 border border-white/10 hover:border-white/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]' }}">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Settings</span>
                            <svg class="w-3 h-3 text-slate-400 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="origin-top-left absolute left-0 mt-2 w-52 rounded-2xl shadow-[0_25px_60px_-10px_rgba(0,0,0,0.8),inset_0_1px_1px_rgba(255,255,255,0.15)] bg-slate-950/95 backdrop-blur-2xl border border-white/15 p-1.5 text-slate-200 z-50">
                            <a href="{{ route('settings.general') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('settings.general*') ? 'text-indigo-400 bg-white/10 font-bold' : '' }}">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                <span>General Store & GST</span>
                            </a>
                            <a href="{{ route('settings.printers') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('settings.printers*') ? 'text-indigo-400 bg-white/10 font-bold' : '' }}">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Thermal & Label Printers</span>
                            </a>
                            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                                <a href="{{ route('settings.users') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 flex items-center space-x-2 transition {{ request()->routeIs('settings.users*') ? 'text-indigo-400 bg-white/10 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <span>Staff &amp; Users</span>
                                </a>
                            @endif
                        </div>
                    </div>

                </nav>
            </div>

            <!-- Right: Branch Pill & Shortcuts & Profile -->
            <div class="flex items-center space-x-3">
                <!-- Glossy Branch / Store Capsule -->
                <a href="{{ route('settings.general') }}" class="hidden lg:flex items-center text-xs font-semibold text-slate-200 hover:text-white bg-slate-900/80 hover:bg-slate-850 px-3.5 py-1.5 rounded-xl border border-emerald-500/40 hover:border-emerald-400/70 shadow-[0_0_15px_rgba(16,185,129,0.18),inset_0_1px_1px_rgba(255,255,255,0.15)] transition group" title="Click to edit Store Profile & Settings">
                    <span class="relative flex h-2 w-2 mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="group-hover:text-indigo-200 transition font-medium">{{ auth()->user()->branch->name ?? \App\Models\AppSetting::get('store_name', 'Salem Megastore') }}</span>
                </a>

                <!-- Shortcuts button -->
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-shortcuts-modal'))" class="hidden sm:flex items-center text-xs font-mono text-slate-300 bg-slate-900/80 hover:bg-slate-800 hover:text-white px-2.5 py-1.5 rounded-xl border border-white/10 hover:border-white/20 transition shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]" title="Press F1 for keyboard shortcuts">
                    <span class="bg-slate-950 px-1.5 py-0.5 rounded text-[10px] mr-1.5 text-indigo-400 font-bold border border-white/10">F1</span>
                    <span>Shortcuts</span>
                </button>

                <!-- User Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 text-sm focus:outline-none bg-slate-900/80 hover:bg-slate-800 p-1.5 pr-3 rounded-2xl border border-white/10 hover:border-white/20 transition shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 via-indigo-600 to-violet-600 text-white flex items-center justify-center font-black text-xs shadow-[inset_0_1px_1px_rgba(255,255,255,0.4)] border border-white/20">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-xs font-bold text-slate-200 leading-tight">{{ auth()->user()->name ?? 'Admin User' }}</div>
                            <div class="text-[10px] text-indigo-400 uppercase font-bold tracking-wider">{{ auth()->user()->role ?? 'Admin' }}</div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 ml-1 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown menu with Glossy Obsidian Glass -->
                    <div x-show="open" @click.away="open = false" x-cloak class="origin-top-right absolute right-0 mt-2 w-64 rounded-2xl shadow-[0_25px_60px_-10px_rgba(0,0,0,0.85),inset_0_1px_1px_rgba(255,255,255,0.15)] bg-slate-950/95 backdrop-blur-2xl border border-white/15 py-1.5 text-slate-200 z-50 divide-y divide-white/5">
                        <div class="px-4 py-3 bg-white/5">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Signed in as</p>
                            <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@nextdna.com' }}</p>
                            <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-black uppercase rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">{{ auth()->user()->role ?? 'admin' }}</span>
                        </div>
                        <div class="p-1">
                            <a href="{{ route('profile') }}" class="px-3 py-2 text-xs font-bold rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center space-x-2 transition {{ request()->routeIs('profile*') ? 'bg-indigo-600/30 text-indigo-300 font-black' : '' }}">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>My Profile &amp; Password</span>
                            </a>
                            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                                <a href="{{ route('settings.users') }}" class="px-3 py-2 text-xs font-semibold rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center space-x-2 transition {{ request()->routeIs('settings.users*') ? 'bg-purple-600/30 text-purple-300 font-bold' : '' }}">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <span>Staff &amp; User Management</span>
                                </a>
                            @endif
                            <a href="{{ route('cash-shifts') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Register / Cash Shift</span>
                            </a>
                            <a href="{{ route('settings.general') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Store & GST Settings</span>
                            </a>
                            <a href="{{ route('settings.printers') }}" class="px-3 py-2 text-xs font-medium rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Thermal & Label Printers</span>
                            </a>
                        </div>
                        <div class="p-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold text-rose-400 hover:bg-rose-500/20 rounded-xl flex items-center space-x-2 transition">
                                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash notifications with glossy aesthetic -->
    @if (session()->has('success'))
        <div class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 text-white px-4 py-2.5 text-sm font-medium flex items-center justify-between shadow-lg border-b border-emerald-400/30 no-print animate-fade-in relative z-20">
            <div class="flex items-center space-x-2 max-w-7xl mx-auto w-full">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-gradient-to-r from-rose-600 via-rose-500 to-pink-600 text-white px-4 py-2.5 text-sm font-medium flex items-center justify-between shadow-lg border-b border-rose-400/30 no-print animate-fade-in relative z-20">
            <div class="flex items-center space-x-2 max-w-7xl mx-auto w-full">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content View Area -->
    <main class="flex-1 flex flex-col min-h-0 overflow-y-auto">
        {{ $slot }}
    </main>

    <!-- Global F1 Keyboard Shortcuts Modal with Glossy Glassmorphism -->
    <div x-data="{ showShortcuts: false }" 
         @open-shortcuts-modal.window="showShortcuts = true"
         @keydown.window.f1.prevent="showShortcuts = true"
         @keydown.escape.window="showShortcuts = false"
         x-show="showShortcuts" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white/95 backdrop-blur-2xl rounded-3xl max-w-xl w-full p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5),inset_0_1px_1px_rgba(255,255,255,1)] border border-white/80" @click.away="showShortcuts = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="p-2.5 bg-gradient-to-tr from-indigo-500 to-violet-600 text-white rounded-2xl shadow-md shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 leading-tight">Retail POS Keyboard Shortcuts</h3>
                        <p class="text-[11px] text-slate-400">High-speed keyboard navigation keys</p>
                    </div>
                </div>
                <button @click="showShortcuts = false" class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-slate-700">Open Product Search</span>
                    <kbd class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg font-mono font-bold text-indigo-600 shadow-sm">F2</kbd>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-slate-700">Focus Barcode Scanner</span>
                    <kbd class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg font-mono font-bold text-indigo-600 shadow-sm">F3</kbd>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-slate-700">Attach Customer</span>
                    <kbd class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg font-mono font-bold text-indigo-600 shadow-sm">F7</kbd>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-slate-700">Held Carts & Quotations</span>
                    <kbd class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg font-mono font-bold text-indigo-600 shadow-sm">F8</kbd>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-slate-700">Open Payment & Checkout</span>
                    <kbd class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg font-mono font-bold text-emerald-600 shadow-sm">F9</kbd>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between shadow-xs">
                    <span class="font-medium text-slate-700">Close Modals / Cancel</span>
                    <kbd class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg font-mono font-bold text-slate-600 shadow-sm">ESC</kbd>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>Hardware: USB Barcode Scanner (TVS BS-L100+) & Thermal Printer (TVS RP 3200)</span>
                <button @click="showShortcuts = false" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition shadow-sm">Got it</button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('keydown', function(e) {
            // Global F2 navigation: If outside POS, jump directly to POS Billing
            if (e.key === 'F2' && window.location.pathname !== '/pos' && !window.location.pathname.endsWith('/pos')) {
                e.preventDefault();
                window.location.href = "{{ route('pos') }}";
            }
        });
    </script>

    @livewireScripts
</body>
</html>
