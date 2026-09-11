<div x-data="konsumsiAdminComponent()" wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Dokumentasi Konsumsi</h1>
            <p class="text-sm text-base-content/60 mt-1">Rekap dan Dokumentasi Konsumsi Makan Minum Personil</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li><a href="{{ route('absensi') }}">Rekap Absensi</a></li>
                <li>
                    <span class="text-base-content font-bold">Dokumentasi Konsumsi</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Tab Navigation Menu ─────────────────────────────────────────────── --}}
    <div class="border-b border-base-300 mb-6">
        <nav class="-mb-px flex space-x-6 sm:space-x-8" aria-label="Tabs">
            <a href="{{ route('absensi') }}" wire:navigate
                class="inline-flex items-center gap-2 py-3 px-1 border-b-4 text-sm transition-all border-transparent text-base-content/60 hover:text-base-content hover:border-base-300 font-medium cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Rekap Absensi</span>
            </a>

            <a href="{{ route('dokumentasi-konsumsi') }}"
                class="inline-flex items-center gap-2 py-3 px-1 border-b-4 text-sm transition-all border-primary text-primary font-bold cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Rekap Dokumentasi Konsumsi</span>
            </a>
        </nav>
    </div>

    {{-- Tombol action tambah dokumentasi konsumsi --}}
    <div class="flex flex-col gap-4 mb-6">
        <div class="flex flex-wrap gap-2 justify-start">
            <button type="button" wire:click="openAddKonsumsiModal"
                class="btn btn-sm md:btn-md btn-primary text-white gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
                <span>Upload Dokumentasi</span>
            </button>
        </div>
    </div>

    {{-- ─── Matrix Toolbar ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-4 mb-6">
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
                    <select wire:model.live="paperSize"
                        class="select select-bordered select-sm md:select-md join-item">
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
            </div>
        </div>
    </div>

    @php
        $dates = $this->dates;
        $summary = $this->monthlySummary;
        $firstDate = !empty($dates) ? \Carbon\Carbon::parse($dates[0]) : \Carbon\Carbon::now();
        $dokMap = $this->dokumentasiMap;
    @endphp

    {{-- ─── TABEL 1: JUMLAH KONSUMSI PER BULAN ──────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden mb-6">
        <div class="card-body p-0">
            <div
                class="px-4 pt-4 pb-2 border-base-200 flex flex-wrap items-center justify-between gap-3 bg-base-200/20">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-sm md:text-base text-base-content uppercase">
                        Tabel Jumlah Konsumsi Per Bulan ({{ $firstDate->translatedFormat('F Y') }})
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-warning font-bold text-xs gap-1 py-3 px-3 shadow-xs">
                        Siang: {{ number_format($summary['totalSiang']) }}
                    </span>
                    <span class="badge bg-neutral text-white font-bold text-xs gap-1 py-3 px-3 shadow-xs">
                        Malam: {{ number_format($summary['totalMalam']) }}
                    </span>
                    <span class="badge badge-neutral font-bold text-xs gap-1 py-3 px-3 shadow-xs">
                        Total: {{ number_format($summary['grandTotal']) }} Porsi
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto">
                <table class="table table-sm w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-30 bg-base-100 shadow-xs">
                        <tr>
                            <th
                                class="sticky left-0 z-40 bg-base-100 border-b border-r border-t border-base-200 min-w-44 text-left align-middle font-black text-xs uppercase px-4">
                                {{ $firstDate->translatedFormat('F Y') }}
                            </th>
                            @foreach ($dates as $date)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $isWeekend = $carbonDate->isWeekend();
                                    $isToday = $carbonDate->isToday();
                                @endphp
                                <th
                                    class="text-center border-b border-r border-t border-base-200 min-w-16 p-1.5 {{ $isToday ? 'bg-primary/10' : ($isWeekend ? 'bg-error/5 text-error' : '') }}">
                                    <div class="text-[10px] uppercase opacity-60 leading-none mb-1">
                                        {{ $carbonDate->translatedFormat('D') }}
                                    </div>
                                    <div class="text-xs font-bold">
                                        {{ $carbonDate->format('d/m') }}
                                    </div>
                                </th>
                            @endforeach
                            <th
                                class="text-center border-b border-t border-base-200 min-w-24 bg-base-200/50 align-middle font-black text-xs uppercase">
                                TOTAL
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$readyToLoad)
                            <tr>
                                <td colspan="{{ count($dates) + 2 }}" class="text-center py-8">
                                    <span class="loading loading-spinner loading-md text-primary"></span>
                                    <p class="text-xs text-base-content/60 mt-2">Memuat data konsumsi...</p>
                                </td>
                            </tr>
                        @else
                            {{-- Baris 1: SIANG --}}
                            <tr class="hover:bg-warning/5 transition-colors">
                                <td
                                    class="sticky left-0 z-20 bg-base-100 border-b border-r border-base-200 font-bold text-xs py-3 px-4 align-middle">
                                    SIANG
                                </td>
                                @foreach ($dates as $date)
                                    @php
                                        $countSiang = $summary['daily'][$date]['siang'] ?? 0;
                                        $isToday = \Carbon\Carbon::parse($date)->isToday();
                                        $dok = $dokMap->get($date);
                                        $fotoSiang = $dok?->foto_siang;
                                    @endphp
                                    <td
                                        class="text-center border-b border-r border-base-200 p-0 font-bold text-xs {{ $isToday ? 'bg-primary/5' : '' }} align-middle relative">
                                        @if ($fotoSiang)
                                            <div wire:click="openEditKonsumsiModal('{{ $date }}', 'siang')"
                                                role="button" tabindex="0"
                                                title="Edit Dokumentasi Siang ({{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}) - Klik untuk mengedit"
                                                class="relative w-full h-12 flex items-center justify-center overflow-hidden group cursor-pointer hover:opacity-90 select-none">
                                                {{-- Background Foto --}}
                                                <img src="{{ asset('storage/' . $fotoSiang) }}"
                                                    alt="Dokumentasi Siang"
                                                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                    loading="lazy" />

                                                {{-- Overlay Gelap Transparan agar angka tetap kontras & jelas --}}
                                                <div
                                                    class="absolute inset-0 bg-black/45 group-hover:bg-black/30 transition-colors">
                                                </div>

                                                {{-- Icon Ceklist Melayang di Sudut Kanan Atas --}}
                                                <div class="absolute top-1 right-1 z-2 pointer-events-none">
                                                    <span
                                                        class="inline-flex items-center justify-center size-3.5 rounded-full bg-success text-white shadow-xs"
                                                        title="Dokumentasi sudah tersedia">
                                                        <svg class="size-2.5 stroke-3" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </span>
                                                </div>

                                                {{-- Angka Porsi di Tengah --}}
                                                <div
                                                    class="relative z-3 w-full h-full flex flex-col items-center justify-center p-1">
                                                    @if ($countSiang > 0)
                                                        <span
                                                            class="inline-flex items-center justify-center size-6 rounded-full bg-warning text-warning-content text-xs font-black shadow-md ring-1 ring-white/40">
                                                            {{ $countSiang }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center justify-center size-5 rounded-full bg-black/60 text-white/90 text-[11px] font-bold ring-1 ring-white/20">
                                                            0
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div wire:click="openAddKonsumsiModal('{{ $date }}', 'siang')"
                                                role="button" tabindex="0"
                                                title="Tambah Dokumentasi Siang ({{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}) - Klik untuk menambah"
                                                class="relative w-full h-12 flex items-center justify-center p-1 group cursor-pointer hover:bg-warning/15 transition-colors select-none">
                                                {{-- Teks + Melayang di Sudut Kanan Atas --}}
                                                <div class="absolute top-1 right-1 z-2 pointer-events-none">
                                                    <span
                                                        class="inline-flex items-center justify-center size-3.5 rounded-full bg-base-200/80 text-base-content/50 group-hover:bg-warning group-hover:text-warning-content text-xs font-bold leading-none shadow-2xs transition-colors">
                                                        +
                                                    </span>
                                                </div>

                                                @if ($countSiang > 0)
                                                    <span
                                                        class="inline-flex items-center justify-center size-6 rounded-full bg-warning/20 text-warning-content text-xs font-bold group-hover:scale-105 transition-transform">
                                                        {{ $countSiang }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="text-base-content/25 text-xs group-hover:text-base-content/50 transition-colors">0</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                                <td
                                    class="text-center border-b border-base-200 p-2 font-black text-sm bg-warning/15 text-warning-content align-middle">
                                    {{ number_format($summary['totalSiang']) }}
                                </td>
                            </tr>

                            {{-- Baris 2: MALAM --}}
                            <tr class="hover:bg-neutral-900/5 transition-colors">
                                <td
                                    class="sticky left-0 z-20 bg-base-100 border-b border-r border-base-200 font-bold text-xs py-3 px-4 align-middle">
                                    MALAM
                                </td>
                                @foreach ($dates as $date)
                                    @php
                                        $countMalam = $summary['daily'][$date]['malam'] ?? 0;
                                        $isToday = \Carbon\Carbon::parse($date)->isToday();
                                        $dok = $dokMap->get($date);
                                        $fotoMalam = $dok?->foto_malam;
                                    @endphp
                                    <td
                                        class="text-center border-b border-r border-base-200 p-0 font-bold text-xs {{ $isToday ? 'bg-primary/5' : '' }} align-middle relative">
                                        @if ($fotoMalam)
                                            <div wire:click="openEditKonsumsiModal('{{ $date }}', 'malam')"
                                                role="button" tabindex="0"
                                                title="Edit Dokumentasi Malam ({{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}) - Klik untuk mengedit"
                                                class="relative w-full h-12 flex items-center justify-center overflow-hidden group cursor-pointer hover:opacity-90 select-none">
                                                {{-- Background Foto --}}
                                                <img src="{{ asset('storage/' . $fotoMalam) }}"
                                                    alt="Dokumentasi Malam"
                                                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                                    loading="lazy" />

                                                {{-- Overlay Gelap Transparan agar angka tetap kontras & jelas --}}
                                                <div
                                                    class="absolute inset-0 bg-black/45 group-hover:bg-black/30 transition-colors">
                                                </div>

                                                {{-- Icon Ceklist Melayang di Sudut Kanan Atas --}}
                                                <div class="absolute top-1 right-1 z-2 pointer-events-none">
                                                    <span
                                                        class="inline-flex items-center justify-center size-3.5 rounded-full bg-success text-white shadow-xs"
                                                        title="Dokumentasi sudah tersedia">
                                                        <svg class="size-2.5 stroke-3" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </span>
                                                </div>

                                                {{-- Angka Porsi di Tengah --}}
                                                <div
                                                    class="relative z-3 w-full h-full flex flex-col items-center justify-center p-1">
                                                    @if ($countMalam > 0)
                                                        <span
                                                            class="inline-flex items-center justify-center size-6 rounded-full bg-neutral-900 text-white text-xs font-black shadow-md ring-1 ring-white/40">
                                                            {{ $countMalam }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center justify-center size-5 rounded-full bg-black/60 text-white/90 text-[11px] font-bold ring-1 ring-white/20">
                                                            0
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div wire:click="openAddKonsumsiModal('{{ $date }}', 'malam')"
                                                role="button" tabindex="0"
                                                title="Tambah Dokumentasi Malam ({{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}) - Klik untuk menambah"
                                                class="relative w-full h-12 flex items-center justify-center p-1 group cursor-pointer hover:bg-neutral-900/10 transition-colors select-none">
                                                {{-- Teks + Melayang di Sudut Kanan Atas --}}
                                                <div class="absolute top-1 right-1 z-2 pointer-events-none">
                                                    <span
                                                        class="inline-flex items-center justify-center size-3.5 rounded-full bg-base-200/80 text-base-content/50 group-hover:bg-neutral-900 group-hover:text-white text-xs font-bold leading-none shadow-2xs transition-colors">
                                                        +
                                                    </span>
                                                </div>

                                                @if ($countMalam > 0)
                                                    <span
                                                        class="inline-flex items-center justify-center size-6 rounded-full bg-neutral-900/15 text-neutral-900 dark:text-neutral-100 text-xs font-bold group-hover:scale-105 transition-transform">
                                                        {{ $countMalam }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="text-base-content/25 text-xs group-hover:text-base-content/50 transition-colors">0</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                                <td
                                    class="text-center border-b border-base-200 p-2 font-black text-sm bg-neutral-900/10 text-neutral-900 dark:text-neutral-100 align-middle">
                                    {{ number_format($summary['totalMalam']) }}
                                </td>
                            </tr>

                            {{-- Baris TOTAL --}}
                            <tr class="bg-base-200/50 font-black">
                                <td
                                    class="sticky left-0 z-20 bg-base-200 border-b border-r border-base-200 text-xs py-3 px-4 uppercase text-base-content">
                                    TOTAL KONSUMSI
                                </td>
                                @foreach ($dates as $date)
                                    @php
                                        $countTotal = $summary['daily'][$date]['total'] ?? 0;
                                        $isToday = \Carbon\Carbon::parse($date)->isToday();
                                    @endphp
                                    <td
                                        class="text-center border-b border-r border-base-200 p-2 font-black text-xs {{ $isToday ? 'bg-primary/15' : '' }}">
                                        {{ $countTotal }}
                                    </td>
                                @endforeach
                                <td
                                    class="text-center border-b border-base-200 p-2 font-black text-sm bg-base-300 text-base-content">
                                    {{ number_format($summary['grandTotal']) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ─── TABEL 2: RINCIAN KONSUMSI PER PERSONEL ────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden mb-8">
        <div class="card-body p-0">
            <div class="px-4 pt-4 pb-2 flex flex-wrap items-center justify-between gap-3 bg-base-200/20">
                <div>
                    <h2 class="font-bold text-sm md:text-base text-base-content uppercase">
                        Rincian Konsumsi Per Personil
                    </h2>
                    <p class="text-xs text-base-content/60 mt-0.5">
                        Menampilkan jatah konsumsi personil berdasarkan kehadiran dan shift tugas
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs text-base-content/70">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-warning"></span> Siang (S)
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-neutral-900"></span> Malam (M)
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-success"></span> 24 Jam (S+M)
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)]">
                <table class="table table-sm w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-30 bg-base-100 shadow-xs">
                        <tr>
                            <th rowspan="2"
                                class="sticky left-0 z-40 bg-base-100 border-b border-t border-r border-base-200 min-w-56 text-left align-middle px-4 py-3">
                                <span class="font-bold text-xs uppercase">Personnel</span>
                            </th>
                            @foreach ($dates as $date)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $isToday = $carbonDate->isToday();
                                    $isWeekend = $carbonDate->isWeekend();
                                @endphp
                                <th rowspan="2"
                                    class="text-center border-b border-r border-t border-base-200 min-w-16 p-1.5 align-middle {{ $isToday ? 'bg-primary/10' : ($isWeekend ? 'bg-error/5 text-error' : '') }}">
                                    <div class="text-[10px] uppercase opacity-60 leading-none mb-1">
                                        {{ $carbonDate->translatedFormat('D') }}
                                    </div>
                                    <div class="text-xs font-bold">
                                        {{ $carbonDate->format('d/m') }}
                                    </div>
                                </th>
                            @endforeach
                            <th colspan="3"
                                class="text-center border-b border-t border-base-200 bg-base-200/50 p-1.5 font-bold text-xs">
                                TOTAL
                            </th>
                        </tr>
                        <tr>
                            <th
                                class="text-center border-b border-r border-base-200 bg-warning/10 text-warning-content font-bold text-[10px] p-1">
                                SIANG
                            </th>
                            <th
                                class="text-center border-b border-r border-base-200 bg-neutral-900/10 text-neutral-900 dark:text-neutral-100 font-bold text-[10px] p-1">
                                MALAM
                            </th>
                            <th class="text-center border-b border-base-200 bg-base-200 font-bold text-[10px] p-1">
                                JML
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$readyToLoad)
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td class="sticky left-0 z-20 bg-base-100 border-b border-r border-base-200 p-3">
                                        <div class="flex items-center gap-3 animate-pulse">
                                            <div class="size-8 rounded-full bg-base-300"></div>
                                            <div class="space-y-1.5 flex-1">
                                                <div class="h-3 bg-base-300 rounded w-24"></div>
                                                <div class="h-2 bg-base-200 rounded w-16"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($dates as $d)
                                        <td class="border-b border-r border-base-200 p-2 text-center">
                                            <div class="size-5 bg-base-200 rounded-full mx-auto animate-pulse"></div>
                                        </td>
                                    @endforeach
                                    <td class="border-b border-r border-base-200 bg-warning/5"></td>
                                    <td class="border-b border-r border-base-200 bg-neutral-900/5"></td>
                                    <td class="border-b border-base-200 bg-base-200/20"></td>
                                </tr>
                            @endfor
                        @else
                            @forelse ($this->personnels as $personnel)
                                <tr class="hover:bg-base-200/30 transition-colors">
                                    {{-- Kolom Sticky Personnel --}}
                                    <td
                                        class="sticky left-0 z-20 bg-base-100 border-b border-r border-base-200 px-4 py-2.5">
                                        <div class="flex items-center gap-3">
                                            <div class="avatar shrink-0">
                                                <div class="size-8 rounded-full ring-1 ring-base-300">
                                                    @if ($personnel->foto)
                                                        <img src="{{ asset('storage/' . $personnel->foto) }}"
                                                            alt="{{ $personnel->name }}" />
                                                    @else
                                                        <div
                                                            class="bg-primary/10 text-primary flex items-center justify-center font-bold text-xs h-full w-full">
                                                            {{ strtoupper(substr($personnel->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="min-w-0 max-w-44">
                                                <div class="font-bold text-xs text-base-content truncate"
                                                    title="{{ $personnel->name }}">
                                                    {{ $personnel->name }}
                                                </div>
                                                <div
                                                    class="flex items-center gap-1.5 text-[10px] text-base-content/60 mt-0.5">
                                                    @if ($personnel->regu)
                                                        <span class="badge badge-ghost badge-xs font-semibold">
                                                            {{ $personnel->regu }}
                                                        </span>
                                                    @endif
                                                    <span
                                                        class="truncate">{{ $personnel->penugasan?->name ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom Tanggal --}}
                                    @foreach ($dates as $date)
                                        @php
                                            $abs = $personnel->absensi_map->get($date);
                                            $jadwal = $personnel->jadwal_map->get($date);

                                            $isHadir =
                                                $abs &&
                                                ($abs->status === 'HADIR' ||
                                                    $abs->status === 'TELAT' ||
                                                    !empty($abs->jam_masuk));

                                            $isToday = \Carbon\Carbon::parse($date)->isToday();

                                            $cellType = 'none';
                                            if ($isHadir && $jadwal && $jadwal->shift) {
                                                $konsumsis = $jadwal->shift->konsumsis
                                                    ->pluck('nama')
                                                    ->map(fn($k) => strtolower(trim($k)))
                                                    ->toArray();
                                                $hasSiang = in_array('siang', $konsumsis);
                                                $hasMalam = in_array('malam', $konsumsis);

                                                if ($hasSiang && $hasMalam) {
                                                    $cellType = 'both';
                                                } elseif ($hasSiang) {
                                                    $cellType = 'siang';
                                                } elseif ($hasMalam) {
                                                    $cellType = 'malam';
                                                } else {
                                                    $cellType = 'hadir-no-meal';
                                                }
                                            } elseif (
                                                $abs &&
                                                in_array($abs->status, ['ALPA', 'IZIN', 'SAKIT', 'CUTI'])
                                            ) {
                                                $cellType = strtolower($abs->status);
                                            }
                                        @endphp
                                        <td
                                            class="text-center border-b border-r border-base-200 p-1.5 {{ $isToday ? 'bg-primary/5' : '' }}">
                                            @if ($cellType === 'both')
                                                <span
                                                    class="badge badge-success badge-xs font-black text-[9px] px-1.5 py-2 shadow-xs"
                                                    title="24 Jam (Siang & Malam)">
                                                    S+M
                                                </span>
                                            @elseif ($cellType === 'siang')
                                                <span
                                                    class="badge badge-warning badge-xs font-black text-[9px] px-1.5 py-2 shadow-xs"
                                                    title="Konsumsi Siang">
                                                    S
                                                </span>
                                            @elseif ($cellType === 'malam')
                                                <span
                                                    class="badge bg-neutral-900 text-white border-0 badge-xs font-black text-[9px] px-1.5 py-2 shadow-xs"
                                                    title="Konsumsi Malam">
                                                    M
                                                </span>
                                            @elseif ($cellType === 'hadir-no-meal')
                                                <span class="text-base-content/40 text-xs font-semibold"
                                                    title="Hadir (Tanpa Jatah Konsumsi)">
                                                    ✓
                                                </span>
                                            @elseif ($cellType === 'alpa')
                                                <span class="text-error font-bold text-[10px]" title="Alpa">A</span>
                                            @elseif ($cellType === 'izin')
                                                <span class="text-info font-bold text-[10px]" title="Izin">I</span>
                                            @elseif ($cellType === 'sakit')
                                                <span class="text-warning font-bold text-[10px]"
                                                    title="Sakit">S</span>
                                            @elseif ($cellType === 'cuti')
                                                <span class="text-purple-600 font-bold text-[10px]"
                                                    title="Cuti">C</span>
                                            @else
                                                <span class="text-base-content/20 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- Kolom Summary Personel --}}
                                    <td
                                        class="text-center border-b border-r border-base-200 p-2 font-bold text-xs bg-warning/5 text-warning-content">
                                        {{ $personnel->total_siang }}
                                    </td>
                                    <td
                                        class="text-center border-b border-r border-base-200 p-2 font-bold text-xs bg-neutral-900/5 text-neutral-900 dark:text-neutral-100">
                                        {{ $personnel->total_malam }}
                                    </td>
                                    <td
                                        class="text-center border-b border-base-200 p-2 font-black text-xs bg-base-200/40">
                                        {{ $personnel->total_konsumsi }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dates) + 4 }}"
                                        class="text-center py-10 text-base-content/60">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="size-10 mx-auto opacity-30 mb-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-medium text-sm">Tidak ada data personel ditemukan</p>
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            <div class="ps-4 pb-4 pr-4 pt-2 bg-base-50">
                @if ($readyToLoad)
                    {{ $this->personnels->links('components.admin.pagination') }}
                @endif
            </div>
        </div>
    </div>

    {{-- ─── MODAL EXPORT PDF ────────────────────────────────────────────────── --}}
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
                            <div class="size-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                                    <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                    <path d="M17 18h2" />
                                    <path d="M20 15h-3v6" />
                                    <path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1" />
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content"
                            x-text="exportStatus === 'processing' ? 'Memproses Dokumen' : (exportStatus === 'success' ? 'Export Berhasil' : (exportStatus === 'error' ? 'Export Gagal' : 'Konfirmasi Unduh PDF'))">
                        </h3>
                        <p class="text-xs text-base-content/60"
                            x-text="exportStatus === 'processing' ? 'Mohon tunggu sejenak hingga file PDF selesai digenerate' : (exportStatus === 'success' ? 'File laporan dokumentasi konsumsi siap digunakan' : (exportStatus === 'error' ? 'Terjadi kendala saat memproses dokumen' : 'Unduh Rekap Dokumentasi Konsumsi format PDF'))">
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeExportModal()" :disabled="exportStatus === 'processing'"
                    class="btn btn-sm btn-ghost btn-circle text-base-content/50 hover:text-base-content disabled:opacity-30">✕</button>
            </div>

            {{-- Body Modal --}}
            <div class="py-4 text-sm">
                {{-- State: Idle --}}
                <div x-show="exportStatus === 'idle'" class="space-y-4">
                    <div class="bg-base-200/60 rounded-xl p-3 space-y-2 border border-base-200">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-base-content/60">Format File:</span>
                            <span class="font-bold uppercase text-primary">PDF Document (.pdf)</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-base-content/60">Ukuran Kertas:</span>
                            <span class="font-bold uppercase" x-text="exportPaperSize.toUpperCase()"></span>
                        </div>
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
                </div>

                {{-- State: Processing --}}
                <div x-show="exportStatus === 'processing'" class="py-6 space-y-4">
                    <div class="w-full bg-base-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-primary h-3 rounded-full transition-all duration-300"
                            :style="'width: ' + exportProgress + '%'"></div>
                    </div>
                    <div class="flex justify-between items-center text-xs text-base-content/60">
                        <span x-text="exportStatusText">Mempersiapkan data...</span>
                        <span class="font-bold font-mono" x-text="exportProgress + '%'"></span>
                    </div>
                </div>

                {{-- State: Success --}}
                <div x-show="exportStatus === 'success'" class="py-6 space-y-3 text-center">
                    <div
                        class="size-16 rounded-full bg-success/15 text-success mx-auto flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="font-semibold text-base text-base-content">Dokumen Berhasil Dibuat!</p>
                    <p class="text-xs text-base-content/60 font-mono" x-text="exportedFilename"></p>
                </div>

                {{-- State: Error --}}
                <div x-show="exportStatus === 'error'" class="py-6 space-y-3 text-center">
                    <div class="size-16 rounded-full bg-error/15 text-error mx-auto flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="font-semibold text-base text-error">Terjadi Kesalahan</p>
                    <p class="text-xs text-base-content/60 max-w-sm mx-auto" x-text="exportErrorMessage"></p>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="pt-3 border-t border-base-200 flex items-center justify-end gap-2">
                <template x-if="exportStatus === 'idle'">
                    <div class="flex gap-2">
                        <button type="button" @click="closeExportModal()"
                            class="btn btn-sm btn-ghost">Batal</button>
                        <button type="button" @click="startExport()"
                            class="btn btn-sm btn-primary text-white gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Sekarang</span>
                        </button>
                    </div>
                </template>

                <template x-if="exportStatus === 'success'">
                    <div class="flex gap-2">
                        <button type="button" @click="triggerRedownload()" class="btn btn-sm btn-outline gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Unduh Ulang
                        </button>
                        <button type="button" @click="closeExportModal()"
                            class="btn btn-sm btn-neutral">Tutup</button>
                    </div>
                </template>

                <template x-if="exportStatus === 'error'">
                    <div class="flex gap-2">
                        <button type="button" @click="startExport()"
                            class="btn btn-sm btn-error text-white gap-1.5">
                            Coba Lagi
                        </button>
                        <button type="button" @click="closeExportModal()"
                            class="btn btn-sm btn-ghost">Tutup</button>
                    </div>
                </template>
            </div>
        </div>
    </dialog>

    {{-- ─── MODAL UPLOAD DOKUMENTASI KONSUMSI ────────────────────────────── --}}
    <dialog
        class="modal modal-bottom sm:modal-middle backdrop-blur-xs z-99999 {{ $showAddModal ? 'modal-open' : '' }}">
        <div class="modal-box max-w-lg rounded-2xl shadow-2xl border border-base-200 max-h-[90vh] overflow-y-auto"
            x-data="dokumentasiUploadModal()">
            {{-- Header Modal --}}
            <div class="flex items-start justify-between pb-3 border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div
                        class="size-10 rounded-xl {{ $modalMode === 'edit' ? 'bg-warning/15 text-warning-content' : 'bg-primary/10 text-primary' }} flex items-center justify-center shrink-0">
                        @if ($modalMode === 'edit')
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content">
                            {{ $modalMode === 'edit' ? 'Edit Dokumentasi Konsumsi' : 'Upload Dokumentasi Konsumsi' }}
                        </h3>
                        <p class="text-xs text-base-content/60">
                            {{ $modalMode === 'edit' ? 'Perbarui porsi, ganti foto, atau hapus dokumentasi' : 'Unggah bukti foto dan jumlah porsi konsumsi' }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="resetAll()" wire:click="closeAddKonsumsiModal"
                    class="btn btn-sm btn-ghost btn-circle text-base-content/50 hover:text-base-content">✕</button>
            </div>

            <form wire:submit="saveKonsumsi" class="py-4 space-y-4 text-sm">
                {{-- Hidden File Inputs --}}
                <input type="file" x-ref="fileInputSiang" wire:key="foto-siang-{{ $uploadIteration }}"
                    @change="onFileChange($event, 'fotoSiang')" accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="hidden" />
                <input type="file" x-ref="fileInputMalam" wire:key="foto-malam-{{ $uploadIteration }}"
                    @change="onFileChange($event, 'fotoMalam')" accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="hidden" />

                {{-- 1. Input Tanggal --}}
                <div>
                    <label class="label text-xs py-1 text-base-content/80 font-bold">1. Tanggal Dokumentasi</label>
                    <input type="date" wire:model.live="uploadTanggal"
                        {{ $modalMode === 'edit' ? 'readonly' : '' }}
                        class="input input-bordered w-full text-xs scheme-light dark:scheme-dark {{ $modalMode === 'edit' ? 'bg-base-200/60 cursor-not-allowed' : '' }}"
                        required />
                </div>

                {{-- Card Grey: Status Dokumentasi Tanggal Terpilih --}}
                @if ($uploadTanggal)
                    @php
                        $carbonTgl = \Carbon\Carbon::parse($uploadTanggal);
                        $tglFormatted = $carbonTgl->translatedFormat('l, d-m-Y');
                    @endphp
                    <div class="bg-base-200/80 border border-base-300 rounded-2xl p-3.5 space-y-2.5 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold text-base-content flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-base-content/70"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Tanggal: <span
                                        class="font-black text-base-content">{{ $tglFormatted }}</span></span>
                            </div>
                            @if ($existingFotoSiang && $existingFotoMalam)
                                <span
                                    class="badge badge-success badge-xs font-bold text-[10px] gap-1 py-2 px-2 shadow-2xs">
                                    Lengkap
                                </span>
                            @elseif (!$existingFotoSiang && !$existingFotoMalam)
                                <span
                                    class="badge badge-ghost badge-xs font-medium text-[10px] text-base-content/60 py-2 px-2">
                                    Belum Ada Data
                                </span>
                            @else
                                <span class="badge badge-warning badge-xs font-bold text-[10px] py-2 px-2 shadow-2xs">
                                    Sebagian
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-base-300">
                            {{-- Kolom Siang --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-warning-content flex items-center gap-1">
                                        <span class="size-2 rounded-full bg-warning"></span>
                                        Siang
                                    </span>
                                    <template x-if="processedPreviewSiang">
                                        <span
                                            class="badge badge-warning badge-xs font-bold text-[10px] shadow-2xs">Foto
                                            Baru</span>
                                    </template>
                                    <template x-if="!processedPreviewSiang">
                                        <span>
                                            @if ($existingFotoSiang)
                                                <span
                                                    class="badge badge-warning badge-xs font-semibold text-[10px]">Tersimpan</span>
                                            @else
                                                <span class="text-[10px] text-base-content/50">Belum Ada</span>
                                            @endif
                                        </span>
                                    </template>
                                </div>

                                {{-- Card Foto Siang --}}
                                <div class="relative rounded-xl overflow-hidden aspect-video shadow-xs border transition-all duration-200 group"
                                    :class="processedPreviewSiang ? 'border-warning ring-2 ring-warning/30 bg-base-100' :
                                        '{{ $existingFotoSiang ? 'border-warning/40 bg-base-100' : 'border-dashed border-base-300 hover:border-warning/70 bg-base-100/60 hover:bg-warning/5' }}'">

                                    {{-- 1. State: Ada Foto Baru yang di-upload / diproses --}}
                                    <template x-if="processedPreviewSiang">
                                        <div class="w-full h-full relative">
                                            <img :src="processedPreviewSiang" alt="Preview Foto Siang"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                            <div class="absolute top-2 left-2 z-10">
                                                <span
                                                    class="badge bg-warning text-warning-content border-0 badge-xs font-bold shadow-sm text-[9px] py-1 px-2">
                                                    WebP • <span x-text="processedSizeSiang"></span>
                                                </span>
                                            </div>
                                            {{-- Overlay saat hover --}}
                                            <div
                                                class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2 backdrop-blur-[2px] z-10">
                                                <button type="button" @click.stop="pickFile('siang', false)"
                                                    class="btn btn-xs btn-warning text-warning-content font-bold shadow-md gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    Ubah Foto
                                                </button>
                                                <button type="button" @click.stop="clearPhoto('fotoSiang')"
                                                    class="btn btn-xs btn-error text-white font-bold shadow-md gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- 2. State: Tidak ada foto baru, cek apakah ada foto tersimpan atau belum ada --}}
                                    <template x-if="!processedPreviewSiang">
                                        <div class="w-full h-full relative">
                                            @if ($existingFotoSiang)
                                                <img src="{{ asset('storage/' . $existingFotoSiang) }}"
                                                    alt="Foto Konsumsi Siang"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                <div class="absolute top-2 left-2 z-10">
                                                    <span
                                                        class="badge bg-black/75 text-white border-0 badge-xs text-[9px] font-semibold shadow-sm backdrop-blur-xs py-1 px-2 gap-1">
                                                        <span class="size-1.5 rounded-full bg-success"></span>
                                                        Tersimpan
                                                    </span>
                                                </div>
                                                {{-- Overlay saat hover --}}
                                                <div
                                                    class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2 backdrop-blur-[2px] z-10">
                                                    <a href="{{ asset('storage/' . $existingFotoSiang) }}"
                                                        target="_blank"
                                                        class="btn btn-xs btn-ghost text-white border border-white/40 hover:bg-white/20 font-medium shadow-md gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Lihat
                                                    </a>
                                                    <button type="button" @click.stop="pickFile('siang', true)"
                                                        class="btn btn-xs btn-warning text-warning-content font-bold shadow-md gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Ubah Foto
                                                    </button>
                                                </div>
                                            @else
                                                <div @click="pickFile('siang', false)"
                                                    class="w-full h-full flex flex-col items-center justify-center p-2 text-center cursor-pointer select-none">
                                                    <div
                                                        class="size-8 rounded-full bg-base-200/80 flex items-center justify-center mb-1 text-base-content/50 group-hover:bg-warning/20 group-hover:text-warning-content transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </div>
                                                    <span
                                                        class="text-[11px] font-semibold text-base-content/70 group-hover:text-warning-content transition-colors">Upload
                                                        Foto Siang</span>
                                                    <span class="text-[9px] text-base-content/40">Klik atau arahkan
                                                        kursor</span>

                                                    {{-- Overlay saat hover --}}
                                                    <div
                                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center p-2 backdrop-blur-[1px] z-10">
                                                        <button type="button" @click.stop="pickFile('siang', false)"
                                                            class="btn btn-xs btn-warning text-warning-content font-bold shadow-md gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                            </svg>
                                                            Upload Foto
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </template>

                                    {{-- Loading / Processing Spinner Overlay --}}
                                    <div x-show="isProcessing && rawFileSiang"
                                        class="absolute inset-0 bg-base-300/90 backdrop-blur-xs flex flex-col items-center justify-center p-2 text-center z-20">
                                        <span class="loading loading-spinner loading-sm text-warning mb-1"></span>
                                        <span class="text-[10px] font-bold text-base-content leading-tight px-1"
                                            x-text="processingStatus"></span>
                                    </div>
                                </div>

                                {{-- Error Message Siang --}}
                                <template x-if="errorMessage && rawFileSiang">
                                    <span class="text-error text-[10px] block mt-1" x-text="errorMessage"></span>
                                </template>
                                @error('fotoSiang')
                                    <span class="text-error text-[10px] block mt-1">{{ $message }}</span>
                                @enderror

                                {{-- Pilihan Crop Aspect Ratio Siang --}}
                                <div class="pt-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11px] font-bold text-base-content flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="size-3 text-base-content/60" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 3v12a3 3 0 003 3h12M18 21V9a3 3 0 00-3-3H3" />
                                            </svg>
                                            Rasio Crop
                                        </label>
                                        <span class="text-[9px] text-base-content/50 font-medium">Maks. 100KB</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                        <label
                                            class="cursor-pointer border rounded-lg py-1 px-1 flex items-center justify-center gap-1 transition-all text-center"
                                            :class="cropRatioSiang === '16:9' ?
                                                'border-warning bg-warning/15 ring-1 ring-warning shadow-2xs font-bold text-warning-content' :
                                                'border-base-300 bg-base-100 hover:bg-base-200/50 text-base-content/70'">
                                            <input type="radio" x-model="cropRatioSiang" value="16:9"
                                                @change="onCropRatioChange('fotoSiang')"
                                                class="radio radio-xs radio-warning" />
                                            <span class="text-[10px]">16:9</span>
                                        </label>

                                        <label
                                            class="cursor-pointer border rounded-lg py-1 px-1 flex items-center justify-center gap-1 transition-all text-center"
                                            :class="cropRatioSiang === '4:3' ?
                                                'border-warning bg-warning/15 ring-1 ring-warning shadow-2xs font-bold text-warning-content' :
                                                'border-base-300 bg-base-100 hover:bg-base-200/50 text-base-content/70'">
                                            <input type="radio" x-model="cropRatioSiang" value="4:3"
                                                @change="onCropRatioChange('fotoSiang')"
                                                class="radio radio-xs radio-warning" />
                                            <span class="text-[10px]">4:3</span>
                                        </label>

                                        <label
                                            class="cursor-pointer border rounded-lg py-1 px-1 flex items-center justify-center gap-1 transition-all text-center"
                                            :class="cropRatioSiang === 'original' ?
                                                'border-warning bg-warning/15 ring-1 ring-warning shadow-2xs font-bold text-warning-content' :
                                                'border-base-300 bg-base-100 hover:bg-base-200/50 text-base-content/70'">
                                            <input type="radio" x-model="cropRatioSiang" value="original"
                                                @change="onCropRatioChange('fotoSiang')"
                                                class="radio radio-xs radio-warning" />
                                            <span class="text-[10px]">Asli</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Malam --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-1">
                                        <span class="size-2 rounded-full bg-neutral-900"></span>
                                        Malam
                                    </span>
                                    <template x-if="processedPreviewMalam">
                                        <span
                                            class="badge bg-neutral-900 text-white border-0 badge-xs font-bold text-[10px] shadow-2xs">Foto
                                            Baru</span>
                                    </template>
                                    <template x-if="!processedPreviewMalam">
                                        <span>
                                            @if ($existingFotoMalam)
                                                <span
                                                    class="badge bg-neutral-900 text-white border-0 badge-xs font-semibold text-[10px]">Tersimpan</span>
                                            @else
                                                <span class="text-[10px] text-base-content/50">Belum Ada</span>
                                            @endif
                                        </span>
                                    </template>
                                </div>

                                {{-- Card Foto Malam --}}
                                <div class="relative rounded-xl overflow-hidden aspect-video shadow-xs border transition-all duration-200 group"
                                    :class="processedPreviewMalam ?
                                        'border-neutral-900 ring-2 ring-neutral-900/30 bg-base-100' :
                                        '{{ $existingFotoMalam ? 'border-neutral-900/40 bg-base-100' : 'border-dashed border-base-300 hover:border-neutral-900/70 bg-base-100/60 hover:bg-neutral-900/5' }}'">

                                    {{-- 1. State: Ada Foto Baru yang di-upload / diproses --}}
                                    <template x-if="processedPreviewMalam">
                                        <div class="w-full h-full relative">
                                            <img :src="processedPreviewMalam" alt="Preview Foto Malam"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                            <div class="absolute top-2 left-2 z-10">
                                                <span
                                                    class="badge bg-neutral-900 text-white border-0 badge-xs font-bold shadow-sm text-[9px] py-1 px-2">
                                                    WebP • <span x-text="processedSizeMalam"></span>
                                                </span>
                                            </div>
                                            {{-- Overlay saat hover --}}
                                            <div
                                                class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2 backdrop-blur-[2px] z-10">
                                                <button type="button" @click.stop="pickFile('malam', false)"
                                                    class="btn btn-xs btn-neutral bg-neutral-900 hover:bg-black text-white font-bold shadow-md gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    Ubah Foto
                                                </button>
                                                <button type="button" @click.stop="clearPhoto('fotoMalam')"
                                                    class="btn btn-xs btn-error text-white font-bold shadow-md gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- 2. State: Tidak ada foto baru, cek apakah ada foto tersimpan atau belum ada --}}
                                    <template x-if="!processedPreviewMalam">
                                        <div class="w-full h-full relative">
                                            @if ($existingFotoMalam)
                                                <img src="{{ asset('storage/' . $existingFotoMalam) }}"
                                                    alt="Foto Konsumsi Malam"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                <div class="absolute top-2 left-2 z-10">
                                                    <span
                                                        class="badge bg-black/75 text-white border-0 badge-xs text-[9px] font-semibold shadow-sm backdrop-blur-xs py-1 px-2 gap-1">
                                                        <span class="size-1.5 rounded-full bg-success"></span>
                                                        Tersimpan
                                                    </span>
                                                </div>
                                                {{-- Overlay saat hover --}}
                                                <div
                                                    class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2 backdrop-blur-[2px] z-10">
                                                    <a href="{{ asset('storage/' . $existingFotoMalam) }}"
                                                        target="_blank"
                                                        class="btn btn-xs btn-ghost text-white border border-white/40 hover:bg-white/20 font-medium shadow-md gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Lihat
                                                    </a>
                                                    <button type="button" @click.stop="pickFile('malam', true)"
                                                        class="btn btn-xs btn-neutral bg-neutral-900 hover:bg-black text-white font-bold shadow-md gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Ubah Foto
                                                    </button>
                                                </div>
                                            @else
                                                <div @click="pickFile('malam', false)"
                                                    class="w-full h-full flex flex-col items-center justify-center p-2 text-center cursor-pointer select-none">
                                                    <div
                                                        class="size-8 rounded-full bg-base-200/80 flex items-center justify-center mb-1 text-base-content/50 group-hover:bg-neutral-900/20 group-hover:text-neutral-900 dark:group-hover:text-white transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </div>
                                                    <span
                                                        class="text-[11px] font-semibold text-base-content/70 group-hover:text-neutral-900 dark:group-hover:text-white transition-colors">Upload
                                                        Foto Malam</span>
                                                    <span class="text-[9px] text-base-content/40">Klik atau arahkan
                                                        kursor</span>

                                                    {{-- Overlay saat hover --}}
                                                    <div
                                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center p-2 backdrop-blur-[1px] z-10">
                                                        <button type="button" @click.stop="pickFile('malam', false)"
                                                            class="btn btn-xs btn-neutral bg-neutral-900 hover:bg-black text-white font-bold shadow-md gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                            </svg>
                                                            Upload Foto
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </template>

                                    {{-- Loading / Processing Spinner Overlay --}}
                                    <div x-show="isProcessing && rawFileMalam"
                                        class="absolute inset-0 bg-base-300/90 backdrop-blur-xs flex flex-col items-center justify-center p-2 text-center z-20">
                                        <span class="loading loading-spinner loading-sm text-neutral-900 mb-1"></span>
                                        <span class="text-[10px] font-bold text-base-content leading-tight px-1"
                                            x-text="processingStatus"></span>
                                    </div>
                                </div>

                                {{-- Error Message Malam --}}
                                <template x-if="errorMessage && rawFileMalam">
                                    <span class="text-error text-[10px] block mt-1" x-text="errorMessage"></span>
                                </template>
                                @error('fotoMalam')
                                    <span class="text-error text-[10px] block mt-1">{{ $message }}</span>
                                @enderror

                                {{-- Pilihan Crop Aspect Ratio Malam --}}
                                <div class="pt-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11px] font-bold text-base-content flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="size-3 text-base-content/60" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 3v12a3 3 0 003 3h12M18 21V9a3 3 0 00-3-3H3" />
                                            </svg>
                                            Rasio Crop
                                        </label>
                                        <span class="text-[9px] text-base-content/50 font-medium">Maks. 100KB</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                        <label
                                            class="cursor-pointer border rounded-lg py-1 px-1 flex items-center justify-center gap-1 transition-all text-center"
                                            :class="cropRatioMalam === '16:9' ?
                                                'border-neutral-900 bg-neutral-900/15 ring-1 ring-neutral-900 shadow-2xs font-bold text-neutral-900 dark:text-white' :
                                                'border-base-300 bg-base-100 hover:bg-base-200/50 text-base-content/70'">
                                            <input type="radio" x-model="cropRatioMalam" value="16:9"
                                                @change="onCropRatioChange('fotoMalam')"
                                                class="radio radio-xs radio-neutral" />
                                            <span class="text-[10px]">16:9</span>
                                        </label>

                                        <label
                                            class="cursor-pointer border rounded-lg py-1 px-1 flex items-center justify-center gap-1 transition-all text-center"
                                            :class="cropRatioMalam === '4:3' ?
                                                'border-neutral-900 bg-neutral-900/15 ring-1 ring-neutral-900 shadow-2xs font-bold text-neutral-900 dark:text-white' :
                                                'border-base-300 bg-base-100 hover:bg-base-200/50 text-base-content/70'">
                                            <input type="radio" x-model="cropRatioMalam" value="4:3"
                                                @change="onCropRatioChange('fotoMalam')"
                                                class="radio radio-xs radio-neutral" />
                                            <span class="text-[10px]">4:3</span>
                                        </label>

                                        <label
                                            class="cursor-pointer border rounded-lg py-1 px-1 flex items-center justify-center gap-1 transition-all text-center"
                                            :class="cropRatioMalam === 'original' ?
                                                'border-neutral-900 bg-neutral-900/15 ring-1 ring-neutral-900 shadow-2xs font-bold text-neutral-900 dark:text-white' :
                                                'border-base-300 bg-base-100 hover:bg-base-200/50 text-base-content/70'">
                                            <input type="radio" x-model="cropRatioMalam" value="original"
                                                @change="onCropRatioChange('fotoMalam')"
                                                class="radio radio-xs radio-neutral" />
                                            <span class="text-[10px]">Asli</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 2. Radio Button Pilihan Sesi Konsumsi --}}
                <div>
                    <label class="label text-xs py-1 text-base-content/80 font-bold">2. Pilih Sesi Konsumsi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            class="cursor-pointer border rounded-2xl p-3 flex items-center gap-3 transition-all {{ $sesiKonsumsi === 'siang' ? 'border-warning bg-warning/10 ring-2 ring-warning/30 shadow-xs' : 'border-base-200 bg-base-100 hover:bg-base-200/50' }} {{ $existingFotoSiang ? 'opacity-95' : '' }}">
                            <input type="radio" wire:model.live="sesiKonsumsi" value="siang"
                                class="radio radio-sm radio-warning" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-base-content">Siang</span>
                                    </div>
                                    @if ($existingFotoSiang)
                                        <span class="badge badge-warning badge-xs font-bold text-[9px] gap-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-2.5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Tersimpan
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-base-content/60 mt-0.5">
                                    Tersedia: <span
                                        class="font-bold text-warning-content">{{ $this->calculatedSiang }}</span>
                                    porsi
                                </div>
                            </div>
                        </label>

                        <label
                            class="cursor-pointer border rounded-2xl p-3 flex items-center gap-3 transition-all {{ $sesiKonsumsi === 'malam' ? 'border-neutral-900 bg-neutral-900/10 ring-2 ring-neutral-900/30 shadow-xs' : 'border-base-200 bg-base-100 hover:bg-base-200/50' }} {{ $existingFotoMalam ? 'opacity-95' : '' }}">
                            <input type="radio" wire:model.live="sesiKonsumsi" value="malam"
                                class="radio radio-sm radio-neutral" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-base-content">Malam</span>
                                    </div>
                                    @if ($existingFotoMalam)
                                        <span
                                            class="badge bg-neutral-900 text-white border-0 badge-xs font-bold text-[9px] gap-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-2.5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Tersimpan
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-base-content/60 mt-0.5">
                                    Tersedia: <span
                                        class="font-bold text-neutral-900 dark:text-neutral-100">{{ $this->calculatedMalam }}</span>
                                    porsi
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('sesiKonsumsi')
                        <span class="text-error text-xs block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                @php
                    $maxSiang = $this->calculatedSiang;
                    $maxMalam = $this->calculatedMalam;
                    $isCurrentSesiLocked =
                        $modalMode === 'create' &&
                        (($sesiKonsumsi === 'siang' && $existingFotoSiang) ||
                            ($sesiKonsumsi === 'malam' && $existingFotoMalam));
                    $hasExistingCurrentPhoto = $sesiKonsumsi === 'siang' ? $existingFotoSiang : $existingFotoMalam;
                @endphp

                {{-- 3. Form Input Sesi Makan Siang (Tampil Jika Sesi Siang Dipilih) --}}
                @if ($sesiKonsumsi === 'siang')
                    @if ($modalMode === 'create' && $existingFotoSiang)
                        {{-- Notifikasi Bahwa Dokumentasi Siang Sudah Tersimpan Pada Mode Tambah --}}
                        <div class="p-4 bg-warning/10 border border-warning/30 rounded-2xl space-y-2.5">
                            <div class="flex items-center gap-2.5 text-warning-content font-bold text-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="text-sm">Dokumentasi Makan Siang Sudah Tersimpan</span>
                            </div>
                            <p class="text-xs text-base-content/70 leading-relaxed">
                                Dokumentasi makan siang untuk tanggal ini sudah tersimpan. Klik tombol di bawah jika
                                Anda ingin mengubah atau menghapusnya.
                            </p>
                            <div class="pt-1 flex flex-wrap items-center gap-2">
                                <button type="button"
                                    wire:click="openEditKonsumsiModal('{{ $uploadTanggal }}', 'siang')"
                                    class="btn btn-xs btn-warning text-warning-content gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Data Siang
                                </button>
                                @if (!$existingFotoMalam)
                                    <button type="button" wire:click="$set('sesiKonsumsi', 'malam')"
                                        class="btn btn-xs btn-neutral bg-neutral-900 hover:bg-black text-white gap-1">
                                        Pindah ke Input Makan Malam &rarr;
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-warning/5 border border-warning/20 rounded-2xl space-y-4">
                            {{-- Input Jumlah Siang --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-bold text-base-content flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-warning"></span>
                                        Jumlah Porsi Makan Siang
                                    </label>
                                    <span class="badge badge-warning badge-sm font-bold text-[11px]">
                                        Maks. {{ $maxSiang }} Porsi
                                    </span>
                                </div>
                                <input type="number" wire:model.live="jumlahSiang" min="0"
                                    max="{{ $maxSiang }}"
                                    x-on:input="if (parseInt($el.value) > {{ $maxSiang }}) $el.value = {{ $maxSiang }}; if (parseInt($el.value) < 0) $el.value = 0;"
                                    class="input input-bordered input-sm w-full text-xs font-bold focus:border-warning focus:outline-warning"
                                    placeholder="Masukkan jumlah makan siang (maks. {{ $maxSiang }})" required />
                                @error('jumlahSiang')
                                    <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                                <p class="text-[11px] text-base-content/60 mt-1">
                                    *Jumlah porsi tidak boleh lebih dari {{ $maxSiang }} (total konsumsi makan
                                    siang terdata pada tanggal ini).
                                </p>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- 4. Form Input Sesi Makan Malam (Tampil Jika Sesi Malam Dipilih) --}}
                @if ($sesiKonsumsi === 'malam')
                    @if ($modalMode === 'create' && $existingFotoMalam)
                        {{-- Notifikasi Bahwa Dokumentasi Malam Sudah Tersimpan Pada Mode Tambah --}}
                        <div class="p-4 bg-neutral-900/10 border border-neutral-900/30 rounded-2xl space-y-2.5">
                            <div
                                class="flex items-center gap-2.5 text-neutral-900 dark:text-neutral-100 font-bold text-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="text-sm">Dokumentasi Makan Malam Sudah Tersimpan</span>
                            </div>
                            <p class="text-xs text-base-content/70 leading-relaxed">
                                Dokumentasi makan malam untuk tanggal ini sudah tersimpan. Klik tombol di bawah jika
                                Anda ingin mengubah atau menghapusnya.
                            </p>
                            <div class="pt-1 flex flex-wrap items-center gap-2">
                                <button type="button"
                                    wire:click="openEditKonsumsiModal('{{ $uploadTanggal }}', 'malam')"
                                    class="btn btn-xs btn-neutral bg-neutral-900 hover:bg-black text-white gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Data Malam
                                </button>
                                @if (!$existingFotoSiang)
                                    <button type="button" wire:click="$set('sesiKonsumsi', 'siang')"
                                        class="btn btn-xs btn-warning text-warning-content gap-1">
                                        Pindah ke Input Makan Siang &rarr;
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-neutral-900/5 border border-neutral-900/20 rounded-2xl space-y-4">
                            {{-- Input Jumlah Malam --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-bold text-base-content flex items-center gap-1.5">
                                        <span class="size-2 rounded-full bg-neutral-900"></span>
                                        Jumlah Porsi Makan Malam
                                    </label>
                                    <span
                                        class="badge bg-neutral-900 text-white border-0 badge-sm font-bold text-[11px]">
                                        Maks. {{ $maxMalam }} Porsi
                                    </span>
                                </div>
                                <input type="number" wire:model.live="jumlahMalam" min="0"
                                    max="{{ $maxMalam }}"
                                    x-on:input="if (parseInt($el.value) > {{ $maxMalam }}) $el.value = {{ $maxMalam }}; if (parseInt($el.value) < 0) $el.value = 0;"
                                    class="input input-bordered input-sm w-full text-xs font-bold focus:border-neutral-900 focus:outline-neutral-900"
                                    placeholder="Masukkan jumlah makan malam (maks. {{ $maxMalam }})" required />
                                @error('jumlahMalam')
                                    <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                                <p class="text-[11px] text-base-content/60 mt-1">
                                    *Jumlah porsi tidak boleh lebih dari {{ $maxMalam }} (total konsumsi makan
                                    malam terdata pada tanggal ini).
                                </p>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Footer Modal --}}
                <div class="pt-3 border-t border-base-200 flex items-center justify-between gap-2">
                    <div>
                        {{-- Tombol Hapus Dokumentasi (Tampil jika sesi saat ini memiliki foto tersimpan atau mode edit) --}}
                        @if ($hasExistingCurrentPhoto)
                            <button type="button" wire:click="deleteDokumentasi"
                                wire:confirm="Apakah Anda yakin ingin menghapus data dokumentasi {{ $sesiKonsumsi === 'siang' ? 'makan siang' : 'makan malam' }} tanggal {{ \Carbon\Carbon::parse($uploadTanggal)->translatedFormat('d F Y') }}?"
                                wire:loading.attr="disabled" wire:target="deleteDokumentasi,saveKonsumsi"
                                class="btn btn-sm btn-error text-white gap-1.5 shadow-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span wire:loading.remove wire:target="deleteDokumentasi">Hapus</span>
                                <span wire:loading wire:target="deleteDokumentasi"
                                    class="loading loading-spinner loading-xs"></span>
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="resetAll()" wire:click="closeAddKonsumsiModal"
                            class="btn btn-sm btn-ghost">Batal</button>
                        @if ($isCurrentSesiLocked)
                            <button type="button"
                                wire:click="openEditKonsumsiModal('{{ $uploadTanggal }}', '{{ $sesiKonsumsi }}')"
                                class="btn btn-sm btn-warning text-warning-content gap-1.5 shadow-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Beralih ke Form Edit
                            </button>
                        @else
                            <button type="submit" class="btn btn-sm btn-primary text-white gap-1.5 shadow-xs"
                                wire:loading.attr="disabled" :disabled="isProcessing"
                                wire:target="fotoSiang,fotoMalam,saveKonsumsi,deleteDokumentasi">
                                <span wire:loading.remove wire:target="saveKonsumsi" x-show="!isProcessing">
                                    {{ $modalMode === 'edit' ? 'Simpan Perubahan' : 'Simpan ' . ($sesiKonsumsi === 'siang' ? 'Makan Siang' : 'Makan Malam') }}
                                </span>
                                <span wire:loading wire:target="saveKonsumsi"
                                    class="loading loading-spinner loading-xs"></span>
                                <span x-show="isProcessing" class="loading loading-spinner loading-xs"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── ALPINE JAVASCRIPT COMPONENT ────────────────────────────────────── --}}
    <script>
        function konsumsiAdminComponent() {
            return {
                showExportModal: false,
                exportStartDate: @entangle('startDate').live,
                exportEndDate: @entangle('endDate').live,
                exportOpdId: @entangle('selectedOpd').live,
                exportPaperSize: @entangle('paperSize').live,
                exportStatus: 'idle',
                exportProgress: 0,
                exportStatusText: '',
                exportErrorMessage: '',
                downloadBlobUrl: null,
                exportedFilename: '',

                openExportModal(type) {
                    this.exportStatus = 'idle';
                    this.exportProgress = 0;
                    this.exportStatusText = '';
                    this.exportErrorMessage = '';
                    this.downloadBlobUrl = null;
                    this.exportedFilename = '';

                    const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                    if (wire) {
                        this.exportStartDate = wire.get('startDate') || wire.get('filterStartDate') || '';
                        this.exportEndDate = wire.get('endDate') || wire.get('filterEndDate') || '';
                        this.exportOpdId = wire.get('selectedOpd') || '';
                        this.exportPaperSize = wire.get('paperSize') || 'a4';
                    }

                    this.showExportModal = true;
                },

                closeExportModal() {
                    if (this.exportStatus === 'processing') return;
                    this.showExportModal = false;
                },

                async startExport() {
                    this.exportStatus = 'processing';
                    this.exportProgress = 10;
                    this.exportStatusText = 'Mempersiapkan data dokumentasi konsumsi...';

                    let progressTimer = setInterval(() => {
                        if (this.exportProgress < 85) {
                            this.exportProgress += Math.floor(Math.random() * 12) + 5;
                            if (this.exportProgress > 40 && this.exportProgress < 70) {
                                this.exportStatusText = 'Menghitung rekapitulasi porsi & shift...';
                            } else if (this.exportProgress >= 70) {
                                this.exportStatusText = 'Menyusun berkas PDF...';
                            }
                        }
                    }, 400);

                    try {
                        let base = '{{ route('dokumentasi-konsumsi.export-pdf') }}';
                        let params = new URLSearchParams();

                        if (this.exportStartDate) params.append('startDate', this.exportStartDate);
                        if (this.exportEndDate) params.append('endDate', this.exportEndDate);
                        if (this.exportOpdId) params.append('opd_id', this.exportOpdId);
                        if (this.exportPaperSize) params.append('paperSize', this.exportPaperSize);

                        const fullUrl = base + (params.toString() ? '?' + params.toString() : '');

                        const response = await fetch(fullUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/pdf, application/json',
                                'X-Requested-With': 'XMLHttpRequest'
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
                            const sDate = formatIndo(this.exportStartDate);
                            const eDate = formatIndo(this.exportEndDate);
                            filename = 'rekap_konsumsi_' + (sDate && eDate ? `${sDate}_${eDate}` : (sDate || '')) +
                                '.pdf';
                        }

                        const blob = await response.blob();
                        this.exportProgress = 100;
                        this.exportStatusText = 'Selesai!';

                        const blobUrl = window.URL.createObjectURL(blob);
                        this.downloadBlobUrl = blobUrl;
                        this.exportedFilename = filename;

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
                }
            };
        }

        function dokumentasiUploadModal() {
            return {
                cropRatioSiang: '16:9',
                cropRatioMalam: '16:9',
                cropRatio: '16:9',
                rawFileSiang: null,
                rawFileMalam: null,
                processedPreviewSiang: null,
                processedPreviewMalam: null,
                processedSizeSiang: null,
                processedSizeMalam: null,
                isProcessing: false,
                processingStatus: '',
                errorMessage: '',

                init() {
                    this.$watch('$wire.showAddModal', (val) => {
                        if (!val) {
                            this.resetAll();
                        }
                    });
                    this.$watch('$wire.uploadTanggal', () => {
                        this.resetAll();
                    });
                },

                async onFileChange(event, target) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) return;

                    if (!file.type.match(/^image\//i)) {
                        this.errorMessage = 'File yang dipilih harus berupa format gambar (JPG, JPEG, PNG, WEBP).';
                        return;
                    }

                    this.errorMessage = '';
                    if (target === 'fotoSiang') {
                        this.rawFileSiang = file;
                    } else {
                        this.rawFileMalam = file;
                    }

                    await this.processAndUpload(target);
                },

                async onCropRatioChange(target) {
                    const file = (target === 'fotoSiang') ? this.rawFileSiang : this.rawFileMalam;
                    if (file) {
                        await this.processAndUpload(target);
                    }
                },

                async processAndUpload(target) {
                    const file = (target === 'fotoSiang') ? this.rawFileSiang : this.rawFileMalam;
                    if (!file) return;

                    this.isProcessing = true;
                    this.errorMessage = '';
                    this.processingStatus = 'Mengubah format ke WebP & menyesuaikan ukuran (Maks. 100KB)...';

                    try {
                        const ratio = (target === 'fotoSiang') ? (this.cropRatioSiang || '16:9') : (this
                            .cropRatioMalam || '16:9');
                        const webpFile = await this.cropAndCompressToWebp(file, ratio);

                        const previewUrl = URL.createObjectURL(webpFile);
                        const sizeKb = (webpFile.size / 1024).toFixed(1) + ' KB';

                        if (target === 'fotoSiang') {
                            if (this.processedPreviewSiang) URL.revokeObjectURL(this.processedPreviewSiang);
                            this.processedPreviewSiang = previewUrl;
                            this.processedSizeSiang = sizeKb;
                        } else {
                            if (this.processedPreviewMalam) URL.revokeObjectURL(this.processedPreviewMalam);
                            this.processedPreviewMalam = previewUrl;
                            this.processedSizeMalam = sizeKb;
                        }

                        this.processingStatus = 'Mengunggah gambar terkompresi...';

                        const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                        if (wire) {
                            wire.upload(target, webpFile,
                                () => {
                                    this.isProcessing = false;
                                    this.processingStatus = '';
                                },
                                (err) => {
                                    this.isProcessing = false;
                                    this.processingStatus = '';
                                    this.errorMessage = 'Gagal mengunggah foto ke server. Silakan coba lagi.';
                                    console.error(err);
                                },
                                (e) => {
                                    if (e.detail && e.detail.progress) {
                                        this.processingStatus = `Mengunggah gambar... ${e.detail.progress}%`;
                                    }
                                }
                            );
                        } else {
                            this.isProcessing = false;
                            this.processingStatus = '';
                        }
                    } catch (err) {
                        console.error(err);
                        this.isProcessing = false;
                        this.processingStatus = '';
                        this.errorMessage = 'Terjadi kesalahan saat memproses gambar: ' + (err.message || 'Error');
                    }
                },

                cropAndCompressToWebp(file, ratio) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onerror = () => reject(new Error('Gagal membaca file gambar.'));
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onerror = () => reject(new Error('Gagal memuat gambar ke browser.'));
                            img.onload = async () => {
                                try {
                                    const origW = img.naturalWidth || img.width;
                                    const origH = img.naturalHeight || img.height;

                                    let sx = 0,
                                        sy = 0,
                                        sw = origW,
                                        sh = origH;

                                    if (ratio === '16:9') {
                                        const targetRatio = 16 / 9;
                                        const curRatio = origW / origH;
                                        if (curRatio > targetRatio) {
                                            sw = Math.round(origH * targetRatio);
                                            sh = origH;
                                            sx = Math.round((origW - sw) / 2);
                                            sy = 0;
                                        } else {
                                            sw = origW;
                                            sh = Math.round(origW / targetRatio);
                                            sx = 0;
                                            sy = Math.round((origH - sh) / 2);
                                        }
                                    } else if (ratio === '4:3') {
                                        const targetRatio = 4 / 3;
                                        const curRatio = origW / origH;
                                        if (curRatio > targetRatio) {
                                            sw = Math.round(origH * targetRatio);
                                            sh = origH;
                                            sx = Math.round((origW - sw) / 2);
                                            sy = 0;
                                        } else {
                                            sw = origW;
                                            sh = Math.round(origW / targetRatio);
                                            sx = 0;
                                            sy = Math.round((origH - sh) / 2);
                                        }
                                    }

                                    let destW = sw;
                                    let destH = sh;
                                    const maxDim = 1280;
                                    if (destW > maxDim || destH > maxDim) {
                                        if (destW > destH) {
                                            destH = Math.round((destH * maxDim) / destW);
                                            destW = maxDim;
                                        } else {
                                            destW = Math.round((destW * maxDim) / destH);
                                            destH = maxDim;
                                        }
                                    }

                                    const canvas = document.createElement('canvas');
                                    canvas.width = destW;
                                    canvas.height = destH;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, sx, sy, sw, sh, 0, 0, destW, destH);

                                    const maxBytes = 100 * 1024;
                                    let currentCanvas = canvas;
                                    let quality = 0.85;

                                    while (true) {
                                        const blob = await new Promise((res) => {
                                            currentCanvas.toBlob(res, 'image/webp', quality);
                                        });

                                        if (!blob) {
                                            throw new Error('Gagal menghasilkan format WebP.');
                                        }

                                        if (blob.size <= maxBytes) {
                                            const baseName = file.name.replace(/\.[^/.]+$/, '').replace(
                                                /[^a-zA-Z0-9_-]/g, '_');
                                            const webpFile = new File([blob], `${baseName}.webp`, {
                                                type: 'image/webp'
                                            });
                                            resolve(webpFile);
                                            return;
                                        }

                                        if (quality > 0.3) {
                                            quality = Math.max(0.2, quality - 0.12);
                                        } else {
                                            if (currentCanvas.width > 350 && currentCanvas.height >
                                                250) {
                                                const nextW = Math.round(currentCanvas.width * 0.8);
                                                const nextH = Math.round(currentCanvas.height * 0.8);
                                                const scaledCvs = document.createElement('canvas');
                                                scaledCvs.width = nextW;
                                                scaledCvs.height = nextH;
                                                const sCtx = scaledCvs.getContext('2d');
                                                sCtx.drawImage(currentCanvas, 0, 0, nextW, nextH);
                                                currentCanvas = scaledCvs;
                                                quality = 0.75;
                                            } else {
                                                quality = Math.max(0.05, quality - 0.05);
                                                if (quality <= 0.05) {
                                                    const baseName = file.name.replace(/\.[^/.]+$/, '')
                                                        .replace(/[^a-zA-Z0-9_-]/g, '_');
                                                    const webpFile = new File([blob],
                                                        `${baseName}.webp`, {
                                                            type: 'image/webp'
                                                        });
                                                    resolve(webpFile);
                                                    return;
                                                }
                                            }
                                        }
                                    }
                                } catch (e) {
                                    reject(e);
                                }
                            };
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                },

                pickFile(sesi, isExisting = false) {
                    const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                    if (wire) {
                        const mode = wire.get('modalMode') || wire.modalMode;
                        if (isExisting && mode === 'create') {
                            const tgl = wire.get('uploadTanggal') || wire.uploadTanggal;
                            wire.openEditKonsumsiModal(tgl, sesi);
                            setTimeout(() => {
                                if (sesi === 'siang' && this.$refs.fileInputSiang) this.$refs.fileInputSiang
                                    .click();
                                if (sesi === 'malam' && this.$refs.fileInputMalam) this.$refs.fileInputMalam
                                    .click();
                            }, 250);
                            return;
                        }
                        wire.set('sesiKonsumsi', sesi);
                    }
                    if (sesi === 'siang' && this.$refs.fileInputSiang) {
                        this.$refs.fileInputSiang.click();
                    } else if (sesi === 'malam' && this.$refs.fileInputMalam) {
                        this.$refs.fileInputMalam.click();
                    }
                },

                clearPhoto(target) {
                    if (target === 'fotoSiang') {
                        this.rawFileSiang = null;
                        if (this.processedPreviewSiang) {
                            URL.revokeObjectURL(this.processedPreviewSiang);
                            this.processedPreviewSiang = null;
                        }
                        this.processedSizeSiang = null;
                        if (this.$refs.fileInputSiang) {
                            this.$refs.fileInputSiang.value = '';
                        }
                    } else {
                        this.rawFileMalam = null;
                        if (this.processedPreviewMalam) {
                            URL.revokeObjectURL(this.processedPreviewMalam);
                            this.processedPreviewMalam = null;
                        }
                        this.processedSizeMalam = null;
                        if (this.$refs.fileInputMalam) {
                            this.$refs.fileInputMalam.value = '';
                        }
                    }

                    const wire = this.$wire || (typeof $wire !== 'undefined' ? $wire : null);
                    if (wire) {
                        wire.set(target, null);
                    }

                    const inputEl = document.querySelector(target === 'fotoSiang' ? 'input[wire\\:key^="foto-siang"]' :
                        'input[wire\\:key^="foto-malam"]');
                    if (inputEl) {
                        inputEl.value = '';
                    }
                },

                resetAll() {
                    this.rawFileSiang = null;
                    this.rawFileMalam = null;
                    if (this.processedPreviewSiang) {
                        URL.revokeObjectURL(this.processedPreviewSiang);
                        this.processedPreviewSiang = null;
                    }
                    if (this.processedPreviewMalam) {
                        URL.revokeObjectURL(this.processedPreviewMalam);
                        this.processedPreviewMalam = null;
                    }
                    this.processedSizeSiang = null;
                    this.processedSizeMalam = null;
                    this.cropRatioSiang = '16:9';
                    this.cropRatioMalam = '16:9';
                    this.cropRatio = '16:9';
                    this.isProcessing = false;
                    this.processingStatus = '';
                    this.errorMessage = '';

                    if (this.$refs.fileInputSiang) this.$refs.fileInputSiang.value = '';
                    if (this.$refs.fileInputMalam) this.$refs.fileInputMalam.value = '';

                    const inputs = document.querySelectorAll('input[wire\\:key^="foto-"]');
                    inputs.forEach(input => {
                        input.value = '';
                    });
                }
            };
        }
    </script>
</div>
