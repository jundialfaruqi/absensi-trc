<div class="animate-in fade-in duration-500" wire:init="load">
    {{-- @push('styles')
        @vite(['resources/views/components/admin/jadwal/jadwal.css'])
    @endpush --}}

    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Manajemen Jadwal</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data jadwal shift personnel per bulan</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data</li>
                <li>
                    <a href="{{ route('jadwal') }}">
                        <span class="text-base-content font-bold">Jadwal</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar: Search + Aksi ──────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-4">
        <div class="relative w-full sm:w-auto">
            <input type="text" placeholder="Cari nama personnel..." wire:model.live.debounce.400ms="search"
                class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100 placeholder:text-base-content/40" />
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            @if ($search)
                <button type="button" wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 justify-end">
            <a wire:navigate href="{{ route('jadwal.generate') }}" class="btn btn-primary text-white gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Generate Otomatis
            </a>
            <a wire:navigate href="{{ route('jadwal.import', ['month' => $month, 'year' => $year]) }}"
                class="btn btn-success text-white gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Import Excel
            </a>
        </div>
    </div>

    {{-- ─── Toolbar: Filters ──────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="join">
                <span
                    class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                <select wire:model.live="perPage" class="select join-item w-20 rounded-end-md">
                    <option value="10">10</option>
                    <option value="20">20</option>
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
                        @for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="join w-full sm:w-auto">
                    <div
                        class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                        Dari</div>
                    <input type="date" id="startDate" wire:model.live="startDate"
                        class="input input-bordered join-item w-full sm:w-auto scheme-light dark:scheme-dark text-base-content/60" />
                    <div
                        class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                        S/D</div>
                    <input type="date" id="endDate" wire:model.live="endDate"
                        class="input input-bordered join-item w-full sm:w-auto scheme-light dark:scheme-dark text-base-content/60" />

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
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6 overflow-hidden min-h-150" wire:key="jadwal-main-container">
        <div class="card-body p-0">
            {{-- ─── Loading State (Skeleton) ────────────────────────────────── --}}
            <div @if ($readyToLoad) wire:loading wire:target="month, year, search, perPage, startDate, endDate, resetFilters, gotoPage, nextPage, previousPage" @endif
                class="w-full {{ !$readyToLoad ? '' : 'hidden' }}">
                <div class="overflow-x-auto">
                    <table class="table table-sm w-full border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="bg-base-100 border-b border-r border-base-200 min-w-50 p-4">
                                    <div class="skeleton h-4 w-32 mx-auto"></div>
                                </th>
                                @for ($i = 0; $i < 15; $i++)
                                    <th class="border-b border-r border-base-200 min-w-15 p-2 text-center">
                                        <div class="skeleton h-3 w-8 mx-auto mb-1"></div>
                                        <div class="skeleton h-5 w-5 mx-auto"></div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @for ($r = 0; $r < 10; $r++)
                                <tr>
                                    <td class="border-r border-base-200 p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                                            <div class="flex flex-col gap-2 w-full">
                                                <div class="skeleton h-3 w-24"></div>
                                                <div class="skeleton h-2 w-16"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @for ($c = 0; $c < 15; $c++)
                                        <td class="border-r border-base-200 p-1">
                                            <div class="skeleton h-12 w-full rounded-lg"></div>
                                        </td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ─── Real Table Data ────────────────────────────────────────── --}}
            @if ($readyToLoad)
                <div wire:loading.remove
                    wire:target="month, year, search, perPage, startDate, endDate, resetFilters, gotoPage, nextPage, previousPage"
                    class="overflow-x-auto max-h-150 overflow-y-auto">
                    <table class="table table-sm table-zebra w-full border-separate border-spacing-0">
                        <thead class="sticky top-0 z-20 bg-base-100">
                            <tr>
                                <th
                                    class="sticky left-0 z-30 bg-base-100 border-b border-r border-base-200 min-w-50 text-center">
                                    Personnel</th>
                                @foreach ($this->dates as $date)
                                    <th
                                        class="text-center border-b border-r border-base-200 min-w-15 p-2 {{ \Carbon\Carbon::parse($date)->isToday() ? 'bg-primary/10' : '' }}">
                                        <div class="text-[10px] uppercase opacity-50">
                                            {{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}</div>
                                        <div class="text-sm font-bold">{{ \Carbon\Carbon::parse($date)->format('d') }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $currentOpd = null; 
                                $isSuperAdmin = Auth::user()->hasRole('super-admin');
                            @endphp
                            @forelse ($this->personnels as $p)
                                @if ($isSuperAdmin && $currentOpd !== $p->opd_id)
                                    <tr class="bg-base-200">
                                        <td colspan="{{ count($this->dates) + 1 }}" class="sticky left-0 z-10 py-2 px-4 border-b border-base-200">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-4 bg-primary rounded-full"></div>
                                                <span class="text-[11px] font-black uppercase tracking-[0.2em] text-primary">
                                                    {{ $p->opd->name }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $currentOpd = $p->opd_id; @endphp
                                @endif
                                <tr>
                                    <td class="sticky left-0 z-40 bg-base-100 border-r border-base-200 p-3 w-50">
                                        <div class="flex items-center gap-2 ps-4">
                                            <div class="avatar placeholder">
                                                @if ($p->foto)
                                                    <div class="w-10 rounded-full">
                                                        <img src="{{ asset('storage/' . $p->foto) }}"
                                                            alt="{{ $p->name }}" />
                                                    </div>
                                                @else
                                                    <div class="bg-neutral text-neutral-content w-8 rounded-full">
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
                                            $j = $p->jadwal_map[$date] ?? null;
                                            $isToday = \Carbon\Carbon::parse($date)->isToday();

                                            $cellClass = match ($j->status ?? '') {
                                                'LIBUR' => 'bg-yellow-500 text-white',
                                                default => '',
                                            };

                                            $style =
                                                $j && $j->status === 'SHIFT'
                                                    ? 'background-color: ' .
                                                        ($j->shift->color ?? '#64748b') .
                                                        '; color: white;'
                                                    : '';
                                        @endphp
                                        <td class="text-center border-r border-base-200 p-0 h-14 cursor-pointer hover:opacity-80 transition-all relative {{ $isToday && !$j ? 'bg-primary/10' : '' }} {{ $cellClass }} {{ $j && $j->is_manual ? 'bg-pattern-manual' : '' }}"
                                            style="{{ $style }}"
                                            wire:click="openQuickAdd('{{ $p->id }}', '{{ $date }}')"
                                            wire:loading.class="opacity-40 pointer-events-none"
                                            wire:target="openQuickAdd('{{ $p->id }}', '{{ $date }}')">

                                            <div class="relative w-full h-full flex items-center justify-center">
                                                {{-- Specific Cell Loader --}}
                                                <div wire:loading
                                                    wire:target="openQuickAdd('{{ $p->id }}', '{{ $date }}')"
                                                    class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 flex items-center justify-center z-20">
                                                    <span
                                                        class="loading loading-spinner loading-xs text-primary"></span>
                                                </div>

                                                @if ($j)
                                                    <div
                                                        class="flex flex-col items-center justify-center w-full h-full relative font-bold">
                                                        @if ($j->status === 'SHIFT')
                                                            <span
                                                                class="text-[10px] leading-tight">{{ $j->shift->name ?? 'N/A' }}</span>
                                                            <span class="text-[8px] opacity-80 mt-0.5">
                                                                {{ $j->shift ? \Carbon\Carbon::parse($j->shift->start_time)->format('H:i') : '' }}
                                                            </span>
                                                            <span class="text-[8px] opacity-80 mt-0.1">
                                                                {{ $j->shift ? \Carbon\Carbon::parse($j->shift->end_time)->format('H:i') : '' }}
                                                            </span>
                                                        @else
                                                            <span
                                                                class="text-[10px] whitespace-nowrap">{{ $j->status }}</span>
                                                        @endif

                                                        {{-- Manual Change Badge --}}
                                                        @if ($j && $j->is_manual)
                                                            @php
                                                                $isShift = $j->status === 'SHIFT';
                                                                $iconBg = $isShift ? 'bg-blue-500' : 'bg-yellow-400';
                                                                $iconColor = $isShift
                                                                    ? 'text-white'
                                                                    : 'text-yellow-900';
                                                            @endphp
                                                            <div class="absolute top-0.5 right-0.5 z-10">
                                                                <div
                                                                    class="{{ $iconBg }} {{ $iconColor }} rounded-full p-0.5 shadow-sm border border-white/50">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2"
                                                                        stroke-linecap="round" stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-switch-2 size-2.5">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M3 17h5l1.67 -2.386m3.66 -5.227l1.67 -2.387h6" />
                                                                        <path d="M18 4l3 3l-3 3" />
                                                                        <path d="M3 7h5l7 10h6" />
                                                                        <path d="M18 20l3 -3l-3 -3" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div
                                                        class="w-full h-full flex items-center justify-center opacity-10">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="size-3">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 4.5v15m7.5-7.5h-15" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="32" class="text-center text-sm text-base-content/60 py-12">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="size-12 opacity-20 mb-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                            Tidak ada data personnel atau jadwal ditemukan.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">
                    @if ($readyToLoad)
                        {{ $this->personnels->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Modal Quick Add/Edit ────────────────────────────────────── --}}
    <livewire:admin::jadwal-quick-modal />

    {{-- ─── Modal Delete (Legacy/Confirm) ─────────────────────────────────── --}}
    <dialog id="jadwal-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'jadwal-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'jadwal-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus data jadwal ini?
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('jadwal-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>
</div>
