<div class="space-y-8 animate-in fade-in duration-700">
    {{-- Welcome Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest italic">System Active</span>
            </div>
            <h1 class="text-3xl font-black text-white uppercase italic sm:text-3xl">
                Halo, <span
                    class="text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-cyan-300">{{ $personnel->name }}</span>
            </h1>
            <p class="text-slate-400 font-medium text-sm">Selamat datang kembali di pusat kendali personil TRC.</p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-3">
            <a href="{{ url('/personnel/profile') }}"
                class="flex-1 md:flex-none px-6 h-12 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl flex items-center justify-center gap-2 text-white font-black uppercase text-[10px] tracking-widest transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                Ubah Data
            </a>
        </div>
    </div>

    {{-- Stats Grid / Quick Info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Personnel Identity Card --}}
        <div class="md:col-span-2 glass-panel p-8 rounded-[2.5rem] relative group overflow-hidden">

            <div class="flex flex-col sm:flex-row gap-8 items-center sm:items-start relative z-10">
                <div class="relative">
                    <div
                        class="absolute -inset-2 bg-blue-500/20 rounded-3xl blur-xl group-hover:bg-blue-500/30 transition-all opacity-0 group-hover:opacity-100">
                    </div>
                    <div
                        class="h-40 w-32 rounded-3xl border-2 border-white/10 overflow-hidden shadow-2xl relative z-10">
                        <img src="{{ $personnel->foto ? asset('storage/' . $personnel->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($personnel->name) . '&size=256&background=1e293b&color=38bdf8' }}"
                            alt="Foto Personil" class="h-full w-full object-cover">
                    </div>
                </div>

                <div class="space-y-4 text-center sm:text-left flex-1">
                    <div class="flex flex-col">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Selamat Datang,
                        </p>
                        <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter leading-none">
                            {{ $personnel->name }}</h1>
                    </div>
                    <p class="text-sm font-bold text-slate-400">{{ $personnel->email }}</p>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Organisasi (OPD)
                            </p>
                            <p class="text-xs font-black text-white uppercase tracking-tighter italic leading-tight">
                                {{ $personnel->opd?->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Unit Penugasan</p>
                            <p class="text-xs font-black text-blue-400 uppercase tracking-tighter italic leading-tight">
                                {{ $personnel->penugasan?->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-white/5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span
                            class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Kantor:</span>
                        <span
                            class="text-[10px] font-black text-slate-300 uppercase italic">{{ $personnel->kantor?->name ?? 'Mobile Unit' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20 text-[9px] font-black text-blue-400 uppercase italic text-nowrap">
                            {{ $personnel->wajib_absen_di_lokasi ? 'Wajib Geofence' : 'Bebas Lokasi' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact & Status Card --}}
        <div
            class="glass-panel p-8 rounded-[2.5rem] flex flex-col justify-between border-white/5 relative overflow-hidden">
            <div class="space-y-6 relative z-10">
                <div>
                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.3em] mb-4 text-center">Status
                        Keamanan</p>
                    <div
                        class="p-4 bg-slate-900/50 rounded-2xl border border-white/5 flex flex-col items-center justify-center gap-1">
                        <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Kode PIN
                            Aktif</span>
                        <span class="text-2xl font-black text-white tracking-[0.5em] italic">••••••</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white/3 border border-white/5">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest text-wrap">Nomor
                            HP</span>
                        <span class="text-xs font-black text-white italic">{{ $personnel->nomor_hp ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white/3 border border-white/5">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest text-wrap">Terdaftar
                            Sejak</span>
                        <span
                            class="text-xs font-black text-white italic">{{ $personnel->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dashboard Menu Navigation --}}
    <div class="space-y-6 pt-4">
        <div class="flex items-center gap-3 px-2">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.4em] italic whitespace-nowrap">Menu
                Utama Navigasi</h3>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Navigation Cards --}}
            <a href="{{ url('/personnel/profile') }}"
                class="group relative p-8 glass-panel rounded-[2.5rem] border-white/5 hover:border-blue-500/40 hover:bg-blue-600/5 transition-all duration-500 overflow-hidden shadow-xl">
                <div
                    class="absolute -top-12 -right-12 h-32 w-32 bg-blue-600/10 blur-3xl group-hover:bg-blue-600/20 transition-all">
                </div>

                <div
                    class="h-16 w-16 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>

                <h4 class="text-lg font-black text-white uppercase italic tracking-wider leading-tight">Ubah
                    Data<br />Akun</h4>
                <p
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2 group-hover:text-blue-300 transition-colors">
                    Email, Password & PIN</p>

                <div
                    class="mt-6 flex items-center gap-2 text-blue-400 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                    <span class="text-[9px] font-black uppercase tracking-widest italic">Akses Sekarang</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

            {{-- Riwayat Absensi --}}
            <a href="{{ route('personnel.riwayat') }}"
                class="group relative p-8 glass-panel rounded-[2.5rem] border-white/5 hover:border-emerald-500/40 hover:bg-emerald-600/5 transition-all duration-500 overflow-hidden shadow-xl">
                <div
                    class="absolute -top-12 -right-12 h-32 w-32 bg-emerald-600/10 blur-3xl group-hover:bg-emerald-600/20 transition-all">
                </div>

                <div
                    class="h-16 w-16 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <h4 class="text-lg font-black text-white uppercase italic tracking-wider leading-tight">
                    Riwayat<br />Absensi</h4>
                <p
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2 group-hover:text-emerald-300 transition-colors">
                    Lihat Record Presensi</p>

                <div
                    class="mt-6 flex items-center gap-2 text-emerald-400 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                    <span class="text-[9px] font-black uppercase tracking-widest italic">Lihat Detail</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

            {{-- Lihat Jadwal --}}
            <a href="{{ route('personnel.jadwal') }}"
                class="group relative p-8 glass-panel rounded-[2.5rem] border-white/5 hover:border-blue-500/40 hover:bg-blue-600/5 transition-all duration-500 overflow-hidden shadow-xl">
                <div
                    class="absolute -top-12 -right-12 h-32 w-32 bg-blue-600/10 blur-3xl group-hover:bg-blue-600/20 transition-all">
                </div>

                <div
                    class="h-16 w-16 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <h4 class="text-lg font-black text-white uppercase italic tracking-wider leading-tight">
                    Lihat<br />Jadwal</h4>
                <p
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2 group-hover:text-blue-300 transition-colors">
                    Agenda Penugasan Bulan Ini</p>

                <div
                    class="mt-6 flex items-center gap-2 text-blue-400 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                    <span class="text-[9px] font-black uppercase tracking-widest italic">Buka Matriks</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

            {{-- Ajukan Cuti --}}
            <a href="{{ route('personnel.ajukan-cuti') }}"
                class="group relative p-8 glass-panel rounded-[2.5rem] border-white/5 hover:border-blue-500/40 hover:bg-blue-600/5 transition-all duration-500 overflow-hidden shadow-xl">
                <div
                    class="absolute -top-12 -right-12 h-32 w-32 bg-blue-600/10 blur-3xl group-hover:bg-blue-600/20 transition-all">
                </div>

                <div
                    class="h-16 w-16 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <h4 class="text-lg font-black text-white uppercase italic tracking-wider leading-tight">
                    Ajukan<br />Cuti</h4>
                <p
                    class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2 group-hover:text-blue-300 transition-colors">
                    Permohonan Izin & Cuti</p>

                <div
                    class="mt-6 flex items-center gap-2 text-blue-400 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                    <span class="text-[9px] font-black uppercase tracking-widest italic">Ajukan Sekarang</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
