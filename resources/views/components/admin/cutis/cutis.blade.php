<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-xl font-bold">Manajemen Master Cuti</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola jenis-jenis cuti yang tersedia bagi personel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data</li>
                <li>
                    <a href="{{ route('cuti') }}">
                        <span class="text-base-content">Cuti</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar: Search + Buttons ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
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
                    <input type="text" placeholder="Cari" wire:model.live.debounce.400ms="search"
                        class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" wire:click="openAddModal" class="btn btn-neutral gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Cuti
            </button>
        </div>
    </div>

    {{-- ─── Data Table ────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-center w-16">#</th>
                            <th>Nama Cuti</th>
                            <th>Deskripsi</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->cutis as $index => $r)
                            <tr class="hover:bg-base-200/30 transition-colors group">
                                <td class="text-center font-bold">
                                    {{ $this->cutis->firstItem() + $index }}
                                </td>
                                <td>
                                    <div class="font-bold text-base-content">{{ $r->name }}</div>
                                </td>
                                <td>
                                    <div class="text-xs opacity-60 line-clamp-1 max-w-lg">
                                        {{ $r->keterangan ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button type="button" class="btn btn-sm"
                                            wire:click="openEditModal({{ $r->id }})">Edit</button>
                                        <button type="button" class="btn btn-sm text-error"
                                            wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">
                                            Delete
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-16">
                                    <div class="flex flex-col items-center gap-3 opacity-20">
                                        <div
                                            class="w-16 h-16 rounded-full border-2 border-dashed border-base-content flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>
                                        <span class="font-medium">Tidak ada data Cuti ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                {{ $this->cutis->links() }}
            </div>

        </div>
    </div>

    {{-- ─── Modal: Form Cuti ────────────────────────────────────────────────── --}}
    <dialog id="cuti-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'cuti-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'cuti-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-xl p-0 overflow-hidden bg-base-100 rounded-2xl">
            <div class="p-6 border-b border-base-200 bg-base-200/30 flex justify-between items-center">
                <h3 class="font-bold text-lg">
                    {{ $cutiId ? 'Edit Jenis Cuti' : 'Tambah Jenis Cuti' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('cuti-modal').close()">✕</button>
            </div>

            <form wire:submit="save" class="p-6 space-y-6">
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-sm font-medium text-base-content">Nama Jenis
                            Cuti
                            <span class="text-error">*</span></span></label>
                    <div class="relative">
                        <input type="text" wire:model="name" placeholder="Contoh: Cuti Tahunan, Cuti Sakit, dll"
                            class="input w-full text-base-content/60" />
                    </div>
                    @error('name')
                        <span class="text-xs text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label py-1"><span
                            class="label-text text-sm font-medium text-base-content">Keterangan</span></label>
                    <textarea wire:model="keterangan" class="textarea w-full h-32 text-base-content/60"
                        placeholder="Jelaskan detail mengenai syarat atau ketentuan jenis cuti ini..."></textarea>
                    @error('keterangan')
                        <span
                            class="text-[10px] text-error mt-1 font-semibold uppercase tracking-wider">{{ $message }}</span>
                    @enderror
                </div>

                <div
                    class="modal-action bg-base-200/40 p-5 -mx-6 -mb-6 border-t border-base-200 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('cuti-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary px-10">
                        <span wire:loading.remove wire:target="save">Simpan Data</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner"></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ────────────────────────────────────────── --}}
    <dialog id="cuti-delete-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'cuti-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'cuti-delete-modal' && $el.close()">
        <div class="modal-box shadow text-center p-8">
            <div class="mx-auto w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-8 text-error">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </div>
            <h3 class="font-bold text-xl mb-2">Konfirmasi Hapus</h3>
            <p class="text-base-content/60">
                Apakah Anda yakin ingin menghapus jenis cuti <span
                    class="font-bold text-base-content underline decoration-error/30">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action flex justify-center gap-3 mt-6">
                <button type="button" class="btn btn-ghost"
                    onclick="document.getElementById('cuti-delete-modal').close()">Batal</button>
                <button type="button" wire:click="executeDelete"
                    class="btn btn-error px-10 shadow-lg shadow-error/20 text-white">
                    <span wire:loading.remove wire:target="executeDelete">Ya, Hapus</span>
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner"></span>
                </button>
            </div>
        </div>
    </dialog>
</div>
