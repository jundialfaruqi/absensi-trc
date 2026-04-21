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

        .carousel-view {
            perspective: 2000px;
            transform-style: preserve-3d;
        }

        .carousel-card {
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }

        .card-active {
            transform: translate3d(0, 0, 100px) rotateY(0deg);
            z-index: 50;
            opacity: 1;
            filter: blur(0) brightness(1.1);
        }

        .card-prev {
            transform: translate3d(-60%, 0, -100px) rotateY(35deg);
            z-index: 30;
            opacity: 0.4;
            filter: blur(4px) grayscale(0.5);
        }

        .card-next {
            transform: translate3d(60%, 0, -100px) rotateY(-35deg);
            z-index: 30;
            opacity: 0.4;
            filter: blur(4px) grayscale(0.5);
        }

        .card-far-left {
            transform: translate3d(-100%, 0, -300px) rotateY(45deg);
            z-index: 10;
            opacity: 0;
            pointer-events: none;
        }

        .card-far-right {
            transform: translate3d(100%, 0, -300px) rotateY(-45deg);
            z-index: 10;
            opacity: 0;
            pointer-events: none;
        }

        .hud-overlay {
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.2) 50%),
                linear-gradient(90deg, rgba(255, 0, 0, 0.05), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.05));
            background-size: 100% 3px, 3px 100%;
            pointer-events: none;
        }

        .scan-line {
            width: 100%;
            height: 100px;
            z-index: 60;
            background: linear-gradient(0deg, rgba(0, 0, 0, 0) 0%, rgba(59, 130, 246, 0.1) 50%, rgba(0, 0, 0, 0) 100%);
            opacity: 0.1;
            position: absolute;
            bottom: 100%;
            animation: scan 4s linear infinite;
        }

        @keyframes scan {
            0% {
                bottom: 100%;
            }

            100% {
                bottom: -20%;
            }
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-marquee {
            animation: marquee 30s linear infinite;
        }

        .animate-marquee:hover {
            animation-play-state: paused;
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
            <svg class="absolute top-[-5%] left-[-5%] w-150 h-150 text-blue-400 animate-float" viewBox="0 0 100 100">
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
            <svg class="absolute bottom-[-10%] right-[-10%] w-175 h-175 text-blue-400 opacity-80 rotate-12 animate-float-slow"
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
            class="absolute top-1/4 left-[-10%] w-full h-px bg-linear-to-r from-transparent via-blue-500/30 to-transparent rotate-12">
        </div>
        <div
            class="absolute top-1/3 right-[-10%] w-full h-px bg-linear-to-r from-transparent via-red-500/20 to-transparent -rotate-12">
        </div>
    </div>

    {{-- ─── Navigation ──────────────────────────────────────────────────────── --}}
    <nav
        class="relative z-50 px-4 md:px-6 py-4 md:py-6 flex items-center justify-between max-w-7xl mx-auto w-full gap-2">
        <div class="flex items-center gap-1">
            <div class="shrink-0">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                    class="h-10 w-10 md:h-13 md:w-13 object-contain" />
            </div>
            <div class="flex flex-col leading-none">
                <span class="text-sm md:text-xl font-black tracking-tighter text-white uppercase whitespace-nowrap">TRC
                    PEKANBARU</span>
                <span
                    class="text-[8px] md:text-[10px] font-bold text-blue-400 tracking-widest md:tracking-[0.2em] uppercase whitespace-nowrap">Emergency
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
                    class="text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-cyan-300">AMAN</span> DALAM
                SATU GENGGAMAN.
            </h1>

            <p class="text-slate-400 text-lg lg:text-xl font-medium leading-relaxed max-w-xl">
                Transformasi layanan publik Kota Pekanbaru melalui Tim Reaksi Cepat (TRC) 112. Penanganan berbagai
                kejadian darurat kini lebih cepat, terpadu, dan profesional.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                <a href="tel:112"
                    class="w-full sm:w-auto px-10 py-5 bg-linear-to-br from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white text-lg font-black rounded-2xl shadow-xl transition-all neon-glow-red text-center">
                    HUBUNGI 112
                </a>
                <div class="flex items-center gap-4 px-6 py-5 glass-panel rounded-2xl border-white/5">
                    <div class="flex -space-x-4">
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-kominfo-logo-only.png') }}" alt="DISKOMINFO"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-bpbd.jpg') }}" alt="BPBD"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-damkar.png') }}" alt="DAMKAR"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-kemenkes.png') }}" alt="KEMENKES"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-polresta-pku.jpg') }}" alt="POLRESTA"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-satpol-pp.png') }}" alt="SATPOL PP"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-tni.png') }}" alt="TNI"
                                class="w-full h-full object-cover">
                        </div>
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

        {{-- Visual HUD Element: 3D Holographic Carousel --}}
        <div class="hidden lg:flex justify-center items-center relative min-h-125 w-full" x-data="{
            active: 0,
            total: 7,
            images: [
                '{{ asset('assets/images/carousel/1.jpg') }}',
                '{{ asset('assets/images/carousel/2.jpg') }}',
                '{{ asset('assets/images/carousel/3.jpg') }}',
                '{{ asset('assets/images/carousel/4.jpg') }}',
                '{{ asset('assets/images/carousel/5.jpg') }}',
                '{{ asset('assets/images/carousel/6.jpg') }}',
                '{{ asset('assets/images/carousel/7.jpg') }}'
            ],
            next() { this.active = (this.active + 1) % this.total },
            prev() { this.active = (this.active - 1 + this.total) % this.total },
            getCardClass(index) {
                if (index === this.active) return 'card-active';
                if (index === (this.active - 1 + this.total) % this.total) return 'card-prev';
                if (index === (this.active + 1) % this.total) return 'card-next';
                if (index < this.active) return 'card-far-left';
                return 'card-far-right';
            }
        }"
            x-init="setInterval(() => next(), 5000)">

            {{-- HUD Background Decorations --}}
            <div
                class="absolute w-140 h-140 rounded-full border-2 border-blue-500/5 animate-[spin_40s_linear_infinite]">
            </div>
            <div
                class="absolute w-110 h-110 rounded-full border border-blue-400/5 animate-[spin_25s_linear_infinite_reverse]">
            </div>

            {{-- 3D Carousel Container --}}
            <div class="relative w-full h-100 carousel-view flex items-center justify-center">
                <template x-for="(img, index) in images" :key="index">
                    <div class="absolute w-[320px] h-112.5 carousel-card rounded-3xl overflow-hidden border border-white/10 shadow-2xl"
                        :class="getCardClass(index)">

                        {{-- Image with HUD Overlay --}}
                        <div class="relative w-full h-full">
                            <img :src="img" class="w-full h-full object-cover" alt="Carousel Image">

                            {{-- Holographic HUD Overlay --}}
                            <div class="absolute inset-0 hud-overlay opacity-40"></div>
                            <div class="absolute inset-0 bg-linear-to-t from-slate-900 via-transparent to-transparent">
                            </div>
                            <div class="scan-line"></div>

                            {{-- Digital Corner Labels --}}
                            <div class="absolute top-4 left-4 flex gap-1">
                                <div class="h-1 w-4 bg-blue-500/50"></div>
                                <div class="h-4 w-1 bg-blue-500/50"></div>
                            </div>
                            <div class="absolute bottom-4 right-4 flex flex-col items-end">
                                <span class="text-[8px] font-black text-blue-400/70 tracking-widest uppercase"
                                    x-text="'CAM-' + (index + 1).toString().padStart(3, '0')"></span>
                                <span class="text-[10px] font-bold text-white/50"
                                    x-text="new Date().toLocaleTimeString()"></span>
                            </div>

                            {{-- Central Target Reticle --}}
                            <div
                                class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
                                <div
                                    class="w-12 h-12 border border-blue-400/30 rounded-full flex items-center justify-center">
                                    <div class="w-1 h-1 bg-blue-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Technical Pagination dots --}}
            <div class="absolute -bottom-8 flex gap-3">
                <template x-for="i in total" :key="i - 1">
                    <button @click="active = i-1" class="h-1 transition-all duration-500 rounded-full"
                        :class="active === i - 1 ? 'w-8 bg-blue-500 shadow-lg shadow-blue-500/50' :
                            'w-2 bg-slate-700 hover:bg-slate-500'">
                    </button>
                </template>
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
                class="glass-panel p-8 rounded-4xl hover:border-red-500/30 transition-all group overflow-hidden relative">
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
                class="glass-panel p-8 rounded-4xl hover:border-blue-500/30 transition-all group overflow-hidden relative">
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
                class="glass-panel p-8 rounded-4xl hover:border-amber-500/30 transition-all group overflow-hidden relative">
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
                class="glass-panel p-8 rounded-4xl hover:border-emerald-500/30 transition-all group overflow-hidden relative">
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
        <div class="grid lg:grid-cols-5 gap-16 items-center">
            {{-- Portrait Section --}}
            <div class="lg:col-span-2 order-2 lg:order-1">
                <div class="relative group">
                    {{-- Animated Glow --}}
                    <div
                        class="absolute -inset-4 bg-blue-600/20 rounded-[3rem] blur-2xl group-hover:bg-blue-600/30 transition-all duration-700 opacity-50 group-hover:opacity-100">
                    </div>

                    {{-- Tech Border Frame --}}
                    <div
                        class="relative glass-panel rounded-[2.8rem] overflow-hidden border-white/10 shadow-2xl transform group-hover:-translate-y-2 transition-all duration-500">
                        <img src="{{ asset('assets/images/agug-nugroho.jpeg') }}"
                            alt="Wali Kota Pekanbaru saat Peluncuran TRC"
                            class="w-full h-auto object-cover aspect-3/4 filter contrast-110 grayscale group-hover:grayscale-0 transition-all duration-1000 scale-105 group-hover:scale-100">

                        {{-- Identity Badge --}}
                        <div
                            class="absolute bottom-0 left-0 right-0 p-8 bg-linear-to-t from-[#0a192f] via-[#0a192f]/80 to-transparent">
                            <div class="space-y-1">
                                <h3 class="text-xl font-black text-white tracking-widest uppercase italic">Agung
                                    Nugroho
                                </h3>
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-[0.3em]">Wali Kota
                                    Pekanbaru</p>
                            </div>
                        </div>

                        {{-- Decoration Labels --}}
                        <div
                            class="absolute flex top-6 right-6 px-3 py-2 rounded-full bg-blue-500/20 border border-blue-500/30 backdrop-blur-md">
                            <span class="text-[8px] font-black text-blue-400 uppercase tracking-widest">Official
                                Speech</span>
                        </div>
                    </div>

                    {{-- Decorative Corners --}}
                    <div
                        class="absolute -top-2 -left-2 w-12 h-12 border-t-2 border-l-2 border-blue-500/40 rounded-tl-3xl">
                    </div>
                    <div
                        class="absolute -bottom-2 -right-2 w-12 h-12 border-b-2 border-r-2 border-blue-500/40 rounded-br-3xl">
                    </div>
                </div>
            </div>

            {{-- Text/Quote Section --}}
            <div class="lg:col-span-3 space-y-10 order-1 lg:order-2">
                <div class="space-y-4">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 w-fit">
                        <span class="text-[10px] font-black uppercase tracking-widest text-red-500 italic">Visi & Misi
                            TRC</span>
                    </div>
                    <h2 class="text-4xl lg:text-5xl font-black text-white leading-none uppercase tracking-tighter">
                        TRANSFORMASI <br />
                        <span class="text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-cyan-300">POTENSI
                            LAYANAN</span> PUBLIK.
                    </h2>
                    <div
                        class="h-1 w-24 bg-linear-to-r from-blue-600 to-transparent rounded-full shadow-lg shadow-blue-600/50">
                    </div>
                </div>

                <div class="relative">
                    <div
                        class="absolute -top-10 -left-6 text-9xl font-serif text-blue-500/5 select-none tracking-tighter">
                        “
                    </div>
                    <p
                        class="text-xl lg:text-2xl font-medium text-slate-300 leading-relaxed italic relative z-10 border-l-4 border-blue-500/30 pl-8">
                        "Masyarakat cukup dengan menekan angka 112, seluruh laporan mulai dari kecelakaan, kebakaran,
                        gangguan keamanan, hingga kebutuhan medis darurat akan langsung ditindaklanjuti. Ini adalah
                        transformasi layanan yang nyata."
                    </p>
                </div>

                <div class="space-y-6">
                    <p class="text-slate-400 text-lg leading-relaxed font-medium">
                        Diluncurkan pada April 2026, TRC Pekanbaru Aman 112 merupakan jawaban atas aspirasi warga yang
                        menginginkan kehadiran pemerintah secara cepat saat situasi darurat.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            class="p-5 glass-panel rounded-2xl border-white/5 space-y-3 hover:border-blue-500/20 transition-all group">
                            <div
                                class="h-10 w-10 flex items-center justify-center rounded-xl bg-blue-500/10 text-blue-400 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h4 class="text-xs font-black text-white uppercase tracking-widest">SOP Respon Ketat</h4>
                            <p class="text-[10px] text-slate-500 font-bold leading-relaxed uppercase">Memastikan unit
                                bantuan tiba di lokasi kejadian dengan standar waktu optimal.</p>
                        </div>
                        <div
                            class="p-5 glass-panel rounded-2xl border-white/5 space-y-3 hover:border-red-500/20 transition-all group">
                            <div
                                class="h-10 w-10 flex items-center justify-center rounded-xl bg-red-500/10 text-red-400 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.005 3.005 0 013.75-2.906z" />
                                </svg>
                            </div>
                            <h4 class="text-xs font-black text-white uppercase tracking-widest">Lintas Sektor</h4>
                            <p class="text-[10px] text-slate-500 font-bold leading-relaxed uppercase">Integrasi 7
                                instansi
                                strategis untuk penanganan darurat yang komprehensif.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── Navigation/Menu Section ────────────────────────────────────────── --}}
    <section class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24">
        <div class="text-center mb-16 max-w-2xl mx-auto">
            <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tight uppercase">APLIKASI TRC</h2>
            <p class="text-slate-400 font-medium italic">Aplikasi TRC mengintegrasikan seluruh unit reaksi cepat kota
                Pekanbaru.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Absensi Menu --}}
            <a href="{{ route('absensi-web') }}"
                class="glass-panel p-8 rounded-4xl hover:border-blue-500/30 transition-all group overflow-hidden relative flex flex-col items-center justify-center text-center gap-4">
                <div
                    class="h-16 w-16 rounded-full bg-red-500/10 flex items-center justify-center p-2 group-hover:scale-110 transition-transform">
                    <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                        class="h-full w-full object-contain" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Absensi TRC</h3>
                    <p class="text-xs font-medium text-slate-400 mt-2">Portal absensi kehadiran anggota TRC.</p>
                </div>
            </a>

            {{-- Personil Login Menu --}}
            <a href="{{ route('personnel.login') }}"
                class="glass-panel p-8 rounded-4xl hover:border-emerald-500/30 transition-all group overflow-hidden relative flex flex-col items-center justify-center text-center gap-4">
                <div
                    class="h-16 w-16 rounded-full bg-purple-500/10 flex items-center justify-center p-2 group-hover:scale-110 transition-transform">
                    <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                        class="h-full w-full object-contain" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Zona Personel</h3>
                    <p class="text-xs font-medium text-slate-400 mt-2">Dashboard personel TRC.</p>
                </div>
            </a>

            {{-- Admin Login Menu --}}
            <a href="{{ route('login') }}"
                class="glass-panel p-8 rounded-4xl hover:border-purple-500/30 transition-all group overflow-hidden relative flex flex-col items-center justify-center text-center gap-4">
                <div
                    class="h-16 w-16 rounded-full bg-blue-500/10 flex items-center justify-center p-2 group-hover:scale-110 transition-transform">
                    <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                        class="h-full w-full object-contain" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tight">Dashboard TRC</h3>
                    <p class="text-xs font-medium text-slate-400 mt-2">Manajemen Sistem Absensi TRC.</p>
                </div>
            </a>
        </div>
    </section>

    {{-- ─── Lintas Sektoral Section ────────────────────────────────────────── --}}
    <section class="relative z-10 py-12 lg:py-20 overflow-hidden border-t border-white/5 bg-slate-900/10">
        <div class="max-w-7xl mx-auto px-6 mb-12 text-center">
            <h2 class="text-3xl font-black text-white tracking-widest uppercase">Lintas Sektoral</h2>
            <div class="h-1 w-20 bg-blue-500 mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="flex relative overflow-hidden">
            <div class="flex animate-marquee whitespace-nowrap gap-8 py-8">
                @php
                    $partners = [
                        ['logo' => 'logo-kominfo-logo-only.png', 'name' => 'Dinas Kominfo Pekanbaru'],
                        ['logo' => 'logo-bpbd.jpg', 'name' => 'BPBD Pekanbaru'],
                        ['logo' => 'logo-damkar.png', 'name' => 'Damkar Pekanbaru'],
                        ['logo' => 'logo-kemenkes.png', 'name' => 'Dinas Kesehatan Pekanbaru'],
                        ['logo' => 'logo-polresta-pku.jpg', 'name' => 'Polresta Pekanbaru'],
                        ['logo' => 'logo-satpol-pp.png', 'name' => 'Satpol PP Pekanbaru'],
                        ['logo' => 'logo-tni.png', 'name' => 'TNI Kodim 0301/PBR'],
                    ];
                @endphp

                @foreach (array_merge($partners, $partners, $partners) as $p)
                    <div
                        class="flex flex-col items-center justify-center p-8 bg-white rounded-3xl shadow-2xl w-64 shrink-0 gap-6 border border-slate-200 transform transition-transform hover:scale-105">
                        <div class="h-24 w-24 flex items-center justify-center">
                            <img src="{{ asset('assets/logo/' . $p['logo']) }}" alt="{{ $p['name'] }}"
                                class="max-h-full max-w-full object-contain">
                        </div>
                        <span
                            class="text-sm font-black text-slate-800 uppercase tracking-tighter text-center whitespace-normal leading-tight h-10 flex items-center">{{ $p['name'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Atmospheric Overlays --}}
            <div class="absolute inset-y-0 left-0 w-40 bg-linear-to-r from-[#0a192f] to-transparent z-10"></div>
            <div class="absolute inset-y-0 right-0 w-40 bg-linear-to-l from-[#0a192f] to-transparent z-10"></div>
        </div>
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
