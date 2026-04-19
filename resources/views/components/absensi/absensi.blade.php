<div class="min-h-screen bg-base-200 flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full">
        {{-- ─── Logo & Header ──────────────────────────────────────────────── --}}
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center p-4 bg-primary text-primary-content rounded-2xl shadow-lg mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-10">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-base-content uppercase">Absensi Personnel</h1>
            <p class="text-base-content/60 font-medium">Satukan Barisan, Tertib Kehadiran</p>
            
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
            }" x-init="update(); setInterval(() => update(), 1000)">
                <div wire:ignore class="flex flex-col items-center">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 mb-1" x-text="date">Memuat Tanggal...</div>
                    <div class="text-4xl font-black tracking-widest text-primary tabular-nums" x-text="time">00:00:00</div>
                </div>
                
                <div class="flex items-center gap-1.5 mt-1">
                    @if($apiSource !== 'local')
                        <div class="flex items-center gap-1 text-[8px] font-black uppercase tracking-widest text-success bg-success/10 px-2 py-0.5 rounded-full border border-success/20">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-success"></span>
                            </span>
                            Network Time Synced ({{ $apiSource }})
                        </div>
                    @else
                        <div class="flex items-center gap-1 text-[8px] font-black uppercase tracking-widest text-warning bg-warning/10 px-2 py-0.5 rounded-full border border-warning/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 12 24c4.408 0 8.411-2.384 10.606-6.044M11.277 5.889A11.959 11.959 0 0 1 12 2.25c4.408 0 8.411 2.384 10.606 6.044" />
                            </svg>
                            Local Server Time
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── Step 1: Selection & Time Sync Check ────────────────────────── --}}
        @if (!$isTimeSynced)
            <div class="card bg-base-100 shadow-xl border border-error/20 overflow-hidden animate-in fade-in zoom-in-95">
                <div class="bg-error/10 p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-error text-error-content rounded-full flex items-center justify-center mb-4 shadow-lg shadow-error/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-error uppercase tracking-tight">Sinkronisasi Gagal</h2>
                    <p class="text-xs font-medium text-base-content/60 mt-2 px-4">Sistem tidak dapat memverifikasi waktu jaringan yang akurat. Akses absensi ditutup untuk mencegah manipulasi data.</p>
                </div>
                <div class="card-body p-6 bg-base-50">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-base-100 rounded-xl border border-base-200">
                            <div class="p-2 bg-base-200 rounded-lg text-base-content/40">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold opacity-70 uppercase">Periksa Koneksi Internet Server</span>
                        </div>
                        <button onclick="window.location.reload()" class="btn btn-outline btn-block rounded-xl font-bold uppercase tracking-widest text-[10px]">Coba Lagi</button>
                    </div>
                </div>
            </div>
        @elseif ($step === 1)
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title justify-center mb-4">Cari Nama Anda</h2>
                    <div class="relative mb-6">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Ketik nama di sini..."
                            class="input input-bordered input-lg w-full pl-12 focus:border-primary shadow-inner"
                            autofocus />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-6 absolute left-4 top-1/2 -translate-y-1/2 opacity-30">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        @foreach ($this->personnels() as $p)
                            <button wire:click="selectPersonnel({{ $p->id }})"
                                class="btn btn-outline btn-lg w-full justify-between h-auto py-4 group hover:bg-primary hover:text-primary-content transition-all border-base-300">
                                <div class="flex items-center gap-4">
                                    <div class="avatar {{ !$p->foto ? 'placeholder' : '' }}">
                                        <div class="bg-primary/10 text-primary group-hover:bg-white/20 group-hover:text-white w-10 rounded-full overflow-hidden">
                                            @if ($p->foto)
                                                <img src="{{ asset('storage/' . $p->foto) }}" class="object-cover" />
                                            @else
                                                <span class="text-sm font-bold">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
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
                </div>
            </div>
        @endif

        {{-- ─── Step 2: PIN Input ─────────────────────────────────────────── --}}
        @if ($step === 2)
            <div class="card bg-base-100 shadow-xl border border-base-300 animate-in fade-in slide-in-from-bottom-4">
                <div class="card-body">
                    <div class="text-center mb-6">
                        <div class="avatar mb-4 {{ !$selectedPersonnel->foto ? 'placeholder' : '' }}">
                            <div class="bg-primary/10 text-primary w-20 rounded-full border-4 border-white shadow-md overflow-hidden">
                                @if ($selectedPersonnel->foto)
                                    <img src="{{ asset('storage/' . $selectedPersonnel->foto) }}" class="object-cover" alt="Foto Personnel" />
                                @else
                                    <span class="text-2xl font-black">{{ strtoupper(substr($selectedPersonnel->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        </div>
                        <h2 class="text-xl font-bold uppercase truncate px-4">{{ $selectedPersonnel->name }}</h2>
                        <p class="text-xs opacity-50">Masukkan PIN Keamanan</p>
                    </div>

                    <div class="flex justify-center gap-4 mb-8">
                        @for ($i = 0; $i < 4; $i++)
                            <div
                                class="w-12 h-16 rounded-xl border-2 {{ strlen($pin) > $i ? 'border-primary bg-primary/5' : 'border-base-300' }} flex items-center justify-center text-3xl font-bold transition-all shadow-sm">
                                {{ strlen($pin) > $i ? '●' : '' }}
                            </div>
                        @endfor
                    </div>

                    @error('pin')
                        <div class="alert alert-error my-4 py-2 px-4 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                            <button type="button" wire:click="appendPin({{ $num }})"
                                class="btn btn-ghost bg-base-200 btn-lg h-16 text-xl font-bold hover:bg-primary hover:text-primary-content active:scale-95 transition-all">{{ $num }}</button>
                        @endforeach
                        <button type="button" wire:click="clearPin"
                            class="btn btn-ghost bg-base-200 btn-lg h-16 text-error active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button type="button" wire:click="appendPin(0)"
                            class="btn btn-ghost bg-base-200 btn-lg h-16 text-xl font-bold active:scale-95 transition-all">0</button>
                        <button type="button" wire:click="resetForm" class="btn btn-ghost bg-base-200 btn-lg h-16">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="size-6">
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
                x-init="initVerification('{{ $selectedPersonnel->foto ? asset('storage/' . $selectedPersonnel->foto) : '' }}')"
                class="card bg-base-100 shadow-xl border border-base-300 animate-in zoom-in-95">
                <div class="card-body p-4 sm:p-6">
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
                        class="relative aspect-square w-full max-w-70 mx-auto rounded-3xl overflow-hidden bg-black shadow-2xl border-4 border-base-200 group">
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
                                <div class="text-[10px] font-black uppercase tracking-widest">Wajah Terverifikasi</div>
                            </div>
                        </template>
                    </div>

                    {{-- Status Indicators --}}
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <div :class="gpsStatus === 'OK' ? 'bg-success/5 border-success/20' : 'bg-base-200 border-base-300'"
                            class="p-2 rounded-xl border flex items-center gap-2 transition-all">
                            <div :class="gpsStatus === 'OK' ? 'text-success' : 'text-base-content/30'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[8px] font-black uppercase opacity-40 leading-none mb-0.5">Lokasi
                                </div>
                                <div class="text-[10px] font-bold" x-text="gpsMessage">Mencari...</div>
                            </div>
                        </div>

                        <div :class="isMatched ? 'bg-success/5 border-success/20' : 'bg-base-200 border-base-300'"
                            class="p-2 rounded-xl border flex items-center gap-2 transition-all">
                            <div :class="isMatched ? 'text-success' : 'text-base-content/30'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.191 6.062h.008v.008h-.008V6.062ZM18 8.812h.008v.008H18V8.812ZM15.191 11.562h.008v.008h-.008v-.008ZM18 14.312h.008v.008H18v-.008ZM21 17.062h.008v.008H21v-.008ZM12.181 8.68c.341-1.107 1.467-1.875 2.65-1.875 1.157 0 2.1 1.607 1.969 2.45-.12.772-.646 1.393-1.307 1.816-.656.422-1.202.911-1.377 1.513l-.111.452m-1.146-5.303-.01.013m2.706 3.103c.19.116.446.126.646.017a1.322 1.322 0 0 0 .512-.575 1.31 1.31 0 0 0 .08-.611c-.027-.317-.184-.504-.39-.554a.705.705 0 0 0-.662.131.6.6 0 0 0-.186.418.604.604 0 0 0 0 .15l.019.014c.032.022.03.024.03.024a.5.5 0 0 1-.038-.027ZM11.182 18H9.122a2 2 0 0 1-1.928-1.464l-1.071-3.75a2 2 0 0 1 .728-2.22l3.07-2.1c.176-.121.377-.183.583-.183h.004c.158 0 .313.036.452.106l1.652.825" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[8px] font-black uppercase opacity-40 leading-none mb-0.5">Biometrik
                                </div>
                                <div class="text-[10px] font-bold" x-text="faceMessage">Memindai...</div>
                            </div>
                        </div>
                    </div>

                    {{-- Info Lokasi Kantor --}}
                    @if(!empty($infoLokasi))
                        <div class="mt-4 animate-in fade-in slide-in-from-top-2">
                            @if(is_null($infoLokasi['is_within_radius']))
                                {{-- Tidak ada kantor terhubung, tidak perlu tampilkan info --}}
                            @elseif($infoLokasi['boleh'] && $infoLokasi['is_within_radius'])
                                <div class="alert alert-success py-2 px-3 border-none bg-success/10 text-success text-[10px] font-bold uppercase tracking-tight">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Dalam radius {{ $infoLokasi['kantor_name'] }} (±{{ $infoLokasi['jarak_meter'] }}m)</span>
                                </div>
                            @elseif($infoLokasi['boleh'] && !$infoLokasi['is_within_radius'])
                                <div class="alert alert-warning py-2 px-3 border-none bg-warning/10 text-warning text-[10px] font-bold uppercase tracking-tight">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Luar radius {{ $infoLokasi['kantor_name'] }} ({{ $infoLokasi['jarak_meter'] }}m). Absensi diperbolehkan.</span>
                                </div>
                            @else
                                <div class="alert alert-error py-2 px-3 border-none bg-error/10 text-error text-[10px] font-bold uppercase tracking-tight leading-tight">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>{{ $infoLokasi['pesan'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    @if ($activeJadwal->shift)
                        <div class="space-y-3 mt-6">
                            @if (!$activeAbsensi)
                                <button type="button" x-on:click="submit('in')"
                                    :disabled="!isMatched || gpsStatus !== 'OK' || @js(!empty($infoLokasi) && $infoLokasi['boleh'] === false)"
                                    class="btn btn-primary btn-lg w-full shadow-lg shadow-primary/20 flex flex-col items-center py-2 h-auto group">
                                    <span class="text-sm font-black">ABSEN MASUK</span>
                                    <span class="text-[10px] opacity-60 font-medium group-disabled:hidden">SIAP KIRIM
                                        DATA</span>
                                    <span
                                        class="text-[10px] opacity-60 font-medium hidden group-disabled:block uppercase tracking-tighter">Verifikasi
                                        Identitas & Lokasi...</span>
                                </button>
                            @else
                                <button type="button" x-on:click="submit('out')"
                                    :disabled="{{ $activeAbsensi && $activeAbsensi->jam_pulang ? 'true' : 'false' }} || !isMatched || gpsStatus !== 'OK' || @js(!empty($infoLokasi) && $infoLokasi['boleh'] === false)"
                                    class="btn btn-secondary btn-lg w-full shadow-lg shadow-secondary/20 flex flex-col items-center py-2 h-auto group">
                                    <span class="text-sm font-black uppercase">Absen Pulang</span>
                                    <span
                                        class="text-[10px] opacity-60 font-medium group-disabled:hidden uppercase tracking-tighter">Selesaikan
                                        Kerja Hari Ini</span>
                                    <span
                                        class="text-[10px] opacity-60 font-medium hidden group-disabled:block uppercase tracking-tighter">Verifikasi
                                        Identitas & Lokasi...</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-error/5 border border-error/20 text-center mt-6">
                            <h3 class="font-bold text-error uppercase text-xs mb-1">Akses Absensi Ditutup</h3>
                            <p class="text-[10px] opacity-60 uppercase font-bold tracking-tighter">Status:
                                {{ $activeJadwal->status }}</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button wire:click="resetForm" x-on:click="stopCamera()"
                            class="btn btn-ghost btn-xs btn-block opacity-40 font-bold uppercase tracking-widest hover:bg-transparent">Kembali</button>
                    </div>
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
                class="card bg-base-100 shadow-2xl border-t-8 {{ $isSuccess ? 'border-success' : 'border-error' }} animate-in zoom-in-95 duration-300">
                <div class="card-body items-center text-center py-10">
                    @if ($isSuccess)
                        <div
                            class="w-24 h-24 bg-success/10 text-success rounded-full flex items-center justify-center mb-6 scale-animation">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="3" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    @else
                        <div
                            class="w-24 h-24 bg-error/10 text-error rounded-full flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="3" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    @endif

                    <h2 class="text-3xl font-black uppercase mb-2">{{ $isSuccess ? 'Berhasil' : 'Gagal' }}</h2>
                    <p class="text-base-content/60 font-medium mb-8">{{ $message }}</p>

                    @if ($isSuccess && $lastAbsensi)
                        <div
                            class="grid grid-cols-2 gap-4 w-full bg-base-200 p-4 rounded-2xl mb-8 border border-base-300 shadow-inner">
                            <div class="text-left">
                                <div class="text-[10px] uppercase opacity-50 font-bold">Waktu</div>
                                <div class="font-bold">{{ $lastAbsensi->jam_pulang ?? $lastAbsensi->jam_masuk }}</div>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] uppercase opacity-50 font-bold">Tanggal</div>
                                <div class="font-bold">
                                    {{ \Carbon\Carbon::parse($lastAbsensi->tanggal)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @endif

                    <button wire:click="resetForm"
                        class="btn btn-outline btn-lg w-full uppercase font-bold tracking-widest">Kembali Ke
                        Awal</button>
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
    <div class="mt-12 text-[10px] text-base-content/40 uppercase tracking-[0.2em] animate-pulse text-center">
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

                    async initVerification(refImageUrl) {
                        // Reset state for new attempt
                        this.isMatched = false;
                        this.isScanning = false;
                        this.faceMessage = 'Menyiapkan...';
                        this.gpsStatus = 'WAIT';
                        this.refDescriptor = null;

                        this.$nextTick(async () => {
                            try {
                                await this.loadModels();
                                await this.startCamera();
                                if (refImageUrl) {
                                    await this.loadReference(refImageUrl);
                                }
                                this.startGps();
                                this.startRecognitionLoop();
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
