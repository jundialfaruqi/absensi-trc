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
            <a href="{{ route('absensi-web') }}"
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
