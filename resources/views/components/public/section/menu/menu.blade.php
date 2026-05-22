<section class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24">
    <div class="text-center mb-16 max-w-2xl mx-auto">
        <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tight uppercase">APLIKASI TRC</h2>
        <p class="text-slate-400 font-medium italic">Aplikasi TRC mengintegrasikan seluruh unit reaksi cepat kota
            Pekanbaru.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Absensi Menu --}}
        <a href="{{ route('absensi-web') }}"
            class="p-8 rounded-4xl hover:border-blue-500/30 transition-all group overflow-hidden relative flex flex-col items-center justify-center text-center gap-4">
            <div
                class="h-16 w-16 rounded-full bg-red-500/10 flex items-center justify-center p-2 group-hover:scale-110 transition-transform">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC" class="h-full w-full object-contain" />
            </div>
            <div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight">Absensi TRC</h3>
                <p class="text-xs font-medium text-slate-400 mt-2">Portal absensi kehadiran anggota TRC.</p>
            </div>
        </a>

        {{-- Personil Login Menu --}}
        <a href="{{ route('personnel.login') }}"
            class="p-8 rounded-4xl hover:border-emerald-500/30 transition-all group overflow-hidden relative flex flex-col items-center justify-center text-center gap-4">
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
            class="p-8 rounded-4xl hover:border-purple-500/30 transition-all group overflow-hidden relative flex flex-col items-center justify-center text-center gap-4">
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
