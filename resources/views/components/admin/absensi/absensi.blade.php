<div x-data="absensiAdminComponent()" wire:init="load">
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
                        <span class="text-base-content font-bold">Rekap Absensi</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Tab Navigation Menu ─────────────────────────────────────────────── --}}
    <div class="border-b border-base-300 mb-6">
        <nav class="-mb-px flex space-x-6 sm:space-x-8" aria-label="Tabs">
            <a href="{{ route('absensi') }}" @click.prevent="activeTab = 'absensi'"
                :class="activeTab === 'absensi' ? 'border-primary text-primary font-bold' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300 font-medium'"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-4 text-sm transition-all border-primary text-primary font-bold cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Rekap Absensi</span>
            </a>

            @can('lihat-dokumentasi-konsumsi')
            <a href="{{ route('dokumentasi-konsumsi') }}" wire:navigate
                :class="activeTab === 'konsumsi' ? 'border-primary text-primary font-bold' : 'border-transparent text-base-content/60 hover:text-base-content hover:border-base-300 font-medium'"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-4 text-sm transition-all border-transparent text-base-content/60 hover:text-base-content hover:border-base-300 font-medium cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Rekap Dokumentasi Konsumsi</span>
            </a>
            @endcan
        </nav>
    </div>

    {{-- ─── Rekap Absensi Content ───────────────────────────────────────────── --}}
    <div x-show="activeTab === 'absensi'">
        {{-- ─── Matrix Toolbar ──────────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-4 mb-6">
        {{-- Search Input --}}
        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Nama personnel..." wire:model.live.debounce.400ms="search"
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

        {{-- Filters and Actions --}}
        <div class="flex flex-col md:flex-row justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="join">
                    <span
                        class="btn btn-sm md:btn-md btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                    <select wire:model.live="perPage"
                        class="select select-bordered select-sm md:select-md join-item w-20 rounded-end-md">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                </div>

                @if (auth()->user()->hasRole('super-admin'))
                    <div class="w-full sm:w-auto">
                        <select wire:model.live="selectedOpd"
                            class="select select-bordered select-sm md:select-md w-full sm:w-64 bg-base-100">
                            <option value="">Semua OPD (Filter)</option>
                            @foreach ($this->opds as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <div class="join w-full sm:w-auto">
                        <div
                            class="join-item flex items-center btn btn-sm md:btn-md btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                            Dari</div>
                        <input type="date" id="startDate" wire:model="filterStartDate"
                            class="input input-bordered input-sm md:input-md join-item w-full sm:w-auto scheme-light dark:scheme-dark text-base-content/70" />
                        <div
                            class="join-item flex items-center btn btn-sm md:btn-md btn-disabled pointer-events-none px-3 text-[10px] uppercase text-base-content">
                            S/D</div>
                        <input type="date" id="endDate" wire:model="filterEndDate"
                            class="input input-bordered input-sm md:input-md join-item w-full sm:w-auto scheme-light dark:scheme-dark text-base-content/70" />
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit"
                            class="btn btn-sm md:btn-md btn-primary gap-1.5 flex-1 sm:flex-initial shadow-sm">
                            <span wire:loading.remove wire:target="applyFilter" class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Terapkan</span>
                            </span>
                            <span wire:loading wire:target="applyFilter" class="flex items-center gap-1.5">
                                <span class="loading loading-spinner loading-xs"></span>
                                <span>Terapkan</span>
                            </span>
                        </button>

                        <button type="button" wire:click="resetFilters"
                            class="btn btn-sm md:btn-md btn-outline border-base-300 text-base-content/70 hover:text-error hover:border-error gap-1.5 flex-1 sm:flex-initial"
                            title="Reset filter tanggal">
                            <span wire:loading.remove wire:target="resetFilters" class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <span>Reset</span>
                            </span>
                            <span wire:loading wire:target="resetFilters" class="flex items-center gap-1.5">
                                <span class="loading loading-spinner loading-xs"></span>
                                <span>Reset</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap gap-2 justify-end">

                <div class="join">
                    <select wire:model.live="paperSize" class="select select-bordered select-sm md:select-md join-item">
                        <option value="a4">Kertas A4</option>
                        <option value="f4">Kertas F4 / Folio</option>
                        <option value="legal">Kertas Legal</option>
                    </select>
                    <button type="button" @click="openExportModal('pdf')"
                        class="btn btn-sm md:btn-md btn-neutral join-item gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-pdf">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                            <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                            <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                            <path d="M17 18h2" />
                            <path d="M20 15h-3v6" />
                            <path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1" />
                        </svg>
                        Export PDF
                    </button>
                </div>

                <button type="button" @click="openExportModal('excel')"
                    class="btn btn-sm md:btn-md btn-neutral text-white gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-file-spreadsheet">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                        <path d="M8 11h8v7h-8l0 -7" />
                        <path d="M8 15h8" />
                        <path d="M11 11v7" />
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Perbaikan Poin 4: Cache variable dates agar tidak panggil method Computed berkali-kali --}}
    @php
        $dates = $this->dates;
    @endphp

    {{-- ─── Absensi Matrix ────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
        <div class="card-body p-0">
            {{-- ─── Skeleton Loading ────────────────────────── --}}
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
                                    IN</th>
                                <th
                                    class="text-[9px] font-black text-center border-b border-r border-base-200 p-1 bg-base-200/30">
                                    OUT</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < ($perPage > 10 ? 10 : $perPage); $i++)
                            <tr
                                @if ($readyToLoad) wire:loading wire:target="perPage, selectedOpd, search, applyFilter, resetFilters, gotoPage, nextPage, previousPage" @endif>
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
                        {{-- ─── Real Table Data ────────────────────────────────────────── --}}
                        @if ($readyToLoad)
                            @php
                                $currentOpd = null;
                                $isSuperAdmin = auth()->user()->hasRole('super-admin');
                            @endphp
                            @forelse ($this->personnels as $p)
                                @if ($isSuperAdmin && $currentOpd !== $p->opd_id)
                                    <tr class="bg-base-200" wire:key="opd-header-{{ $p->opd_id }}"
                                        wire:loading.remove
                                        wire:target="perPage, selectedOpd, search, applyFilter, resetFilters, gotoPage, nextPage, previousPage">
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
                                <tr class="group" wire:key="personnel-row-{{ $p->id }}" wire:loading.remove
                                    wire:target="perPage, selectedOpd, search, applyFilter, resetFilters, gotoPage, nextPage, previousPage">
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
                                                    @if ($p->attendance_type === 'FLEXIBLE')
                                                        <span
                                                            class="px-1 py-0.5 rounded bg-warning/10 text-warning text-[8px] font-bold border border-warning/20 leading-none">
                                                            FLEX
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
                                                if (in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALPA', 'CUTI'])) {
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
                                                if (in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALPA', 'CUTI'])) {
                                                    $cellClassM .= ' bg-neutral/10';
                                                } elseif ($a->status_masuk === 'HADIR') {
                                                    $cellClassM .= ' bg-success/10';
                                                } elseif ($a->status_masuk === 'TELAT') {
                                                    $cellClassM .= ' bg-error/10';
                                                } elseif ($j && $j->shift && $j->shift->type === 'off') {
                                                    // Handled below via inline style for dynamic color
                                                } elseif ($a->status === 'LIBUR') {
                                                    $cellClassM .= ' bg-base-200/50';
                                                }
                                            } elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday) {
                                                $cellClassM .= ' bg-base-300/30';
                                            }
                                            $cellStyleM =
                                                $a && $j && $j->shift && $j->shift->type === 'off'
                                                    ? "background-color: {$j->shift->color}20;"
                                                    : '';
                                        @endphp
                                        <td wire:key="cell-in-{{ $p->id }}-{{ $date }}"
                                            wire:click="editAbsensi({{ $p->id }}, '{{ $date }}', 'in')"
                                            wire:loading.class="opacity-40 pointer-events-none"
                                            wire:target="editAbsensi({{ $p->id }}, '{{ $date }}', 'in')"
                                            class="{{ $cellClassM }}" style="{{ $cellStyleM }}">
                                            <div class="relative w-full h-full flex items-center justify-center">
                                                {{-- Specific Cell Loader --}}
                                                <div wire:loading
                                                    wire:target="editAbsensi({{ $p->id }}, '{{ $date }}', 'in')"
                                                    class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 flex items-center justify-center z-20">
                                                    <span
                                                        class="loading loading-spinner loading-xs text-primary"></span>
                                                </div>



                                                @if ($a)
                                                    @if ($j && $j->shift && $j->shift->type === 'off')
                                                        <span class="text-[10px] font-black"
                                                            style="color: {{ $j->shift->color }};">{{ $j->shift->name }}</span>
                                                    @elseif ($a->status === 'LIBUR')
                                                        <span class="text-[10px] font-black opacity-30">LIBUR</span>
                                                    @elseif ($a->jam_masuk)
                                                        <div class="flex flex-col items-center justify-center">
                                                            <div
                                                                class="text-[11px] font-black leading-tight {{ $a->status_masuk === 'TELAT' ? 'text-error' : 'text-success' }}">
                                                                {{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') }}
                                                            </div>
                                                            <div class="text-[8px] font-black uppercase opacity-60">
                                                                {{ $a->status_masuk }}</div>
                                                            {{-- Indikator Radius Masuk --}}
                                                            {{-- Baris Ikon (Radius & Edit) --}}
                                                            <div class="flex items-center justify-center gap-1 mt-0.5">
                                                                {{-- Indikator Radius Masuk --}}
                                                                <div class="tooltip tooltip-primary flex justify-center"
                                                                    data-tip="{{ $a->is_within_radius ? 'Dalam Radius' : 'Luar Radius' }} ({{ number_format($a->jarak_meter, 0) }}m)">
                                                                    @if ($a->is_within_radius)
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="size-3 text-success"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                                            <path
                                                                                d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0" />
                                                                        </svg>
                                                                    @else
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="size-3 text-error animate-pulse"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M9.442 9.432a3 3 0 0 0 4.113 4.134m1.445 -2.566a3 3 0 0 0 -3 -3" />
                                                                            <path
                                                                                d="M17.152 17.162l-3.738 3.738a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 0 1 -.476 -10.794m2.18 -1.82a8.003 8.003 0 0 1 10.91 10.912" />
                                                                            <path d="M3 3l18 18" />
                                                                        </svg>
                                                                    @endif
                                                                </div>

                                                                {{-- Indikator Edit Manual Masuk --}}
                                                                @if ($a->original_status_masuk)
                                                                    <div class="tooltip tooltip-primary flex justify-center"
                                                                        data-tip="Diedit oleh Sistem/Admin (Asli: {{ $a->original_status_masuk }})">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="size-3 text-primary"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                                            <path
                                                                                d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415" />
                                                                            <path d="M16 5l3 3" />
                                                                        </svg>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-[9px] font-black {{ $a->status_masuk === 'ALPA' ? 'text-error' : 'text-neutral' }}">{{ $a->status_masuk ?: $a->status }}</span>
                                                    @endif
                                                @elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday)
                                                    <span class="text-[10px] font-black text-error">ALPA</span>
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
                                                if (in_array($a->status_pulang, ['SAKIT', 'IZIN', 'ALPA', 'CUTI'])) {
                                                    $cellClassP .= ' bg-neutral/10';
                                                } elseif ($a->status_pulang === 'HADIR') {
                                                    $cellClassP .= ' bg-success/10';
                                                } elseif ($a->status_pulang === 'PC') {
                                                    $cellClassP .= ' bg-warning/10';
                                                } elseif ($j && $j->shift && $j->shift->type === 'off') {
                                                    // Handled via inline style
                                                } elseif ($a->status === 'LIBUR') {
                                                    $cellClassP .= ' bg-base-200/50';
                                                }
                                            } elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday) {
                                                $cellClassP .= ' bg-base-300/30';
                                            }
                                            $cellStyleP =
                                                $a && $j && $j->shift && $j->shift->type === 'off'
                                                    ? "background-color: {$j->shift->color}20;"
                                                    : '';
                                        @endphp
                                        <td wire:key="cell-out-{{ $p->id }}-{{ $date }}"
                                            wire:click="editAbsensi({{ $p->id }}, '{{ $date }}', 'out')"
                                            wire:loading.class="opacity-40 pointer-events-none"
                                            wire:target="editAbsensi({{ $p->id }}, '{{ $date }}', 'out')"
                                            class="{{ $cellClassP }}" style="{{ $cellStyleP }}">
                                            <div class="relative w-full h-full flex items-center justify-center">
                                                {{-- Specific Cell Loader --}}
                                                <div wire:loading
                                                    wire:target="editAbsensi({{ $p->id }}, '{{ $date }}', 'out')"
                                                    class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 flex items-center justify-center z-20">
                                                    <span
                                                        class="loading loading-spinner loading-xs text-primary"></span>
                                                </div>



                                                @if ($a)
                                                    @if ($j && $j->shift && $j->shift->type === 'off')
                                                        <span class="text-[10px] font-black"
                                                            style="color: {{ $j->shift->color }};">{{ $j->shift->name }}</span>
                                                    @elseif ($a->status === 'LIBUR')
                                                        <span class="text-[10px] font-black opacity-30">LIBUR</span>
                                                    @elseif ($a->jam_pulang)
                                                        <div class="flex flex-col items-center justify-center">
                                                            <div
                                                                class="text-[11px] font-black leading-tight {{ $a->status_pulang === 'PC' ? 'text-warning' : 'text-success' }}">
                                                                {{ \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') }}
                                                            </div>
                                                            <div class="text-[8px] font-black uppercase opacity-60">
                                                                {{ $a->status_pulang }}</div>
                                                            {{-- Baris Ikon (Radius & Edit) --}}
                                                            <div class="flex items-center justify-center gap-1 mt-0.5">
                                                                {{-- Indikator Radius Pulang --}}
                                                                <div class="tooltip tooltip-primary flex justify-center"
                                                                    data-tip="{{ $a->is_within_radius_pulang ? 'Dalam Radius' : 'Luar Radius' }} ({{ number_format($a->jarak_meter_pulang, 0) }}m)">
                                                                    @if ($a->is_within_radius_pulang)
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="size-3 text-success"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                                            <path
                                                                                d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0" />
                                                                        </svg>
                                                                    @else
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="size-3 text-error animate-pulse"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M9.442 9.432a3 3 0 0 0 4.113 4.134m1.445 -2.566a3 3 0 0 0 -3 -3" />
                                                                            <path
                                                                                d="M17.152 17.162l-3.738 3.738a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 0 1 -.476 -10.794m2.18 -1.82a8.003 8.003 0 0 1 10.91 10.912" />
                                                                            <path d="M3 3l18 18" />
                                                                        </svg>
                                                                    @endif
                                                                </div>

                                                                {{-- Indikator Edit Manual Pulang --}}
                                                                @if ($a->original_status_pulang)
                                                                    <div class="tooltip tooltip-primary flex justify-center"
                                                                        data-tip="Diedit oleh Sistem/Admin (Asli: {{ $a->original_status_pulang }})">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="size-3 text-primary"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                                            <path
                                                                                d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415" />
                                                                            <path d="M16 5l3 3" />
                                                                        </svg>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-[9px] font-black {{ $a->status_pulang === 'ALPA' ? 'text-error' : 'text-neutral' }}">{{ $a->status_pulang ?: $a->status }}</span>
                                                    @endif
                                                @elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday)
                                                    <span class="text-[10px] font-black text-error">ALPA</span>
                                                @elseif ($j)
                                                    <div class="opacity-20 text-[8px] font-black">
                                                        {{ $j->shift?->name ?? $j->status }}</div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr wire:loading.remove
                                    wire:target="perPage, selectedOpd, search, applyFilter, resetFilters, gotoPage, nextPage, previousPage">
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
                    {{ $this->personnels->links('components.admin.pagination') }}
                @endif
            </div>
        </div>
    </div>
    </div>

    {{-- ─── Rekap Dokumentasi Konsumsi Placeholder ─────────────────────────── --}}
    @can('lihat-dokumentasi-konsumsi')
    <div x-show="activeTab === 'konsumsi'" x-cloak class="bg-base-100 rounded-2xl border border-base-200 p-12 text-center my-6">
        <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h3 class="text-base font-bold text-base-content">Rekap Dokumentasi Konsumsi</h3>
        <p class="text-sm text-base-content/60 max-w-md mx-auto mt-1">
            Menu dokumentasi konsumsi sedang disiapkan.
        </p>
    </div>
    @endcan

    <livewire:admin::absensi-edit-modal />

    {{-- ─── Modal Konfirmasi Download (PDF & Excel) ────────────────────────── --}}
    <dialog class="modal modal-bottom sm:modal-middle backdrop-blur-xs z-99999"
        :class="{ 'modal-open': showExportModal }">
        <div class="modal-box max-w-lg rounded-2xl shadow-2xl border border-base-200">
            {{-- Header Modal --}}
            <div class="flex items-start justify-between pb-3 border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="text-base-content">
                        <template x-if="exportStatus === 'success'">
                            <div class="size-9 rounded-xl bg-success/15 text-success flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="exportStatus === 'error'">
                            <div class="size-9 rounded-xl bg-error/15 text-error flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </template>
                        <template x-if="exportStatus === 'processing'">
                            <div class="size-9 rounded-xl bg-primary/15 text-primary flex items-center justify-center">
                                <span class="loading loading-spinner loading-sm text-primary"></span>
                            </div>
                        </template>
                        <template x-if="exportStatus === 'idle'">
                            <div>
                                <template x-if="exportType === 'pdf'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                                        <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                        <path d="M17 18h2" />
                                        <path d="M20 15h-3v6" />
                                        <path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1" />
                                    </svg>
                                </template>
                                <template x-if="exportType === 'excel'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path
                                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                                        <path d="M8 11h8v7h-8l0 -7" />
                                        <path d="M8 15h8" />
                                        <path d="M11 11v7" />
                                    </svg>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content"
                            x-text="exportStatus === 'processing' ? 'Memproses Dokumen' : (exportStatus === 'success' ? 'Export Berhasil' : (exportStatus === 'error' ? 'Export Gagal' : 'Konfirmasi Download'))">
                        </h3>
                        <p class="text-xs text-base-content/60"
                            x-text="exportStatus === 'processing' ? 'Mohon tunggu sejenak hingga file selesai dibuat' : (exportStatus === 'success' ? 'File laporan absensi siap digunakan' : (exportStatus === 'error' ? 'Terjadi kendala saat memproses dokumen' : (exportType === 'pdf' ? 'Unduh Rekap Absensi format PDF (.pdf)' : 'Unduh Rekap Absensi format Excel (.xlsx)')))">
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeExportModal()" :disabled="exportStatus === 'processing'"
                    class="btn btn-sm btn-ghost btn-circle text-base-content/50 hover:text-base-content disabled:opacity-30">✕</button>
            </div>

            {{-- Body Modal --}}
            <div class="py-4 text-sm">
                {{-- State: Idle (Form Pengaturan Export) --}}
                <div x-show="exportStatus === 'idle'" class="space-y-4">
                    {{-- Info Ringkasan Export --}}
                    <div class="bg-base-200/60 rounded-xl p-3 space-y-2 border border-base-200">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-base-content/60">Format File:</span>
                            <span class="font-bold uppercase"
                                x-text="exportType === 'pdf' ? 'PDF Document' : 'Excel Spreadsheet'"></span>
                        </div>

                        @if ($search)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-base-content/60">Filter Nama:</span>
                                <span
                                    class="font-semibold text-primary truncate max-w-50">"{{ $search }}"</span>
                            </div>
                        @endif
                    </div>

                    {{-- Target OPD --}}
                    @if (auth()->user()->hasRole('super-admin'))
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-base-content/70">
                                Target OPD
                            </label>
                            <select x-model="exportOpdId" class="select select-bordered w-full text-xs bg-base-100">
                                <option value="">Semua OPD</option>
                                @foreach ($this->opds as $opd)
                                    <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-base-content/70">
                                Target OPD
                            </label>
                            <input type="text" value="{{ auth()->user()->opd()?->name ?? 'OPD Anda' }}" disabled
                                class="input input-bordered w-full text-xs bg-base-200/60 text-base-content/70 cursor-not-allowed" />
                        </div>
                    @endif

                    {{-- Ubah Range Tanggal --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-base-content/70">
                            Atur Periode Tanggal
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="label text-xs py-1 text-base-content/60 font-medium">Dari Tanggal</label>
                                <input type="date" x-model="exportStartDate"
                                    class="input input-bordered w-full text-xs scheme-light dark:scheme-dark" />
                            </div>
                            <div>
                                <label class="label text-xs py-1 text-base-content/60 font-medium">Sampai
                                    Tanggal</label>
                                <input type="date" x-model="exportEndDate"
                                    class="input input-bordered w-full text-xs scheme-light dark:scheme-dark" />
                            </div>
                        </div>
                        <p class="text-[11px] text-base-content/50 italic flex items-center gap-1 mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Rentang tanggal maksimal 31 hari.
                        </p>
                    </div>

                    {{-- Pilih Shift (Opsional) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-base-content/70">
                                Pilih Shift (Opsional)
                            </label>
                            <template x-if="selectedShifts.length > 0">
                                <span class="text-[11px] font-semibold text-primary"
                                    x-text="selectedShifts.length + ' shift dipilih'"></span>
                            </template>
                        </div>
                        <div
                            class="border border-base-200 rounded-xl overflow-hidden max-h-48 overflow-y-auto bg-base-100 shadow-inner">
                            <table class="table table-xs w-full text-left border-collapse">
                                <thead
                                    class="bg-base-200 sticky top-0 z-10 text-[10px] uppercase text-base-content/70">
                                    <tr class="border-b border-base-200">
                                        <th class="w-8 text-center p-2"></th>
                                        <th class="p-2">Shift</th>
                                        <th class="p-2">Keterangan</th>
                                        <th class="p-2">Konsumsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-200 text-xs">
                                    @forelse ($this->shifts as $shift)
                                        @php
                                            $konsumsiNames = $shift->konsumsis->pluck('nama')->filter()->values();
                                            $konsumsiText = $konsumsiNames->isEmpty()
                                                ? '-'
                                                : $konsumsiNames->implode(', ');
                                        @endphp
                                        <tr class="hover:bg-base-200/50 cursor-pointer transition-colors"
                                            :class="{ 'bg-primary/5': selectedShifts.includes({{ $shift->id }}) }"
                                            @click="if (selectedShifts.includes({{ $shift->id }})) { selectedShifts = selectedShifts.filter(id => id !== {{ $shift->id }}) } else { selectedShifts.push({{ $shift->id }}) }">
                                            <td class="text-center p-2" @click.stop>
                                                <input type="checkbox" :value="{{ $shift->id }}"
                                                    x-model.number="selectedShifts"
                                                    class="checkbox checkbox-xs checkbox-primary" />
                                            </td>
                                            <td class="font-bold p-2 text-base-content">{{ $shift->name }}</td>
                                            <td class="p-2 text-base-content/70">{{ $shift->keterangan ?: '-' }}</td>
                                            <td class="p-2 text-base-content/70">
                                                @if ($konsumsiNames->isEmpty())
                                                    <span class="text-base-content/40">-</span>
                                                @else
                                                    <span
                                                        class="badge badge-ghost badge-xs font-normal">{{ $konsumsiText }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-xs text-base-content/50">
                                                Tidak ada data shift
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[11px] text-base-content/50 italic">
                            Shift yang diceklis tidak akan dihitung pada kolom H (Hadir) dan ditandai dengan *H.
                        </p>
                    </div>

                    {{-- Opsi Ukuran Kertas (Khusus PDF) --}}
                    <template x-if="exportType === 'pdf'">
                        <div class="space-y-1.5 pt-1">
                            <label class="block text-xs font-bold text-base-content/70">
                                Ukuran Kertas (PDF)
                            </label>
                            <select x-model="exportPaperSize" class="select select-bordered w-full text-xs">
                                <option value="a4">Kertas A4</option>
                                <option value="f4">Kertas F4 / Folio</option>
                                <option value="legal">Kertas Legal</option>
                            </select>
                        </div>
                    </template>
                </div>

                {{-- State: Processing (Progress Bar Realtime) --}}
                <div x-show="exportStatus === 'processing'"
                    class="py-6 flex flex-col items-center justify-center text-center space-y-5">
                    <div
                        class="size-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                        <span class="loading loading-spinner loading-lg text-primary"></span>
                    </div>
                    <div class="w-full space-y-2.5 max-w-md mx-auto">
                        <div class="flex justify-between items-center text-xs font-semibold px-1">
                            <span class="text-base-content/80 text-left" x-text="exportStatusText"></span>
                            <span class="text-primary font-bold tabular-nums text-sm shrink-0 ml-2"
                                x-text="exportProgress + '%'"></span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-3 overflow-hidden p-0.5 shadow-inner">
                            <div class="bg-primary h-full transition-all duration-300 ease-out rounded-full shadow"
                                :style="`width: ${exportProgress}%`"></div>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60 max-w-xs">
                        Sistem sedang menyusun rekap data absensi personel. File akan otomatis diunduh saat selesai.
                    </p>
                </div>

                {{-- State: Success (File Saved & Ready) --}}
                <div x-show="exportStatus === 'success'" class="py-2 space-y-4">
                    <div class="bg-success/10 border border-success/30 rounded-2xl p-4 flex items-center gap-3.5">
                        <div
                            class="size-11 rounded-xl bg-success text-success-content flex items-center justify-center shrink-0 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="space-y-0.5 text-left">
                            <h4 class="font-bold text-sm text-base-content">File Berhasil Digenerate!</h4>
                            <p class="text-xs text-base-content/70">
                                File telah disimpan dan otomatis diunduh ke folder Downloads.
                            </p>
                        </div>
                    </div>

                    <div class="bg-base-200/60 rounded-2xl p-4 border border-base-200 space-y-2">
                        <div class="space-y-1 text-left">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-base-content/50">Nama
                                File:</span>
                            <div class="flex items-center gap-2">
                                <span
                                    class="font-mono text-xs font-bold text-base-content break-all bg-base-100 px-3 py-2 rounded-lg border border-base-200 w-full select-all"
                                    x-text="exportedFilename"></span>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-base-content/50 italic text-center">
                        Jika file tidak otomatis terunduh di browser, klik tombol <strong>Unduh Ulang</strong> di bawah.
                    </p>
                </div>

                {{-- State: Error --}}
                <div x-show="exportStatus === 'error'" class="py-2 space-y-4">
                    <div class="bg-error/10 border border-error/30 rounded-2xl p-4 flex items-start gap-3.5">
                        <div
                            class="size-11 rounded-xl bg-error text-error-content flex items-center justify-center shrink-0 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-1 text-left">
                            <h4 class="font-bold text-sm text-base-content">Export Gagal Diproses</h4>
                            <p class="text-xs text-base-content/70" x-text="exportErrorMessage"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Aksi Modal --}}
            <div class="modal-action pt-3 border-t border-base-200">
                <template x-if="exportStatus === 'idle'">
                    <div class="flex items-center justify-end gap-2 w-full">
                        <button type="button" @click="closeExportModal()"
                            class="btn btn-ghost text-base-content/70">
                            Batal
                        </button>
                        <button type="button" @click="startExport()"
                            class="btn btn-neutral text-white gap-2 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download</span>
                        </button>
                    </div>
                </template>

                <template x-if="exportStatus === 'processing'">
                    <div class="flex items-center justify-end w-full">
                        <button type="button" disabled
                            class="btn btn-neutral text-white gap-2 opacity-75 cursor-not-allowed">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>Memproses Dokumen...</span>
                        </button>
                    </div>
                </template>

                <template x-if="exportStatus === 'success'">
                    <div class="flex items-center justify-between w-full">
                        <button type="button" @click="triggerRedownload()"
                            class="btn btn-outline btn-neutral gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Ulang</span>
                        </button>
                        <button type="button" @click="closeExportModal()" class="btn btn-neutral text-white">
                            Selesai
                        </button>
                    </div>
                </template>

                <template x-if="exportStatus === 'error'">
                    <div class="flex items-center justify-end gap-2 w-full">
                        <button type="button" @click="exportStatus = 'idle'"
                            class="btn btn-ghost text-base-content/70">
                            Kembali
                        </button>
                        <button type="button" @click="startExport()" class="btn btn-error text-white gap-2">
                            Coba Lagi
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Backdrop --}}
        <div class="modal-backdrop bg-neutral/40" @click="exportStatus !== 'processing' && closeExportModal()"></div>
    </dialog>

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

    <script>
        function absensiAdminComponent() {
            return {
                activeTab: 'absensi',
                previewUrl: null,
                showPreview: false,
                previewX: 0,
                previewY: 0,
                triggerPreview(url, event) {
                    this.previewUrl = url;
                    this.showPreview = true;
                    this.previewX = event.clientX;
                    this.previewY = event.clientY;
                },
                hidePreview() {
                    this.showPreview = false;
                },
                showExportModal: false,
                exportType: 'pdf',
                exportStartDate: '{{ $startDate }}',
                exportEndDate: '{{ $endDate }}',
                exportPaperSize: '{{ $paperSize }}',
                exportOpdId: '{{ $selectedOpd }}',
                selectedShifts: [],
                exportStatus: 'idle',
                exportProgress: 0,
                exportStatusText: '',
                exportedFilename: '',
                exportErrorMessage: '',
                downloadBlobUrl: null,
                openExportModal(type) {
                    this.exportType = type;
                    const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                    this.exportStartDate = (wire && wire.get('startDate')) || '{{ $startDate }}';
                    this.exportEndDate = (wire && wire.get('endDate')) || '{{ $endDate }}';
                    this.exportPaperSize = (wire && wire.get('paperSize')) || '{{ $paperSize }}';
                    this.exportOpdId = (wire && wire.get('selectedOpd')) || '';
                    this.selectedShifts = [];
                    this.exportStatus = 'idle';
                    this.exportProgress = 0;
                    this.exportStatusText = '';
                    this.exportedFilename = '';
                    this.exportErrorMessage = '';
                    if (this.downloadBlobUrl) {
                        URL.revokeObjectURL(this.downloadBlobUrl);
                        this.downloadBlobUrl = null;
                    }
                    this.showExportModal = true;
                },
                closeExportModal() {
                    this.showExportModal = false;
                    setTimeout(() => {
                        if (this.downloadBlobUrl) {
                            URL.revokeObjectURL(this.downloadBlobUrl);
                            this.downloadBlobUrl = null;
                        }
                        this.exportStatus = 'idle';
                        this.exportProgress = 0;
                        this.exportStatusText = '';
                        this.exportedFilename = '';
                        this.exportErrorMessage = '';
                    }, 300);
                },
                async startExport() {
                    this.exportStatus = 'processing';
                    this.exportProgress = 10;
                    this.exportStatusText = 'Menghubungkan ke server & menyiapkan data...';
                    this.exportErrorMessage = '';

                    let progressTimer = setInterval(() => {
                        if (this.exportProgress < 40) {
                            this.exportProgress += 10;
                            this.exportStatusText = 'Mengumpulkan data absensi & shift personel...';
                        } else if (this.exportProgress < 75) {
                            this.exportProgress += 5;
                            this.exportStatusText = this.exportType === 'pdf' ?
                                'Menyusun halaman dokumen PDF...' : 'Menyusun lembar kerja Excel...';
                        } else if (this.exportProgress < 90) {
                            this.exportProgress += 2;
                            this.exportStatusText = 'Menyimpan & menyelesaikan file...';
                        }
                    }, 250);

                    try {
                        const response = await fetch(this.exportUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': this.exportType === 'pdf' ? 'application/pdf' :
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream',
                            }
                        });

                        clearInterval(progressTimer);

                        if (!response.ok) {
                            let errorMsg = 'Gagal mengekspor file (' + response.status + ' ' + response.statusText +
                            ')';
                            try {
                                const errData = await response.json();
                                if (errData.message) errorMsg = errData.message;
                            } catch (e) {}
                            throw new Error(errorMsg);
                        }

                        this.exportProgress = 95;
                        this.exportStatusText = 'Menyiapkan unduhan...';

                        let filename = response.headers.get('X-Filename');
                        const disposition = response.headers.get('Content-Disposition');
                        if (!filename && disposition) {
                            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '').trim();
                            }
                        }
                        if (!filename) {
                            const formatIndo = (d) => {
                                if (!d) return '';
                                const parts = d.split('-');
                                return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : d;
                            };
                            const s = formatIndo(this.exportStartDate);
                            const e = formatIndo(this.exportEndDate);
                            filename = 'rekap_absensi_' + (s && e ? `${s}_${e}` : (s || '')) + '.' + (this.exportType === 'pdf' ? 'pdf' : 'xlsx');
                        }

                        const blob = await response.blob();
                        this.exportProgress = 100;
                        this.exportStatusText = 'Selesai!';

                        const blobUrl = window.URL.createObjectURL(blob);
                        this.downloadBlobUrl = blobUrl;
                        this.exportedFilename = filename;

                        // Trigger browser download without opening a new tab
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);

                        this.exportStatus = 'success';
                    } catch (err) {
                        clearInterval(progressTimer);
                        this.exportStatus = 'error';
                        this.exportErrorMessage = err.message || 'Terjadi kesalahan saat memproses export.';
                    }
                },
                triggerRedownload() {
                    if (this.downloadBlobUrl && this.exportedFilename) {
                        const a = document.createElement('a');
                        a.href = this.downloadBlobUrl;
                        a.download = this.exportedFilename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }
                },
                get exportUrl() {
                    let base = this.exportType === 'pdf' ?
                        '{{ route('absensi.export-pdf') }}' :
                        '{{ route('absensi.export-excel') }}';
                    let params = new URLSearchParams();
                    const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                    let s = wire ? wire.get('search') : null;
                    if (s) params.append('search', s);
                    if (this.exportOpdId) params.append('opd_id', this.exportOpdId);
                    params.append('startDate', this.exportStartDate);
                    params.append('endDate', this.exportEndDate);
                    if (this.exportType === 'pdf') {
                        params.append('paperSize', this.exportPaperSize);
                    }
                    if (this.selectedShifts.length > 0) {
                        this.selectedShifts.forEach(id => {
                            params.append('excluded_shifts[]', id);
                        });
                    }
                    return base + '?' + params.toString();
                }
            };
        }
    </script>
</div>
