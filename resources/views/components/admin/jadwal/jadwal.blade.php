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
        </div>

        <div class="flex flex-wrap gap-2 justify-end">
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
                                <td class="sticky left-0 z-10 bg-base-100 border-r border-base-200 p-2">
                                    <div class="flex items-center gap-2 ps-4">
                                        <div class="avatar placeholder">
                                            @if ($p->foto)
                                                <div class="w-8 rounded-full">
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
                                            <div class="text-[10px] opacity-50 truncate max-w-30">
                                                {{ $p->penugasan?->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($this->dates as $date)
                                    @php
                                        $j = $p->jadwal_map[$date] ?? null;
                                        $isToday = \Carbon\Carbon::parse($date)->isToday();

                                        $cellClass = match ($j->status ?? '') {
                                            'LIBUR' => 'bg-neutral text-neutral-content',
                                            default => '',
                                        };

                                        $style =
                                            $j && $j->status === 'SHIFT'
                                                ? 'background-color: ' .
                                                    ($j->shift->color ?? '#64748b') .
                                                    '; color: white;'
                                                : '';
                                    @endphp
                                    <td class="text-center border-r border-base-200 p-0 h-14 cursor-pointer hover:opacity-80 transition-all {{ $isToday && !$j ? 'bg-primary/10' : '' }} {{ $cellClass }}"
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
                                                @else
                                                    <span
                                                        class="text-[10px] whitespace-nowrap">{{ $j->status }}</span>
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
            <h3 class="font-bold text-lg mb-1">Set Jadwal / Status</h3>
            <p class="text-xs text-base-content/60 mb-6">
                {{ $quickDate ? \Carbon\Carbon::parse($quickDate)->translatedFormat('l, d M Y') : '' }}
            </p>

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
                                    <input type="radio" wire:model.live="quickStatus" value="{{ $status }}"
                                        class="radio radio-primary radio-xs">
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
                                        <input type="radio" wire:model="quickShiftId" value="{{ $s->id }}"
                                            class="radio radio-primary radio-sm">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-xs">{{ $s->name }}</span>
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
