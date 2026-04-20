<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' - Dashboard Personnel' : 'Dashboard Personnel - TRC Pekanbaru' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-sans antialiased bg-[#0a192f] text-slate-200">
    <div class="min-h-screen relative overflow-hidden">
        {{-- Background Decoration Layers --}}
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,#1a3a8a_0%,#0a192f_70%)]"></div>

            {{-- Digital Plexus (Simplified for Dashboard) --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="absolute top-0 left-0 w-full h-full text-blue-500" viewBox="0 0 100 100"
                    preserveAspectRatio="none">
                    <circle cx="10" cy="10" r="0.5" fill="currentColor" />
                    <circle cx="40" cy="20" r="0.5" fill="currentColor" />
                    <circle cx="70" cy="15" r="0.5" fill="currentColor" />
                    <circle cx="90" cy="40" r="0.5" fill="currentColor" />
                    <circle cx="20" cy="60" r="0.5" fill="currentColor" />
                    <line x1="10" y1="10" x2="40" y2="20" stroke="currentColor"
                        stroke-width="0.1" />
                    <line x1="40" y1="20" x2="70" y2="15" stroke="currentColor"
                        stroke-width="0.1" />
                    <line x1="70" y1="15" x2="90" y2="40" stroke="currentColor"
                        stroke-width="0.1" />
                </svg>
            </div>
        </div>

        {{-- Main Navigation (Top-Bar) --}}
        <header class="relative z-50 border-b border-white/5 backdrop-blur-md bg-[#0a192f]/50">
            <div class="max-w-5xl mx-auto px-4 h-20 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-10 w-10 p-1.5 bg-white/5 rounded-lg flex items-center justify-center">
                        <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo"
                            class="h-full w-full object-contain">
                    </div>

                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-sm font-black text-white italic">
                            TRC PEKANBARU
                        </span>
                        <span class="text-[8px] font-bold text-blue-400 uppercase tracking-widest">
                            Personnel Portal
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div x-data="{ userMenuOpen: false }" class="relative">
                        <button @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-3 p-1 rounded-xl transition-all outline-none group">
                            <div class="hidden md:block text-right mr-1">
                                <p class="text-[10px] font-black text-white uppercase italic leading-none">
                                    {{ Auth::guard('personnel')->user()->name }}</p>
                                <p class="text-[8px] font-bold text-blue-400 uppercase tracking-widest mt-1">Online</p>
                            </div>

                            <div
                                class="h-10 w-10 rounded-full border border-white/10 overflow-hidden group-hover:border-blue-500/50 transition-all shadow-inner">
                                <img src="{{ Auth::guard('personnel')->user()->foto ? asset('storage/' . Auth::guard('personnel')->user()->foto) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::guard('personnel')->user()->name) . '&background=1e293b&color=38bdf8' }}"
                                    alt="Avatar" class="h-full w-full object-cover">
                            </div>

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-slate-500 transition-transform duration-300"
                                :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="userMenuOpen" @click.away="userMenuOpen = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                            class="absolute right-0 mt-3 w-56 bg-[#111c35] rounded-2xl shadow-2xl border border-white/10 overflow-hidden z-[100]"
                            style="display: none;">

                            <div class="p-4 border-b border-white/5 bg-white/5">
                                <p class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em] mb-1">Akses
                                    Sekarang</p>
                                <p class="text-xs font-black text-white uppercase italic truncate">
                                    {{ Auth::guard('personnel')->user()->name }}</p>
                            </div>

                            <div class="p-2">
                                <a href="{{ url('/personnel/profile') }}"
                                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white transition-all group">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest">My Profile</span>
                                </a>

                                <button onclick="logout_modal_profile.showModal()"
                                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-red-400 transition-all group">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-slate-500/10 group-hover:bg-red-500/10 flex items-center justify-center text-slate-500 group-hover:text-red-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Logout Portal</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <livewire:logout />
                </div>
            </div>
        </header>

        {{-- Content Area --}}
        <main class="relative z-10 max-w-5xl mx-auto px-4 py-8">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="relative z-10 max-w-5xl mx-auto px-4 py-8 border-t border-white/5">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">
                    &copy; {{ date('Y') }} DISKOMINFO KOTA PEKANBARU
                </p>
                <div class="flex items-center gap-4 opacity-40">
                    <img src="{{ asset('assets/logo/aman.webp') }}" alt="AMAN" class="h-4">
                    <img src="{{ asset('assets/logo/logo-diskominfo-pekanbaru.webp') }}" alt="Diskominfo"
                        class="h-4">
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .neon-border-blue {
            box-shadow: inset 0 0 15px rgba(59, 130, 246, 0.05), 0 0 15px rgba(59, 130, 246, 0.05);
        }
    </style>
</body>

</html>
