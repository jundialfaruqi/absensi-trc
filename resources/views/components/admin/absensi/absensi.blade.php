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
}">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Monitoring Absensi</h1>
            <p class="text-sm text-base-content/60 mt-1">Pantau kehadiran personel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
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
                    <thead class="sticky top-0 z-110 bg-base-100">
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
                                    class="sticky left-0 z-120 bg-base-100 border-r border-base-200 p-2 group-hover:bg-base-200 transition-colors">
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
                                    <td wire:click="editAbsensi({{ $p->id }}, '{{ $date }}')"
                                        class="text-center border-base-200 p-2 min-h-16 h-16 cursor-pointer hover:bg-base-100 hover:ring-primary/30 transition-all hover:z-100 relative {{ $cellClass }} {{ $isToday && !$a ? 'bg-primary/5' : '' }}">
                                        @if ($a)
                                            <div class="flex flex-col h-full relative">
                                                {{-- Edited indicator --}}
                                                @if ($a->edited_at)
                                                    <div class="absolute -top-1 -right-1 p-0 z-20">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                                                            class="size-3 text-primary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                            <path
                                                                d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415" />
                                                            <path d="M16 5l3 3" />
                                                        </svg>
                                                    </div>
                                                @endif

                                                {{-- Top Half: Masuk --}}
                                                <div class="flex-1 flex flex-col items-center justify-center py-2">
                                                    @if ($a->foto_masuk)
                                                        <div class="avatar mb-1 group/photo relative z-10"
                                                            x-on:mouseenter="triggerPreview('{{ asset('storage/' . $a->foto_masuk) }}', $event)"
                                                            x-on:mouseleave="hidePreview()">
                                                            <div
                                                                class="w-15 h-15 rounded-full ring-1 ring-primary/30 ring-offset-1 overflow-hidden transition-all duration-300">
                                                                <img src="{{ asset('storage/' . $a->foto_masuk) }}"
                                                                    class="object-cover bg-base-200" loading="lazy" />
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <span
                                                        class="text-[12px] font-bold {{ $a->status_masuk === 'TELAT' ? 'text-error' : 'text-success' }}">
                                                        {{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '--:--' }}
                                                    </span>
                                                    <div class="flex items-center gap-1">
                                                        <span
                                                            class="text-[9px] font-black uppercase tracking-tighter
                                                            @if (in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALFA', 'CUTI'])) text-neutral border-neutral/30 bg-neutral/10
                                                            @elseif($a->status_masuk === 'TELAT')
                                                                text-error border-error/30 bg-error/10
                                                            @elseif($a->status_masuk === 'DINAS')
                                                                text-info border-info/30 bg-info/10
                                                            @else
                                                                text-success border-success/30 bg-success/10 @endif
                                                            border px-1 rounded-xs leading-none">
                                                            {{ $a->status_masuk }}
                                                        </span>
                                                        @if (!is_null($a->is_within_radius))
                                                            <div class="tooltip tooltip-right"
                                                                data-tip="{{ $a->kantor?->name }}: ±{{ $a->jarak_meter }}m">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="3"
                                                                    stroke="currentColor"
                                                                    class="size-3 {{ $a->is_within_radius ? 'text-success' : 'text-error' }}">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>

                                                </div>

                                                {{-- Bottom Half: Pulang --}}
                                                <div class="flex-1 flex flex-col items-center justify-center py-2">
                                                    @if ($a->foto_pulang)
                                                        <div class="avatar mb-1 group/photo relative z-10"
                                                            x-on:mouseenter="triggerPreview('{{ asset('storage/' . $a->foto_pulang) }}', $event)"
                                                            x-on:mouseleave="hidePreview()">
                                                            <div
                                                                class="w-15 h-15 rounded-full ring-1 ring-secondary/30 ring-offset-1 overflow-hidden transition-all duration-300">
                                                                <img src="{{ asset('storage/' . $a->foto_pulang) }}"
                                                                    class="object-cover bg-base-200" loading="lazy" />
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <span
                                                        class="text-[12px] font-bold {{ $a->status_pulang === 'PC' ? 'text-error' : 'text-success' }}">
                                                        {{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '--:--' }}
                                                    </span>
                                                    @if ($a->jam_pulang || in_array($a->status_pulang, ['SAKIT', 'IZIN', 'ALFA', 'CUTI', 'DINAS']))
                                                        <div class="flex items-center gap-1">
                                                            <span
                                                                class="text-[9px] font-black uppercase tracking-tighter
                                                                @if (in_array($a->status_pulang, ['SAKIT', 'IZIN', 'ALFA', 'CUTI'])) text-neutral border-neutral/30 bg-neutral/10
                                                                @elseif($a->status_pulang === 'PC')
                                                                    text-warning border-warning/30 bg-warning/10
                                                                @elseif($a->status_pulang === 'DINAS')
                                                                    text-info border-info/30 bg-info/10
                                                                @else
                                                                    text-success border-success/30 bg-success/10 @endif
                                                                border px-1 rounded-xs leading-none">
                                                                {{ $a->status_pulang ?? 'PULANG' }}
                                                            </span>
                                                            @if (!is_null($a->is_within_radius_pulang))
                                                                <div class="tooltip tooltip-right"
                                                                    data-tip="{{ $a->kantorPulang?->name ?? $a->kantor?->name }}: ±{{ $a->jarak_meter_pulang }}m">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="3" stroke="currentColor"
                                                                        class="size-3 {{ $a->is_within_radius_pulang ? 'text-success' : 'text-error' }}">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif ($j && \Carbon\Carbon::parse($date)->isPast() && !$isToday)
                                            <div class="flex flex-col items-center justify-center opacity-40">
                                                <span class="text-[9px] font-bold uppercase text-error">ALFA</span>
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
            <span>Tidak Absen (ALFA)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-neutral/20 border border-neutral/30 rounded"></div>
            <span>Sakit/Izin/Cuti/Alfa (Manual)</span>
        </div>
        <div class="flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor" class="size-3 text-primary">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415" />
                <path d="M16 5l3 3" />
            </svg>
            <span>Data Diedit Admin</span>
        </div>
        <div class="flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                stroke="currentColor" class="size-3 text-success">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <span>Dalam Radius</span>
        </div>
        <div class="flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                stroke="currentColor" class="size-3 text-error">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <span>Luar Radius</span>
        </div>
    </div>

    {{-- ─── Edit Absensi Modal ────────────────────────────────────────────── --}}
    <dialog id="edit-absensi-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'edit-absensi-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'edit-absensi-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-2xl p-0 overflow-hidden bg-base-100">
            <div class="p-6 border-b border-base-200 bg-base-200/30 flex justify-between items-center">
                <h3 class="font-bold text-lg">
                    {{ $editingPersonnelName }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('edit-absensi-modal').close()">✕</button>
            </div>

            <form wire:submit="saveEdit" class="p-6 space-y-5">
                <div class="bg-primary/5 p-4 flex items-center justify-between rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="size-5 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-black opacity-40 tracking-widest">Tanggal Absen
                            </div>
                            <div class="font-bold uppercase">
                                {{ $editingTanggal ? \Carbon\Carbon::parse($editingTanggal)->translatedFormat('l, d F Y') : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Status Masuk --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Status
                                Masuk</span></label>
                        <select wire:model.live="statusMasuk"
                            class="select select-bordered w-full bg-base-50 focus:border-primary">
                            <option value="">Pilih Status</option>
                            <option value="HADIR">HADIR</option>
                            <option value="TELAT">TELAT</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="IZIN">IZIN</option>
                            <option value="CUTI">CUTI</option>
                            <option value="DINAS">DINAS</option>
                            <option value="ALFA">ALFA</option>
                        </select>
                        @error('statusMasuk')
                            <span class="text-error text-[10px] mt-1 font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Status Pulang --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Status
                                Pulang</span></label>
                        <select wire:model.live="statusPulang"
                            class="select select-bordered w-full bg-base-50 focus:border-primary">
                            <option value="">Pilih Status</option>
                            <option value="HADIR">HADIR</option>
                            <option value="PC">PC (Pulang Cepat)</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="IZIN">IZIN</option>
                            <option value="CUTI">CUTI</option>
                            <option value="DINAS">DINAS</option>
                            <option value="ALFA">ALFA</option>
                        </select>
                    </div>

                    {{-- Jam Masuk --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Jam
                                Masuk</span></label>
                        <div class="relative">
                            <input type="time" wire:model="jamMasuk" step="60"
                                class="input input-bordered w-full pl-10 bg-base-50 focus:border-primary" />
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Jam Pulang --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-medium">Jam
                                Pulang</span></label>
                        <div class="relative">
                            <input type="time" wire:model="jamPulang" step="60"
                                class="input input-bordered w-full pl-10 bg-base-50 focus:border-primary" />
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conditional Fields --}}
                <div class="grid grid-cols-1 gap-4">
                    @if (in_array($statusMasuk, ['SAKIT', 'IZIN']) || in_array($statusPulang, ['SAKIT', 'IZIN']))
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm font-medium">
                                    Nomor Surat (Sakit/Izin)
                                </span></label>
                            <input type="text" wire:model="nomorSurat" placeholder="Contoh: 123/SKP/IV/2026..."
                                class="input w-full" />
                        </div>
                    @endif

                    @if ($statusMasuk === 'CUTI' || $statusPulang === 'CUTI')
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm font-medium">Jenis
                                    Cuti</span></label>
                            <select wire:model="cutiId"
                                class="select select-bordered w-full bg-base-50 focus:border-primary">
                                <option value="">Pilih Jenis Cuti</option>
                                @foreach ($this->cutis as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Alasan Edit --}}
                <div class="form-control w-full">
                    <label class="label py-1.5"><span class="label-text text-sm font-medium">
                            Alasan Perubahan / Keterangan
                        </span></label>
                    <textarea wire:model="alasanEdit"
                        class="textarea textarea-bordered w-full h-32 bg-base-50 focus:border-primary border-base-300 transition-all"
                        placeholder="Jelaskan alasan pengeditan data secara detail untuk justifikasi perubahan data"></textarea>
                    @error('alasanEdit')
                        <span class="text-error text-[10px] mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div
                    class="modal-action bg-base-200/40 p-5 -mx-6 -mb-6 border-t border-base-200 flex justify-end gap-3 rounded-b-xl">
                    @if ($isEdited)
                        <button type="button" wire:click="resetToOriginal" class="btn btn-error mr-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Reset
                        </button>
                    @endif
                    <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('edit-absensi-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary">
                        <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                        <span wire:loading wire:target="saveEdit" class="loading loading-spinner"></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>
    {{-- Teleport Preview Overlay --}}
    @teleport('body')
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90" class="fixed pointer-events-none z-[9999]"
            :style="`left: ${previewX}px; top: ${previewY}px; transform: translate(-50%, -100%) translateY(-20px);`"
            x-cloak>
            <div class="bg-base-100 p-1.5 rounded-2xl shadow-2xl ring-1 ring-base-content/10">
                <img :src="previewUrl" class="w-64 h-64 object-cover rounded-xl shadow-inner bg-base-200" />
            </div>
        </div>
    @endteleport
</div>
