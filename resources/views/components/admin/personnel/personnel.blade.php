<div wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Personnel</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data dan akun personnel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li>
                    <a href="{{ route('personnel') }}">
                        <span class="text-base-content font-bold">Personnel</span>
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
                    <input type="text" placeholder="Cari nama/email..." wire:model.live.debounce.400ms="search"
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
            <button type="button" wire:click="openAddModal" wire:loading.attr="disabled" class="btn btn-neutral gap-2">
                <span wire:loading wire:target="openAddModal" class="loading loading-spinner loading-xs"></span>
                <svg wire:loading.remove wire:target="openAddModal" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span wire:loading.remove wire:target="openAddModal">Tambah Personnel</span>
            </button>
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            {{-- ─── Skeleton Loading (While Not Ready) ────────────────────────── --}}
            @if (!$readyToLoad)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="text-center w-16">#</th>
                                <th>Personnel</th>
                                <th>Kontak</th>
                                <th>Penugasan</th>
                                <th>Kantor / Lokasi</th>
                                <th>OPD Induk</th>
                                <th class="text-center">Face</th>
                                <th class="text-center w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < $perPage; $i++)
                                <tr>
                                    <td class="text-center">
                                        <div class="skeleton h-4 w-4 mx-auto"></div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                                            <div class="flex flex-col gap-2">
                                                <div class="skeleton h-4 w-32"></div>
                                                <div class="skeleton h-3 w-24"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-4 w-24"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-5 w-20 rounded-full"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-4 w-32 mb-1"></div>
                                        <div class="skeleton h-3 w-16"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-4 w-28"></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="skeleton h-4 w-12 mx-auto rounded-full"></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="skeleton h-6 w-6 mx-auto rounded-full"></div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ─── Real Table Data ────────────────────────────────────────── --}}
            @if ($readyToLoad)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="text-center w-16">#</th>
                                <th>Personnel</th>
                                <th>Kontak</th>
                                <th>Penugasan</th>
                                <th>Kantor / Lokasi</th>
                                <th>OPD Induk</th>
                                <th class="text-center">Face</th>
                                <th class="text-center w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->personnels as $r)
                                <tr class="hover:bg-base-200/50">
                                    <td class="text-center font-bold">
                                        {{ $this->personnels->firstItem() + $loop->index }}
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                @if ($r->foto)
                                                    <div class="w-10 rounded-full">
                                                        <img src="{{ asset('storage/' . $r->foto) }}"
                                                            alt="{{ $r->name }}" />
                                                    </div>
                                                @else
                                                    <div class="bg-neutral text-neutral-content w-10 rounded-full">
                                                        <span
                                                            class="text-lg">{{ strtoupper(substr($r->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold">{{ $r->name }}</div>
                                                <div class="text-xs opacity-70">{{ $r->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $r->nomor_hp ?: '-' }}</div>
                                    </td>
                                    <td>
                                        @if ($r->penugasan)
                                            <div class="badge badge-secondary badge-sm font-medium text-nowrap">
                                                {{ $r->penugasan->name }}</div>
                                        @else
                                            <div class="text-xs italic text-base-content/50">-</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->kantor)
                                            <div class="text-sm font-bold">{{ $r->kantor->name }}</div>
                                            @if ($r->wajib_absen_di_lokasi)
                                                <div class="badge badge-error badge-xs font-bold text-[8px] uppercase">
                                                    Wajib
                                                    Lokasi</div>
                                            @else
                                                <div
                                                    class="badge badge-ghost badge-xs font-bold text-[8px] uppercase opacity-50">
                                                    Luar Lokasi OK</div>
                                            @endif
                                        @else
                                            <div class="text-xs italic text-base-content/50">Belum diatur</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->opd)
                                            <div class="text-sm font-semibold mb-1">{{ $r->opd->name }}</div>
                                        @else
                                            <div class="text-sm text-base-content/50 italic mb-1">Tanpa OPD</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($r->face_recognition)
                                            <div class="badge badge-success badge-xs font-bold text-[8px] uppercase">
                                                Aktif
                                            </div>
                                        @else
                                            <div
                                                class="badge badge-ghost badge-xs font-bold text-[8px] uppercase opacity-50">
                                                Non-Aktif</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown dropdown-left dropdown-end">
                                            <button tabindex="0"
                                                class="btn btn-ghost btn-xs btn-square rounded-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                </svg>
                                            </button>
                                            <ul tabindex="0"
                                                class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                                <li>
                                                    <button type="button"
                                                        wire:click="openEditModal({{ $r->id }})">Edit</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="text-error"
                                                        wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">Delete</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-sm text-base-content/60 py-8">Tidak ada
                                        data Personnel</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">
                    @if ($readyToLoad)
                        {{ $this->personnels->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Modal: Modal Form ─────────────────────────────────────────────────────── --}}
    <dialog id="personnel-modal" class="modal backdrop-blur-xs modal-bottom sm:modal-middle" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'personnel-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'personnel-modal' && $el.close()">
        <div class="modal-box shadow max-h-[80vh] max-w-2xl overflow-y-auto relative" x-data="personnelCamera()">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">
                    {{ $personnelId ? 'Edit Personnel' : 'Tambah Personnel' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    @click="stopCamera(); document.getElementById('personnel-modal').close()">✕</button>
            </div>
            <form wire:submit="save">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6 md:gap-y-4">

                        {{-- Nama --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Nama Lengkap <span
                                        class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="name"
                                class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('name') input-error @enderror"
                                placeholder="Cth: John Doe">
                            @error('name')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- NIK --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">NIK (No Induk Kependudukan) <span
                                        class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="nik" maxlength="16" pattern="[0-9]*"
                                inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('nik') input-error @enderror"
                                placeholder="16 digit NIK personel...">
                            @error('nik')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($personnelId)
                            {{-- Email --}}
                            <div class="form-control w-full">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Alamat Email <span
                                            class="text-error">*</span></span>
                                </label>
                                <input type="email" wire:model="email"
                                    class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('email') input-error @enderror"
                                    placeholder="Cth: john@example.com">
                                @error('email')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        {{-- Nomor HP --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Nomor HP</span>
                            </label>
                            <input type="tel" wire:model="nomor_hp"
                                class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('nomor_hp') input-error @enderror"
                                placeholder="Cth: 08123456789">
                            @error('nomor_hp')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($personnelId)
                            {{-- Password --}}
                            <div class="form-control w-full" x-data="{ show: false }">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Password @if (!$personnelId)
                                            <span class="text-error">*</span>
                                        @endif
                                    </span>
                                </label>
                                <div class="relative flex items-center">
                                    <input x-bind:type="show ? 'text' : 'password'" wire:model="password"
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full pr-10 transition-all @error('password') input-error @enderror"
                                        placeholder="{{ $personnelId ? '(Kosongkan jika tidak diubah)' : 'Masukkan password...' }}">
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-base-content/50 hover:text-base-content focus:outline-none">
                                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="w-5 h-5 hidden" :class="{ 'hidden': !show }">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Password Confirmation --}}
                            <div class="form-control w-full" x-data="{ show: false }">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Ketik Ulang Password
                                        @if (!$personnelId)
                                            <span class="text-error">*</span>
                                        @endif
                                    </span>
                                </label>
                                <div class="relative flex items-center">
                                    <input x-bind:type="show ? 'text' : 'password'" wire:model="password_confirmation"
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full pr-10 transition-all"
                                        placeholder="{{ $personnelId ? '(Kosongkan jika tidak diubah)' : 'Masukkan ulang password...' }}">
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-base-content/50 hover:text-base-content focus:outline-none">
                                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="w-5 h-5 hidden" :class="{ 'hidden': !show }">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- OPD Induk --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Pilih OPD Induk <span
                                        class="text-error">*</span></span>
                            </label>
                            <select wire:model="opd_id"
                                class="select select-bordered focus:select-primary w-full transition-all @error('opd_id') select-error @enderror"
                                @if (!auth()->user()->hasRole('super-admin')) disabled @endif>
                                <option value="">-- Pilih OPD --</option>
                                @foreach ($this->opds as $opd)
                                    <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Penugasan --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Penugasan <span
                                        class="text-error">*</span></span>
                            </label>
                            <select wire:model="penugasan_id"
                                class="select select-bordered focus:select-primary w-full transition-all @error('penugasan_id') select-error @enderror">
                                <option value="">-- Pilih Penugasan --</option>
                                @foreach ($this->penugasans as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('penugasan_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- PIN --}}
                        <div class="form-control w-full" x-data="{ show: false }">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">PIN (6 Digit)
                                    <span class="text-error">*</span>
                                </span>
                            </label>
                            <div class="relative flex items-center">
                                <input x-bind:type="show ? 'text' : 'password'" wire:model="pin" maxlength="6"
                                    pattern="[0-9]*" inputmode="numeric"
                                    class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full pr-10 transition-all @error('pin') input-error @enderror"
                                    placeholder="6 digit PIN otomatis...">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-base-content/50 hover:text-base-content focus:outline-none">
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                        class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                        class="w-5 h-5 hidden" :class="{ 'hidden': !show }">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('pin')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control w-full md:col-span-2">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Pilih Kantor</span>
                            </label>
                            <select wire:model="kantor_id"
                                class="select select-bordered focus:select-primary w-full transition-all @error('kantor_id') select-error @enderror">
                                <option value="">-- Tidak Terikat Kantor --</option>
                                @foreach ($this->kantors as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                            @error('kantor_id')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control w-full md:col-span-2">
                            <label
                                class="label w-full cursor-pointer justify-start gap-4 bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <input type="checkbox" wire:model="wajib_absen_di_lokasi"
                                    class="checkbox checkbox-md checkbox-primary">
                                <div class="flex-1 min-w-0">
                                    <span
                                        class="label-text font-bold block uppercase text-xs whitespace-normal text-base-content/70">Wajib
                                        Absen di Lokasi
                                        Kantor</span>
                                    <span
                                        class="text-[10px] text-base-content opacity-60 block whitespace-normal wrap-break-word">Jika
                                        dicentang, personil tidak bisa absen jika
                                        berada di luar radius kantor.</span>
                                </div>
                            </label>
                        </div>

                        <div class="form-control w-full md:col-span-2">
                            <label
                                class="label w-full cursor-pointer justify-start gap-4 bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <input type="checkbox" wire:model="face_recognition"
                                    class="checkbox checkbox-md checkbox-secondary">
                                <div class="flex-1 min-w-0">
                                    <span
                                        class="label-text font-bold block uppercase text-xs whitespace-normal text-base-content/70">Aktifkan
                                        Face Recognition</span>
                                    <span
                                        class="text-[10px] text-base-content opacity-60 block whitespace-normal wrap-break-word">Jika
                                        aktif, personil wajib scan wajah saat absen. Jika tidak, hanya ambil foto
                                        biasa.</span>
                                </div>
                            </label>
                        </div>

                        {{-- Foto --}}
                        <div class="form-control w-full md:col-span-2">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Foto Personnel
                                    @if (!$personnelId)
                                        <span class="text-error">*</span>
                                    @endif
                                </span>
                            </label>
                            
                            <div class="flex flex-col gap-4">
                                {{-- Preview & Camera View --}}
                                <div class="flex flex-col sm:flex-row gap-4 items-start">
                                    {{-- Camera / Current Foto --}}
                                    <div class="relative w-40 h-48 bg-base-300 rounded-lg overflow-hidden border-2 border-base-200">
                                        <video x-ref="video" x-show="isCameraOpen" autoplay muted playsinline class="w-full h-full object-cover"></video>
                                        <canvas x-ref="canvas" class="hidden"></canvas>
                                        
                                        <div x-show="!isCameraOpen" class="w-full h-full flex items-center justify-center">
                                            @if ($foto && !$errors->has('foto'))
                                                <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif ($oldFoto)
                                                <img src="{{ asset('storage/' . $oldFoto) }}" class="w-full h-full object-cover">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 opacity-20">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                </svg>
                                            @endif
                                        </div>

                                        {{-- Loading Models Overlay --}}
                                        <div x-show="isLoadingModels" class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white z-10">
                                            <span class="loading loading-spinner loading-xs mb-2"></span>
                                            <span class="text-[8px] uppercase font-bold tracking-widest">AI Engine...</span>
                                        </div>
                                    </div>

                                    <div class="flex-1 space-y-3 w-full">
                                        <div class="flex flex-wrap gap-2">
                                            {{-- Toggle Camera --}}
                                            <button type="button" @click="isCameraOpen ? stopCamera() : startCamera()" 
                                                class="btn btn-sm" :class="isCameraOpen ? 'btn-error' : 'btn-neutral'">
                                                <svg x-show="!isCameraOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                </svg>
                                                <span x-text="isCameraOpen ? 'Tutup Kamera' : 'Ambil dari Kamera'"></span>
                                            </button>

                                            {{-- Capture Button --}}
                                            <button x-show="isCameraOpen" type="button" @click="capture()" class="btn btn-sm btn-primary">
                                                Jepret Foto
                                            </button>

                                            {{-- File Upload --}}
                                            <div class="relative">
                                                <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload($event)">
                                                <button type="button" @click="$refs.fileInput.click()" class="btn btn-sm btn-outline">
                                                    Upload File
                                                </button>
                                            </div>
                                        </div>

                                        <p class="text-[10px] text-base-content/50 leading-relaxed italic">
                                            Direkomendasikan mengambil foto langsung agar AI dapat mendeteksi wajah dengan lebih akurat.
                                        </p>

                                        @error('foto')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                        
                                        @if($face_descriptor)
                                            <div class="badge badge-success badge-xs gap-1 py-2 px-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Face Code Ready
                                            </div>
                                        @else
                                            <div class="badge badge-warning badge-xs gap-1 py-2 px-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                </svg>
                                                Face Code Not Extracted
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-action mt-8 pt-4 border-t border-base-200">
                    <button type="button" class="btn btn-ghost"
                        @click="stopCamera(); document.getElementById('personnel-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary px-8" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="save text-white">Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="personnel-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'personnel-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'personnel-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus data Personnel
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('personnel-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    (function() {
        const initPersonnelCamera = () => {
            if (window.Alpine && !Alpine.data('personnelCamera')) {
                Alpine.data('personnelCamera', () => ({
                    isCameraOpen: false,
                    isLoadingModels: false,
                    stream: null,
                    faceApiLoaded: false,

                    async startCamera() {
                        this.isCameraOpen = true;
                        if (!this.faceApiLoaded) {
                            await this.loadModels();
                        }
                        
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                            this.$refs.video.srcObject = this.stream;
                        } catch (err) {
                            console.error("Error accessing camera: ", err);
                            alert("Tidak dapat mengakses kamera.");
                            this.isCameraOpen = false;
                        }
                    },

                    stopCamera() {
                        if (this.stream) {
                            this.stream.getTracks().forEach(track => track.stop());
                        }
                        this.isCameraOpen = false;
                    },

                    async loadModels() {
                        this.isLoadingModels = true;
                        const MODEL_URL = '/models';
                        try {
                            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                            this.faceApiLoaded = true;
                        } catch (err) {
                            console.error("Error loading face-api models: ", err);
                        } finally {
                            this.isLoadingModels = false;
                        }
                    },

                    async handleFileUpload(event) {
                        const file = event.target.files[0];
                        if (!file) return;

                        // Preview & Upload to Livewire
                        @this.upload('foto', file);

                        // Extract descriptor
                        if (!this.faceApiLoaded) await this.loadModels();
                        
                        const img = await faceapi.bufferToImage(file);
                        const detection = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
                        
                        if (detection) {
                            @this.set('face_descriptor', JSON.stringify(Array.from(detection.descriptor)));
                        } else {
                            alert("Wajah tidak terdeteksi pada file tersebut. Silakan coba foto lain.");
                            @this.set('face_descriptor', '');
                        }
                    },

                    async capture() {
                        const video = this.$refs.video;
                        const canvas = this.$refs.canvas;
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        
                        const context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);
                        
                        // Extract descriptor from canvas
                        if (!this.faceApiLoaded) await this.loadModels();
                        const detection = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
                        
                        if (detection) {
                            @this.set('face_descriptor', JSON.stringify(Array.from(detection.descriptor)));
                            
                            // Convert to Blob and upload
                            canvas.toBlob((blob) => {
                                const file = new File([blob], "capture.jpg", { type: "image/jpeg" });
                                @this.upload('foto', file);
                                this.stopCamera();
                            }, 'image/jpeg', 0.9);
                        } else {
                            alert("Wajah tidak terdeteksi! Pastikan wajah terlihat jelas di depan kamera.");
                        }
                    }
                }));
            }
        };

        if (window.Alpine) {
            initPersonnelCamera();
        } else {
            document.addEventListener('alpine:init', initPersonnelCamera);
        }
    })();
</script>

<script>
    function handlePersonnelImageUpload(input) {
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
            console.log('File personnel terlalu besar, melakukan kompresi...');
            resizePersonnelImage(file, 1200, 1200, 0.85, (resizedFile) => {
                @this.upload('foto', resizedFile);
            });
        } else {
            @this.upload('foto', file);
        }
    }

    function resizePersonnelImage(file, maxWidth, maxHeight, quality, callback) {
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
</div>
