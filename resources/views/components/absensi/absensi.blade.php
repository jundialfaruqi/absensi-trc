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
            <svg class="absolute top-[30%] right-[-5%] w-100 h-100 text-blue-400 opacity-60"
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
                /* Animation Disabled */
            }

            .animate-float-slow {
                /* Animation Disabled */
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
        <div class="absolute top-1/4 right-10 w-2 h-2 bg-red-500 rounded-full blur-xs"></div>
        <div class="absolute bottom-1/3 left-10 w-1.5 h-1.5 bg-red-400 rounded-full blur-xs">
        </div>

        {{-- Light Streaks --}}
        <div class="absolute top-[10%] left-[20%] w-[60%] h-[20%] bg-blue-500/10 blur-[100px] rounded-full"></div>
    </div>

    <div class="max-w-md w-full relative z-10">
        {{-- ─── Logo & Header ──────────────────────────────────────────────── --}}
        <div class="text-center mb-8">

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
                            Network Local Time
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── Main Content Area ────────────────────────── --}}
        @if (!$isTimeSynced)
            {{-- Time Sync Error --}}
            <div class="card bg-base-100 shadow-xl border border-error/20 overflow-hidden animate-in fade-in zoom-in-95">
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
                        waktu jaringan yang akurat. Akses absensi ditutup untuk mencegah manipulasi data.</p>
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
                            <span class="text-[10px] font-bold opacity-70 uppercase">Periksa Koneksi Internet Server</span>
                        </div>
                        <button onclick="window.location.reload()"
                            class="btn btn-outline btn-block rounded-xl font-bold uppercase tracking-widest text-[10px]">Coba Lagi</button>
                    </div>
                </div>
            </div>

        @elseif ($step === 4)
            {{-- Portal Closed --}}
            <div class="card bg-slate-900 border border-error/20 shadow-2xl animate-in zoom-in-95">
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
                        class="btn bg-white/10 hover:bg-white/20 border-white/20 text-white btn-block rounded-2xl font-black uppercase tracking-widest">Kembali ke Beranda</a>
                </div>
            </div>

        @else
            {{-- Attendance Steps (1, 2, 3) --}}
            {{-- ─── Step 1: PIN Identification ─────────────────────────────────── --}}
            @if ($step === 1)
                <div
                    class="card bg-slate-900 border border-white/10 shadow-2xl animate-in fade-in slide-in-from-bottom-4">
                    <div class="card-body">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-blue-500/20 text-blue-200 rounded-full border-4 border-white/10 shadow-xl overflow-hidden mx-auto flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold uppercase text-white drop-shadow-sm">Identitas Personel</h2>
                            <p class="text-xs text-blue-200/50 uppercase tracking-widest font-bold">Masukkan 6 Digit PIN Anda</p>
                        </div>

                        <div class="flex justify-center gap-2 sm:gap-3 mb-8">
                            @for ($i = 0; $i < 6; $i++)
                                <div
                                    class="w-10 h-14 sm:w-12 sm:h-16 rounded-xl border-2 {{ strlen($pin) > $i ? 'border-primary bg-primary/20 shadow-[0_0_15px_rgba(var(--color-primary),0.3)]' : 'border-white/10 bg-white/5' }} flex items-center justify-center text-2xl sm:text-3xl font-bold transition-all text-white">
                                    {{ strlen($pin) > $i ? '●' : '' }}
                                </div>
                            @endfor
                        </div>

                        @error('pin')
                            <div class="alert alert-error mb-6 py-2 px-4 shadow-sm rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5"
                                    fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-[10px] uppercase font-bold tracking-tight">{{ $message }}</span>
                            </div>
                        @enderror

                        <div class="grid grid-cols-3 gap-3 mb-4">
                            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                                <button type="button" wire:click="appendPin({{ $num }})"
                                    class="btn btn-ghost bg-white/5 border border-white/5 h-14 sm:h-16 text-xl font-black text-white hover:bg-white/10 hover:border-white/20 active:scale-95 transition-all rounded-2xl">{{ $num }}</button>
                            @endforeach
                            <button type="button" wire:click="clearPin"
                                class="btn btn-ghost bg-white/5 border border-white/5 h-14 sm:h-16 text-red-400 hover:text-red-500 hover:bg-red-500/10 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <button type="button" wire:click="appendPin(0)"
                                class="btn btn-ghost bg-white/5 border border-white/5 h-14 sm:h-16 text-xl font-black text-white hover:bg-white/10 rounded-2xl">0</button>
                            <div class="flex items-center justify-center">
                                <div wire:loading wire:target="verifyPin" class="loading loading-spinner text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ─── Step 2: Biometric & Location Verification ──────────────────────── --}}
            @if ($step === 2)
                <div wire:key="step-2-verification-{{ $selectedPersonnel->id }}" x-data="absensiVerification()"
                    x-init="initVerification('{{ $selectedPersonnel->face_descriptor }}', {{ $selectedPersonnel->face_recognition ? 'true' : 'false' }})"
                    class="card bg-slate-900 border border-white/10 shadow-2xl animate-in zoom-in-95">
                    <div class="card-body p-4 sm:p-6 text-white text-center">
                        
                        {{-- Camera & Detection View --}}
                        <div
                            class="relative aspect-square w-full max-w-70 mx-auto rounded-3xl overflow-hidden bg-black shadow-2xl border-4 border-white/10 group mb-6">
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
                                        Wajah Terverifikasi
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Personnel Info Display (Below Camera) --}}
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex items-center gap-4 text-left mb-6 animate-in slide-in-from-bottom-2">
                            <div class="avatar">
                                <div class="w-14 rounded-xl border border-white/20 shadow-lg overflow-hidden">
                                    @if ($selectedPersonnel->foto)
                                        <img src="{{ asset('storage/' . $selectedPersonnel->foto) }}" class="object-cover" />
                                    @else
                                        <div class="bg-blue-500/20 w-full h-full flex items-center justify-center font-bold text-blue-300">
                                            {{ strtoupper(substr($selectedPersonnel->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <h3 class="font-black uppercase text-sm truncate leading-tight">{{ $selectedPersonnel->name }}</h3>
                                <p class="text-[10px] font-bold text-blue-300/60 uppercase tracking-widest truncate">{{ $selectedPersonnel->opd?->name ?? 'N/A' }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if ($activeJadwal->shift)
                                        <span class="badge badge-primary badge-xs py-2 px-2 uppercase font-black text-[8px]">{{ $activeJadwal->shift->name }}</span>
                                    @else
                                        <span class="badge badge-error badge-xs py-2 px-2 uppercase font-black text-[8px]">{{ $activeJadwal->status }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Status Indicators --}}
                        <div class="grid grid-cols-2 gap-2">
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
                                <div class="text-left">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.191 6.062h.008v.008h-.008V6.062ZM18 8.812h.008v.008H18V8.812ZM15.191 11.562h.008v.008h-.008v-.008ZM18 14.312h.008v.008H18v-.008ZM21 17.062h.008v.008H21v-.008ZM12.181 8.68c.341-1.107 1.467-1.875 2.65-1.875 1.157 0 2.1 1.607 1.969 2.45-.12.772-.646 1.393-1.307 1.816-.656.422-1.202.911-1.377 1.513l-.111.452m-1.146-5.303-.01.013m2.706 3.103c.19.116.446.126.646.017a1.322 1.322 0 0 0 .512-.575 1.31 1.31 0 0 0 .08-.611c-.027-.317-.184-.504-.39-.554a.705.705 0 0 0-.662.131.6.6 0 0 0-.186.418.604.604 0 0 0 0 .15l.019.014c.032.022.03.024.03.024a.5.5 0 0 1-.038-.027ZM11.182 18H9.122a2 2 0 0 1-1.928-1.464l-1.071-3.75a2 2 0 0 1 .728-2.22l3.07-2.1c.176-.121.377-.183.583-.183h.004c.158 0 .313.036.452.106l1.652.825" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-[8px] font-black uppercase opacity-40 leading-none mb-0.5">Biometrik
                                    </div>
                                    <div class="text-[10px] font-bold" x-text="faceMessage">Memindai...</div>
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
                                        class="alert py-2 px-3 border-none bg-green-500/10 text-green-400 text-[10px] font-bold uppercase tracking-tight border border-green-500/20 shadow-[0_0_10px_rgba(34,197,94,0.1)]">
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
                                        class="alert py-2 px-3 border-none bg-amber-500/10 text-amber-400 text-[10px] font-bold uppercase tracking-tight border border-amber-500/20 shadow-[0_0_10px_rgba(245,158,11,0.1)]">
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
                                        class="alert py-2 px-3 border-none bg-red-500/10 text-red-400 text-[10px] font-bold uppercase tracking-tight leading-tight border border-red-500/20 shadow-[0_0_10px_rgba(239,68,68,0.1)]">
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
                        <div class="space-y-3 mt-4">
                            @if ((!$activeAbsensi || !$activeAbsensi->jam_masuk) && !$isTooLateToIn)
                                {{-- Mode: Normal Masuk --}}
                                <button type="button" x-on:click="submit('in')" wire:loading.attr="disabled"
                                    :disabled="!isMatched || gpsStatus !== 'OK' || {{ json_encode(!empty($infoLokasi) && $infoLokasi['boleh'] === false) }}"
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
                                            class="text-[10px] opacity-50 font-medium hidden group-disabled:block uppercase tracking-tighter text-center">Verifikasi
                                            Identitas & Lokasi...</span>
                                    </div>
                                </button>
                            @else
                                {{-- Mode: Pulang --}}
                                @if ((!$activeAbsensi || !$activeAbsensi->jam_masuk) && $isTooLateToIn)
                                    <div
                                        class="alert py-2 px-3 mb-3 border-none bg-red-500/10 text-red-400 text-[10px] font-black uppercase tracking-widest text-center border border-red-500/20">
                                        ⚠️ Batas waktu Masuk berakhir. Silakan Absen Pulang.
                                    </div>
                                @endif

                                <button type="button" x-on:click="submit('out')" wire:loading.attr="disabled"
                                    :disabled="{{ $activeAbsensi && $activeAbsensi->jam_pulang ? 'true' : 'false' }} || !isMatched || gpsStatus !== 'OK' || {{ json_encode(!empty($infoLokasi) && $infoLokasi['boleh'] === false) }}"
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
                                            class="text-[10px] opacity-50 font-medium hidden group-disabled:block uppercase tracking-tighter text-center">Verifikasi
                                            Identitas & Lokasi...</span>
                                    </div>
                                </button>
                            @endif
                        </div>

                        <div class="mt-4">
                            <button wire:click="resetForm" x-on:click="stopCamera()"
                                class="btn btn-ghost btn-xs btn-block text-white/30 font-bold uppercase tracking-widest rounded-xl hover:bg-transparent hover:text-white/30 hover:shadow-none hover:border-none">Bukan Saya? Reset</button>
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
                </div>
            @endif

            {{-- ─── Step 3: Result ─────────────────────────────────────────── --}}
            @if ($step === 3)
                <div
                    class="card bg-slate-900 border border-white/10 shadow-2xl animate-in zoom-in-95 duration-300">
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
                                class="grid grid-cols-2 gap-4 w-full bg-white/5 p-4 rounded-2xl mb-8 border border-white/10 shadow-inner">
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
        @endif
    </div>


    <script>
        (function() {
            const initAbsensiVerification = () => {
                if (window.Alpine && !Alpine.data('absensiVerification')) {
                    Alpine.data('absensiVerification', () => ({
                        isLoadingModels: true,
                        isScanning: false,
                        isMatched: false,
                        faceMessage: 'Memuat AI...',
                        gpsStatus: 'WAIT',
                        gpsMessage: 'Mencari...',
                        refDescriptor: null,
                        isFaceRecognitionEnabled: true,
                        stream: null,

                        async initVerification(faceDescriptorJson, faceRecognitionEnabled) {
                            this.isFaceRecognitionEnabled = faceRecognitionEnabled;
                            try {
                                console.log("Initializing verification...");
                                
                                // Parse face descriptor from DB
                                if (faceDescriptorJson) {
                                    try {
                                        const parsed = JSON.parse(faceDescriptorJson);
                                        this.refDescriptor = new Float32Array(parsed);
                                    } catch (err) {
                                        console.error("Error parsing face descriptor:", err);
                                    }
                                }

                                await this.loadModels();
                                this.isLoadingModels = false;
                                
                                await this.startCamera();
                                this.startGps();
                                this.startFaceDetection();
                            } catch (e) {
                                console.error("Initialization Error:", e);
                                this.faceMessage = 'Gagal Akses';
                            }
                        },

                        async loadModels() {
                            const MODEL_URL = '/models';
                            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                        },

                        async startCamera() {
                            try {
                                this.stream = await navigator.mediaDevices.getUserMedia({
                                    video: {
                                        facingMode: 'user',
                                        width: { ideal: 640 },
                                        height: { ideal: 640 }
                                    }
                                });
                                this.$refs.video.srcObject = this.stream;
                            } catch (err) {
                                console.error("Camera Error:", err);
                                this.faceMessage = 'Kamera tidak diizinkan';
                                throw err;
                            }
                        },

                        stopCamera() {
                            if (this.stream) {
                                this.stream.getTracks().forEach(track => track.stop());
                                this.stream = null;
                            }
                        },

                        startGps() {
                            if (!navigator.geolocation) {
                                this.gpsStatus = 'ERROR';
                                this.gpsMessage = 'GPS tidak didukung';
                                return;
                            }

                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    this.gpsStatus = 'OK';
                                    this.gpsMessage = 'Terkunci';
                                    @this.terimaCoordsLokasi(pos.coords.latitude, pos.coords.longitude);
                                },
                                (err) => {
                                    console.error("GPS Error:", err);
                                    this.gpsStatus = 'ERROR';
                                    this.gpsMessage = 'GPS Error';
                                }, {
                                    enableHighAccuracy: true,
                                    timeout: 10000
                                }
                            );
                        },

                        async startFaceDetection() {
                            this.isScanning = true;
                            this.faceMessage = 'Memindai...';

                            const detect = async () => {
                                if (this.isMatched || !this.isScanning || !this.stream) return;

                                const video = this.$refs.video;
                                const canvas = this.$refs.overlay;

                                if (!video || video.paused || video.ended) {
                                    requestAnimationFrame(detect);
                                    return;
                                }

                                try {
                                    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                                        .withFaceLandmarks()
                                        .withFaceDescriptor();

                                    if (detection) {
                                        // Visual feedback (optional)
                                        const displaySize = { width: video.videoWidth, height: video.videoHeight };
                                        faceapi.matchDimensions(canvas, displaySize);
                                        
                                        if (!this.isFaceRecognitionEnabled) {
                                            this.isMatched = true;
                                            this.faceMessage = 'AI Siap (Identitas Terkonfirmasi)';
                                            this.isScanning = false;
                                            return;
                                        }

                                        if (this.refDescriptor) {
                                            const distance = faceapi.euclideanDistance(detection.descriptor, this.refDescriptor);
                                            // Threshold 0.5 is strict enough
                                            if (distance < 0.5) {
                                                this.isMatched = true;
                                                this.faceMessage = 'Wajah Cocok';
                                                this.isScanning = false;
                                                return;
                                            } else {
                                                this.faceMessage = 'Wajah Tidak Cocok';
                                            }
                                        } else {
                                            // Safety fallback if no descriptor stored
                                            this.isMatched = true;
                                            this.faceMessage = 'Identitas Siap';
                                            this.isScanning = false;
                                            return;
                                        }
                                    } else {
                                        this.faceMessage = 'Wajah tidak terlihat';
                                    }
                                } catch (err) {
                                    console.error("Detection Loop Error:", err);
                                }

                                requestAnimationFrame(detect);
                            };

                            detect();
                        },

                        async submit(type) {
                            if (!this.isMatched) return;

                            const video = this.$refs.video;
                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            canvas.getContext('2d').drawImage(video, 0, 0);
                            const imageData = canvas.toDataURL('image/jpeg', 0.8);

                            // Call Livewire
                            @this.submitAttendance(type, null, null, imageData);
                        }
                    }));
                }
            };

            if (window.Alpine) {
                initAbsensiVerification();
            } else {
                document.addEventListener('alpine:init', initAbsensiVerification);
            }
        })();
    </script>
</div>
