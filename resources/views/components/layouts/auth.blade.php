<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#070913]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ \App\Models\AppSetting::get('store_name', 'NextDNA Retail POS') }}</title>

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

    <style>
        .glossy-mesh-bg {
            background-color: #070913;
            background-image: 
                radial-gradient(at 10% 15%, rgba(99, 102, 241, 0.22) 0px, transparent 55%),
                radial-gradient(at 85% 20%, rgba(168, 85, 247, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 85%, rgba(16, 185, 129, 0.14) 0px, transparent 55%),
                radial-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 28px 28px;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-100 font-sans glossy-mesh-bg relative overflow-x-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Ambient glowing light orbs for glossy depth -->
    <div class="fixed top-[-10%] left-[-10%] w-[550px] h-[550px] rounded-full bg-indigo-600/25 blur-[140px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[550px] h-[550px] rounded-full bg-violet-600/20 blur-[150px] pointer-events-none"></div>
    <div class="fixed top-[35%] right-[5%] w-[400px] h-[400px] rounded-full bg-emerald-500/12 blur-[130px] pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10 my-8">
        {{ $slot }}
    </div>

</body>
</html>
