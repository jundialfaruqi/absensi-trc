<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Layanan Darurat Terintegrasi' }} | TRC Pekanbaru Aman 112</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(10px, 15px) rotate(2deg); }
        }

        .animate-float {
            animation: float 15s ease-in-out infinite;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .neon-glow-blue {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5), 0 0 40px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>

<body class="bg-[#0a192f] text-slate-100 min-h-screen relative overflow-x-hidden antialiased">

    {{-- Background Decoration --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,#1a3a8a_0%,#0a192f_70%)]"></div>
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-10">
            <svg class="absolute top-[-5%] left-[-5%] w-150 h-150 text-blue-400 animate-float" viewBox="0 0 100 100">
                <circle cx="10" cy="10" r="1" fill="currentColor" />
                <circle cx="30" cy="20" r="1.2" fill="currentColor" />
                <circle cx="50" cy="15" r="1.5" fill="currentColor" />
                <line x1="10" y1="10" x2="30" y2="20" stroke="currentColor" stroke-width="0.3" />
                <line x1="30" y1="20" x2="50" y2="15" stroke="currentColor" stroke-width="0.3" />
            </svg>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="relative z-50 px-6 py-6 flex items-center justify-between max-w-7xl mx-auto w-full">
        <a href="/" class="flex items-center gap-1">
            <div class="shrink-0">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC" class="h-10 w-10 object-contain" />
            </div>
            <div class="flex flex-col leading-none">
                <span class="text-lg font-black tracking-tighter text-white uppercase">TRC PEKANBARU</span>
                <span class="text-[8px] font-bold text-blue-400 tracking-widest uppercase">Emergency 112</span>
            </div>
        </a>
        <a href="/" class="btn btn-ghost btn-sm text-slate-300">Kembali ke Beranda</a>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="relative z-10 border-t border-white/5 mt-12 py-12 px-6 text-center">
        <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">
            &copy; 2026 Pemerintah Kota Pekanbaru. Seluruh Hak Cipta Dilindungi.
        </p>
    </footer>

    @livewireScripts
</body>
</html>
