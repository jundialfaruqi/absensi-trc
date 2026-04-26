<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Manajemen OPD</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data Organisasi Perangkat Daerah</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data</li>
                <li>
                    <a href="{{ route('opd') }}">
                        <span class="text-base-content font-bold">OPD</span>
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
                    <input type="text" placeholder="Cari nama OPD..." wire:model.live.debounce.400ms="search"
                        class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100 placeholder:text-base-content/60" />
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
            <button type="button" wire:click="openAddModal" wire:loading.attr="disabled" class="btn btn-neutral gap-2">
                <span wire:loading wire:target="openAddModal" class="loading loading-spinner loading-xs"></span>
                <svg wire:loading.remove wire:target="openAddModal" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span wire:loading.remove wire:target="openAddModal">Tambah OPD</span>
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
                            <th>Logo</th>
                            <th>Nama OPD</th>
                            <th>Singkatan</th>
                            <th>Pengguna</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->opds as $r)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center font-bold">{{ $this->opds->firstItem() + $loop->index }}</td>
                                <td>
                                    @if ($r->logo_url)
                                        <div class="avatar">
                                            <div class="w-12 h-12 rounded p-1">
                                                <img src="{{ $r->logo_url }}" alt="Logo" class="object-contain" />
                                            </div>
                                        </div>
                                    @else
                                        <div class="avatar placeholder">
                                            <div
                                                class="bg-neutral text-neutral-content rounded w-12 h-12 flex items-center justify-center">
                                                <span class="text-xs uppercase">{{ substr($r->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $r->name }}</div>
                                    <div class="text-xs text-base-content/60">{{ str()->limit($r->alamat, 50) }}</div>
                                </td>
                                <td>
                                    @if ($r->singkatan)
                                        <div
                                            class="badge badge-primary badge-outline badge-sm border-none bg-primary/10 text-primary font-semibold">
                                            {{ $r->singkatan }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="flex -space-x-2 overflow-hidden items-center">
                                        @foreach ($r->users->take(3) as $u)
                                            <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center text-xs font-bold ring ring-base-100"
                                                title="{{ $u->name }}">
                                                {{ substr($u->name, 0, 1) }}
                                            </div>
                                        @endforeach
                                        @if ($r->users_count > 3)
                                            <div
                                                class="bg-base-300 text-base-content rounded-full w-8 h-8 flex items-center justify-center text-xs ring ring-base-100">
                                                +{{ $r->users_count - 3 }}
                                            </div>
                                        @endif
                                        @if ($r->users_count === 0)
                                            <span class="text-sm text-base-content/50">Belum ada</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown dropdown-left dropdown-end">
                                        <button tabindex="0" class="btn btn-ghost btn-xs btn-square rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                        </button>
                                        <ul tabindex="0"
                                            class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                            <li>
                                                <button type="button" wire:click="openEditModal({{ $r->id }})">
                                                    Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="text-error"
                                                    wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">
                                                    Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-sm text-base-content/60 py-8">Tidak ada
                                    data OPD</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->opds->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Modal: OPD ─────────────────────────────────────────────────────── --}}
    <dialog id="opd-modal" class="modal backdrop-blur-xs modal-bottom sm:modal-middle" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'opd-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'opd-modal' && $el.close()">
        <div class="modal-box shadow p-0 max-h-[80vh] max-w-2xl overflow-y-auto relative">
            <div class="p-6 border-b border-base-200 bg-base-200 flex justify-between items-center sticky top-0 z-50">
                <h3 class="font-bold text-lg">
                    {{ $opdId ? 'Edit OPD' : 'Tambah OPD' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('opd-modal').close()">✕</button>
            </div>
            <form wire:submit="save">
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Nama --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Nama OPD <span
                                        class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="name"
                                class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('name') input-error @enderror"
                                placeholder="Cth: Dinas Komunikasi dan Informatika">
                            @error('name')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Singkatan --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Singkatan</span>
                            </label>
                            <input type="text" wire:model="singkatan"
                                class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('singkatan') input-error @enderror"
                                placeholder="Cth: Diskominfo">
                            @error('singkatan')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div class="form-control w-full">
                        <label class="label mb-1 px-1">
                            <span class="label-text text-sm font-medium text-base-content">Alamat Lengkap</span>
                        </label>
                        <textarea wire:model="alamat"
                            class="textarea textarea-bordered focus:textarea-primary placeholder:text-base-content/60 w-full transition-all h-20 @error('alamat') textarea-error @enderror"
                            placeholder="Masukkan alamat lengkap instansi..."></textarea>
                        @error('alamat')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Logo --}}
                    <div class="form-control w-full">
                        <label class="label mb-1 px-1">
                            <span class="label-text text-sm font-medium text-base-content">Logo Instansi</span>
                        </label>
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            <div class="w-full sm:flex-1">
                                <input type="file"
                                    class="file-input file-input-bordered focus:file-input-primary transition-all w-full @error('logo') file-input-error @enderror"
                                    accept="image/png, image/jpeg, image/jpg" onchange="handleOPDLogoUpload(this)" />
                                @error('logo')
                                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                                <div wire:loading wire:target="logo"
                                    class="mt-2 text-xs text-info font-medium italic">Mengunggah file...</div>
                            </div>

                            {{-- Preview section --}}
                            @if ($logo && !$errors->has('logo'))
                                <div
                                    class="avatar p-1 border border-base-200 rounded-lg shadow-sm bg-base-100 shrink-0">
                                    <div class="w-16 h-16 rounded">
                                        <img src="{{ $logo->temporaryUrl() }}" class="object-contain bg-white">
                                    </div>
                                </div>
                            @elseif ($oldLogo)
                                <div
                                    class="avatar p-1 border border-base-200 rounded-lg shadow-sm bg-base-200/50 shrink-0">
                                    <div class="w-16 h-16 rounded">
                                        <img src="{{ asset('storage/' . $oldLogo) }}" class="object-contain">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="divider my-1 text-sm text-base-content/50">Pengaturan Akses Pengguna</div>

                    {{-- User ID (Multiple selection with Search) --}}
                    <div class="form-control w-full">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end mb-2 gap-2 px-1">
                            <div>
                                <label class="label p-0 pb-1"><span class="label-text text-sm font-medium">Pengguna
                                        Terkait</span></label>
                                <p class="text-xs text-base-content/60">Pilih pengguna. (Otomatis dilepas dari OPD lain
                                    jika ditambahkan kesini)</p>
                            </div>
                            <div class="relative w-full sm:w-64">
                                <input type="text" wire:model.live.debounce.300ms="userSearch"
                                    placeholder="Cari nama pengguna..."
                                    class="input input-sm input-bordered placeholder:text-base-content/60 w-full pl-8 focus:input-primary transition-all">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-base-content/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        class="w-4 h-4">
                                        <path fill-rule="evenodd"
                                            d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="border border-base-content/10 rounded-xl overflow-hidden bg-base-200/30">
                            <div class="max-h-56 overflow-y-auto p-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5">
                                    @forelse ($this->allUsers as $u)
                                        <label
                                            class="label cursor-pointer justify-start gap-3 hover:bg-base-100 hover:shadow-sm p-2 rounded-lg transition-all border border-transparent hover:border-base-content/10 select-none">
                                            <input type="checkbox" wire:model="selectedUsers"
                                                value="{{ $u->id }}"
                                                class="checkbox checkbox-sm checkbox-primary" />
                                            <span
                                                class="label-text truncate font-medium text-sm text-base-content/60">{{ $u->name }}</span>
                                        </label>
                                    @empty
                                        <div
                                            class="col-span-full flex flex-col items-center justify-center text-center py-6 text-base-content/50">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-8 h-8 mb-2 opacity-50">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                            <span
                                                class="text-sm text-base-content/60">{{ $userSearch ? 'Tidak ada pengguna yang dicari.' : 'Belum ada pengguna.' }}</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-action mt-6 px-6 pb-6">
                    <button type="button" class="btn btn-ghost"
                        x-on:click="document.getElementById('opd-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="save">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="opd-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'opd-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'opd-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus OPD
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <p class="text-xs text-error mt-2 italic">Pengguna yang terhubung dengan OPD ini tidak akan dihapus, hanya
                dilepaskan dari OPD ini.</p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('opd-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>
</div>

<script>
    function handleOPDLogoUpload(input) {
        const file = input.files[0];
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        const allowedExtensions = ['jpg', 'jpeg', 'png'];
        const extension = file.name.split('.').pop().toLowerCase();

        if (!allowedTypes.includes(file.type) || !allowedExtensions.includes(extension)) {
            alert('File tidak valid! Hanya format JPG, JPEG, dan PNG yang diperbolehkan.');
            input.value = '';
            return;
        }

        const maxSize = 2000 * 1024; // 2000KB

        if (file.size > maxSize) {
            console.log('Logo OPD terlalu besar, melakukan kompresi...');
            resizeOPDLogo(file, 1000, 1000, 0.85, (resizedFile) => {
                @this.upload('logo', resizedFile);
            });
        } else {
            @this.upload('logo', file);
        }
    }

    function resizeOPDLogo(file, maxWidth, maxHeight, quality, callback) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    const resizedFile = new File([blob], file.name, {
                        type: file.type,
                        lastModified: Date.now()
                    });
                    callback(resizedFile);
                }, file.type, quality);
            };
        };
    }
</script>
