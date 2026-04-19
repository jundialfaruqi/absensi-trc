<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-xl font-bold">Manajemen Master Cuti</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola jenis-jenis cuti yang tersedia bagi personel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data Master</li>
                <li>
                    <a href="{{ route('cuti') }}">
                        <span class="text-base-content">Manajemen Cuti</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Action Toolbar ────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="join shadow-sm border border-base-300 rounded-lg">
                <span class="btn btn-sm btn-disabled join-item text-base-content/50 pointer-events-none bg-base-100 border-none">Show</span>
                <select wire:model.live="perPage" class="select select-sm select-bordered join-item w-20 border-none focus:ring-0">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            
            <div class="relative w-full sm:w-80 group">
                <input type="text" placeholder="Cari nama atau deskripsi..." wire:model.live.debounce.400ms="search"
                    class="input input-bordered w-full pl-10 pr-10 bg-base-100 focus:border-primary transition-all shadow-sm h-10" />
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none opacity-40 group-focus-within:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                @if ($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 text-base-content/50 hover:text-error">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <div>
            <button type="button" wire:click="openAddModal" class="btn btn-primary btn-sm rounded-lg shadow-lg shadow-primary/20 px-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Jenis Cuti
            </button>
        </div>
    </div>

    {{-- ─── Data Table ────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden rounded-xl">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm w-full border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th class="w-16 text-center py-4 border-b border-base-200">No</th>
                            <th class="py-4 border-b border-base-200">Nama Jenis Cuti</th>
                            <th class="py-4 border-b border-base-200">Deskripsi</th>
                            <th class="w-32 text-center py-4 border-b border-base-200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->cutis as $index => $r)
                            <tr class="hover:bg-base-200/30 transition-colors group">
                                <td class="text-center font-bold opacity-30 group-hover:opacity-100 transition-opacity border-b border-base-100">
                                    {{ $this->cutis->firstItem() + $index }}
                                </td>
                                <td class="border-b border-base-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-black text-xs">
                                            {{ strtoupper(substr($r->name, 0, 1)) }}
                                        </div>
                                        <div class="font-bold text-base-content">{{ $r->name }}</div>
                                    </div>
                                </td>
                                <td class="border-b border-base-100">
                                    <div class="text-xs opacity-60 line-clamp-1 max-w-lg">
                                        {{ $r->keterangan ?? '-' }}
                                    </div>
                                </td>
                                <td class="text-center border-b border-base-100">
                                    <div class="flex justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" wire:click="openEditModal({{ $r->id }})" class="btn btn-ghost btn-xs btn-square text-primary hover:bg-primary/10" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                        <button type="button" wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')" class="btn btn-ghost btn-xs btn-square text-error hover:bg-error/10" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-16">
                                    <div class="flex flex-col items-center gap-3 opacity-20">
                                        <div class="w-16 h-16 rounded-full border-2 border-dashed border-base-content flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
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

            @if ($this->cutis->hasPages())
                <div class="p-4 border-t border-base-200 bg-base-50">
                    {{ $this->cutis->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Modal: Form Cuti ────────────────────────────────────────────────── --}}
    <dialog id="cuti-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'cuti-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'cuti-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-xl p-0 overflow-hidden bg-base-100 rounded-2xl">
            <div class="p-6 border-b border-base-200 bg-base-200/30 flex justify-between items-center">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <div class="p-1.5 bg-primary/10 rounded-lg text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                        </svg>
                    </div>
                    {{ $cutiId ? 'Edit Jenis Cuti' : 'Tambah Jenis Cuti' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="document.getElementById('cuti-modal').close()">✕</button>
            </div>
            
            <form wire:submit="save" class="p-6 space-y-6">
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text font-bold text-base-content/80">Nama Jenis Cuti <span class="text-error">*</span></span></label>
                    <div class="relative">
                        <input type="text" wire:model="name" placeholder="Contoh: Cuti Tahunan, Cuti Sakit, dll" class="input input-bordered w-full pl-10 bg-base-50 focus:border-primary border-base-300 transition-all shadow-inner" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 0 0 3.182 0l4.318-4.318a2.25 2.25 0 0 0 0-3.182L10.58 3.659A2.25 2.25 0 0 0 9.568 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                            </svg>
                        </div>
                    </div>
                    @error('name') <span class="text-[10px] text-error mt-1 font-semibold uppercase tracking-wider">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text font-bold text-base-content/80">Keterangan</span></label>
                    <textarea wire:model="keterangan" class="textarea textarea-bordered w-full h-32 bg-base-50 focus:border-primary border-base-300 shadow-inner transition-all" placeholder="Jelaskan detail mengenai syarat atau ketentuan jenis cuti ini..."></textarea>
                    <label class="label">
                        <span class="label-text-alt text-base-content/40 italic flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                            </svg>
                            Keterangan ini akan tampil saat admin memilih jenis cuti
                        </span>
                    </label>
                    @error('keterangan') <span class="text-[10px] text-error mt-1 font-semibold uppercase tracking-wider">{{ $message }}</span> @enderror
                </div>

                <div class="modal-action bg-base-200/40 p-5 -mx-6 -mb-6 border-t border-base-200 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" class="btn btn-ghost rounded-lg border border-base-300" onclick="document.getElementById('cuti-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-primary/20 rounded-lg">
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-error">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </div>
            <h3 class="font-bold text-xl mb-2">Konfirmasi Hapus</h3>
            <p class="text-base-content/60">
                Apakah Anda yakin ingin menghapus jenis cuti <span class="font-bold text-base-content underline decoration-error/30">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action flex justify-center gap-3 mt-6">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('cuti-delete-modal').close()">Batal</button>
                <button type="button" wire:click="executeDelete" class="btn btn-error px-10 shadow-lg shadow-error/20 text-white">
                    <span wire:loading.remove wire:target="executeDelete">Ya, Hapus</span>
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner"></span>
                </button>
            </div>
        </div>
    </dialog>
</div>
