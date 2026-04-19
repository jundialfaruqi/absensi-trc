<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TRC Pekanbaru Aman 112 | Layanan Darurat Terintegrasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

        @keyframes float-slow {

            0%,
            100% {
                transform: translate(0, 0) rotate(12deg);
            }

            50% {
                transform: translate(-15px, -10px) rotate(15deg);
            }
        }

        .animate-float {
            animation: float 15s ease-in-out infinite;
        }

        .animate-float-slow {
            animation: float-slow 20s ease-in-out infinite;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .neon-glow-red {
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.5), 0 0 40px rgba(239, 68, 68, 0.2);
        }

        .neon-text-blue {
            text-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>

<body class="bg-[#0a192f] text-slate-100 min-h-screen relative overflow-x-hidden antialiased">

    {{-- ─── Background Decoration Layers ────────────────────────────────────── --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        {{-- Base Gradient --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,#1a3a8a_0%,#0a192f_70%)]"></div>

        {{-- Digital Plexus Dots & Lines --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
            {{-- Top Left cluster --}}
            <svg class="absolute top-[-5%] left-[-5%] w-[600px] h-[600px] text-blue-400 animate-float"
                viewBox="0 0 100 100">
                <circle cx="10" cy="10" r="1" fill="currentColor" />
                <circle cx="30" cy="20" r="1.2" fill="currentColor" />
                <circle cx="15" cy="40" r="1" fill="currentColor" />
                <circle cx="50" cy="15" r="1.5" fill="currentColor" />
                <circle cx="45" cy="45" r="1" fill="currentColor" />
                <circle cx="70" cy="10" r="1" fill="currentColor" />
                <circle cx="25" cy="65" r="1.2" fill="currentColor" />
                <circle cx="55" cy="35" r="1" fill="currentColor" />
                <line x1="10" y1="10" x2="30" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="30" y1="20" x2="50" y2="15" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="15" y1="40" x2="30" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="50" y1="15" x2="70" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="45" y1="45" x2="55" y2="35" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>

            {{-- Bottom Right cluster --}}
            <svg class="absolute bottom-[-10%] right-[-10%] w-[700px] h-[700px] text-blue-400 opacity-80 rotate-12 animate-float-slow"
                viewBox="0 0 100 100">
                <circle cx="80" cy="80" r="1" fill="currentColor" />
                <circle cx="60" cy="70" r="1.5" fill="currentColor" />
                <circle cx="90" cy="50" r="1" fill="currentColor" />
                <circle cx="40" cy="90" r="1.2" fill="currentColor" />
                <circle cx="30" cy="60" r="1.2" fill="currentColor" />
                <circle cx="50" cy="40" r="1" fill="currentColor" />
                <circle cx="75" cy="55" r="1" fill="currentColor" />
                <line x1="80" y1="80" x2="60" y2="70" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="60" y1="70" x2="90" y2="50" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="40" y1="90" x2="30" y2="60" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="50" y1="40" x2="60" y2="70" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>
        </div>

        {{-- Neon Tech Lines --}}
        <div
            class="absolute top-1/4 left-[-10%] w-full h-px bg-gradient-to-r from-transparent via-blue-500/30 to-transparent rotate-12">
        </div>
        <div
            class="absolute top-1/3 right-[-10%] w-full h-px bg-gradient-to-r from-transparent via-red-500/20 to-transparent -rotate-12">
        </div>
    </div>

    {{-- ─── Navigation ──────────────────────────────────────────────────────── --}}
    <nav
        class="relative z-50 px-4 md:px-6 py-4 md:py-6 flex items-center justify-between max-w-7xl mx-auto w-full gap-2">
        <div class="flex items-center gap-1">
            <div class="flex-shrink-0">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                    class="h-10 w-10 md:h-13 md:w-13 object-contain" />
            </div>
            <div class="flex flex-col leading-none">
                <span class="text-sm md:text-xl font-black tracking-tighter text-white uppercase whitespace-nowrap">TRC
                    PEKANBARU</span>
                <span
                    class="text-[8px] md:text-[10px] font-bold text-blue-400 tracking-[0.1em] md:tracking-[0.2em] uppercase whitespace-nowrap">Emergency
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

    {{-- ─── Hero Section ────────────────────────────────────────────────────── --}}
    <main class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
        <div class="flex flex-col space-y-8">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 w-fit">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-400">Siaga 24 Jam
                    Penuh</span>
            </div>

            <h1 class="text-5xl lg:text-7xl font-black text-white leading-[1.05] tracking-tighter">
                PEKANBARU <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">AMAN</span> DALAM
                SATU GENGGAMAN.
            </h1>

            <p class="text-slate-400 text-lg lg:text-xl font-medium leading-relaxed max-w-xl">
                Transformasi layanan publik Kota Pekanbaru melalui Tim Reaksi Cepat (TRC) 112. Penanganan berbagai
                kejadian darurat kini lebih cepat, terpadu, dan profesional.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                <a href="tel:112"
                    class="w-full sm:w-auto px-10 py-5 bg-gradient-to-br from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white text-lg font-black rounded-2xl shadow-xl transition-all neon-glow-red text-center">
                    HUBUNGI 112
                </a>
                <div class="flex items-center gap-4 px-6 py-5 glass-panel rounded-2xl border-white/5">
                    <div class="flex -space-x-3">
                        <div
                            class="h-8 w-8 rounded-full border-2 border-slate-900 bg-blue-600 flex items-center justify-center text-[10px] font-bold text-white uppercase">
                            BPBD</div>
                        <div
                            class="h-8 w-8 rounded-full border-2 border-slate-900 bg-red-600 flex items-center justify-center text-[10px] font-bold text-white uppercase">
                            DMK</div>
                        <div
                            class="h-8 w-8 rounded-full border-2 border-slate-900 bg-emerald-600 flex items-center justify-center text-[10px] font-bold text-white uppercase">
                            MED</div>
                    </div>
                    <span class="text-xs font-bold text-slate-300">Terintegrasi Lintas Sektoral</span>
                </div>
            </div>

            <div class="pt-8 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">24/7</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Respon Aktif</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-blue-400">112</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nomor Tunggal</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">FAST</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Response Time</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">ALL</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">City Coverage</span>
                </div>
            </div>
        </div>

        {{-- Visual HUD Element --}}
        <div class="hidden lg:flex justify-center items-center relative">
            <div
                class="absolute w-[500px] h-[500px] rounded-full border-2 border-blue-500/10 animate-[spin_30s_linear_infinite]">
            </div>
            <div
                class="absolute w-[400px] h-[400px] rounded-full border border-blue-400/5 animate-[spin_20s_linear_infinite_reverse]">
            </div>

            <div
                class="relative glass-panel rounded-[2.5rem] p-8 w-[380px] shadow-2xl border-white/10 overflow-hidden transform rotate-3">
                <div class="absolute top-0 right-0 p-4">
                    <div class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black tracking-widest text-blue-400 uppercase">System
                            Status</span>
                        <span class="text-[10px] font-bold text-emerald-400 uppercase">Operational</span>
                    </div>

                    <div class="p-6 bg-slate-900/50 rounded-2xl border border-white/5 space-y-4">
                        <div class="flex justify-between items-end">
                            <span class="text-xs font-bold text-slate-400 uppercase">Current Load</span>
                            <span class="text-2xl font-black text-white">82%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full w-4/5 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="p-4 glass-panel rounded-2xl border-white/5 flex flex-col items-center text-center space-y-2">
                            <div class="h-8 w-8 rounded-lg bg-red-500/20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black text-white uppercase tracking-tighter">Emergency</span>
                        </div>
                        <div
                            class="p-4 glass-panel rounded-2xl border-white/5 flex flex-col items-center text-center space-y-2">
                            <div class="h-8 w-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4a1 1 0 01-.8 1.6H6a1 1 0 01-1-1V7a1 1 0 00-1-1H3z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black text-white uppercase tracking-tighter">Response</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/5 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full border border-blue-500/30 overflow-hidden bg-slate-800">
                            <div
                                class="w-full h-full bg-blue-600/20 flex items-center justify-center italic font-black text-blue-400">
                                112</div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-black text-white">Dispatcher 051</span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active
                                Session</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- ─── Services Grid ──────────────────────────────────────────────────── --}}
    <section class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24">
        <div class="text-center mb-16 max-w-2xl mx-auto space-y-4">
            <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tight uppercase">APAPUN DARURATNYA. SATU
                NOMORNYA.</h2>
            <p class="text-slate-400 font-medium italic">Layanan Call Center 112 mengintegrasikan seluruh unit reaksi
                cepat kota Pekanbaru.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Fire --}}
            <div
                class="glass-panel p-8 rounded-[2rem] hover:border-red-500/30 transition-all group overflow-hidden relative">
                <div
                    class="absolute -right-4 -top-4 text-slate-800/20 rotate-12 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                    </svg>
                </div>
                <div class="relative z-10 space-y-4">
                    <div
                        class="h-12 w-12 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500 shadow-lg shadow-red-500/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Kebakaran</h3>
                    <p class="text-sm font-medium text-slate-400 leading-relaxed">Koordinasi cepat dengan Dinas Pemadam
                        Kebakaran untuk penanganan kobaran api dan penyelamatan jiwa.</p>
                </div>
            </div>

            {{-- Medical --}}
            <div
                class="glass-panel p-8 rounded-[2rem] hover:border-blue-500/30 transition-all group overflow-hidden relative">
                <div
                    class="absolute -right-4 -top-4 text-slate-800/20 rotate-12 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <div class="relative z-10 space-y-4">
                    <div class="h-12 w-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Medis Darurat</h3>
                    <p class="text-sm font-medium text-slate-400 leading-relaxed">Tim medis dari Dinas Kesehatan siaga
                        mengirimkan bantuan ambulans dan pertolongan pertama secepat mungkin.</p>
                </div>
            </div>

            {{-- Security --}}
            <div
                class="glass-panel p-8 rounded-[2rem] hover:border-amber-500/30 transition-all group overflow-hidden relative">
                <div
                    class="absolute -right-4 -top-4 text-slate-800/20 rotate-12 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="relative z-10 space-y-4">
                    <div class="h-12 w-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Keamanan</h3>
                    <p class="text-sm font-medium text-slate-400 leading-relaxed">Gangguan ketertiban yang melibatkan
                        Satpol PP serta koordinasi taktis dengan Polresta Pekanbaru.</p>
                </div>
            </div>

            {{-- Disaster --}}
            <div
                class="glass-panel p-8 rounded-[2rem] hover:border-emerald-500/30 transition-all group overflow-hidden relative">
                <div
                    class="absolute -right-4 -top-4 text-slate-800/20 rotate-12 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative z-10 space-y-4">
                    <div
                        class="h-12 w-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Sosial & Bencana</h3>
                    <p class="text-sm font-medium text-slate-400 leading-relaxed">Penanganan cepat dampak bencana alam
                        melalui koordinasi dengan BPBD serta persoalan sosial kritis.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── Message/Mayor Section ────────────────────────────────────────── --}}
    <section class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24">
        <div class="grid lg:grid-cols-5 gap-12 items-center">
            <div class="lg:col-span-3 space-y-8">
                <div class="space-y-4">
                    <h2 class="text-3xl font-black text-white uppercase tracking-tight">Transformasi Layanan Publik.
                    </h2>
                    <div class="h-1 w-20 bg-blue-500 rounded-full shadow-lg shadow-blue-500/50"></div>
                </div>

                <div class="relative">
                    <svg class="absolute -top-6 -left-6 h-12 w-12 text-blue-500/10" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.987z" />
                    </svg>
                    <p class="text-xl lg:text-2xl font-medium text-slate-300 leading-relaxed italic relative z-10">
                        "Masyarakat cukup dengan menekan angka 112, seluruh laporan mulai dari kecelakaan, kebakaran,
                        gangguan keamanan, hingga kebutuhan medis darurat akan langsung ditindaklanjuti."
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <div
                        class="h-12 w-12 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center font-black text-blue-400">
                        AN</div>
                    <div>
                        <p class="text-white font-black uppercase tracking-wider">Agung Nugroho</p>
                        <p class="text-xs font-bold text-blue-400 uppercase tracking-widest">Wali Kota Pekanbaru</p>
                    </div>
                </div>

                <p class="text-slate-400 leading-relaxed">
                    Diluncurkan pada April 2026, TRC Pekanbaru Aman 112 merupakan jawaban atas aspirasi warga yang
                    menginginkan kehadiran pemerintah secara cepat saat situasi darurat. Tim lapangan dibekali dengan
                    **Standar Operasional Prosedur (SOP)** waktu respon yang ketat untuk memastikan bantuan tiba secepat
                    mungkin.
                </p>
            </div>

            <div class="lg:col-span-2">
                <div class="glass-panel p-8 rounded-[2.5rem] border-white/5 space-y-6">
                    <h3
                        class="text-sm font-black text-white uppercase tracking-widest text-center border-b border-white/5 pb-4">
                        Instansi Terintegrasi</h3>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach ([['icon' => '🏢', 'name' => 'Diskominfo Pekanbaru'], ['icon' => '🎖️', 'name' => 'Kodim & Polresta'], ['icon' => '🚒', 'name' => 'DPK & BPBD'], ['icon' => '🏥', 'name' => 'Dinkes & RSUD'], ['icon' => '🛂', 'name' => 'Satpol PP & Dishub']] as $dept)
                            <div
                                class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-xl border border-white/5 hover:border-blue-500/30 transition-all">
                                <span class="text-xl">{{ $dept['icon'] }}</span>
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-300">{{ $dept['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── Integration List Section (Previously refined) ────────────────────── --}}
    <section class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24 overflow-hidden hidden">
        {{-- Section removed in favor of Mayor quote integration above --}}
    </section>

    {{-- ─── Footer ────────────────────────────────────────────────────────── --}}
    <footer class="relative z-10 border-t border-white/5 mt-12 py-12 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex flex-col items-center md:items-start space-y-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 p-1 rounded-lg glass-panel border-white/10">
                        <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                            class="h-full w-full object-contain" />
                    </div>
                    <span class="font-black text-white tracking-tighter uppercase text-xl italic">TRC Pekanbaru Aman
                        112</span>
                </div>
                <p
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest max-w-md text-center md:text-left leading-relaxed">
                    Layanan pengaduan kedaruratan 24 jam pemerintah kota pekanbaru terintegrasi lintas sektoral untuk
                    keselamatan publik.
                </p>
            </div>

            <div class="flex flex-col items-center md:items-end space-y-4">
                <div class="flex items-center gap-6">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] opacity-40">Powered by
                        Diskominfo Pekanbaru</span>
                </div>
                <div class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">
                    &copy; 2026 Pemerintah Kota Pekanbaru. Seluruh Hak Cipta Dilindungi.
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Hero animation on load
        window.addEventListener('load', () => {
            document.body.classList.add('ready');
        });

        // Simple scroll visual interaction
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 40) {
                nav.classList.add('glass-panel', 'shadow-2xl', 'backdrop-blur-xl', 'py-4');
                nav.classList.remove('py-6');
            } else {
                nav.classList.remove('glass-panel', 'shadow-2xl', 'backdrop-blur-xl', 'py-4');
                nav.classList.add('py-6');
            }
        });
    </script>
</body>

</html>
