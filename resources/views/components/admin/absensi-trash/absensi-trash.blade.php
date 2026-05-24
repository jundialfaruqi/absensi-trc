<div wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Kotak Sampah Absensi</h1>
            <p class="text-sm text-base-content/60 mt-1">Data absensi yang telah dihapus</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Absensi</li>
                <li>
                    <a href="{{ route('absensi.trash') }}">
                        <span class="text-base-content font-bold">Kotak Sampah</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-auto">
                <input type="text" placeholder="Nama personnel..." wire:model.live.debounce.400ms="search"
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
            <select wire:model.live="filterOpd" class="select select-bordered w-full sm:w-auto">
                <option value="">Semua OPD</option>
                @foreach ($this->opds as $opd)
                    <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                @endforeach
            </select>
            <style>
                input[type="date"]::-webkit-calendar-picker-indicator {
                    filter: invert(0.5);
                }
            </style>
            <input type="date" wire:model.live="filterDate"
                class="input input-bordered w-full sm:w-auto bg-base-100 text-base-content" />
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if (count($selectedIds) > 0)
                <button type="button" wire:click="confirmBulkForceDelete" class="btn btn-error btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Hapus Terpilih ({{ count($selectedIds) }})
                </button>
            @endif
            <button type="button" wire:click="confirmEmptyTrash" class="btn btn-error btn-outline btn-sm gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Kosongkan Kotak Sampah
            </button>
            <div class="join">
                <span
                    class="btn btn-disabled btn-sm join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                <select wire:model.live="perPage" class="select select-sm join-item w-20 rounded-end-md">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ─── Table Card ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">

            {{-- ─── Skeleton Loading ────────────────────────── --}}
            <div class="overflow-x-auto"
                @if ($readyToLoad) wire:loading wire:target="perPage, search, filterOpd, filterDate, gotoPage, nextPage, previousPage" @endif>
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-center w-12"></th>
                            <th class="text-center w-16">#</th>
                            <th>Personnel</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Status Masuk</th>
                            <th class="text-center">Status Pulang</th>
                            <th>Dihapus Oleh</th>
                            <th class="text-center">Tanggal Dihapus</th>
                            <th class="text-center w-28">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < ($perPage > 10 ? 10 : $perPage); $i++)
                            <tr>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-4 mx-auto"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-4 mx-auto"></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="skeleton h-8 w-8 rounded-full shrink-0"></div>
                                        <div class="flex flex-col gap-1.5">
                                            <div class="skeleton h-4 w-28"></div>
                                            <div class="skeleton h-3 w-16"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-20 mx-auto"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-5 w-16 mx-auto rounded-full"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-5 w-16 mx-auto rounded-full"></div>
                                </td>
                                <td>
                                    <div class="skeleton h-4 w-24"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-28 mx-auto"></div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <div class="skeleton h-6 w-6 rounded"></div>
                                        <div class="skeleton h-6 w-6 rounded"></div>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- ─── Real Table Data ────────────────────────────────────────── --}}
            @if ($readyToLoad)
                <div class="overflow-x-auto" wire:loading.remove
                    wire:target="perPage, search, filterOpd, filterDate, gotoPage, nextPage, previousPage">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="text-center w-12">
                                    <input type="checkbox" class="checkbox checkbox-xs"
                                        wire:model.live="selectAll" />
                                </th>
                                <th class="text-center w-16">#</th>
                                <th>Personnel</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Status Masuk</th>
                                <th class="text-center">Status Pulang</th>
                                <th>Dihapus Oleh</th>
                                <th class="text-center">Tanggal Dihapus</th>
                                <th class="text-center w-28">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->trashedAbsensis as $absensi)
                                <tr class="hover:bg-base-200/50">
                                    <td class="text-center">
                                        <input type="checkbox" class="checkbox checkbox-xs"
                                            value="{{ $absensi->id }}" wire:model.live="selectedIds" />
                                    </td>
                                    <td class="text-center font-bold">
                                        {{ $this->trashedAbsensis->firstItem() + $loop->index }}
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div
                                                    class="flex items-center justify-center bg-neutral text-neutral-content rounded-full w-8">
                                                    @if ($absensi->personnel?->foto)
                                                        <img
                                                            src="{{ asset('storage/' . $absensi->personnel->foto) }}" />
                                                    @else
                                                        <span
                                                            class="text-xs">{{ substr($absensi->personnel?->name ?? '?', 0, 1) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm">
                                                    {{ $absensi->personnel?->name ?? '-' }}
                                                </div>
                                                <div class="text-[10px] uppercase tracking-wider opacity-50">
                                                    {{ $absensi->personnel?->opd?->singkatan ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="font-mono text-sm font-bold">{{ \Carbon\Carbon::parse($absensi->tanggal)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $colorMasuk = match ($absensi->status_masuk) {
                                                'HADIR' => 'badge-success',
                                                'ALPA' => 'badge-error',
                                                'TERLAMBAT' => 'badge-warning',
                                                default => 'badge-ghost',
                                            };
                                        @endphp
                                        <span
                                            class="badge badge-sm {{ $colorMasuk }} font-bold text-[10px]">{{ $absensi->status_masuk ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $colorPulang = match ($absensi->status_pulang) {
                                                'HADIR' => 'badge-success',
                                                'ALPA' => 'badge-error',
                                                'PULANG CEPAT' => 'badge-warning',
                                                default => 'badge-ghost',
                                            };
                                        @endphp
                                        <span
                                            class="badge badge-sm {{ $colorPulang }} font-bold text-[10px]">{{ $absensi->status_pulang ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium">
                                            {{ $absensi->deleter?->name ?? 'Sistem' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="text-xs opacity-70">{{ $absensi->deleted_at?->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="restore({{ $absensi->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="restore({{ $absensi->id }})"
                                                class="btn btn-success btn-xs btn-outline gap-1" title="Kembalikan">
                                                <span wire:loading wire:target="restore({{ $absensi->id }})"
                                                    class="loading loading-spinner loading-[10px]"></span>
                                                <svg wire:loading.remove wire:target="restore({{ $absensi->id }})"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3.5 h-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                </svg>
                                            </button>
                                            <button
                                                wire:click="confirmForceDelete({{ $absensi->id }}, '{{ addslashes($absensi->personnel?->name ?? 'Unknown') }}')"
                                                class="btn btn-error btn-xs btn-outline gap-1" title="Hapus Permanen">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3.5 h-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-20">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"
                                                class="w-16 h-16 opacity-10">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span class="text-sm text-base-content/40 font-medium italic">Kotak sampah
                                                kosong</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-actions justify-between items-center p-4 border-t border-base-200" wire:loading.remove
                    wire:target="perPage, search, filterOpd, filterDate, gotoPage, nextPage, previousPage">
                    <div class="w-full">{{ $this->trashedAbsensis->links('components.admin.pagination') }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Modal Force Delete ─────────────────────────────────────── --}}
    <dialog id="force-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'force-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'force-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Hapus Permanen</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus permanen data absensi milik
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <p class="text-xs text-error/80 mt-2 font-medium">
                ⚠️ Data dan foto absensi akan dihapus secara permanen dan tidak dapat dikembalikan lagi.
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('force-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeForceDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeForceDelete"
                        class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeForceDelete">Hapus Permanen</span>
                </button>
            </div>
        </div>
    </dialog>

    {{-- ─── Modal Bulk Force Delete ─────────────────────────────────────── --}}
    <dialog id="bulk-force-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'bulk-force-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'bulk-force-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Hapus Permanen Terpilih</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus secara permanen <span
                    class="font-semibold">{{ count($selectedIds) }}</span> data absensi terpilih?
            </p>
            <p class="text-xs text-error/80 mt-2 font-medium">
                ⚠️ Semua data dan foto absensi terpilih akan dihapus secara permanen dari server dan tidak dapat
                dikembalikan lagi.
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('bulk-force-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeBulkForceDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeBulkForceDelete"
                        class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeBulkForceDelete">Hapus Permanen</span>
                </button>
            </div>
        </div>
    </dialog>

    {{-- ─── Modal Empty Trash ─────────────────────────────────────── --}}
    <dialog id="empty-trash-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'empty-trash-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'empty-trash-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Kosongkan Kotak Sampah</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus **SEMUA** data absensi yang berada di dalam kotak sampah?
            </p>
            <p class="text-xs text-error/80 mt-2 font-medium">
                ⚠️ Seluruh data dan berkas foto absensi di kotak sampah akan dihapus secara permanen tanpa tersisa.
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('empty-trash-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeEmptyTrash"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeEmptyTrash"
                        class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeEmptyTrash">Kosongkan Sekarang</span>
                </button>
            </div>
        </div>
    </dialog>
</div>
