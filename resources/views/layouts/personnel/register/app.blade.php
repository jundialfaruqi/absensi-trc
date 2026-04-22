<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' - Portall Personnel TRC' : 'Register Personnel - TRC Pekanbaru' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes floating {
            0% { transform: translateY(0px) translate(0, 0); }
            50% { transform: translateY(-10px) translate(5px, 5px); }
            100% { transform: translateY(0px) translate(0, 0); }
        }
    </style>
</head>

<body class="bg-[#0a192f] text-slate-100 min-h-screen relative overflow-x-hidden antialiased">
    {{-- Background Decoration Layers --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        {{-- Base Gradient --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,#1a3a8a_0%,#0a192f_70%)]"></div>

        {{-- Digital Plexus Dots & Lines --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
            {{-- Top Left cluster --}}
            <svg class="absolute top-[-5%] left-[-5%] w-150 h-150 text-blue-400" viewBox="0 0 100 100" style="animation: floating 15s ease-in-out infinite;">
                <circle cx="10" cy="10" r="1" fill="currentColor" />
                <circle cx="30" cy="20" r="1.2" fill="currentColor" />
                <circle cx="15" cy="40" r="1" fill="currentColor" />
                <circle cx="50" cy="15" r="1.5" fill="currentColor" />
                <circle cx="45" cy="45" r="1" fill="currentColor" />
                <circle cx="70" cy="10" r="1" fill="currentColor" />
                <circle cx="25" cy="65" r="1.2" fill="currentColor" />
                <circle cx="55" cy="35" r="1" fill="currentColor" />
                <line x1="10" y1="10" x2="30" y2="20" stroke="currentColor" stroke-width="0.3" />
                <line x1="30" y1="20" x2="50" y2="15" stroke="currentColor" stroke-width="0.3" />
                <line x1="15" y1="40" x2="30" y2="20" stroke="currentColor" stroke-width="0.3" />
                <line x1="50" y1="15" x2="70" y2="10" stroke="currentColor" stroke-width="0.3" />
                <line x1="45" y1="45" x2="55" y2="35" stroke="currentColor" stroke-width="0.3" />
            </svg>

            {{-- Bottom Right cluster --}}
            <svg class="absolute bottom-[-10%] right-[-10%] w-175 h-175 text-blue-400 opacity-80 rotate-12" viewBox="0 0 100 100" style="animation: floating 20s ease-in-out infinite reverse;">
                <circle cx="80" cy="80" r="1" fill="currentColor" />
                <circle cx="60" cy="70" r="1.5" fill="currentColor" />
                <circle cx="90" cy="50" r="1" fill="currentColor" />
                <circle cx="40" cy="90" r="1.2" fill="currentColor" />
                <circle cx="30" cy="60" r="1.2" fill="currentColor" />
                <circle cx="50" cy="40" r="1" fill="currentColor" />
                <circle cx="75" cy="55" r="1" fill="currentColor" />
                <line x1="80" y1="80" x2="60" y2="70" stroke="currentColor" stroke-width="0.3" />
                <line x1="60" y1="70" x2="90" y2="50" stroke="currentColor" stroke-width="0.3" />
                <line x1="40" y1="90" x2="30" y2="60" stroke="currentColor" stroke-width="0.3" />
                <line x1="50" y1="40" x2="60" y2="70" stroke="currentColor" stroke-width="0.3" />
            </svg>
        </div>

        {{-- Neon Tech Lines --}}
        <div class="absolute top-1/4 left-[-10%] w-full h-px bg-linear-to-r from-transparent via-blue-500/30 to-transparent rotate-12"></div>
        <div class="absolute top-1/3 right-[-10%] w-full h-px bg-linear-to-r from-transparent via-red-500/20 to-transparent -rotate-12"></div>
    </div>

    <div class="relative z-10">
        {{ $slot }}
    </div>

    @livewireScripts
</body>

</html>
