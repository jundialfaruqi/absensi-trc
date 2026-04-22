<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-base-content">Generate Jadwal Otomatis</h1>
            <p class="text-sm text-base-content/60 mt-1">Sistem rotasi cerdas untuk efisiensi penjadwalan personnel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li><a href="{{ route('jadwal') }}">Jadwal</a></li>
                <li class="font-bold text-base-content">Generate</li>
            </ul>
        </div>
    </div>

    {{-- ─── Step Indicator ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-center mb-12">
        <ul class="steps steps-horizontal w-full max-w-2xl">
            @if(Auth::user()->hasRole('super-admin'))
                <li class="step {{ $step >= 1 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Pilih OPD</li>
            @endif
            <li class="step {{ $step >= 2 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Personel</li>
            <li class="step {{ $step >= 3 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Siklus Shift</li>
            <li class="step {{ $step >= 4 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Tanggal & Eksekusi</li>
        </ul>
    </div>

    {{-- ─── Main Content ────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-2xl border border-base-200 overflow-hidden">
        <div class="card-body p-8">

            {{-- STEP 1: OPD SELECTION --}}
            @if ($step === 1)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">1</span>
                        Pilih Organisasi Perangkat Daerah (OPD)
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($this->opds as $opd)
                            <label class="flex items-start gap-4 p-4 border rounded-2xl cursor-pointer transition-all hover:bg-base-100 {{ $selectedOpdId == $opd->id ? 'border-primary bg-primary/5' : 'border-base-200 bg-base-50' }}">
                                <div class="mt-1">
                                    <input type="radio" wire:model="selectedOpdId" value="{{ $opd->id }}" class="radio radio-primary radio-sm">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm uppercase leading-tight break-words">{{ $opd->name }}</div>
                                    <div class="text-[10px] opacity-60 mt-1 line-clamp-2">{{ $opd->alamat ?? 'Alamat belum diset' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- STEP 2: PERSONNEL SELECTION --}}
            @if ($step === 2)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">2</span>
                        Pilih Personel
                    </h2>

                    <div class="flex justify-end mb-4">
                        <label class="label cursor-pointer gap-2 bg-base-200/50 px-4 py-1.5 rounded-xl border border-base-200">
                            <span class="text-[10px] font-bold uppercase opacity-60">Pilih Semua</span>
                            <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="checkbox checkbox-primary checkbox-xs">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-2">
                        @forelse ($this->personnels as $p)
                            <label class="label cursor-pointer flex items-center justify-start gap-4 p-3 border rounded-xl transition-all hover:bg-base-200 {{ in_array($p->id, $selectedPersonnelIds) ? 'border-primary bg-primary/5' : 'border-base-200' }}">
                                <input type="checkbox" wire:model="selectedPersonnelIds" value="{{ $p->id }}" class="checkbox checkbox-primary checkbox-sm">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                        @if($p->foto)
                                            <img src="{{ asset('storage/'.$p->foto) }}" />
                                        @else
                                            <span class="text-xs">{{ substr($p->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold text-xs uppercase">{{ $p->name }}</div>
                                    <div class="text-[9px] opacity-60 italic">{{ $p->penugasan?->name ?? 'Belum ada penugasan' }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-2 text-center py-10 opacity-40">
                                <p>Tidak ada personel ditemukan di OPD ini.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4 text-[10px] font-bold text-primary uppercase">
                        {{ count($selectedPersonnelIds) }} Personel terpilih
                    </div>
                </div>
            @endif

            {{-- STEP 3: SHIFT SEQUENCE --}}
            @if ($step === 3)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">3</span>
                        Konfigurasi Siklus Shift
                    </h2>
                    
                    <div class="space-y-3 mb-6">
                        @foreach ($shiftSequence as $index => $seq)
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 p-4 bg-base-200/40 rounded-2xl border border-base-200 relative animate-in zoom-in-95">
                                <div class="form-control flex flex-col w-full sm:w-36">
                                    <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase opacity-60">Tipe</span></label>
                                    <select wire:model.live="shiftSequence.{{ $index }}.type" class="select select-bordered select-sm font-bold rounded-lg h-9 min-h-0">
                                        <option value="SHIFT">KERJA (SHIFT)</option>
                                        <option value="LIBUR">LIBUR</option>
                                    </select>
                                </div>

                                @if ($seq['type'] === 'SHIFT')
                                    <div class="form-control flex flex-col flex-1">
                                        <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase opacity-60">Jam Kerja</span></label>
                                        <select wire:model="shiftSequence.{{ $index }}.shift_id" class="select select-bordered select-sm font-bold text-xs rounded-lg h-9 min-h-0">
                                            <option value="">-- Pilih Shift --</option>
                                            @foreach ($this->shifts as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="form-control flex flex-col flex-1">
                                        <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase opacity-60">Status</span></label>
                                        <div class="flex items-center h-9 text-[10px] font-bold uppercase tracking-widest text-base-content/30 px-3 bg-base-100/50 rounded-lg border border-dashed border-base-300">
                                            Hari Libur
                                        </div>
                                    </div>
                                @endif

                                <div class="flex gap-2 items-end">
                                    <div class="form-control flex flex-col w-20 sm:w-24">
                                        <label class="label pt-0"><span class="label-text text-[10px] font-bold uppercase opacity-60">Durasi</span></label>
                                        <div class="join">
                                            <input type="number" wire:model="shiftSequence.{{ $index }}.duration" class="input input-bordered input-sm font-bold text-center w-full join-item h-9 min-h-0" min="1">
                                            <span class="join-item bg-base-300 flex items-center px-2 text-[10px] font-bold uppercase opacity-60">Hari</span>
                                        </div>
                                    </div>

                                    @if(count($shiftSequence) > 1)
                                        <button type="button" wire:click="removeSequence({{ $index }})" class="btn btn-error btn-square btn-sm h-9 w-9 min-h-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-center">
                        <button type="button" wire:click="addSequence" class="btn btn-ghost btn-sm gap-2 text-primary hover:bg-primary/5 rounded-lg border-2 border-dashed border-primary/20 px-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Urutan
                        </button>
                    </div>
                    
                    <div class="mt-8 p-5 bg-primary/5 rounded-2xl border border-primary/10">
                        <div class="text-[10px] font-bold uppercase tracking-widest mb-3 opacity-40">Ringkasan Siklus:</div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($shiftSequence as $seq)
                                <div class="px-3 py-1.5 rounded-lg {{ $seq['type'] == 'SHIFT' ? 'bg-primary text-primary-content' : 'bg-neutral text-neutral-content' }} text-[10px] font-bold flex items-center gap-2 shadow-sm">
                                    {{ $seq['duration'] }}D {{ $seq['type'] }}
                                </div>
                                @if(!$loop->last)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3 opacity-20">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- STEP 4: DATE RANGE & EXECUTION --}}
            @if ($step === 4)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">4</span>
                        Pilih Rentang Tanggal & Generate
                    </h2>

                    <div class="max-w-2xl mx-auto">
                        <div class="bg-base-200/50 p-6 rounded-3xl border border-base-200 mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control">
                                    <label class="label mb-1"><span class="label-text font-bold text-[10px] uppercase opacity-60">Tanggal Mulai</span></label>
                                    <input type="date" wire:model="startDate" x-on:click="$el.showPicker()" class="input input-bordered font-bold focus:input-primary rounded-xl cursor-pointer">
                                </div>
                                <div class="form-control">
                                    <label class="label mb-1"><span class="label-text font-bold text-[10px] uppercase opacity-60">Tanggal Selesai</span></label>
                                    <input type="date" wire:model="endDate" x-on:click="$el.showPicker()" class="input input-bordered font-bold focus:input-primary rounded-xl cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div class="alert bg-neutral/5 border-neutral/10 text-base-content shadow-sm rounded-2xl mb-8">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-neutral shrink-0 w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div class="text-[11px] opacity-70 leading-snug">
                                Penjadwalan akan dilakukan secara bertahap (*staggered*) untuk memastikan pemerataan personel. Data absensi placeholder akan dibuat otomatis.
                            </div>
                        </div>

                        <button type="button" wire:click="generate" wire:loading.attr="disabled" class="btn btn-primary btn-block shadow-lg shadow-primary/20 h-14 rounded-xl group relative">
                            <div wire:loading.remove wire:target="generate" class="flex items-center justify-center gap-3">
                                <span class="font-bold uppercase tracking-wide">Eksekusi Generate Jadwal</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 group-hover:translate-x-1 transition-transform">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                            <div wire:loading wire:target="generate" class="flex items-center gap-3">
                                <span class="loading loading-spinner loading-sm"></span>
                                <span class="font-bold uppercase tracking-wide">Memproses...</span>
                            </div>
                        </button>
                    </div>
                </div>
            @endif

            {{-- ─── NAVIGATION BUTTONS ────────────────────────────────────────── --}}
            <div class="card-actions justify-between items-center mt-12 pt-8 border-t border-base-200">
                <div>
                    @if ($step > 1 && (Auth::user()->hasRole('super-admin') || $step > 2))
                        <button type="button" wire:click="prevStep" class="btn btn-ghost gap-2 font-bold uppercase text-[10px]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Sebelumnya
                        </button>
                    @endif
                </div>
                
                <div class="flex gap-2">
                    <a href="{{ route('jadwal') }}" class="btn btn-ghost font-bold uppercase text-[10px] hidden sm:inline-flex">Batal</a>
                    @if ($step < 4)
                        <button type="button" wire:click="nextStep" class="btn btn-primary px-6 font-bold uppercase text-[10px] tracking-widest gap-2">
                            Lanjut
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
