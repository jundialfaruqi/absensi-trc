<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Manajemen Jadwal</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data jadwal shift personnel per bulan</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
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

    {{-- ─── Toolbar: Search + Filters + Buttons ──────────────────────────────────────── --}}
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
            <div class="relative w-full sm:w-auto">
                <input type="text" placeholder="Cari nama personnel..." wire:model.live.debounce.400ms="search"
                    class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
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

                    .bg-pattern-manual {
                        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent) !important;
                        background-size: 8px 8px !important;
                    }
                </style>

                <div class="join w-full sm:w-auto">
                    <div
                        class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                        Dari</div>
                    <input type="date" id="startDate" wire:model.live="startDate"
                        class="input input-bordered join-item w-full sm:w-auto [color-scheme:light] dark:[color-scheme:dark]" />
                    <div
                        class="join-item flex items-center btn btn-disabled pointer-events-none rounded-left-md px-3 text-[10px] uppercase text-base-content">
                        S/D</div>
                    <input type="date" id="endDate" wire:model.live="endDate"
                        class="input input-bordered join-item w-full sm:w-auto [color-scheme:light] dark:[color-scheme:dark]" />

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

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto max-h-150 overflow-y-auto">
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
                        @forelse ($this->personnels as $p)
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
                                        wire:click="openQuickAdd('{{ $p->id }}', '{{ $date }}')">
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
                                                        $iconColor = $isShift ? 'text-white' : 'text-yellow-900';
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
                                            <div class="w-full h-full flex items-center justify-center opacity-10">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="size-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="32" class="text-center text-sm text-base-content/60 py-12">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-12 opacity-20 mb-3">
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
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->personnels->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Modal Quick Add/Edit ────────────────────────────────────── --}}
    <dialog id="quick-add-modal" class="modal" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'quick-add-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'quick-add-modal' && $el.close()">
        <div class="modal-box max-w-md">
            {{-- Tabs --}}
            <div class="tabs tabs-boxed mb-6 bg-base-200/50 p-1">
                <button type="button" wire:click="$set('activeTab', 'quick')"
                    class="tab tab-sm flex-1 {{ $activeTab === 'quick' ? 'tab-active !bg-base-100 shadow-sm' : '' }}">
                    Quick Edit
                </button>
                <button type="button" wire:click="$set('activeTab', 'swap')"
                    class="tab tab-sm flex-1 {{ $activeTab === 'swap' ? 'tab-active !bg-base-100 shadow-sm' : '' }}">
                    Tukar Shift
                </button>
            </div>

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-lg">
                        {{ $activeTab === 'quick' ? 'Set Jadwal / Status' : 'Tukar Shift (2 Arah)' }}
                    </h3>
                    <p class="text-xs text-base-content/60">
                        {{ $quickDate ? \Carbon\Carbon::parse($quickDate)->translatedFormat('l, d M Y') : '' }}
                    </p>
                </div>
            </div>

            @if ($activeTab === 'quick')
                <form wire:submit="saveQuickJadwal">
                    <div class="space-y-6">
                        {{-- Status Selection --}}
                        <div class="form-control">
                            <label class="label mb-1 px-1">
                                <span class="label-text font-medium text-xs">Pilih Status Kehadiran</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (['SHIFT', 'LIBUR'] as $status)
                                    <label
                                        class="label cursor-pointer justify-start gap-2 p-2 border border-base-200 rounded-lg hover:bg-base-200 transition-all {{ $quickStatus == $status ? 'bg-primary/10 border-primary' : '' }}">
                                        <input type="radio" wire:model.live="quickStatus"
                                            value="{{ $status }}" class="radio radio-primary radio-xs">
                                        <span class="text-xs font-bold">{{ $status }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Shift Selection (Only if status is SHIFT) --}}
                        @if ($quickStatus === 'SHIFT')
                            <div class="form-control w-full animate-in fade-in slide-in-from-top-1">
                                <label class="label mb-1 px-1">
                                    <span class="label-text font-medium text-xs">Pilih Shift</span>
                                </label>
                                <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto pr-1">
                                    @foreach ($this->shifts as $s)
                                        <label
                                            class="label cursor-pointer justify-start gap-3 p-3 border border-base-200 rounded-xl hover:bg-base-200 transition-all {{ $quickShiftId == $s->id ? 'bg-primary/10 border-primary' : '' }}">
                                            <input type="radio" wire:model="quickShiftId"
                                                value="{{ $s->id }}" class="radio radio-primary radio-sm">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-xs">{{ $s->name }}</span>
                                                <span class="text-[10px] opacity-60">{{ $s->keterangan }}</span>
                                                <span
                                                    class="text-[10px] opacity-60">{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                                    - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('quickShiftId')
                                    <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Keterangan (Only for non-SHIFT) --}}
                        @if ($quickStatus !== 'SHIFT')
                            <div class="form-control w-full animate-in fade-in slide-in-from-top-1">
                                <label class="label mb-1 px-1">
                                    <span class="label-text font-medium text-xs">Keterangan Status</span>
                                </label>
                                <textarea wire:model="quickKeterangan" class="textarea textarea-bordered w-full h-24 text-sm focus:textarea-primary"
                                    placeholder="Tulis catatan alasan di sini..."></textarea>
                                @error('quickKeterangan')
                                    <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="modal-action flex justify-between gap-2 mt-8">
                        @if (\App\Models\Jadwal::where('personnel_id', $quickPersonnelId)->where('tanggal', $quickDate)->exists())
                            <button type="button" class="btn btn-error btn-outline btn-sm"
                                wire:click="deleteQuickJadwal">
                                Hapus
                            </button>
                        @else
                            <div></div>
                        @endif
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-ghost btn-sm"
                                x-on:click="document.getElementById('quick-add-modal').close()">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm px-6" wire:loading.attr="disabled">
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="space-y-6">
                    {{-- Origin Personnel Info --}}
                    <div class="bg-base-200/50 rounded-xl p-3 border border-base-200">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-primary text-primary-content rounded-full w-8 flex items-center justify-center">
                                    <span
                                        class="text-xs">{{ substr($this->originPersonnel->name ?? 'P', 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider opacity-60 font-bold">Pemohon
                                    Tukar:</span>
                                <span
                                    class="text-sm font-bold text-primary">{{ $this->originPersonnel->name ?? '-' }}</span>
                                <span class="text-[10px] font-medium opacity-70">
                                    Jadwal Asli:
                                    <span class="font-bold text-primary italic">
                                        {{ ($this->originJadwal->status ?? '') === 'SHIFT' ? $this->originJadwal->shift->name ?? 'SHIFT' : $this->originJadwal->status ?? '-' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Select Target Personnel --}}
                    <div class="form-control">
                        <label class="label mb-1 px-1">
                            <span class="label-text font-medium text-xs text-base-content/70">Pilih Pengganti (Hanya
                                Personel Libur Hari-1)</span>
                        </label>
                        <select wire:model.live="swapTargetPersonnelId"
                            class="select select-bordered w-full select-sm focus:select-primary">
                            <option value="">-- Pilih Personel --</option>
                            @foreach ($this->availableSubstitutes as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-base-content/50 mt-2 italic">* Personel yang sudah "dipesan" di
                            tanggal ini tidak akan muncul.</p>
                    </div>

                    @if ($swapTargetPersonnelId)
                        <div class="form-control animate-in fade-in zoom-in duration-300">
                            <label class="label mb-1 px-1">
                                <span class="label-text font-medium text-xs text-base-content/70">Pilih Tanggal
                                    Pembayaran (Payback):</span>
                            </label>

                            @if (empty($paybackOptions))
                                <div
                                    class="bg-warning/10 text-warning text-[10px] p-3 rounded-lg border border-warning/20">
                                    Tidak ditemukan tanggal payback yang tersedia dalam 30 hari ke depan.
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto pr-1">
                                    @foreach ($paybackOptions as $option)
                                        <label
                                            class="flex items-center justify-between p-3 border border-base-200 rounded-xl cursor-pointer hover:bg-base-200 transition-all {{ $selectedPaybackDate === $option['date'] ? 'bg-primary/5 border-primary/30 ring-1 ring-primary/30' : '' }}">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" wire:model="selectedPaybackDate"
                                                    value="{{ $option['date'] }}"
                                                    class="radio radio-primary radio-xs">
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-bold">{{ $option['label'] }}</span>
                                                    <span class="text-[9px] opacity-60">Jadwal Asli:
                                                        {{ $option['shift_name'] }}</span>
                                                </div>
                                            </div>
                                            @if ($loop->first)
                                                <div class="badge badge-primary badge-outline text-[8px] h-4">Terdekat
                                                </div>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="modal-action flex justify-end gap-2 mt-8 pt-4 border-t border-base-200">
                    <button type="button" class="btn btn-ghost btn-sm"
                        x-on:click="document.getElementById('quick-add-modal').close()">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm px-6" wire:click="executeSwapGuling"
                        wire:loading.attr="disabled" @if (!$swapTargetPersonnelId || !$selectedPaybackDate) disabled @endif>
                        <span wire:loading wire:target="executeSwapGuling"
                            class="loading loading-spinner loading-xs"></span>
                        Proses Tukar Shift
                    </button>
                </div>
            @endif
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

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
