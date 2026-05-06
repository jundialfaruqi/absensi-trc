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

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(10px, 15px) rotate(2deg);
            }
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
                <line x1="10" y1="10" x2="30" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="30" y1="20" x2="50" y2="15" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>
        </div>
    </div>

    <nav
        class="relative z-50 px-4 md:px-6 py-4 md:py-6 flex items-center justify-between max-w-7xl mx-auto w-full gap-2">
        <div class="flex items-center gap-1">
            <div class="shrink-0">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                    class="h-10 w-10 md:h-13 md:w-13 object-contain" />
            </div>
            <div class="flex flex-col leading-none">
                <a href="/"
                    class="text-sm md:text-xl font-black tracking-tighter text-white uppercase whitespace-nowrap">TRC
                    PEKANBARU</a>
                <span
                    class="text-[8px] md:text-[10px] font-bold text-blue-400 tracking-widest md:tracking-[0.2em] uppercase whitespace-nowrap">AMAN
                    112</span>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4 shrink-0">
            <a href="{{ url('/absensi-web') }}"
                class="px-2 md:px-4 py-2 text-xs md:text-sm font-semibold text-slate-300 hover:text-white transition-colors whitespace-nowrap">Absensi</a>

            <a href="tel:112"
                class="px-3 md:px-6 py-2 md:py-2.5 bg-red-600 hover:bg-red-500 text-white font-black rounded-lg transition-all transform hover:scale-105 neon-glow-red flex items-center gap-1.5 md:gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
                <span class="text-[10px] md:text-base">CALL 112</span>
            </a>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="relative z-10 border-t border-white/5 mt-6 py-8 px-6 text-center">
        <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">
            &copy; 2026 Pemerintah Kota Pekanbaru. Seluruh Hak Cipta Dilindungi.
        </p>
    </footer>

    @livewireScripts
</body>

</html>
