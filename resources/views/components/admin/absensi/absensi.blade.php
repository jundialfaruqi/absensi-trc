<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Monitoring Absensi</h1>
            <p class="text-sm text-base-content/60 mt-1">Pantau kehadiran personel secara real-time</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data Master</li>
                <li>
                    <a href="{{ route('absensi') }}">
                        <span class="text-base-content">Monitoring Absensi</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Stats Banner ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-4 flex flex-row items-center gap-4">
                <div class="p-3 bg-primary/10 text-primary rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm opacity-60">Total Logs</div>
                    <div class="text-2xl font-bold">{{ $this->stats['total_logs'] }}</div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-4 flex flex-row items-center gap-4">
                <div class="p-3 bg-success/10 text-success rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm opacity-60">Tepat Waktu</div>
                    <div class="text-2xl font-bold text-success">{{ $this->stats['hadir_tepat_waktu'] }}</div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200">
            <div class="card-body p-4 flex flex-row items-center gap-4">
                <div class="p-3 bg-error/10 text-error rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm opacity-60">Terlambat</div>
                    <div class="text-2xl font-bold text-error">{{ $this->stats['terlambat'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Matrix Toolbar ──────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 mb-6">
        <div class="card-body p-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- Left Side: Filters Group --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="join shadow-xs border border-base-200">
                        <div class="join-item btn btn-sm bg-base-200 pointer-events-none px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-4 opacity-60">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <select wire:model.live="month"
                            class="select select-sm join-item focus:outline-none border-l border-base-200 min-w-35 font-medium">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ sprintf('%02d', $m) }}">
                                    {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                        <select wire:model.live="year"
                            class="select select-sm join-item focus:outline-none border-l border-base-200 w-22.5 font-medium">
                            @for ($y = \Carbon\Carbon::now()->year - 2; $y <= \Carbon\Carbon::now()->year + 1; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="join shadow-xs border border-base-200">
                        <div
                            class="join-item btn btn-sm bg-base-200 pointer-events-none px-3 text-[10px] uppercase font-bold opacity-60">
                            Limit</div>
                        <select wire:model.live="perPage"
                            class="select select-sm join-item focus:outline-none border-l border-base-200 font-medium">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                {{-- Right Side: Search --}}
                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <div class="relative w-full lg:w-72">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari personnel..."
                            class="input input-bordered input-sm w-full pl-9 bg-base-50 focus:bg-base-100 transition-all shadow-xs" />
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Absensi Matrix ────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-400px)]">
                <table class="table table-sm table-zebra w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-20 bg-base-100">
                        <tr>
                            <th
                                class="sticky left-0 z-30 bg-base-100 border-b border-r border-base-200 min-w-50 text-center">
                                Personnel
                            </th>
                            @foreach ($this->dates as $date)
                                <th
                                    class="text-center border-b border-r border-base-200 min-w-20 p-2 {{ \Carbon\Carbon::parse($date)->isToday() ? 'bg-primary/10' : '' }}">
                                    <div class="text-[10px] uppercase opacity-50">
                                        {{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}
                                    </div>
                                    <div class="text-sm font-bold">{{ \Carbon\Carbon::parse($date)->format('d') }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->personnels as $p)
                            <tr class="group">
                                <td
                                    class="sticky left-0 z-10 bg-base-100 border-r border-base-200 p-2 group-hover:bg-base-200 transition-colors">
                                    <div class="flex items-center gap-2 ps-4">
                                        <div class="avatar placeholder">
                                            @if ($p->foto)
                                                <div class="w-8 rounded-full border border-base-300">
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
                                            <div class="font-bold text-xs truncate max-w-30">{{ $p->name }}</div>
                                            <div class="text-[10px] opacity-50 truncate max-w-30">
                                                {{ $p->penugasan?->name ?? 'N/A' }}</div>
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
                                            $cellClass = $a->status_masuk === 'HADIR' ? 'bg-success/20' : 'bg-error/20';
                                        } elseif ($j && \Carbon\Carbon::parse($date)->isPast()) {
                                            $cellClass = 'bg-base-300/50';
                                        }
                                    @endphp
                                    <td
                                        class="text-center border-r border-base-200 p-1 min-h-16 h-16 {{ $cellClass }} {{ $isToday && !$a ? 'bg-primary/5' : '' }}">
                                        @if ($a)
                                            <div class="flex flex-col items-center justify-center h-full gap-0.5">
                                                {{-- Masuk Section --}}
                                                <div class="flex flex-col items-center">
                                                    <span
                                                        class="text-[7px] font-black uppercase tracking-tighter {{ $a->status_masuk === 'TELAT' ? 'text-error border-error/30 bg-error/10' : 'text-success border-success/30 bg-success/10' }} border px-1 rounded-[2px] leading-none mb-0.5">
                                                        {{ $a->status_masuk }}
                                                    </span>
                                                    <span
                                                        class="text-[10px] font-bold {{ $a->status_masuk === 'TELAT' ? 'text-error' : 'text-success' }}">
                                                        {{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '--:--' }}
                                                    </span>
                                                </div>

                                                {{-- Pulang Section --}}
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[10px] font-medium opacity-60">
                                                        {{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '--:--' }}
                                                    </span>
                                                    @if ($a->jam_pulang)
                                                        <span
                                                            class="text-[7px] font-black uppercase tracking-tighter {{ $a->status_pulang === 'PC' ? 'text-warning border-warning/30 bg-warning/10' : 'text-success border-success/30 bg-success/10' }} border px-1 rounded-[2px] leading-none mt-0.5">
                                                            {{ $a->status_pulang }}
                                                        </span>
                                                    @else
                                                        <div
                                                            class="text-[7px] font-bold text-base-content/20 uppercase mt-0.5">
                                                            --</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday)
                                            <div class="flex flex-col items-center justify-center opacity-40">
                                                <span class="text-[9px] font-bold uppercase text-error">ALPHA</span>
                                            </div>
                                        @elseif ($j)
                                            <div class="flex flex-col items-center justify-center opacity-30 mt-1">
                                                @if ($j->shift)
                                                    <div class="text-[9px] font-medium">{{ $j->shift->name }}</div>
                                                    <div class="text-[8px]">
                                                        {{ \Carbon\Carbon::parse($j->shift->start_time)->format('H:i') }}
                                                    </div>
                                                @else
                                                    <div class="text-[9px] font-bold uppercase">{{ $j->status }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($this->dates) + 1 }}" class="text-center py-10 opacity-50">
                                    Tidak ada data personnel
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-base-200 bg-base-50">
                {{ $this->personnels->links() }}
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 text-xs opacity-60">
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-success/20 border border-success/30 rounded"></div>
            <span>Hadir Tepat Waktu</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-error/20 border border-error/30 rounded"></div>
            <span>Terlambat</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-base-300/50 border border-base-400/50 rounded"></div>
            <span>Jadwal Terlewati (Tanpa Absen)</span>
        </div>
    </div>
</div>
