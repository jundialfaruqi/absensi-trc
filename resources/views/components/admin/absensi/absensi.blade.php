<div x-data="{
    previewUrl: null,
    showPreview: false,
    previewX: 0,
    previewY: 0,
    triggerPreview(url, event) {
        this.previewUrl = url;
        this.showPreview = true;
        // Position centering management
        this.previewX = event.clientX;
        this.previewY = event.clientY;
    },
    hidePreview() {
        this.showPreview = false;
    }
}" wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Monitoring Absensi</h1>
            <p class="text-sm text-base-content/60 mt-1">Pantau kehadiran personel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li>
                    <a href="{{ route('absensi') }}">
                        <span class="text-base-content font-bold">Absensi</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Matrix Toolbar ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="join">
                <span
                    class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                <select wire:model.live="perPage" class="select select-bordered join-item w-20 rounded-end-md">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            @if (auth()->user()->hasRole('super-admin'))
                <div class="w-full sm:w-auto">
                    <select wire:model.live="selectedOpd" class="select select-bordered w-full sm:w-64 bg-base-100">
                        <option value="">Semua OPD (Filter)</option>
                        @foreach ($this->opds as $opd)
                            <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <div class="join w-full sm:w-auto">
                    <select wire:model.live="month" class="select select-bordered join-item w-full sm:w-auto">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <select wire:model.live="year" class="select select-bordered join-item w-full sm:w-auto">
                        @for ($y = \Carbon\Carbon::now()->year - 2; $y <= \Carbon\Carbon::now()->year + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <style>
                    input[type="date"]::-webkit-calendar-picker-indicator {
                        display: block !important;
                        cursor: pointer;
                        opacity: 0.5;
                        filter: invert(1);
                    }

                    .dark input[type="date"]::-webkit-calendar-picker-indicator {
                        filter: invert(0);
                    }

                    input[type="date"]::-webkit-calendar-picker-indicator:hover {
                        opacity: 1;
                    }
                </style>

                <div class="join w-full sm:w-auto">
                    <div
                        class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                        Dari</div>
                    <input type="date" id="startDate" wire:model.live="startDate"
                        class="input input-bordered join-item w-full sm:w-auto text-base-content/60" />
                    <div
                        class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                        S/D</div>
                    <input type="date" id="endDate" wire:model.live="endDate"
                        class="input input-bordered join-item w-full sm:w-auto text-base-content/60" />

                    @if ($startDate || $endDate)
                        <button type="button" wire:click="resetFilters"
                            class="btn join-item px-3 text-error btn-bordered">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 justify-end">
            {{-- Optional Action Buttons for Absensi --}}
            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Cari nama personnel..." wire:model.live.debounce.400ms="search"
                    class="input input-bordered w-full pl-10 pr-10 bg-base-100 placeholder:text-base-content/40" />
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                @if ($search)
                    <button type="button" wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>

            <div class="join">
                <select wire:model.live="paperSize" class="select select-bordered join-item">
                    <option value="a4">Kertas A4</option>
                    <option value="f4">Kertas F4 / Folio</option>
                    <option value="legal">Kertas Legal</option>
                </select>
                <a href="{{ route('absensi.export-pdf', ['month' => $month, 'year' => $year, 'search' => $search, 'startDate' => $startDate, 'endDate' => $endDate, 'paperSize' => $paperSize]) }}"
                    target="_blank" class="btn btn-neutral join-item gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ─── Absensi Matrix ────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)]">
                <table class="table table-sm w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-110 bg-base-100">
                        <tr>
                            <th rowspan="2"
                                class="sticky left-0 z-30 bg-base-100 border-b border-r border-base-200 min-w-50 text-center align-middle">
                                Personnel
                            </th>
                            @foreach ($this->dates as $date)
                                <th colspan="2"
                                    class="text-center border-b border-r border-base-200 min-w-32 p-1 {{ \Carbon\Carbon::parse($date)->isToday() ? 'bg-primary/10' : '' }}">
                                    <div class="text-[10px] uppercase opacity-50 leading-none mb-1">
                                        {{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}
                                    </div>
                                    <div class="text-sm font-bold">{{ \Carbon\Carbon::parse($date)->format('d/m') }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($this->dates as $date)
                                <th
                                    class="text-[9px] font-black text-center border-b border-r border-base-200 p-1 bg-base-200/30">
                                    M</th>
                                <th
                                    class="text-[9px] font-black text-center border-b border-r border-base-200 p-1 bg-base-200/30">
                                    P</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ─── Skeleton Loading (While Not Ready) ────────────────────────── --}}
                        @if (!$readyToLoad)
                            @for ($i = 0; $i < $perPage; $i++)
                                <tr>
                                    <td class="sticky left-0 z-10 bg-base-100 border-r border-base-200 p-3 w-50">
                                        <div class="flex items-center gap-2 ps-4">
                                            <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                                            <div class="flex flex-col gap-2">
                                                <div class="skeleton h-3 w-28"></div>
                                                <div class="skeleton h-2 w-20"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($this->dates as $date)
                                        <td class="border-r border-base-200 p-1 min-w-16">
                                            <div class="skeleton h-10 w-full rounded-lg"></div>
                                        </td>
                                        <td class="border-r border-base-200 p-1 min-w-16">
                                            <div class="skeleton h-10 w-full rounded-lg"></div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        @endif

                        {{-- ─── Real Table Data ────────────────────────────────────────── --}}
                        @if ($readyToLoad)
                            @php
                                $currentOpd = null;
                                $isSuperAdmin = auth()->user()->hasRole('super-admin');
                            @endphp
                            @forelse ($this->personnels as $p)
                                @if ($isSuperAdmin && $currentOpd !== $p->opd_id)
                                    <tr class="bg-base-200">
                                        <td colspan="{{ count($this->dates) * 2 + 1 }}"
                                            class="sticky left-0 top-16 z-50 p-0 border-b border-base-200 bg-base-200">
                                            <div class="sticky left-0 w-fit px-4 py-2 flex items-center gap-2">
                                                <div class="w-1.5 h-4 bg-base-content"></div>
                                                <span
                                                    class="text-[11px] font-black uppercase tracking-[0.2em] text-base-content whitespace-nowrap">
                                                    {{ $p->opd->singkatan }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $currentOpd = $p->opd_id; @endphp
                                @endif
                                <tr class="group">
                                    <td class="sticky left-0 z-10 bg-base-100 border-r border-base-200 p-3 w-50">
                                        <div class="flex items-center gap-2 ps-4">
                                            <div class="avatar placeholder">
                                                @if ($p->foto)
                                                    <div class="w-10 h-10 rounded-full">
                                                        <img src="{{ asset('storage/' . $p->foto) }}"
                                                            alt="{{ $p->name }}" />
                                                    </div>
                                                @else
                                                    <div
                                                        class="flex items-center justify-center bg-neutral text-neutral-content w-8 rounded-full">
                                                        <span
                                                            class="text-xs">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="truncate">
                                                <div class="font-bold text-xs truncate max-w-30">{{ $p->name }}
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <div class="text-[9px] opacity-50 truncate max-w-20">
                                                        {{ $p->penugasan?->name ?? 'N/A' }}</div>
                                                    @if ($p->regu)
                                                        <span
                                                            class="px-1 py-0.5 rounded bg-primary/10 text-primary text-[8px] font-bold border border-primary/20 leading-none">
                                                            {{ $p->regu }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($this->dates as $date)
                                        @php
                                            $a = $p->absensi_map[$date] ?? null;
                                            $j = $p->jadwal_map[$date] ?? null;
                                            $isToday = \Carbon\Carbon::parse($date)->isToday();

                                            $cellClass = '';
                                            if ($a) {
                                                if (in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALFA', 'CUTI'])) {
                                                    $cellClass = 'bg-neutral/10';
                                                } elseif ($a->status_masuk === 'HADIR') {
                                                    $cellClass = 'bg-success/20';
                                                } else {
                                                    $cellClass = 'bg-error/20';
                                                }
                                            } elseif ($j && \Carbon\Carbon::parse($date)->isPast()) {
                                                $cellClass = 'bg-base-300/50';
                                            }
                                        @endphp
                                        {{-- Kolom Masuk (M) --}}
                                        @php
                                            $cellClassM =
                                                'border-r border-base-200 cursor-pointer hover:bg-base-200/50 transition-all text-center p-1 min-w-16 h-12 relative';
                                            if ($a) {
                                                if (in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALFA', 'CUTI'])) {
                                                    $cellClassM .= ' bg-neutral/10';
                                                } elseif ($a->status_masuk === 'HADIR') {
                                                    $cellClassM .= ' bg-success/10';
                                                } elseif ($a->status_masuk === 'TELAT') {
                                                    $cellClassM .= ' bg-error/10';
                                                } elseif ($a->status === 'LIBUR') {
                                                    $cellClassM .= ' bg-base-200/50';
                                                }
                                            } elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday) {
                                                $cellClassM .= ' bg-base-300/30';
                                            }
                                        @endphp
                                        <td wire:click="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                            wire:loading.class="opacity-40 pointer-events-none"
                                            wire:target="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                            class="{{ $cellClassM }}">
                                            <div class="relative w-full h-full flex items-center justify-center">
                                                {{-- Specific Cell Loader --}}
                                                <div wire:loading
                                                    wire:target="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                                    class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 flex items-center justify-center z-20">
                                                    <span
                                                        class="loading loading-spinner loading-xs text-primary"></span>
                                                </div>

                                                @if ($a && $a->edited_at)
                                                    <div class="absolute top-0 right-0 p-0 z-20">
                                                        <div class="w-1.5 h-1.5 bg-primary rounded-bl-full"></div>
                                                    </div>
                                                @endif

                                                @if ($a)
                                                    @if ($a->status === 'LIBUR')
                                                        <span class="text-[10px] font-black opacity-30">LIBUR</span>
                                                    @elseif ($a->jam_masuk)
                                                        <div class="flex flex-col items-center justify-center">
                                                            <div
                                                                class="text-[11px] font-black leading-tight {{ $a->status_masuk === 'TELAT' ? 'text-error' : 'text-success' }}">
                                                                {{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') }}
                                                            </div>
                                                            <div class="text-[8px] font-black uppercase opacity-60">
                                                                {{ $a->status_masuk }}</div>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-[9px] font-black {{ $a->status_masuk === 'ALFA' ? 'text-error' : 'text-neutral' }}">{{ $a->status_masuk ?: $a->status }}</span>
                                                    @endif
                                                @elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday)
                                                    <span class="text-[10px] font-black text-error">ALFA</span>
                                                @elseif ($j)
                                                    <div class="opacity-20 text-[8px] font-black">
                                                        {{ $j->shift?->name ?? $j->status }}</div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Kolom Pulang (P) --}}
                                        @php
                                            $cellClassP =
                                                'border-r border-base-200 cursor-pointer hover:bg-base-200/50 transition-all text-center p-1 min-w-16 h-12 relative';
                                            if ($a) {
                                                if (in_array($a->status_pulang, ['SAKIT', 'IZIN', 'ALFA', 'CUTI'])) {
                                                    $cellClassP .= ' bg-neutral/10';
                                                } elseif ($a->status_pulang === 'HADIR') {
                                                    $cellClassP .= ' bg-success/10';
                                                } elseif ($a->status_pulang === 'PC') {
                                                    $cellClassP .= ' bg-warning/10';
                                                } elseif ($a->status === 'LIBUR') {
                                                    $cellClassP .= ' bg-base-200/50';
                                                }
                                            } elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday) {
                                                $cellClassP .= ' bg-base-300/30';
                                            }
                                        @endphp
                                        <td wire:click="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                            wire:loading.class="opacity-40 pointer-events-none"
                                            wire:target="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                            class="{{ $cellClassP }}">
                                            <div class="relative w-full h-full flex items-center justify-center">
                                                {{-- Specific Cell Loader --}}
                                                <div wire:loading
                                                    wire:target="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                                    class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 flex items-center justify-center z-20">
                                                    <span
                                                        class="loading loading-spinner loading-xs text-primary"></span>
                                                </div>

                                                @if ($a && $a->edited_at)
                                                    <div class="absolute top-0 right-0 p-0 z-20">
                                                        <div class="w-1.5 h-1.5 bg-primary rounded-bl-full"></div>
                                                    </div>
                                                @endif

                                                @if ($a)
                                                    @if ($a->status === 'LIBUR')
                                                        <span class="text-[10px] font-black opacity-30">LIBUR</span>
                                                    @elseif ($a->jam_pulang)
                                                        <div class="flex flex-col items-center justify-center">
                                                            <div
                                                                class="text-[11px] font-black leading-tight {{ $a->status_pulang === 'PC' ? 'text-warning' : 'text-success' }}">
                                                                {{ \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') }}
                                                            </div>
                                                            <div class="text-[8px] font-black uppercase opacity-60">
                                                                {{ $a->status_pulang }}</div>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-[9px] font-black {{ $a->status_pulang === 'ALFA' ? 'text-error' : 'text-neutral' }}">{{ $a->status_pulang ?: $a->status }}</span>
                                                    @endif
                                                @elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday)
                                                    <span class="text-[10px] font-black text-error">ALFA</span>
                                                @elseif ($j)
                                                    <div class="opacity-20 text-[8px] font-black">
                                                        {{ $j->shift?->name ?? $j->status }}</div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($this->dates) * 2 + 1 }}"
                                        class="text-center py-12 text-sm text-base-content/60">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="size-12 opacity-20 mb-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                            Tidak ada data personnel
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-base-200 bg-base-50">
                @if ($readyToLoad)
                    {{ $this->personnels->links() }}
                @endif
            </div>
        </div>
    </div>

    <livewire:admin::absensi-edit-modal />
    {{-- Teleport Preview Overlay --}}
    @teleport('body')
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90" class="fixed pointer-events-none z-9999"
            :style="`left: ${previewX}px; top: ${previewY}px; transform: translate(-50%, -100%) translateY(-20px);`"
            x-cloak>
            <div class="bg-base-100 p-1.5 rounded-2xl shadow-2xl ring-1 ring-base-content/10">
                <img :src="previewUrl" class="w-64 h-64 object-cover rounded-xl shadow-inner bg-base-200" />
            </div>
        </div>
    @endteleport
</div>
