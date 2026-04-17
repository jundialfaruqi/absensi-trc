<div class="min-h-screen bg-base-200 flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full">
        {{-- ─── Logo & Header ──────────────────────────────────────────────── --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-4 bg-primary text-primary-content rounded-2xl shadow-lg mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-base-content uppercase">Absensi Personnel</h1>
            <p class="text-base-content/60 font-medium">Satukan Barisan, Tertib Kehadiran</p>
        </div>

        {{-- ─── Step 1: Selection ────────────────────────────────────────── --}}
        @if ($step === 1)
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h2 class="card-title justify-center mb-4">Cari Nama Anda</h2>
                    <div class="relative mb-6">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik nama di sini..."
                            class="input input-bordered input-lg w-full pl-12 focus:border-primary shadow-inner" autofocus />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 absolute left-4 top-1/2 -translate-y-1/2 opacity-30">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        @foreach ($this->personnels() as $p)
                            <button wire:click="selectPersonnel({{ $p->id }})"
                                class="btn btn-outline btn-lg w-full justify-between h-auto py-4 group hover:bg-primary hover:text-primary-content transition-all border-base-300">
                                <div class="flex items-center gap-4">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary group-hover:bg-white/20 group-hover:text-white w-10 rounded-full">
                                            <span class="text-sm font-bold">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-bold uppercase text-xs">{{ $p->name }}</div>
                                        <div class="text-[10px] opacity-50 font-normal">{{ $p->penugasan?->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-4 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 transition-all">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
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
                        <div class="avatar placeholder mb-4">
                            <div class="bg-primary/10 text-primary w-20 rounded-full border-4 border-white shadow-md">
                                <span class="text-2xl font-black">{{ strtoupper(substr($selectedPersonnel->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold uppercase truncate px-4">{{ $selectedPersonnel->name }}</h2>
                        <p class="text-xs opacity-50">Masukkan PIN Keamanan</p>
                    </div>

                    <div class="flex justify-center gap-4 mb-8">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="w-12 h-16 rounded-xl border-2 {{ strlen($pin) > $i ? 'border-primary bg-primary/5' : 'border-base-300' }} flex items-center justify-center text-3xl font-bold transition-all shadow-sm">
                                {{ strlen($pin) > $i ? '●' : '' }}
                            </div>
                        @endfor
                    </div>

                    @error('pin')
                        <div class="alert alert-error my-4 py-2 px-4 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                            <button type="button" wire:click="appendPin({{ $num }})" class="btn btn-ghost bg-base-200 btn-lg h-16 text-xl font-bold hover:bg-primary hover:text-primary-content active:scale-95 transition-all">{{ $num }}</button>
                        @endforeach
                        <button type="button" wire:click="clearPin" class="btn btn-ghost bg-base-200 btn-lg h-16 text-error active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <button type="button" wire:click="appendPin(0)" class="btn btn-ghost bg-base-200 btn-lg h-16 text-xl font-bold active:scale-95 transition-all">0</button>
                        <button type="button" wire:click="resetForm" class="btn btn-ghost bg-base-200 btn-lg h-16">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                        </button>
                    </div>

                    <button wire:click="verifyPin" class="btn btn-primary btn-lg w-full text-lg font-black uppercase shadow-lg shadow-primary/20" @disabled(strlen($pin) < 4)>Lanjut</button>
                </div>
            </div>
        @endif

        {{-- ─── Step 3: Action Selection ──────────────────────────────────── --}}
        @if ($step === 3)
            <div class="card bg-base-100 shadow-xl border border-base-300 animate-in zoom-in-95">
                <div class="card-body">
                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold uppercase">{{ $selectedPersonnel->name }}</h2>
                        @if ($activeJadwal->shift)
                            <p class="text-xs opacity-50">{{ $activeJadwal->shift->name }} ({{ $activeJadwal->shift->start_time }} - {{ $activeJadwal->shift->end_time }})</p>
                        @else
                            <p class="text-xs font-black uppercase text-error tracking-widest bg-error/10 py-1 px-3 rounded-full inline-block mt-1">{{ $activeJadwal->status }}</p>
                        @endif
                        <div class="badge badge-outline badge-xs mt-2 opacity-40 uppercase tracking-tighter">Jadwal: {{ \Carbon\Carbon::parse($activeDate)->format('d M Y') }}</div>
                    </div>

                    @if ($activeJadwal->shift)
                        <div class="space-y-4">
                            {{-- Masuk Section --}}
                            <div class="p-4 rounded-2xl bg-base-200 border border-base-300 flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] font-black uppercase opacity-50">Status Masuk</div>
                                    <div class="font-bold {{ $activeAbsensi?->jam_masuk ? 'text-success' : 'text-base-content/30' }}">
                                        {{ $activeAbsensi?->jam_masuk ? 'Sudah Absen (' . $activeAbsensi->jam_masuk . ')' : 'Belum Absen' }}
                                    </div>
                                </div>
                                @if (!$activeAbsensi)
                                    <button wire:click="submitAttendance('in')" class="btn btn-primary btn-md shadow-md">Absen Masuk</button>
                                @else
                                    <div class="badge badge-success badge-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        OK
                                    </div>
                                @endif
                            </div>

                            {{-- Pulang Section --}}
                            <div class="p-4 rounded-2xl bg-base-200 border border-base-300 flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] font-black uppercase opacity-50">Status Pulang</div>
                                    <div class="font-bold {{ $activeAbsensi?->jam_pulang ? 'text-success' : 'text-base-content/30' }}">
                                        {{ $activeAbsensi?->jam_pulang ? 'Sudah Absen (' . $activeAbsensi->jam_pulang . ')' : 'Belum Absen' }}
                                    </div>
                                </div>
                                @if ($activeAbsensi && !$activeAbsensi->jam_pulang)
                                    <button wire:click="submitAttendance('out')" class="btn btn-secondary btn-md shadow-md">Absen Pulang</button>
                                @elseif ($activeAbsensi?->jam_pulang)
                                    <div class="badge badge-success badge-lg">OK</div>
                                @else
                                    <button class="btn btn-disabled btn-md">Absen Pulang</button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-error/5 border border-error/20 text-center animate-in zoom-in-95">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-error/50 mx-auto mb-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                            </svg>
                            <h3 class="font-bold text-error uppercase text-sm mb-1">Akses Absensi Ditutup</h3>
                            <p class="text-xs opacity-60">Status jadwal Anda saat ini adalah <span class="font-bold">{{ $activeJadwal->status }}</span>. Anda tidak diperkenankan melakukan absensi.</p>
                        </div>
                    @endif



                    <div class="mt-8">
                        <button wire:click="resetForm" class="btn btn-ghost btn-outline btn-block uppercase text-xs tracking-widest">Selesai / Sesi Berakhir</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── Step 4: Result ─────────────────────────────────────────── --}}
        @if ($step === 4)
            <div class="card bg-base-100 shadow-2xl border-t-8 {{ $isSuccess ? 'border-success' : 'border-error' }} animate-in zoom-in-95 duration-300">
                <div class="card-body items-center text-center py-10">
                    @if ($isSuccess)
                        <div class="w-24 h-24 bg-success/10 text-success rounded-full flex items-center justify-center mb-6 scale-animation">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    @else
                        <div class="w-24 h-24 bg-error/10 text-error rounded-full flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    @endif

                    <h2 class="text-3xl font-black uppercase mb-2">{{ $isSuccess ? 'Berhasil' : 'Gagal' }}</h2>
                    <p class="text-base-content/60 font-medium mb-8">{{ $message }}</p>

                    @if ($isSuccess && $lastAbsensi)
                        <div class="grid grid-cols-2 gap-4 w-full bg-base-200 p-4 rounded-2xl mb-8 border border-base-300 shadow-inner">
                            <div class="text-left">
                                <div class="text-[10px] uppercase opacity-50 font-bold">Waktu</div>
                                <div class="font-bold">{{ $lastAbsensi->jam_masuk ?? $lastAbsensi->jam_pulang }}</div>
                            </div>
                            <div class="text-left">
                                <div class="text-[10px] uppercase opacity-50 font-bold">Tanggal</div>
                                <div class="font-bold">{{ \Carbon\Carbon::parse($lastAbsensi->tanggal)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @endif

                    <button wire:click="resetForm" class="btn btn-outline btn-lg w-full uppercase font-bold tracking-widest">Kembali Ke Awal</button>
                </div>
            </div>

            <style>
                .scale-animation {
                    animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                }
                @keyframes scaleUp {
                    from { transform: scale(0.5); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
            </style>
        @endif
    </div>
    
    {{-- Footer Info --}}
    <p class="mt-12 text-[10px] text-base-content/40 uppercase tracking-[0.2em] animate-pulse">
        Sistem Absensi TRC &copy; {{ date('Y') }}
    </p>
</div>