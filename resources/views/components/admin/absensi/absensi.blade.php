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
        </div>

        <div class="flex flex-wrap gap-2 justify-end">
            {{-- Optional Action Buttons for Absensi --}}
            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Cari nama personnel..." wire:model.live.debounce.400ms="search"
                    class="input input-bordered w-full pl-10 pr-10 bg-base-100" />
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
                                                    <div class="flex items-center gap-1">
                                                        <span
                                                            class="text-[7px] font-black uppercase tracking-tighter {{ $a->status_masuk === 'TELAT' ? 'text-error border-error/30 bg-error/10' : 'text-success border-success/30 bg-success/10' }} border px-1 rounded-xs leading-none">
                                                            {{ $a->status_masuk }}
                                                        </span>
                                                        @if(!is_null($a->is_within_radius))
                                                            <div class="tooltip tooltip-right" data-tip="{{ $a->kantor?->name }}: ±{{ $a->jarak_meter }}m">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" 
                                                                    class="size-2.5 {{ $a->is_within_radius ? 'text-success' : 'text-error' }}">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
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
                                                        <div class="flex items-center gap-1 mt-0.5">
                                                            <span
                                                                class="text-[7px] font-black uppercase tracking-tighter {{ $a->status_pulang === 'PC' ? 'text-warning border-warning/30 bg-warning/10' : 'text-success border-success/30 bg-success/10' }} border px-1 rounded-xs leading-none">
                                                                {{ $a->status_pulang }}
                                                            </span>
                                                            @if(!is_null($a->is_within_radius))
                                                                <div class="tooltip tooltip-right" data-tip="{{ $a->kantor?->name }}: ±{{ $a->jarak_meter }}m">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" 
                                                                        class="size-2.5 {{ $a->is_within_radius ? 'text-success' : 'text-error' }}">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
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
            <span>Hadir tepat waktu (HADIR)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-error/20 border border-error/30 rounded"></div>
            <span>Telat (TELAT)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-base-300/50 border border-base-400/50 rounded"></div>
            <span>Tidak Absen (ALPHA)</span>
        </div>
        <div class="flex items-center gap-4 ml-4 pl-4 border-l border-base-300">
            <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-3 text-success">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span class="text-[10px]">Dalam Radius</span>
            </div>
            <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-3 text-error">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span class="text-[10px]">Luar Radius</span>
            </div>
        </div>
    </div>
</div>
