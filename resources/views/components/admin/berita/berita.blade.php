<div wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Manajemen Berita</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola berita dan informasi</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li>
                    <a href="{{ route('berita') }}">
                        <span class="text-base-content font-bold">Berita</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Judul berita..." wire:model.live.debounce.400ms="search"
                    class="input input-bordered w-full pl-10 pr-10 bg-base-100 placeholder:text-base-content/40" />
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
            <select wire:model.live="filterKategori" class="select select-bordered w-full sm:w-auto">
                <option value="">Semua Kategori</option>
                @foreach ($this->kategoris as $k)
                    <option value="{{ $k }}">{{ $k }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3">
            <div class="join">
                <span
                    class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                <select wire:model.live="perPage" class="select join-item w-20 rounded-end-md">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
            <a href="{{ route('berita.create') }}" wire:navigate class="btn btn-neutral gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Berita
            </a>
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Berita</th>
                            <th class="text-center w-32">Banner Aktif</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ─── Skeleton Loading ────────────────────────── --}}
                        @for ($i = 0; $i < ($perPage > 10 ? 10 : $perPage); $i++)
                            <tr @if ($readyToLoad) wire:loading wire:target="perPage, search, filterKategori" @endif>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="skeleton h-4 w-4"></div>
                                        <div class="skeleton h-12 w-12 rounded-lg shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="skeleton h-4 w-48 mb-1"></div>
                                            <div class="skeleton h-3 w-32 mb-1"></div>
                                            <div class="flex items-center gap-2">
                                                <div class="skeleton h-4 w-16 rounded-full"></div>
                                                <div class="skeleton h-3 w-24"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-5 w-12 mx-auto rounded-full"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-4 mx-auto"></div>
                                </td>
                            </tr>
                        @endfor

                        @forelse ($this->beritas as $r)
                            <tr class="hover:bg-base-200/50"
                                @if ($readyToLoad) wire:loading.remove wire:target="perPage, search, filterKategori" @endif>
                                <td>
                                    <div class="flex items-center gap-3">
                                        {{-- Nomor --}}
                                        <div class="font-bold text-base-content/50 text-sm">
                                            {{ $this->beritas->firstItem() + $loop->index }}.
                                        </div>

                                        {{-- Gambar --}}
                                        @if ($r->gambar)
                                            <div class="h-12 w-12 rounded-lg overflow-hidden bg-base-200 shrink-0">
                                                <img src="{{ asset('storage/' . $r->gambar) }}"
                                                    class="w-full h-full object-cover"
                                                    alt="{{ $r->judul }}">
                                            </div>
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-base-200 flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="size-5 opacity-30">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                                </svg>
                                            </div>
                                        @endif

                                        {{-- Info --}}
                                        <div class="min-w-0 flex-1">
                                            {{-- Baris Judul & Admin --}}
                                            <div class="flex items-center justify-between gap-4">
                                                <div class="font-bold">{{ $r->judul }}</div>
                                                <div class="text-[10px] text-base-content/50 shrink-0">{{ $r->creator?->name ?? '-' }}</div>
                                            </div>

                                            {{-- Deskripsi --}}
                                            @if ($r->deskripsi)
                                                <div class="text-xs text-base-content/50 mb-1">
                                                    {{ $r->deskripsi }}</div>
                                            @endif
                                            
                                            {{-- Tanggal & Kategori --}}
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="text-[10px] text-base-content/60">
                                                    Dipublikasikan • {{ $r->created_at->translatedFormat('d M Y') }}
                                                </div>
                                                <div class="badge badge-ghost badge-sm text-xs">{{ $r->kategori }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <input type="checkbox" class="toggle toggle-primary toggle-sm" 
                                            wire:click="toggleBannerActive('{{ $r->id }}')" 
                                            {{ $r->is_banner_active ? 'checked' : '' }} />
                                        <span class="text-xs font-medium">{{ $r->is_banner_active ? 'On' : 'Off' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown dropdown-left dropdown-end">
                                        <button tabindex="0" class="btn btn-ghost btn-xs btn-square rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                        </button>
                                        <ul tabindex="0"
                                            class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                            <li><a href="{{ route('berita.edit', $r->id) }}"
                                                    wire:navigate>Edit</a></li>
                                            <li><button type="button" class="text-error"
                                                    wire:click="confirmDelete('{{ $r->id }}', '{{ addslashes($r->judul) }}')">Delete</button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr @if ($readyToLoad) wire:loading.remove wire:target="perPage, search, filterKategori" @endif>
                                <td colspan="3" class="text-center text-sm text-base-content/60 py-8">Tidak ada
                                    data berita</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->beritas->links('components.admin.pagination') }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="berita-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'berita-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'berita-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error uppercase">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus berita
                <span class="font-black text-base-content">{{ $deleteName }}</span>?
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('berita-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error text-white" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus Sekarang</span>
                </button>
            </div>
        </div>
    </dialog>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('banner-toggled', (event) => {
                console.log('--- WEBSOCKET TRIGGER LOG ---');
                console.log('Event: Banner toggle action detected');
                console.log('Banner ID:', event.id);
                console.log('Status Aktif:', event.active ? 'YA' : 'TIDAK');
                console.log('Aksi: Mengirim perintah broadcast ke server untuk diteruskan ke WebSocket...');
                console.log('Status: Berhasil memicu event App\\Events\\BannerUpdated di server.');
                console.log('------------------------------');
            });
        });
    </script>
</div>
