<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-base-content">Generate Jadwal Otomatis</h1>
            <p class="text-sm text-base-content/60 mt-1">Sistem rotasi cerdas untuk efisiensi penjadwalan personnel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
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
            @if (Auth::user()->hasRole('super-admin'))
                <li class="step {{ $step >= 1 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Pilih
                    OPD</li>
            @endif
            <li class="step {{ $step >= 2 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Personel
            </li>
            <li class="step {{ $step >= 3 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Siklus
                Shift</li>
            <li class="step {{ $step >= 4 ? 'step-primary' : '' }} font-bold text-xs uppercase tracking-widest">Tanggal
                & Eksekusi</li>
        </ul>
    </div>

    {{-- ─── Main Content ────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 border border-base-200 overflow-hidden">
        <div class="card-body p-8">

            {{-- STEP 1: OPD SELECTION --}}
            @if ($step === 1)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">1</span>
                        Pilih Organisasi Perangkat Daerah (OPD)
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($this->opds as $opd)
                            <label
                                class="flex items-start gap-4 p-4 border rounded-2xl cursor-pointer transition-all hover:bg-base-100 {{ $selectedOpdId == $opd->id ? 'border-primary bg-primary/5' : 'border-base-200 bg-base-50' }}">
                                <div class="mt-1">
                                    <input type="radio" wire:model="selectedOpdId" value="{{ $opd->id }}"
                                        class="radio radio-primary radio-sm">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm uppercase leading-tight wrap-break-word">
                                        {{ $opd->name }}</div>
                                    <div class="text-[10px] opacity-60 mt-1 line-clamp-2">
                                        {{ $opd->alamat ?? 'Alamat belum diset' }}</div>
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
                        <span
                            class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">2</span>
                        Pilih & Atur Regu Personel
                    </h2>

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                        {{-- Selection List --}}
                        <div class="lg:col-span-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-[10px] font-bold uppercase tracking-widest opacity-40">Daftar Personel
                                </h3>
                                <label
                                    class="label cursor-pointer gap-2 bg-base-200/50 px-4 py-1.5 rounded-xl border border-base-200">
                                    <span class="text-[10px] font-bold uppercase opacity-60">Pilih Semua</span>
                                    <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll"
                                        class="checkbox checkbox-primary checkbox-xs">
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-125 overflow-y-auto pr-2">
                                @forelse ($this->personnels as $p)
                                    <label
                                        class="label cursor-pointer flex items-center justify-start gap-4 p-3 border rounded-xl transition-all hover:bg-base-200 {{ in_array($p->id, $selectedPersonnelIds) ? 'border-primary bg-primary/5' : 'border-base-200' }}">
                                        <input type="checkbox" wire:model.live="selectedPersonnelIds"
                                            value="{{ $p->id }}" class="checkbox checkbox-primary checkbox-sm">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                @if ($p->foto)
                                                    <img src="{{ asset('storage/' . $p->foto) }}" />
                                                @else
                                                    <span class="text-xs">{{ substr($p->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-xs uppercase truncate">{{ $p->name }}</div>
                                            <div class="text-[9px] opacity-60 italic truncate">
                                                {{ $p->penugasan?->name ?? 'Belum ada penugasan' }}</div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="col-span-2 text-center py-10 opacity-40">
                                        <p>Tidak ada personel ditemukan di OPD ini.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Order & Regu --}}
                        <div class="lg:col-span-2">
                            <div class="flex flex-col gap-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-[10px] font-bold uppercase tracking-widest opacity-40">Urutan &
                                        Regu</h3>
                                    <label class="label cursor-pointer gap-2 py-0">
                                        <span class="text-[10px] font-bold uppercase opacity-60">Pakai Regu</span>
                                        <input type="checkbox" wire:model.live="useRegu"
                                            class="checkbox checkbox-primary checkbox-xs">
                                    </label>
                                </div>

                                @if ($useRegu)
                                    <div
                                        class="flex items-center justify-between bg-base-200/50 p-2 rounded-xl border border-base-200">
                                        <label class="text-[9px] font-bold opacity-60 uppercase">Personel /
                                            Regu:</label>
                                        <input type="number" wire:model.live="peoplePerRegu" min="1"
                                            class="input input-bordered input-xs w-14 text-center font-bold">
                                    </div>
                                @endif
                            </div>

                            <div
                                class="bg-base-200/30 rounded-2xl border border-base-200 p-4 min-h-25 max-h-125 overflow-y-auto">
                                @if (empty($selectedPersonnelIds))
                                    {{-- Empty state --}}
                                    <div
                                        class="flex flex-col items-center justify-center h-40 text-center opacity-30 gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-users-group size-8">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                            <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                            <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                        </svg>
                                        <span class="text-[10px] font-bold uppercase">Pilih personel untuk mengatur
                                            urutan</span>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach ($selectedPersonnelIds as $index => $pId)
                                            @php
                                                $person = $this->personnels->find($pId);
                                                $showLabel =
                                                    $useRegu && $peoplePerRegu > 0 && $index % $peoplePerRegu == 0;
                                            @endphp
                                            @if ($showLabel)
                                                <div class="text-[9px] font-bold opacity-30 mt-4 mb-1 uppercase">Regu
                                                    {{ floor($index / max(1, $peoplePerRegu)) + 1 }}</div>
                                            @endif
                                            <div
                                                class="flex items-center gap-3 p-2 bg-base-100 rounded-xl border border-base-200 group">
                                                <div class="text-[10px] font-bold opacity-30 w-4">{{ $index + 1 }}
                                                </div>
                                                <div class="avatar placeholder">
                                                    <div class="bg-neutral text-neutral-content rounded-full w-6">
                                                        @if ($person?->foto)
                                                            <img src="{{ asset('storage/' . $person->foto) }}" />
                                                        @else
                                                            <span
                                                                class="text-[10px]">{{ substr($person?->name, 0, 1) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-[10px] font-bold uppercase truncate">
                                                        {{ $person?->name }}</div>
                                                </div>
                                                <div class="flex gap-1">
                                                    <button type="button"
                                                        wire:click="movePersonnelUp('{{ $pId }}')"
                                                        @if ($index === 0) disabled @endif
                                                        class="btn btn-ghost btn-xs btn-square {{ $index === 0 ? 'opacity-10' : '' }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="3"
                                                            stroke="currentColor" class="w-3 h-3">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                        </svg>
                                                    </button>
                                                    <button type="button"
                                                        wire:click="movePersonnelDown('{{ $pId }}')"
                                                        @if ($index === count($selectedPersonnelIds) - 1) disabled @endif
                                                        class="btn btn-ghost btn-xs btn-square {{ $index === count($selectedPersonnelIds) - 1 ? 'opacity-10' : '' }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="3"
                                                            stroke="currentColor" class="w-3 h-3">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <p class="text-[9px] opacity-40 mt-3 italic leading-snug text-center">
                                Gunakan tombol panah untuk menentukan urutan pasangan/regu.
                                Personel teratas akan menempati shift pertama pada tanggal mulai.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- STEP 3: SHIFT SEQUENCE --}}
            @if ($step === 3)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">3</span>
                        Konfigurasi Siklus Shift
                    </h2>

                    {{-- Template Selector --}}
                    <div
                        class="bg-base-200/50 p-4 rounded-2xl border border-base-200 mb-8 flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex items-center gap-3 shrink-0">
                            <div
                                class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-layout-grid-add">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 8a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M4 16a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M16 12h6" />
                                    <path d="M19 9v6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider">Gunakan Template</h3>
                                <p class="text-[9px] opacity-60">Pilih konfigurasi yang sudah tersimpan</p>
                            </div>
                        </div>
                        <div class="flex-1 w-full">
                            <select wire:model.live="selectedTemplateId"
                                class="select select-bordered select-sm w-full font-bold rounded-lg">
                                <option value="">-- Pilih Template Konfigurasi --</option>
                                @foreach ($this->templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Mode Selector Tabs --}}
                    <div class="flex items-center justify-center mb-8">
                        <div
                            class="tabs tabs-boxed bg-base-200/50 p-1 rounded-2xl border border-base-200 w-full max-w-sm flex flex-nowrap">
                            <button type="button" wire:click="$set('generateMode', 'cycle')"
                                class="tab tab-sm flex-1 h-9 px-2 rounded-xl font-bold uppercase text-[9px] tracking-widest transition-all {{ $generateMode === 'cycle' ? 'tab-active bg-primary text-primary-content shadow-lg shadow-primary/20' : 'opacity-50 hover:opacity-100' }}">
                                Siklus Berputar
                            </button>
                            <button type="button" wire:click="$set('generateMode', 'weekly')"
                                class="tab tab-sm flex-1 h-9 px-2 rounded-xl font-bold uppercase text-[9px] tracking-widest transition-all {{ $generateMode === 'weekly' ? 'tab-active bg-primary text-primary-content shadow-lg shadow-primary/20' : 'opacity-50 hover:opacity-100' }}">
                                Jadwal Mingguan
                            </button>
                            <button type="button" wire:click="$set('generateMode', 'quota')"
                                class="tab tab-sm flex-1 h-9 px-2 rounded-xl font-bold uppercase text-[9px] tracking-widest transition-all {{ $generateMode === 'quota' ? 'tab-active bg-primary text-primary-content shadow-lg shadow-primary/20' : 'opacity-50 hover:opacity-100' }}">
                                Quota
                            </button>
                        </div>
                    </div>

                    @if ($generateMode === 'cycle')
                        {{-- Mode Cycle: Current UI --}}
                        <div class="space-y-3 mb-6 animate-in fade-in slide-in-from-bottom-4 duration-300">
                            @foreach ($shiftSequence as $index => $seq)
                                <div
                                    class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 p-4 bg-base-200/40 rounded-2xl border border-base-200 relative animate-in zoom-in-95">
                                    <div class="form-control flex flex-col w-full sm:w-36">
                                        <label class="label pt-0"><span
                                                class="label-text text-[10px] font-bold uppercase opacity-60">Tipe</span></label>
                                        <select wire:model.live="shiftSequence.{{ $index }}.type"
                                            class="select select-bordered select-sm font-bold rounded-lg h-9 min-h-0">
                                            <option value="SHIFT">KERJA (SHIFT)</option>
                                            <option value="LIBUR">LIBUR</option>
                                        </select>
                                    </div>

                                    @if ($seq['type'] === 'SHIFT')
                                        <div class="form-control flex flex-col flex-1">
                                            <label class="label pt-0"><span
                                                    class="label-text text-[10px] font-bold uppercase opacity-60">Jam
                                                    Kerja</span></label>
                                            <select wire:model="shiftSequence.{{ $index }}.shift_id"
                                                class="select select-bordered select-sm font-bold text-xs rounded-lg h-9 min-h-0">
                                                <option value="">-- Pilih Shift --</option>
                                                @foreach ($this->shifts as $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}
                                                        ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="form-control flex flex-col flex-1">
                                            <label class="label pt-0"><span
                                                    class="label-text text-[10px] font-bold uppercase opacity-60">Status</span></label>
                                            <div
                                                class="flex items-center h-9 text-[10px] font-bold uppercase tracking-widest text-base-content/30 px-3 bg-base-100/50 rounded-lg border border-dashed border-base-300">
                                                Hari Libur
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex flex-wrap sm:flex-nowrap gap-3 items-end">
                                        <div class="form-control flex flex-col w-24 sm:w-28">
                                            <label class="label pt-0"><span
                                                    class="label-text text-[10px] font-bold uppercase opacity-60">Jumlah
                                                    Personel</span></label>
                                            <div class="join">
                                                <input type="number"
                                                    wire:model="shiftSequence.{{ $index }}.count"
                                                    class="input input-bordered input-sm font-bold text-center w-full join-item h-9 min-h-0"
                                                    min="1">
                                                <span
                                                    class="join-item bg-base-300 flex items-center px-2 text-[10px] font-bold uppercase opacity-60">P</span>
                                            </div>
                                        </div>

                                        <div class="form-control flex flex-col w-24 sm:w-28">
                                            <label class="label pt-0"><span
                                                    class="label-text text-[10px] font-bold uppercase opacity-60">Durasi</span></label>
                                            <div class="join">
                                                <input type="number"
                                                    wire:model="shiftSequence.{{ $index }}.duration"
                                                    class="input input-bordered input-sm font-bold text-center w-full join-item h-9 min-h-0"
                                                    min="1">
                                                <span
                                                    class="join-item bg-base-300 flex items-center px-2 text-[10px] font-bold uppercase opacity-60">Hari</span>
                                            </div>
                                        </div>

                                        @if (count($shiftSequence) > 1)
                                            <button type="button" wire:click="removeSequence({{ $index }})"
                                                class="btn btn-error btn-square btn-sm h-9 w-9 min-h-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                                                    class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-center">
                            <button type="button" wire:click="addSequence"
                                class="btn btn-ghost btn-sm gap-2 text-primary hover:bg-primary/5 rounded-lg border-2 border-dashed border-primary/20 px-10">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Tambah Urutan
                            </button>
                        </div>
                    @elseif($generateMode === 'weekly')
                        {{-- Mode Weekly: New UI --}}
                        <div
                            class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-6 animate-in fade-in slide-in-from-bottom-4 duration-300">
                            @php
                                $days = [
                                    1 => ['name' => 'Senin', 'icon' => 'calendar'],
                                    2 => ['name' => 'Selasa', 'icon' => 'calendar'],
                                    3 => ['name' => 'Rabu', 'icon' => 'calendar'],
                                    4 => ['name' => 'Kamis', 'icon' => 'calendar'],
                                    5 => ['name' => 'Jumat', 'icon' => 'calendar'],
                                    6 => ['name' => 'Sabtu', 'icon' => 'calendar-star'],
                                    0 => ['name' => 'Minggu', 'icon' => 'calendar-event'],
                                ];
                            @endphp

                            @foreach ($days as $index => $day)
                                <div
                                    class="flex items-center gap-3 p-3 bg-base-200/40 rounded-2xl border border-base-200">
                                    <div class="w-10 flex flex-col items-center shrink-0">
                                        <span
                                            class="text-[10px] font-bold uppercase {{ in_array($index, [0, 6]) ? 'text-error' : 'opacity-40' }}">{{ $day['name'] }}</span>
                                    </div>
                                    <div class="flex-1 flex gap-2">
                                        <select wire:model.live="weeklyConfig.{{ $index }}.type"
                                            class="select select-bordered select-xs font-bold rounded-lg h-8 flex-1">
                                            <option value="SHIFT">MASUK</option>
                                            <option value="LIBUR">LIBUR</option>
                                        </select>

                                        @if ($weeklyConfig[$index]['type'] === 'SHIFT')
                                            <select wire:model="weeklyConfig.{{ $index }}.shift_id"
                                                class="select select-bordered select-xs font-bold text-[10px] rounded-lg h-8 flex-2">
                                                <option value="">-- Pilih Shift --</option>
                                                @foreach ($this->shifts as $s)
                                                    <option value="{{ $s->id }}">{{ $s->name }}
                                                        ({{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div
                                                class="flex-1 bg-base-100/50 rounded-lg border border-dashed border-base-300 flex items-center justify-center">
                                                <span
                                                    class="text-[9px] font-bold opacity-20 uppercase tracking-tighter">Libur</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($generateMode === 'quota')
                        {{-- Mode Quota: Smart Distribution UI --}}
                        <div class="max-w-xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-300">
                            <div class="bg-base-200/30 rounded-3xl border border-base-200 p-6">
                                <div class="flex items-center gap-4 mb-6">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-scale">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M7 20l10 0" />
                                            <path d="M6 6l6 -1l6 1" />
                                            <path d="M12 3l0 17" />
                                            <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0" />
                                            <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm uppercase">Kebutuhan Personel Harian</h3>
                                        <p class="text-[10px] opacity-60">Sistem akan membagi jatah kerja secara adil
                                        </p>
                                    </div>
                                </div>

                                {{-- Smart Rules Info --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-6">
                                    <div class="p-3 rounded-xl bg-base-100 border border-base-200 flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" />
                                            </svg>
                                            <span class="text-[9px] font-bold uppercase tracking-tight">Pasca
                                                Malam</span>
                                        </div>
                                        <p class="text-[8px] opacity-50 leading-tight">Personel wajib libur 1 hari
                                            setelah shift malam.</p>
                                    </div>
                                    <div class="p-3 rounded-xl bg-base-100 border border-base-200 flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-success">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M4 12a8 8 0 1 0 16 0a8 8 0 0 0 -16 0" />
                                                <path d="M12 12l3 -3" />
                                                <path d="M9 15l3 -3" />
                                                <path d="M12 12l3 3" />
                                                <path d="M9 9l3 3" />
                                            </svg>
                                            <span class="text-[9px] font-bold uppercase tracking-tight">Keadilan Akhir
                                                Pekan</span>
                                        </div>
                                        <p class="text-[8px] opacity-50 leading-tight">Jatah libur Sabtu-Minggu dibagi
                                            rata sebulan.</p>
                                    </div>
                                    <div class="p-3 rounded-xl bg-base-100 border border-base-200 flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 9l0 3" />
                                                <path d="M12 15l.01 0" />
                                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                            </svg>
                                            <span class="text-[9px] font-bold uppercase tracking-tight">Maks 6
                                                Hari</span>
                                        </div>
                                        <p class="text-[8px] opacity-50 leading-tight">Dilarang masuk lebih dari 6 hari
                                            tanpa libur.</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($this->shifts as $s)
                                        <div
                                            class="flex items-center justify-between p-4 bg-base-100 rounded-2xl border border-base-200 shadow-sm">
                                            <div>
                                                <span
                                                    class="block font-bold text-xs uppercase">{{ $s->name }}</span>
                                                <span
                                                    class="text-[9px] opacity-40 font-bold tracking-tight">{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                                    - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="join border border-base-300 rounded-xl overflow-hidden h-9">
                                                    <button type="button"
                                                        wire:click="$set('quotaConfig.{{ $s->id }}', {{ max(0, $quotaConfig[$s->id] - 1) }})"
                                                        class="btn btn-ghost join-item w-8">-</button>
                                                    <input type="number"
                                                        wire:model.live="quotaConfig.{{ $s->id }}"
                                                        class="input input-ghost join-item w-12 text-center font-bold text-xs focus:bg-transparent"
                                                        min="0">
                                                    <button type="button"
                                                        wire:click="$set('quotaConfig.{{ $s->id }}', {{ $quotaConfig[$s->id] + 1 }})"
                                                        class="btn btn-ghost join-item w-8">+</button>
                                                </div>
                                                <span class="text-[10px] font-bold opacity-40 uppercase">Orang</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6 pt-6 border-t border-dashed border-base-300">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold opacity-60">Total Kebutuhan:</span>
                                        <span class="font-black text-primary">{{ array_sum($quotaConfig) }} Orang /
                                            Hari</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs mt-2">
                                        <span class="font-bold opacity-60">Personel Tersedia:</span>
                                        <span class="font-black">{{ count($selectedPersonnelIds) }} Orang</span>
                                    </div>

                                    @php
                                        $totalNeeded = array_sum($quotaConfig);
                                        $nightQuota = 0;
                                        foreach ($this->shifts as $s) {
                                            if (
                                                stripos($s->name, 'malam') !== false ||
                                                (\Carbon\Carbon::parse($s->start_time)->hour >= 18 ||
                                                    \Carbon\Carbon::parse($s->start_time)->hour < 4)
                                            ) {
                                                $nightQuota += $quotaConfig[$s->id] ?? 0;
                                            }
                                        }
                                        $minPersonilNeeded = $totalNeeded + $nightQuota;
                                        $personilAvailable = count($selectedPersonnelIds);
                                        $isInsufficient = $personilAvailable < $minPersonilNeeded;
                                    @endphp

                                    @if ($isInsufficient)
                                        <div
                                            class="mt-4 p-4 rounded-2xl bg-error/10 border border-error/20 flex flex-col gap-2 animate-pulse">
                                            <div class="flex items-center gap-2 text-error">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                    <path d="M12 9v4" />
                                                    <path d="M12 16v.01" />
                                                </svg>
                                                <span class="text-xs font-black uppercase">Personel Tidak Cukup!</span>
                                            </div>
                                            <p class="text-[10px] font-bold leading-tight opacity-80">
                                                Dibutuhkan minimal <span class="underline">{{ $minPersonilNeeded }}
                                                    orang</span> agar aturan "Libur Pasca Malam" bisa berjalan. Saat ini
                                                Anda hanya memilih {{ $personilAvailable }} orang.
                                            </p>
                                            <p class="text-[9px] italic opacity-60 mt-1">*Sistem akan tetap mencoba
                                                mengisi shift, namun beberapa shift mungkin akan kosong karena aturan
                                                istirahat.</p>
                                        </div>
                                    @else
                                        <div
                                            class="mt-4 p-3 rounded-xl bg-info/5 border border-info/10 flex items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                                class="text-info shrink-0">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                <path d="M12 9h.01" />
                                                <path d="M11 12h1v4h1" />
                                            </svg>
                                            <p class="text-[10px] font-bold leading-tight opacity-70">
                                                Setiap hari akan ada <span
                                                    class="text-info underline">{{ max(0, $personilAvailable - $totalNeeded) }}
                                                    orang</span> yang Libur secara bergantian untuk menjaga keadilan.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Save as Template Option --}}
                    <div class="mt-8 pt-6 border-t border-base-200">
                        <label class="flex items-center gap-3 cursor-pointer group w-fit">
                            <input type="checkbox" wire:model.live="saveAsTemplate"
                                class="checkbox checkbox-primary checkbox-sm">
                            <span
                                class="text-xs font-bold uppercase opacity-60 group-hover:opacity-100 transition-opacity">Simpan
                                konfigurasi ini sebagai template</span>
                        </label>

                        @if ($saveAsTemplate)
                            <div
                                class="mt-4 p-4 bg-primary/5 rounded-2xl border border-primary/20 flex flex-col sm:flex-row items-end gap-3 animate-in zoom-in-95">
                                <div class="form-control flex-1">
                                    <label class="label pt-0"><span
                                            class="label-text text-[10px] font-bold uppercase opacity-60">Nama
                                            Konfigurasi Template</span></label>
                                    <input type="text" wire:model="templateName"
                                        placeholder="Misal: Pola 2-2-2 atau Siklus 6 Hari"
                                        class="input input-bordered input-sm font-bold w-full rounded-lg">
                                    @error('templateName')
                                        <span class="text-[10px] text-error mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="button" wire:click="saveCurrentAsTemplate"
                                    class="btn btn-primary btn-sm rounded-lg px-6">
                                    Simpan Template
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 p-5 bg-primary/5 rounded-2xl border border-primary/10">
                        <div class="text-[10px] font-bold uppercase tracking-widest mb-3 opacity-40">
                            Ringkasan {{ $generateMode === 'cycle' ? 'Siklus' : 'Mingguan' }}:
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($generateMode === 'cycle')
                                @foreach ($shiftSequence as $seq)
                                    <div
                                        class="px-3 py-1.5 rounded-lg {{ $seq['type'] == 'SHIFT' ? 'bg-primary text-primary-content' : 'bg-neutral text-neutral-content' }} text-[10px] font-bold flex items-center gap-2 shadow-sm">
                                        <span class="opacity-70">{{ $seq['count'] }}p</span>
                                        <span>{{ $seq['duration'] }}D {{ $seq['type'] }}</span>
                                    </div>
                                    @if (!$loop->last)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="3" stroke="currentColor" class="w-3 h-3 opacity-20">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    @endif
                                @endforeach
                            @elseif ($generateMode === 'weekly')
                                @php
                                    $dayNames = [
                                        1 => 'Sen',
                                        2 => 'Sel',
                                        3 => 'Rab',
                                        4 => 'Kam',
                                        5 => 'Jum',
                                        6 => 'Sab',
                                        0 => 'Min',
                                    ];
                                @endphp
                                @foreach ($dayNames as $index => $name)
                                    @php $conf = $weeklyConfig[$index]; @endphp
                                    <div
                                        class="px-3 py-1.5 rounded-lg {{ $conf['type'] == 'SHIFT' ? 'bg-primary/20 text-primary border border-primary/20' : 'bg-base-300 text-base-content/40' }} text-[10px] font-bold flex items-center gap-1.5 shadow-sm">
                                        <span class="opacity-50">{{ $name }}:</span>
                                        <span>{{ $conf['type'] === 'SHIFT' ? $this->shifts->find($conf['shift_id'])?->name ?? '---' : 'LIBUR' }}</span>
                                    </div>
                                @endforeach
                            @elseif($generateMode === 'quota')
                                @foreach ($quotaConfig as $shiftId => $count)
                                    @php $s = $this->shifts->find($shiftId); @endphp
                                    @if ($s && $count > 0)
                                        <div
                                            class="px-3 py-1.5 rounded-lg bg-primary/20 text-primary border border-primary/20 text-[10px] font-bold flex items-center gap-1.5 shadow-sm">
                                            <span class="opacity-50">{{ $s->name }}:</span>
                                            <span>{{ $count }}p</span>
                                        </div>
                                    @endif
                                @endforeach
                                <div
                                    class="px-3 py-1.5 rounded-lg bg-base-300 text-base-content/40 text-[10px] font-bold flex items-center gap-1.5 shadow-sm">
                                    <span class="opacity-50">LIBUR:</span>
                                    <span>{{ max(0, count($selectedPersonnelIds) - array_sum($quotaConfig)) }}p</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- STEP 4: DATE RANGE & EXECUTION --}}
            @if ($step === 4)
                <div class="animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-xl font-bold mb-8 flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm shrink-0">4</span>
                        Pilih Rentang Tanggal & Generate
                    </h2>

                    <div class="max-w-2xl mx-auto">
                        <div class="bg-base-200/50 p-6 rounded-3xl border border-base-200 mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-control">
                                    <label class="label mb-1"><span
                                            class="label-text font-bold text-[10px] uppercase opacity-60">Tanggal
                                            Mulai</span></label>
                                    <input type="date" wire:model="startDate" x-on:click="$el.showPicker()"
                                        class="input input-bordered font-bold focus:input-primary rounded-xl cursor-pointer">
                                </div>
                                <div class="form-control">
                                    <label class="label mb-1"><span
                                            class="label-text font-bold text-[10px] uppercase opacity-60">Tanggal
                                            Selesai</span></label>
                                    <input type="date" wire:model="endDate" x-on:click="$el.showPicker()"
                                        class="input input-bordered font-bold focus:input-primary rounded-xl cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div class="alert bg-neutral/5 border-neutral/10 text-base-content shadow-sm rounded-2xl mb-8">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="stroke-neutral shrink-0 w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-[11px] opacity-70 leading-snug">
                                Penjadwalan akan dilakukan secara bertahap (*staggered*) untuk memastikan pemerataan
                                personel. Data absensi placeholder akan dibuat otomatis.
                            </div>
                        </div>

                        <button type="button" wire:click="generate" wire:loading.attr="disabled"
                            class="btn btn-primary btn-block shadow-lg shadow-primary/20 h-14 rounded-xl group relative">
                            <div wire:loading.remove wire:target="generate"
                                class="flex items-center justify-center gap-3">
                                <span class="font-bold uppercase tracking-wide">Generate Jadwal</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="3" stroke="currentColor"
                                    class="w-5 h-5 group-hover:translate-x-1 transition-transform">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
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
                        <button type="button" wire:click="prevStep"
                            class="btn btn-ghost gap-2 font-bold uppercase text-[10px]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="3" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Sebelumnya
                        </button>
                    @endif
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('jadwal') }}"
                        class="btn btn-ghost font-bold uppercase text-[10px] hidden sm:inline-flex">Batal</a>
                    @if ($step < 4)
                        <button type="button" wire:click="nextStep"
                            class="btn btn-primary px-6 font-bold uppercase text-[10px] tracking-widest gap-2">
                            Lanjut
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="3" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Modal Konfirmasi Reset --}}
            @if ($showConfirmModal)
                <div class="modal modal-open backdrop-blur-sm">
                    <div class="modal-box shadow-2xl border border-error/20 max-w-md">
                        <div class="flex items-center gap-4 text-error mb-4">
                            <div class="p-3 bg-error/10 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl leading-tight">Jadwal Sudah Ada!</h3>
                                <p class="text-[10px] uppercase font-black opacity-40 tracking-widest">Konfirmasi
                                    Generate Ulang
                                </p>
                            </div>
                        </div>

                        <div class="py-4 space-y-4">
                            <p class="text-sm leading-relaxed">
                                Sistem mendeteksi bahwa sudah ada data <span class="font-bold">Jadwal</span> untuk
                                personel yang
                                dipilih pada rentang <span
                                    class="badge badge-neutral font-bold">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}
                                    - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>.
                            </p>
                            <div class="alert alert-error bg-error/5 text-[11px] py-3 rounded-xl border-error/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    class="stroke-error shrink-0 w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="font-medium">Melanjutkan proses ini akan <span
                                        class="underline font-bold">MENGHAPUS
                                        PERMANEN</span> seluruh Jadwal, Absensi, dan <span
                                        class="font-bold uppercase">FILE FOTO
                                        ABSENSI</span> pada personel dan rentang tanggal tersebut.</span>
                            </div>
                            <div class="text-center">
                                <p class="text-[11px] text-base-content/50 italic">Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>

                        <div class="modal-action grid grid-cols-2 gap-3 mt-2">
                            <button type="button" wire:click="$set('showConfirmModal', false)"
                                class="btn btn-ghost border-base-300">Batal</button>
                            <button type="button" wire:click="confirmGenerate" class="btn btn-error text-white">
                                <span wire:loading wire:target="confirmGenerate"
                                    class="loading loading-spinner loading-xs"></span>
                                <span wire:loading.remove wire:target="confirmGenerate">Ya, Generate Ulang</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
