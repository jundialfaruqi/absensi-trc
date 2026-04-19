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
                                        class="text-center border-r border-base-200 p-1 min-h-16 h-16 cursor-pointer hover:ring-2 hover:ring-primary/30 transition-all {{ $cellClass }} {{ $isToday && !$a ? 'bg-primary/5' : '' }}">
                                        @if ($a)
                                            <div class="flex flex-col items-center justify-center h-full gap-0.5 relative">
                                                {{-- Edited indicator --}}
                                                @if($a->edited_at)
                                                    <div class="absolute top-0 right-0 p-0.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-2 text-primary opacity-70">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                        </svg>
                                                    </div>
                                                @endif

                                                {{-- Masuk Section --}}
                                                <div class="flex flex-col items-center">
                                                    <div class="flex items-center gap-1">
                                                        <span
                                                            class="text-[7px] font-black uppercase tracking-tighter 
                                                            @if(in_array($a->status_masuk, ['SAKIT', 'IZIN', 'ALFA', 'CUTI']))
                                                                text-neutral border-neutral/30 bg-neutral/10
                                                            @elseif($a->status_masuk === 'TELAT')
                                                                text-error border-error/30 bg-error/10
                                                            @elseif($a->status_masuk === 'DINAS')
                                                                text-info border-info/30 bg-info/10
                                                            @else
                                                                text-success border-success/30 bg-success/10
                                                            @endif
                                                            border px-1 rounded-xs leading-none">
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
                                                    @if ($a->jam_pulang || in_array($a->status_pulang, ['SAKIT', 'IZIN', 'ALFA', 'CUTI', 'DINAS']))
                                                        <div class="flex items-center gap-1 mt-0.5">
                                                            <span
                                                                class="text-[7px] font-black uppercase tracking-tighter 
                                                                @if(in_array($a->status_pulang, ['SAKIT', 'IZIN', 'ALFA', 'CUTI']))
                                                                    text-neutral border-neutral/30 bg-neutral/10
                                                                @elseif($a->status_pulang === 'PC')
                                                                    text-warning border-warning/30 bg-warning/10
                                                                @elseif($a->status_pulang === 'DINAS')
                                                                    text-info border-info/30 bg-info/10
                                                                @else
                                                                    text-success border-success/30 bg-success/10
                                                                @endif
                                                                border px-1 rounded-xs leading-none">
                                                                {{ $a->status_pulang ?? 'PULANG' }}
                                                            </span>
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
        <div class="flex items-center gap-1.5">
            <div class="w-3 h-3 bg-neutral/20 border border-neutral/30 rounded"></div>
            <span>Sakit/Izin/Cuti/Alpha (Manual)</span>
        </div>
        <div class="flex items-center gap-4 ml-4 pl-4 border-l border-base-300">
            <div class="flex items-center gap-1 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
                <span class="text-[10px]">Data Diedit Admin</span>
            </div>
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

    {{-- ─── Edit Absensi Modal ────────────────────────────────────────────── --}}
    <dialog id="edit-absensi-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'edit-absensi-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'edit-absensi-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-2xl p-0 overflow-hidden bg-base-100">
            <div class="p-6 border-b border-base-200 bg-base-200/30 flex justify-between items-center">
                <h3 class="font-bold text-lg">
                    Edit Absensi: <span class="text-primary">{{ $editingPersonnelName }}</span>
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="document.getElementById('edit-absensi-modal').close()">✕</button>
            </div>

            <form wire:submit="saveEdit" class="p-6 space-y-5">
                <div class="bg-primary/5 p-4 rounded-xl flex items-center justify-between border border-primary/10 shadow-inner">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-black opacity-40 tracking-widest">Tanggal Monitoring</div>
                            <div class="font-bold text-primary">{{ $editingTanggal ? \Carbon\Carbon::parse($editingTanggal)->translatedFormat('l, d F Y') : '' }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Status Masuk --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-bold text-base-content/70">Status Masuk</span></label>
                        <select wire:model.live="statusMasuk" class="select select-bordered w-full bg-base-50 focus:border-primary">
                            <option value="">Pilih Status</option>
                            <option value="HADIR">HADIR</option>
                            <option value="TELAT">TELAT</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="IZIN">IZIN</option>
                            <option value="CUTI">CUTI</option>
                            <option value="DINAS">DINAS</option>
                            <option value="ALFA">ALFA</option>
                        </select>
                        @error('statusMasuk') <span class="text-error text-[10px] mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Pulang --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-bold text-base-content/70">Status Pulang</span></label>
                        <select wire:model.live="statusPulang" class="select select-bordered w-full bg-base-50 focus:border-primary">
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
                        <label class="label py-1"><span class="label-text font-bold text-base-content/70">Jam Masuk</span></label>
                        <div class="relative">
                            <input type="time" wire:model="jamMasuk" step="60" class="input input-bordered w-full pl-10 bg-base-50 focus:border-primary" />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Jam Pulang --}}
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text font-bold text-base-content/70">Jam Pulang</span></label>
                        <div class="relative">
                            <input type="time" wire:model="jamPulang" step="60" class="input input-bordered w-full pl-10 bg-base-50 focus:border-primary" />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conditional Fields --}}
                <div class="grid grid-cols-1 gap-4">
                    @if(in_array($statusMasuk, ['SAKIT', 'IZIN']) || in_array($statusPulang, ['SAKIT', 'IZIN']))
                        <div class="form-control animate-in fade-in slide-in-from-top-1">
                            <label class="label py-1"><span class="label-text font-bold text-warning flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                Nomor Surat (Sakit/Izin)
                            </span></label>
                            <input type="text" wire:model="nomorSurat" placeholder="Contoh: 123/SKP/IV/2026..." class="input input-bordered w-full border-warning/30 bg-warning/5 focus:border-warning" />
                        </div>
                    @endif

                    @if($statusMasuk === 'CUTI' || $statusPulang === 'CUTI')
                        <div class="form-control animate-in fade-in slide-in-from-top-1">
                            <label class="label py-1"><span class="label-text font-bold text-info flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.756.75.756h1.384c.414 0 .75-.342.75-.756 0-.23-.035-.454-.1-.664m-2.684 0a4.5 4.5 0 0 0-2.25 4.013c0 2.485 2.015 4.5 4.5 4.5s4.5-2.015 4.5-4.5a4.5 4.5 0 0 0-2.25-4.013m-6.75 12.75h1.5m10.5-3v-4.5m2.25 4.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                Jenis Cuti
                            </span></label>
                            <select wire:model="cutiId" class="select select-bordered border-info/30 bg-info/5 w-full focus:border-info">
                                <option value="">Pilih Jenis Cuti</option>
                                @foreach($this->cutis as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Alasan Edit --}}
                <div class="form-control w-full">
                    <label class="label py-1.5"><span class="label-text font-bold text-base-content/80 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Alasan Perubahan / Keterangan
                    </span></label>
                    <textarea wire:model="alasanEdit" class="textarea textarea-bordered w-full h-32 bg-base-50 focus:border-primary border-base-300 shadow-sm transition-all" placeholder="Jelaskan alasan pengeditan data secara detail agar tercatat di log sistem..."></textarea>
                    <label class="label">
                        <span class="label-text-alt text-base-content/40 italic flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                            </svg>
                            Alasan wajib diisi untuk riwayat audit sistem
                        </span>
                    </label>
                    @error('alasanEdit') <span class="text-error text-[10px] mt-1 font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="modal-action bg-base-200/40 p-5 -mx-6 -mb-6 border-t border-base-200 flex justify-end gap-3 rounded-b-xl">
                    @if($isEdited)
                        <button type="button" 
                            wire:click="resetToOriginal" 
                            class="btn btn-outline btn-error btn-sm rounded-lg mr-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Reset ke Original
                        </button>
                    @endif
                    <button type="button" class="btn btn-ghost btn-sm rounded-lg border border-base-300" onclick="document.getElementById('edit-absensi-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-lg px-10 shadow-lg shadow-primary/20">
                        <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                        <span wire:loading wire:target="saveEdit" class="loading loading-spinner"></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</div>
