<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-black uppercase">Anomali Lokasi</h1>
                @if ($this->totalAnomalies > 0)
                    <div class="badge badge-error badge-sm font-bold gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        {{ $this->totalAnomalies }}
                    </div>
                @endif
            </div>
            <p class="text-sm text-base-content/60 mt-1">Absensi yang terdeteksi memiliki anomali lokasi (kemungkinan
                Fake GPS)</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li><a href="#">Settings</a></li>
                <li>
                    <span class="text-base-content font-bold">Anomali Lokasi</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="join">
                <span class="btn btn-disabled join-item text-base-content pointer-events-none">Show</span>
                <select wire:model.live="perPage" class="select select-bordered join-item w-20">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            @if (Auth::user()->hasRole('super-admin'))
                <select wire:model.live="selectedOpdId" class="select select-bordered w-full sm:w-64">
                    <option value="">-- Semua OPD --</option>
                    @foreach ($this->opds as $opd)
                        <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                    @endforeach
                </select>
            @endif

            <div class="join w-full sm:w-auto">
                <div
                    class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                    Dari</div>
                <input type="date" wire:model.live="dateFrom"
                    class="input input-bordered join-item w-full sm:w-auto scheme-light dark:scheme-dark text-base-content/60" />
                <div
                    class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                    S/D</div>
                <input type="date" wire:model.live="dateTo"
                    class="input input-bordered join-item w-full sm:w-auto scheme-light dark:scheme-dark text-base-content/60" />

                @if ($dateFrom || $dateTo)
                    <button type="button" wire:click="resetFilters" class="btn join-item px-3 text-error btn-bordered">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Cari nama personnel..." wire:model.live.debounce.400ms="search"
                class="input placeholder:text-base-content/60 input-bordered w-full pl-10 pr-10 bg-base-100" />
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

    {{-- ─── Anomaly Table ─────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-100 uppercase text-[10px] tracking-widest text-base-content/50">
                        <tr>
                            <th class="p-4">Tanggal</th>
                            <th>Personnel</th>
                            <th>Tipe</th>
                            <th>Waktu</th>
                            <th>Koordinat</th>
                            <th>Jarak</th>
                            <th>Alasan Anomali</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        @forelse ($this->anomalies as $item)
                            <tr class="group">
                                <td class="p-4">
                                    <div class="font-bold">{{ $item->tanggal->translatedFormat('d F Y') }}</div>
                                    <div class="text-[10px] opacity-60 uppercase">
                                        {{ $item->tanggal->translatedFormat('l') }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                @if ($item->personnel->foto)
                                                    <img src="{{ asset('storage/' . $item->personnel->foto) }}" />
                                                @else
                                                    <span>{{ strtoupper(substr($item->personnel->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $item->personnel->name }}</div>
                                            <div class="text-[10px] opacity-50 uppercase">
                                                {{ $item->personnel->opd->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($item->jam_masuk && !$item->jam_pulang)
                                        <div class="badge badge-info badge-sm font-bold gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor" class="size-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                            </svg>
                                            MASUK
                                        </div>
                                    @else
                                        <div class="badge badge-warning badge-sm font-bold gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                                                class="size-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                            </svg>
                                            M & P
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-mono">
                                        {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '--:--' }}
                                        -
                                        {{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '--:--' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="font-mono text-[10px]">
                                        <div>IN: {{ $item->lat_masuk }}, {{ $item->lng_masuk }}</div>
                                        @if ($item->lat_pulang)
                                            <div class="opacity-60">OUT: {{ $item->lat_pulang }},
                                                {{ $item->lng_pulang }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($item->jarak_meter)
                                        <div
                                            class="font-bold {{ $item->is_within_radius ? 'text-success' : 'text-error' }}">
                                            {{ number_format($item->jarak_meter) }}m
                                        </div>
                                    @else
                                        <span class="opacity-30">-</span>
                                    @endif
                                </td>
                                <td class="max-w-xs whitespace-normal">
                                    <div class="flex items-start gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor"
                                            class="size-4 text-error shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <span class="text-error font-semibold leading-relaxed">
                                            {{ $item->anomaly_reason ?? 'Anomali tidak diketahui' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-20">
                                    <div class="flex flex-col items-center opacity-30">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-12 mb-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="text-sm font-bold uppercase tracking-widest">Tidak ada anomali
                                            terdeteksi</span>
                                        <span class="text-[11px] mt-1">Semua data absensi terlihat normal</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-base-200">
                {{ $this->anomalies->links() }}
            </div>
        </div>
    </div>
</div>
