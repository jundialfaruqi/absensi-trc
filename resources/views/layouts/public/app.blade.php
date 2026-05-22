<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageTitle = $title ?? ($berita->judul ?? 'Layanan Darurat Terintegrasi');
        $pageDescription =
            $description ?? (isset($berita) ? Str::limit(strip_tags($berita->isi), 160) : 'TRC Pekanbaru Aman 112');
        $pageImage =
            $image ??
            (isset($berita) && $berita->gambar
                ? asset('storage/' . $berita->gambar)
                : asset('assets/logo/trc-logo.webp'));
    @endphp
    <title>{{ $pageTitle }} | TRC Pekanbaru Aman 112</title>
    <meta name="description" content="{{ $pageDescription }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $pageTitle }} | TRC Pekanbaru Aman 112">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">

    {{-- Twitter --}}
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $pageTitle }} | TRC Pekanbaru Aman 112">
    <meta property="twitter:description" content="{{ $pageDescription }}">
    <meta property="twitter:image" content="{{ $pageImage }}">

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

        /* Article Content Styles */
        .berita-content p {
            margin-bottom: 0rem;
            line-height: 1.8;
            color: #cbd5e1;
        }

        .berita-content h1,
        .berita-content h2,
        .berita-content h3 {
            color: white;
            font-weight: 900;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .berita-content h1 {
            font-size: 2rem;
        }

        .berita-content h2 {
            font-size: 1.75rem;
        }

        .berita-content h3 {
            font-size: 1.5rem;
        }

        .berita-content ul {
            list-style-type: disc;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
            color: #cbd5e1;
        }

        .berita-content ol {
            list-style-type: decimal;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
            color: #cbd5e1;
        }

        .berita-content li {
            margin-bottom: 0.5rem;
        }

        .berita-content a {
            color: #60a5fa;
            text-decoration: underline;
        }

        .berita-content a:hover {
            color: #93c5fd;
        }

        .berita-content strong,
        .berita-content b {
            color: white;
            font-weight: bold;
        }

        .berita-isi img {
            border-radius: 1rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
            max-width: 100%;
            height: auto;
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

    {{-- Navbar --}}
    <livewire:public.section.nav-menu />

    {{-- Main Content --}}
    <main class="relative z-10">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <livewire:public.section.footer />

    @livewireScripts
</body>

</html>
