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
    <livewire:public.section.nav-menu />

    {{-- ─── Hero Section ────────────────────────────────────────────────────── --}}
    <livewire:public.section.hero />

    {{-- ─── Services Grid ──────────────────────────────────────────────────── --}}
    <livewire:public.section.service />

    {{-- ─── Message/Mayor Section ────────────────────────────────────────── --}}
    <livewire:public.section.mayor />

    {{-- ─── Navigation/Menu Section ────────────────────────────────────────── --}}
    <livewire:public.section.menu />

    {{-- ─── Berita Section ─────────────────────────────────────────────────── --}}
    <livewire:public.section.berita.show />

    {{-- ─── Lintas Sektoral Section ────────────────────────────────────────── --}}
    <livewire:public.section.lintas-sektoral />

    {{-- ─── Footer ────────────────────────────────────────────────────────── --}}
    <livewire:public.section.footer />

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
