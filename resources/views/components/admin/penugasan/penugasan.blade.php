<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Manajemen Penugasan</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data list penugasan</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('penugasan') }}">
                        <span class="text-base-content">Manajemen Penugasan</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Stats Banner ───────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="card bg-linear-to-r from-secondary to-neutral text-base-100 p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-lg text-white font-bold">Manajemen Penugasan</div>
                    <div class="text-sm text-white opacity-80">List Data Penugasan</div>
                </div>
                <div class="flex flex-wrap gap-4 md:gap-0 mt-1 md:mt-0">
                    <div class="text-center">
                        <div class="text-2xl text-white font-bold">{{ $this->stats['total'] ?? 0 }}</div>
                        <div class="text-xs text-white opacity-80">Total Penugasan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Toolbar: Search + Buttons ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="join">
                    <span class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                    <select wire:model.live="perPage" class="select join-item w-20 rounded-end-md">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-auto">
                    <input type="text" placeholder="Search..." wire:model.live.debounce.400ms="search" class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" wire:click="openAddModal" class="btn btn-neutral gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Penugasan
            </button>
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-center w-16">#</th>
                            <th>Nama Penugasan</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->penugasans as $r)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center font-bold">{{ $this->penugasans->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="font-semibold">{{ $r->name }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown dropdown-left dropdown-end">
                                        <button tabindex="0" class="btn btn-ghost btn-xs btn-square rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                        </button>
                                        <ul tabindex="0" class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                            <li>
                                                <button type="button" wire:click="openEditModal({{ $r->id }})">Edit</button>
                                            </li>
                                            <li>
                                                <button type="button" class="text-error" wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">Delete</button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-sm text-base-content/60 py-8">Tidak ada data Penugasan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->penugasans->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Modal: Penugasan ─────────────────────────────────────────────────────── --}}
    <dialog id="penugasan-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'penugasan-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'penugasan-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-md">
            <h3 class="font-bold text-lg mb-4">
                {{ $penugasanId ? 'Edit Penugasan' : 'Tambah Penugasan' }}
            </h3>
            <form wire:submit="save">
                <div class="space-y-4">
                    {{-- Nama --}}
                    <div class="form-control w-full">
                        <label class="label mb-1 px-1">
                            <span class="label-text font-medium">Nama Penugasan <span class="text-error">*</span></span>
                        </label>
                        <input type="text" wire:model="name" class="input input-bordered focus:input-primary w-full transition-all @error('name') input-error @enderror" placeholder="Cth: Tugas Pengawasan Lapangan">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="modal-action mt-6">
                    <button type="button" class="btn btn-ghost" x-on:click="document.getElementById('penugasan-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-neutral" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="save">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="penugasan-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'penugasan-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'penugasan-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus data Penugasan
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action">
                <button type="button" class="btn" x-on:click="document.getElementById('penugasan-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete" wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>

    {{-- ─── FAB ─────────────────────────────────────────────────────────────── --}}
    <div class="fab fab-flower fab-bottom fab-end mb-12 sm:hidden relative z-40">
        <div tabindex="0" role="button" class="btn btn-circle btn-lg btn-neutral">
            <svg aria-label="New" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-6">
                <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
            </svg>
        </div>
        <div class="fab-close">
            <span class="btn btn-circle btn-lg btn-error">✕</span>
        </div>
        <button type="button" class="tooltip btn btn-circle btn-neutral" wire:click="openAddModal" data-tip="Tambah Penugasan">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>
    </div>
</div>