<div class="min-h-screen bg-[#0a192f] flex flex-col items-center justify-center p-4 relative overflow-hidden font-sans">
    {{-- ─── Background Decoration Layers ────────────────────────────────────── --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        {{-- Base Gradient --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,#1a3a8a_0%,#0a192f_70%)]"></div>

        {{-- Digital Plexus Dots & Lines (Corner Decorations) --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-25">
            {{-- Top Left cluster (Enhanced) --}}
            <svg class="absolute top-[-10%] left-[-10%] w-150 h-150 text-blue-400 animate-float" viewBox="0 0 100 100">
                <circle cx="10" cy="10" r="1" fill="currentColor" />
                <circle cx="30" cy="20" r="1.2" fill="currentColor" />
                <circle cx="15" cy="40" r="1" fill="currentColor" />
                <circle cx="50" cy="15" r="1.5" fill="currentColor" />
                <circle cx="45" cy="45" r="1" fill="currentColor" />
                <circle cx="70" cy="10" r="1" fill="currentColor" />
                <circle cx="25" cy="65" r="1.2" fill="currentColor" />
                <circle cx="55" cy="35" r="1" fill="currentColor" />
                <circle cx="20" cy="15" r="0.8" fill="currentColor" />
                <circle cx="40" cy="10" r="0.8" fill="currentColor" />
                <circle cx="65" cy="40" r="1" fill="currentColor" />
                <line x1="10" y1="10" x2="30" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="30" y1="20" x2="50" y2="15" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="10" y1="10" x2="15" y2="40" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="15" y1="40" x2="30" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="15" y1="40" x2="25" y2="65" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="30" y1="20" x2="45" y2="45" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="50" y1="15" x2="70" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="45" y1="45" x2="50" y2="15" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="55" y1="35" x2="45" y2="45" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="55" y1="35" x2="70" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="20" y1="15" x2="10" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="20" y1="15" x2="30" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="40" y1="10" x2="20" y2="15" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="40" y1="10" x2="50" y2="15" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="65" cy1="40" x2="55" y2="35" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="65" cy1="40" x2="70" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>

            {{-- Bottom Right cluster (Enhanced) --}}
            <svg class="absolute bottom-[-15%] right-[-15%] w-175 h-175 text-blue-400 opacity-80 rotate-12 animate-float-slow"
                viewBox="0 0 100 100">
                <circle cx="80" cy="80" r="1" fill="currentColor" />
                <circle cx="60" cy="70" r="1.5" fill="currentColor" />
                <circle cx="90" cy="50" r="1" fill="currentColor" />
                <circle cx="40" cy="90" r="1.2" fill="currentColor" />
                <circle cx="30" cy="60" r="1.2" fill="currentColor" />
                <circle cx="50" cy="40" r="1" fill="currentColor" />
                <circle cx="75" cy="55" r="1" fill="currentColor" />
                <circle cx="45" cy="75" r="1" fill="currentColor" />
                <circle cx="20" cy="80" r="1" fill="currentColor" />
                <circle cx="10" cy="50" r="0.8" fill="currentColor" />
                <line x1="80" y1="80" x2="60" y2="70" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="60" y1="70" x2="90" y2="50" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="60" y1="70" x2="40" y2="90" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="40" y1="90" x2="30" y2="60" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="30" y1="60" x2="50" y2="40" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="50" y1="40" x2="60" y2="70" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="75" y1="55" x2="90" y2="50" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="75" y1="55" x2="60" y2="70" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="45" y1="75" x2="60" y2="70" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="45" y1="75" x2="40" y2="90" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="20" y1="80" x2="30" y2="60" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="20" y1="80" x2="40" y2="90" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="10" y1="50" x2="20" y2="80" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="10" y1="50" x2="30" y2="60" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>

            {{-- Mid Right Cluster (New) --}}
            <svg class="absolute top-[30%] right-[-5%] w-100 h-100 text-blue-400 opacity-60 animate-pulse"
                viewBox="0 0 100 100">
                <circle cx="90" cy="10" r="1" fill="currentColor" />
                <circle cx="70" cy="30" r="1.2" fill="currentColor" />
                <circle cx="85" cy="50" r="1" fill="currentColor" />
                <circle cx="60" cy="15" r="0.8" fill="currentColor" />
                <line x1="90" y1="10" x2="70" y2="30" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="70" y1="30" x2="85" y2="50" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="60" y1="15" x2="70" y2="30" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="60" y1="15" x2="90" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>
        </div>

        <style>
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
        </style>

        {{-- Neon Tech Lines (Red/Orange Glow) --}}
        <div
            class="absolute top-20 right-[-5%] w-64 h-px bg-linear-to-r from-transparent via-red-500 to-transparent opacity-40 blur-xs">
        </div>
        <div
            class="absolute bottom-40 left-[-5%] w-48 h-px bg-linear-to-r from-transparent via-red-400 to-transparent opacity-30 blur-xs rotate-45">
        </div>

        {{-- Pulse Glow for Red Lines --}}
        <div class="absolute top-1/4 right-10 w-2 h-2 bg-red-500 rounded-full blur-xs animate-pulse"></div>
        <div class="absolute bottom-1/3 left-10 w-1.5 h-1.5 bg-red-400 rounded-full blur-xs animate-pulse delay-700">
        </div>

        {{-- Light Streaks --}}
        <div class="absolute top-[10%] left-[20%] w-[60%] h-[20%] bg-blue-500/10 blur-[100px] rounded-full"></div>
    </div>

    <div class="max-w-md w-full relative z-10">
        {{-- ─── Logo & Header ──────────────────────────────────────────────── --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-2 rounded-full">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                    class="h-20 w-20 sm:h-20 sm:w-20 object-contain" />
            </div>
            <h1 class="text-3xl font-black tracking-tight text-white uppercase drop-shadow-md">Absensi TRC</h1>
            <p class="text-blue-100/60 font-medium tracking-wide">Satukan Barisan, Tertib Kehadiran</p>

            {{-- Network Time Status --}}
            <div class="mt-4 flex flex-col items-center gap-1" x-data="{
                time: '',
                date: '',
                offset: {{ $serverTimestamp }} - Date.now(),
                update() {
                    const now = new Date(Date.now() + this.offset);
                    this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                    this.date = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                }
            }" x-init="update();
            setInterval(() => update(), 1000);
            $watch('$wire.serverTimestamp', value => {
                offset = value - Date.now();
            });">
                <div wire:ignore class="flex flex-col items-center">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-300 opacity-60 mb-1"
                        x-text="date">Memuat
                        Tanggal...</div>
                    <div class="text-4xl font-black tracking-widest text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] tabular-nums"
                        x-text="time">00:00:00
                    </div>
                </div>

                <div class="flex items-center gap-1.5 mt-1">
                    @if ($apiSource !== 'local')
                        <div
                            class="flex items-center gap-1 text-[8px] font-black uppercase tracking-widest text-success bg-success/10 px-2 py-0.5 rounded-full border border-success/20">
                            <span class="relative flex h-1.5 w-1.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-success"></span>
                            </span>
                            Network Time Synced ({{ $apiSource }})
                        </div>
                    @else
                        <div
                            class="flex items-center gap-1 text-[8px] font-black uppercase tracking-widest text-warning bg-warning/10 px-2 py-0.5 rounded-full border border-warning/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2.5" stroke="currentColor" class="size-3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 12 24c4.408 0 8.411-2.384 10.606-6.044M11.277 5.889A11.959 11.959 0 0 1 12 2.25c4.408 0 8.411 2.384 10.606 6.044" />
                            </svg>
                            Local Server Time
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── Step 5: Portal Closed ────────────────────────── --}}
        @if ($step === 5)
            <div class="card bg-slate-900/40 backdrop-blur-xl border border-error/20 shadow-2xl animate-in zoom-in-95">
                <div class="card-body items-center text-center py-12">
                    <div
                        class="w-20 h-20 bg-error/20 text-error rounded-full flex items-center justify-center mb-6 border border-error/30 shadow-[0_0_30px_rgba(239,68,68,0.2)]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2.5" stroke="currentColor" class="size-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-white uppercase tracking-tighter mb-4">Portal Ditutup</h2>
                    <p class="text-blue-100/60 font-medium text-sm leading-relaxed mb-8 uppercase tracking-wide">
                        {{ $message }}
                    </p>
                    <a href="/"
                        class="btn bg-white/10 hover:bg-white/20 border-white/20 text-white btn-block rounded-2xl font-black uppercase tracking-widest">Kembali
                        ke Beranda</a>
                </div>
            </div>
        @endif

        {{-- ─── Step 1: Selection & Time Sync Check ────────────────────────── --}}
        @if (!$isTimeSynced)
            <div
                class="card bg-base-100 shadow-xl border border-error/20 overflow-hidden animate-in fade-in zoom-in-95">
                <div class="bg-error/10 p-6 flex flex-col items-center text-center">
                    <div
                        class="w-16 h-16 bg-error text-error-content rounded-full flex items-center justify-center mb-4 shadow-lg shadow-error/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2.5" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-error uppercase tracking-tight">Sinkronisasi Gagal</h2>
                    <p class="text-xs font-medium text-base-content/60 mt-2 px-4">Sistem tidak dapat memverifikasi
                        waktu
                        jaringan yang akurat. Akses absensi ditutup untuk mencegah manipulasi data.</p>
                </div>
                <div class="card-body p-6 bg-base-50">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-base-100 rounded-xl border border-base-200">
                            <div class="p-2 bg-base-200 rounded-lg text-base-content/40">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="size-4">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold opacity-70 uppercase">Periksa Koneksi Internet
                                Server</span>
                        </div>
                        <button onclick="window.location.reload()"
                            class="btn btn-outline btn-block rounded-xl font-bold uppercase tracking-widest text-[10px]">Coba
                            Lagi</button>
                    </div>
                </div>
            </div>
        @elseif ($step === 1)
            <div
                class="card bg-slate-900/40 backdrop-blur-xl border border-white/10 shadow-2xl animate-in fade-in zoom-in-95">
                <div class="card-body">
                    <h2
                        class="card-title justify-center mb-4 text-white uppercase tracking-widest text-sm font-bold opacity-80">
                        Cari Nama Anda</h2>
                    <div class="relative mb-6">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Ketik nama anda..."
                            class="input input-lg w-full pl-12 bg-white/5 border-white/10 text-white placeholder-white/30 focus:border-blue-400 focus:bg-white/10 transition-all rounded-2xl"
                            autofocus />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor"
                            class="size-6 absolute left-4 top-1/2 -translate-y-1/2 text-white/40">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>

                    <div class="space-y-2 relative">
                        {{-- Loading State --}}
                        <div wire:loading wire:target="search" class="absolute inset-0 z-20 flex items-center justify-center bg-[#0a192f]/50 backdrop-blur-[2px] rounded-2xl">
                            <div class="flex flex-col items-center gap-3">
                                <span class="loading loading-spinner loading-lg text-blue-400"></span>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-300">Mencari Data...</span>
                            </div>
                        </div>

                        @if (strlen($search) < 3)
                            <div wire:loading.remove wire:target="search"
                                class="text-center py-10 px-6 border-2 border-dashed border-white/5 rounded-2xl bg-white/2">
                                <div class="text-blue-300/20 mb-3 flex justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline size-12 icon-tabler-user-search">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h1.5" />
                                        <path d="M15 18a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                        <path d="M20.2 20.2l1.8 1.8" />
                                    </svg>
                                </div>
                                <p class="text-[10px] font-old uppercase tracking-[0.2em] text-white/30">Ketik
                                    Nama Anda untuk mencari</p>
                            </div>
                        @endif

                        @foreach ($this->personnels() as $p)
                            <button wire:click="selectPersonnel({{ $p->id }})"
                                class="btn btn-ghost btn-lg w-full justify-between h-auto py-4 group hover:bg-white/10 hover:border-white/20 transition-all border-white/5 rounded-2xl bg-white/5">
                                <div class="flex items-center gap-4 text-white">
                                    <div class="avatar {{ !$p->foto ? 'placeholder' : '' }}">
                                        <div
                                            class="bg-blue-500/20 text-blue-200 group-hover:bg-blue-500/40 w-10 rounded-full overflow-hidden border border-white/10">
                                            @if ($p->foto)
                                                <img src="{{ asset('storage/' . $p->foto) }}" class="object-cover" />
                                            @else
                                                <span
                                                    class="text-sm font-bold">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-bold uppercase text-xs">{{ $p->name }}</div>
                                        <div class="text-[10px] opacity-50 font-normal">
                                            {{ $p->penugasan?->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="3" stroke="currentColor"
                                    class="size-4 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        @endforeach
                    </div>

                    @if (strlen($search) >= 3 && $this->personnels()->isEmpty())
                        <div
                            class="text-center py-10 px-6 border-2 border-dashed border-white/5 rounded-2xl bg-white/2">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-error/60">Personel tidak
                                ditemukan</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="/"
                            class="btn btn-ghost btn-xs btn-block text-white/30 font-bold uppercase tracking-widest rounded-xl hover:bg-transparent hover:text-white/30 hover:shadow-none hover:border-none">Kembali</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── Step 2: PIN Input ─────────────────────────────────────────── --}}
        @if ($step === 2)
            <div
                class="card bg-slate-900/40 backdrop-blur-xl border border-white/10 shadow-2xl animate-in fade-in slide-in-from-bottom-4">
                <div class="card-body">
                    <div class="text-center mb-6">
                        <div class="avatar mb-4 {{ !$selectedPersonnel->foto ? 'placeholder' : '' }}">
                            <div
                                class="bg-blue-500/20 text-blue-200 w-20 rounded-full border-4 border-white/10 shadow-xl overflow-hidden">
                                @if ($selectedPersonnel->foto)
                                    <img src="{{ asset('storage/' . $selectedPersonnel->foto) }}"
                                        class="object-cover" alt="Foto Personnel" />
                                @else
                                    <span
                                        class="text-2xl font-black">{{ strtoupper(substr($selectedPersonnel->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        </div>
                        <h2 class="text-xl font-bold uppercase truncate px-4 text-white drop-shadow-sm">
                            {{ $selectedPersonnel->name }}</h2>
                        <p class="text-xs text-blue-200/50 uppercase tracking-widest font-bold">Masukkan PIN Keamanan
                        </p>
                    </div>

                    <div class="flex justify-center gap-4 mb-8">
                        @for ($i = 0; $i < 4; $i++)
                            <div
                                class="w-12 h-16 rounded-xl border-2 {{ strlen($pin) > $i ? 'border-primary bg-primary/20 shadow-[0_0_15px_rgba(var(--color-primary),0.3)]' : 'border-white/10 bg-white/5' }} flex items-center justify-center text-3xl font-bold transition-all text-white">
                                {{ strlen($pin) > $i ? '●' : '' }}
                            </div>
                        @endfor
                    </div>

                    @error('pin')
                        <div class="alert alert-error my-4 py-2 px-4 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="grid grid-cols-3 gap-3 mb-6">
                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                            <button type="button" wire:click="appendPin({{ $num }})"
                                class="btn btn-ghost bg-white/5 border border-white/5 btn-lg h-16 text-xl font-black text-white hover:bg-white/10 hover:border-white/20 active:scale-95 transition-all rounded-2xl">{{ $num }}</button>
                        @endforeach
                        <button type="button" wire:click="clearPin"
                            class="btn btn-ghost bg-white/5 border border-white/5 btn-lg h-16 text-red-400 hover:text-red-500 hover:bg-red-500/10 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button type="button" wire:click="appendPin(0)"
                            class="btn btn-ghost bg-white/5 border border-white/5 btn-lg h-16 text-xl font-black text-white hover:bg-white/10 rounded-2xl">0</button>
                        <button type="button" wire:click="resetForm"
                            class="btn btn-ghost bg-white/5 border border-white/5 btn-lg h-16 text-blue-400 hover:text-blue-500 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                        </button>
                    </div>

                    <button wire:click="verifyPin" wire:loading.attr="disabled"
                        class="btn btn-primary btn-lg w-full text-lg font-black uppercase shadow-lg shadow-primary/20 flex items-center justify-center gap-3"
                        @disabled(strlen($pin) < 4)>
                        <span wire:loading.remove wire:target="verifyPin">Lanjut</span>
                        <span wire:loading wire:target="verifyPin" class="loading loading-spinner"></span>
                        <span wire:loading wire:target="verifyPin">Memproses...</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- ─── Step 3: Biometric & Location Verification ──────────────────────── --}}
        @if ($step === 3)
            <div wire:key="step-3-verification-{{ $selectedPersonnel->id }}" x-data="absensiVerification()"
                x-init="initVerification('{{ $selectedPersonnel->foto ? asset('storage/' . $selectedPersonnel->foto) : '' }}', {{ $selectedPersonnel->face_recognition ? 'true' : 'false' }})"
                class="card bg-slate-900/40 backdrop-blur-xl border border-white/10 shadow-2xl animate-in zoom-in-95">
                <div class="card-body p-4 sm:p-6 text-white">
                    <div class="text-center mb-4">
                        <h2 class="text-lg font-bold uppercase">{{ $selectedPersonnel->name }}</h2>
                        <div class="flex items-center justify-center gap-2 mt-1">
                            @if ($activeJadwal->shift)
                                <div class="badge badge-primary badge-sm tracking-wider">
                                    {{ $activeJadwal->shift->name }}</div>
                            @else
                                <div class="badge badge-error badge-sm uppercase tracking-widest">
                                    {{ $activeJadwal->status }}</div>
                            @endif
                            <div class="text-[10px] opacity-40 uppercase tracking-tighter">
                                {{ \Carbon\Carbon::parse($activeDate)->format('d M Y') }}</div>
                        </div>
                    </div>

                    {{-- Camera & Detection View --}}
                    <div
                        class="relative aspect-square w-full max-w-70 mx-auto rounded-3xl overflow-hidden bg-black shadow-2xl border-4 border-white/10 group">
                        <video x-ref="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                        <canvas x-ref="overlay" class="absolute inset-0 w-full h-full"></canvas>

                        {{-- Scanning Animation --}}
                        <template x-if="isScanning && !isMatched">
                            <div class="absolute inset-0 pointer-events-none">
                                <div
                                    class="w-full h-1 bg-primary/50 absolute top-0 shadow-[0_0_15px_rgba(255,255,255,0.5)] animate-scan-line">
                                </div>
                                <div
                                    class="absolute inset-x-8 inset-y-8 border-2 border-white/20 rounded-full animate-pulse">
                                </div>
                            </div>
                        </template>

                        {{-- Loading Models Overlay --}}
                        <template x-if="isLoadingModels">
                            <div
                                class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white p-6 text-center">
                                <span class="loading loading-spinner text-primary mb-3"></span>
                                <div class="text-[10px] font-black uppercase tracking-[0.2em]">Memuat AI...</div>
                            </div>
                        </template>

                        {{-- Matched Overlay --}}
                        <template x-if="isMatched">
                            <div
                                class="absolute inset-0 bg-success/20 flex flex-col items-center justify-center text-white border-4 border-success animate-in fade-in duration-300">
                                <div class="bg-success text-white rounded-full p-2 mb-2 shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="3" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                                    {{ $selectedPersonnel->face_recognition ? 'Wajah Terverifikasi' : 'Identitas Siap' }}
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Status Indicators --}}
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <div :class="gpsStatus === 'OK' ? 'bg-success/10 border-success/30 text-success' :
                            'bg-white/5 border-white/10 text-white/40'"
                            class="p-2 rounded-xl border flex items-center gap-2 transition-all shadow-sm">
                            <div :class="gpsStatus === 'OK' ? 'text-success' : 'text-white/20'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[8px] font-black uppercase opacity-60 leading-none mb-0.5">Lokasi
                                </div>
                                <div class="text-[10px] font-bold" x-text="gpsMessage">Mencari...</div>
                            </div>
                        </div>

                        <div :class="isMatched ? 'bg-success/10 border-success/30 text-success' :
                            'bg-white/5 border-white/10 text-white/40'"
                            class="p-2 rounded-xl border flex items-center gap-2 transition-all shadow-sm">
                            <div :class="isMatched ? 'text-success' : 'text-white/20'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.191 6.062h.008v.008h-.008V6.062ZM18 8.812h.008v.008H18V8.812ZM15.191 11.562h.008v.008h-.008v-.008ZM18 14.312h.008v.008H18v-.008ZM21 17.062h.008v.008H21v-.008ZM12.181 8.68c.341-1.107 1.467-1.875 2.65-1.875 1.157 0 2.1 1.607 1.969 2.45-.12.772-.646 1.393-1.307 1.816-.656.422-1.202.911-1.377 1.513l-.111.452m-1.146-5.303-.01.013m2.706 3.103c.19.116.446.126.646.017a1.322 1.322 0 0 0 .512-.575 1.31 1.31 0 0 0 .08-.611c-.027-.317-.184-.504-.39-.554a.705.705 0 0 0-.662.131.6.6 0 0 0-.186.418.604.604 0 0 0 0 .15l.019.014c.032.022.03.024.03.024a.5.5 0 0 1-.038-.027ZM11.182 18H9.122a2 2 0 0 1-1.928-1.464l-1.071-3.75a2 2 0 0 1 .728-2.22l3.07-2.1c.176-.121.377-.183.583-.183h.004c.158 0 .313.036.452.106l1.652.825" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[8px] font-black uppercase opacity-40 leading-none mb-0.5">Biometrik
                                </div>
                                <div class="text-[10px] font-bold"
                                    x-text="faceRecognitionActive ? faceMessage : 'Dilewati'">
                                    Memindai...</div>
                            </div>
                        </div>
                    </div>

                    {{-- Info Lokasi Kantor --}}
                    @if (!empty($infoLokasi))
                        <div class="mt-4 animate-in fade-in slide-in-from-top-2">
                            @if (is_null($infoLokasi['is_within_radius']))
                                {{-- Tidak ada kantor terhubung --}}
                            @elseif($infoLokasi['boleh'] && $infoLokasi['is_within_radius'])
                                <div
                                    class="alert py-2 px-3 border-none bg-green-500/10 text-green-400 text-[10px] font-bold uppercase tracking-tight backdrop-blur-md border border-green-500/20 shadow-[0_0_10px_rgba(34,197,94,0.1)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Dalam radius {{ $infoLokasi['kantor_name'] }}
                                        (±{{ $infoLokasi['jarak_meter'] }}m)</span>
                                </div>
                            @elseif($infoLokasi['boleh'] && !$infoLokasi['is_within_radius'])
                                <div
                                    class="alert py-2 px-3 border-none bg-amber-500/10 text-amber-400 text-[10px] font-bold uppercase tracking-tight backdrop-blur-md border border-amber-500/20 shadow-[0_0_10px_rgba(245,158,11,0.1)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Luar radius {{ $infoLokasi['kantor_name'] }}
                                        ({{ $infoLokasi['jarak_meter'] }}m). Aktif.</span>
                                </div>
                            @else
                                <div
                                    class="alert py-2 px-3 border-none bg-red-500/10 text-red-400 text-[10px] font-bold uppercase tracking-tight leading-tight backdrop-blur-md border border-red-500/20 shadow-[0_0_10px_rgba(239,68,68,0.1)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>{{ $infoLokasi['pesan'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    @if ($activeJadwal->shift)
                        <div class="space-y-3 mt-4">
                            @if ((!$activeAbsensi || !$activeAbsensi->jam_masuk) && !$isTooLateToIn)
                                {{-- Mode: Normal Masuk --}}
                                <button type="button" x-on:click="submit('in')" wire:loading.attr="disabled"
                                    :disabled="!isMatched || gpsStatus !== 'OK' || @js(!empty($infoLokasi) && $infoLokasi['boleh'] === false)"
                                    class="btn bg-linear-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 border-none btn-lg w-full shadow-[0_0_20px_rgba(37,99,235,0.3)] py-2 h-auto group relative flex items-center justify-center min-h-16 text-white rounded-2xl disabled:bg-none disabled:bg-base-300 disabled:shadow-none disabled:opacity-70">

                                    <div wire:loading wire:target="fetchServerTime, submitAttendance">
                                        <span class="loading loading-spinner loading-md"></span>
                                    </div>

                                    <div wire:loading.remove wire:target="fetchServerTime, submitAttendance"
                                        class="flex flex-col items-center">
                                        <span class="text-sm font-black tracking-widest drop-shadow-md">ABSEN
                                            MASUK</span>
                                        <span
                                            class="text-[10px] opacity-70 font-medium group-disabled:hidden uppercase tracking-tighter">SIAP
                                            KIRIM DATA</span>
                                        <span
                                            class="text-[10px] opacity-50 font-medium hidden group-disabled:block uppercase tracking-tighter">Verifikasi
                                            Identitas & Lokasi...</span>
                                    </div>
                                </button>
                            @else
                                {{-- Mode: Pulang --}}
                                @if ((!$activeAbsensi || !$activeAbsensi->jam_masuk) && $isTooLateToIn)
                                    <div
                                        class="alert py-2 px-3 mb-3 border-none bg-red-500/10 text-red-400 text-[10px] font-black uppercase tracking-widest text-center backdrop-blur-md border border-red-500/20">
                                        ⚠️ Batas waktu Masuk berakhir. Silakan Absen Pulang.
                                    </div>
                                @endif

                                <button type="button" x-on:click="submit('out')" wire:loading.attr="disabled"
                                    :disabled="{{ $activeAbsensi && $activeAbsensi->jam_pulang ? 'true' : 'false' }} || !
                                        isMatched || gpsStatus !== 'OK' || @js(!empty($infoLokasi) && $infoLokasi['boleh'] === false)"
                                    class="btn bg-linear-to-r from-secondary to-purple-600 hover:from-secondary/80 hover:to-purple-500 border-none btn-lg w-full shadow-[0_0_20px_rgba(var(--color-secondary),0.3)] py-2 h-auto group relative flex items-center justify-center min-h-16 text-white rounded-2xl disabled:bg-none disabled:bg-base-300 disabled:shadow-none disabled:opacity-70">

                                    <div wire:loading wire:target="fetchServerTime, submitAttendance">
                                        <span class="loading loading-spinner loading-md"></span>
                                    </div>

                                    <div wire:loading.remove wire:target="fetchServerTime, submitAttendance"
                                        class="flex flex-col items-center">
                                        <span class="text-sm font-black uppercase tracking-widest drop-shadow-md">Absen
                                            Pulang</span>
                                        <span
                                            class="text-[10px] opacity-70 font-medium group-disabled:hidden uppercase tracking-tighter">Selesaikan
                                            Kerja Hari Ini</span>
                                        <span
                                            class="text-[10px] opacity-50 font-medium hidden group-disabled:block uppercase tracking-tighter">Verifikasi
                                            Identitas & Lokasi...</span>
                                    </div>
                                </button>
                            @endif
                        </div>
                    @else
                        <div
                            class="p-6 rounded-2xl bg-white/5 border border-white/10 text-center mt-6 backdrop-blur-md">
                            <h3 class="font-black text-rose-400 uppercase text-[10px] mb-1 tracking-widest">Akses
                                Ditutup</h3>
                            <p class="text-[10px] text-white/40 uppercase font-medium tracking-widest">Status:
                                {{ $activeJadwal->status }}</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button wire:click="resetForm" x-on:click="stopCamera()"
                            class="btn btn-ghost btn-xs btn-block text-white/30 font-bold uppercase tracking-widest rounded-xl hover:bg-transparent hover:text-white/30 hover:shadow-none hover:border-none">Kembali</button>
                    </div>
                </div>

                <style>
                    @keyframes scan-line {
                        0% {
                            top: 0;
                        }

                        100% {
                            top: 100%;
                        }
                    }

                    .animate-scan-line {
                        animation: scan-line 2s linear infinite;
                    }
                </style>
        @endif

        {{-- ─── Step 4: Result ─────────────────────────────────────────── --}}
        @if ($step === 4)
            <div
                class="card bg-slate-900/40 backdrop-blur-xl border border-white/10 shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="card-body items-center text-center py-10 text-white">
                    @if ($isSuccess)
                        <div
                            class="w-24 h-24 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center mb-6 scale-animation border border-green-500/30 shadow-[0_0_30px_rgba(34,197,94,0.2)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="3" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    @else
                        <div
                            class="w-24 h-24 bg-red-500/20 text-red-400 rounded-full flex items-center justify-center mb-6 border border-red-500/30 shadow-[0_0_30px_rgba(239,68,68,0.2)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="3" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    @endif

                    <h2 class="text-3xl font-black uppercase mb-2 tracking-tighter drop-shadow-md text-white">
                        {{ $isSuccess ? 'Berhasil' : 'Gagal' }}</h2>
                    <p class="text-blue-100/60 font-medium mb-8 text-sm uppercase tracking-widest">{{ $message }}
                    </p>

                    @if ($isSuccess && $lastAbsensi)
                        <div
                            class="grid grid-cols-2 gap-4 w-full bg-white/5 p-4 rounded-2xl mb-8 border border-white/10 shadow-inner backdrop-blur-sm">
                            <div class="text-left">
                                <div class="text-[10px] uppercase text-white/40 font-black tracking-widest">Waktu</div>
                                <div class="font-black text-white text-lg">
                                    {{ $lastAbsensi->jam_pulang ?? $lastAbsensi->jam_masuk }}</div>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] uppercase text-white/40 font-black tracking-widest">Tanggal
                                </div>
                                <div class="font-black text-white text-lg">
                                    {{ \Carbon\Carbon::parse($lastAbsensi->tanggal)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @endif

                    <button wire:click="resetForm"
                        class="btn bg-white/10 hover:bg-white/20 border-white/20 text-white btn-lg w-full uppercase font-black tracking-widest rounded-2xl transition-all">Kembali
                        Ke Awal</button>
                </div>
            </div>

            <style>
                .scale-animation {
                    animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                }

                @keyframes scaleUp {
                    from {
                        transform: scale(0.5);
                        opacity: 0;
                    }

                    to {
                        transform: scale(1);
                        opacity: 1;
                    }
                }
            </style>
        @endif
    </div>

    {{-- Footer Info --}}
    <div class="mt-12 text-[10px] text-blue-300 opacity-40 uppercase tracking-[0.2em] animate-pulse text-center">
        Sistem Absensi TRC &copy; {{ date('Y') }}
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            if (!Alpine.data('absensiVerification')) {
                Alpine.data('absensiVerification', () => ({
                    isLoadingModels: true,
                    isScanning: false,
                    isMatched: false,
                    gpsStatus: 'WAIT',
                    gpsMessage: 'Mencari...',
                    faceMessage: 'Siapkan AI...',
                    lat: null,
                    lng: null,
                    stream: null,
                    detector: null,
                    refDescriptor: null,
                    faceRecognitionActive: true,

                    async initVerification(refImageUrl, faceRecognitionActive) {
                        // Reset state for new attempt
                        this.faceRecognitionActive = faceRecognitionActive;
                        this.isMatched = false;
                        this.isScanning = false;
                        this.faceMessage = 'Menyiapkan...';
                        this.gpsStatus = 'WAIT';
                        this.refDescriptor = null;

                        this.$nextTick(async () => {
                            try {
                                await this.startCamera();

                                if (this.faceRecognitionActive) {
                                    await this.loadModels();
                                    if (refImageUrl) {
                                        await this.loadReference(refImageUrl);
                                    }
                                    this.startRecognitionLoop();
                                } else {
                                    this.isLoadingModels = false;
                                    this.isMatched = true;
                                    this.faceMessage = 'Scan Dilewati';
                                }

                                this.startGps();
                            } catch (e) {
                                console.error('Initialization Error:', e);
                                this.faceMessage = 'Gagal Akses';
                            }
                        });
                    },

                    async loadModels() {
                        const MODEL_URL = '/models';
                        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                        this.isLoadingModels = false;
                        this.isScanning = true;
                        this.faceMessage = 'Pindai Wajah...';
                    },

                    async startCamera() {
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: 'user'
                                }
                            });
                            this.$refs.video.srcObject = this.stream;
                        } catch (e) {
                            alert('Mohon izinkan akses kamera untuk melanjutkan.');
                            throw e;
                        }
                    },

                    async loadReference(url) {
                        try {
                            const img = await faceapi.fetchImage(url);
                            const detections = await faceapi.detectSingleFace(img, new faceapi
                                    .TinyFaceDetectorOptions()).withFaceLandmarks()
                                .withFaceDescriptor();
                            if (detections) {
                                this.refDescriptor = detections.descriptor;
                            }
                        } catch (e) {
                            console.error('Failed to load reference image:', e);
                        }
                    },

                    startGps() {
                        if (!navigator.geolocation) {
                            this.gpsStatus = 'ERROR';
                            this.gpsMessage = 'Tidak Support';
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.lat = pos.coords.latitude;
                                this.lng = pos.coords.longitude;
                                this.gpsStatus = 'OK';
                                this.gpsMessage = 'Terkunci';

                                @this.terimaCoordsLokasi(this.lat, this.lng);
                            },
                            (err) => {
                                this.gpsStatus = 'ERROR';
                                this.gpsMessage = 'Izin Ditolak';
                            }, {
                                enableHighAccuracy: true,
                                timeout: 5000,
                                maximumAge: 0
                            }
                        );
                    },

                    async startRecognitionLoop() {
                        const video = this.$refs.video;
                        const overlay = this.$refs.overlay;

                        const loop = async () => {
                            if (this.isMatched || !video) return;

                            // Ensure video is ready before processing
                            if (video.videoWidth === 0 || video.videoHeight === 0) {
                                requestAnimationFrame(loop);
                                return;
                            }

                            const displaySize = {
                                width: video.clientWidth,
                                height: video.clientHeight
                            };
                            faceapi.matchDimensions(overlay, displaySize);

                            const detections = await faceapi.detectAllFaces(video,
                                    new faceapi.TinyFaceDetectorOptions())
                                .withFaceLandmarks()
                                .withFaceDescriptors();

                            const resizedDetections = faceapi.resizeResults(detections,
                                displaySize);
                            overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay
                                .height);

                            if (resizedDetections.length > 0) {
                                if (this.refDescriptor) {
                                    const faceMatcher = new faceapi.FaceMatcher(this
                                        .refDescriptor, 0.6);
                                    const match = faceMatcher.findBestMatch(
                                        resizedDetections[0].descriptor);

                                    if (match.label !== 'unknown') {
                                        this.isMatched = true;
                                        this.faceMessage = 'Dikenali';
                                        return;
                                    }
                                } else {
                                    this.isMatched = true;
                                    this.faceMessage = 'Terdeteksi';
                                    return;
                                }
                            }

                            requestAnimationFrame(loop);
                        };
                        loop();
                    },

                    stopCamera() {
                        if (this.stream) {
                            this.stream.getTracks().forEach(track => track.stop());
                        }
                    },

                    async submit(type) {
                        const video = this.$refs.video;
                        if (!video) return;

                        // Sync server time right before submission
                        await this.$wire.fetchServerTime(true);

                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0);
                        const image = canvas.toDataURL('image/jpeg', 0.8);

                        this.$wire.call('submitAttendance', type, this.lat, this.lng, image);
                        this.stopCamera();
                    },

                    destroy() {
                        this.stopCamera();
                    }
                }));
            }
        });
    </script>
</div>
