<div class="space-y-6">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Pengaturan Sistem</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola Pengaturan Sistem</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('pengaturan') }}">
                        <span class="text-base-content font-bold">Pengaturan Sistem</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Settings Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.97 5.97 0 0 0-.942 3.197M12 10.5a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-black uppercase italic">Pendaftaran Personnel</h2>
                </div>

                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between p-4 rounded-xl bg-base-200/50 border border-base-200 group transition-all duration-300 hover:border-primary/30">
                        <div class="space-y-1">
                            <span class="text-sm font-bold block uppercase tracking-tight">Aktifkan Halaman
                                Register</span>
                            <p class="text-[10px] text-base-content/50 uppercase font-medium leading-relaxed max-w-md">
                                Jika dinonaktifkan, personel tidak dapat mendaftar sendiri melalui halaman portal.
                            </p>
                        </div>
                        <input type="checkbox" wire:model.live="registrationEnabled" class="toggle toggle-primary" />
                    </div>

                    <div class="alert alert-info shadow-sm border border-info/20 py-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="stroke-current shrink-0 w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-[10px] font-bold uppercase tracking-wide">
                            Link Registrasi: <span
                                class="select-all underline decoration-dotted">{{ route('personnel.register') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="card bg-primary text-primary-content shadow-xl overflow-hidden relative group">
            <div
                class="absolute -right-10 -bottom-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-64 h-64">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.5 12a7.5 7.5 0 0 0 15 0m-15 0a7.5 7.5 0 1 1 15 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077 1.41-.513m14.095-5.128 1.41-.513M12 3.153V3m0 18v-.153m4.43-4.43.086.086m-9.032-9.032.086.086m0 8.86-.086.086m9.032-9.032-.086.086" />
                </svg>
            </div>
            <div class="card-body relative z-10">
                <h2 class="card-title text-2xl font-black italic uppercase tracking-tighter">Butuh Bantuan?</h2>
                <p class="text-sm font-medium opacity-80 uppercase leading-relaxed mt-2">
                    Pengaturan di halaman ini bersifat global dan berdampak langsung pada operasional sistem. Pastikan
                    Anda telah mempertimbangkan keamanan sebelum membuka akses pendaftaran mandiri.
                </p>
                <div class="card-actions justify-end mt-6">
                    <button
                        class="btn btn-sm bg-white/20 border-none text-white font-black uppercase tracking-widest hover:bg-white/30 transition-all">Dokumentasi</button>
                </div>
            </div>
        </div>
    </div>
</div>
