<div wire:init="load" class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-tight uppercase">Manajemen Perangkat</h2>
            <p class="text-sm text-base-content/60">Kelola lisensi dan akses perangkat mobile personel TRC</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary shadow-lg shadow-primary/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Perangkat
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="hidden sm:grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stats shadow bg-base-100 border border-base-200">
            <div class="stat">
                <div class="stat-title text-[10px] uppercase font-bold opacity-50">Total Perangkat</div>
                <div class="stat-value text-2xl">{{ $readyToLoad ? \App\Models\Device::count() : '...' }}</div>
                <div class="stat-desc">Terdaftar di sistem</div>
            </div>
        </div>
        <div class="stats shadow bg-base-100 border border-base-200">
            <div class="stat">
                <div class="stat-title text-[10px] uppercase font-bold opacity-50 text-success">Aktif</div>
                <div class="stat-value text-2xl text-success">
                    {{ $readyToLoad ? \App\Models\Device::where('status', 'active')->count() : '...' }}</div>
                <div class="stat-desc">Bisa melakukan absensi</div>
            </div>
        </div>
        <div class="stats shadow bg-base-100 border border-base-200">
            <div class="stat">
                <div class="stat-title text-[10px] uppercase font-bold opacity-50 text-error">Suspended</div>
                <div class="stat-value text-2xl text-error">
                    {{ $readyToLoad ? \App\Models\Device::where('status', 'suspended')->count() : '...' }}</div>
                <div class="stat-desc">Akses diblokir</div>
            </div>
        </div>
        <div class="stats shadow bg-base-100 border border-base-200">
            <div class="stat">
                <div class="stat-title text-[10px] uppercase font-bold opacity-50 text-warning">Belum Aktivasi</div>
                <div class="stat-value text-2xl text-warning">
                    {{ $readyToLoad ? \App\Models\Device::where('status', 'inactive')->count() : '...' }}</div>
                <div class="stat-desc">Menunggu input user</div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-0">
            <div class="p-6 border-b border-base-200 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="relative w-full md:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, lisensi, atau ID..."
                        class="input input-bordered w-full pl-10 bg-base-200/50 focus:bg-base-100 transition-all border-none" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="select select-bordered bg-base-200 border-none">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                    </select>
                </div>
            </div>

            <div class="min-h-[400px]">
                {{-- Desktop View --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="table table-md w-full">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="w-12 text-center">No</th>
                                <th>Detail Perangkat</th>
                                <th>Lisensi</th>
                                <th>Status Perangkat</th>
                                <th>Pemegang Perangkat</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!$readyToLoad)
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td colspan="6">
                                            <div class="h-12 bg-base-200 animate-pulse rounded-lg"></div>
                                        </td>
                                    </tr>
                                @endfor
                            @else
                                @forelse($this->devices as $index => $device)
                                    <tr class="hover:bg-base-200/30 transition-colors">
                                        <th class="text-center opacity-40 font-normal">
                                            {{ $this->devices->firstItem() + $index }}
                                        </th>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-sm">{{ $device->name }}</span>
                                                <span
                                                    class="text-[10px] opacity-50 uppercase font-black tracking-widest">{{ $device->opd->name }}</span>
                                                @if ($device->unique_device_id)
                                                    <div class="mt-1 flex items-center gap-2">
                                                        <span
                                                            class="badge badge-ghost badge-xs text-[9px] font-mono opacity-60">{{ $device->unique_device_id }}</span>
                                                        @if ($device->brand)
                                                            <span
                                                                class="text-[9px] font-bold text-primary">{{ $device->brand }}
                                                                {{ $device->model }}
                                                                (v{{ $device->android_version }})
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <code
                                                    class="text-sm font-black text-secondary">{{ $device->license_key }}</code>
                                                @if ($device->activated_at)
                                                    <span class="text-[9px] opacity-50">Aktif:
                                                        {{ $device->activated_at->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-[9px] text-warning italic font-medium">Belum
                                                        digunakan</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                @if ($device->status === 'active')
                                                    <div class="status status-success"></div>
                                                    <span class="text-xs font-bold uppercase text-success">Aktif</span>
                                                @elseif($device->status === 'suspended')
                                                    <div class="status status-error"></div>
                                                    <span
                                                        class="text-xs font-bold uppercase text-error">Suspended</span>
                                                @else
                                                    <div class="status status-warning"></div>
                                                    <span
                                                        class="text-xs font-bold uppercase text-warning">Menunggu</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                @if ($device->user)
                                                    <span class="text-xs font-bold">{{ $device->user->name }}</span>
                                                    <span
                                                        class="text-[9px] opacity-50 uppercase tracking-tighter">System
                                                        User</span>
                                                @elseif($device->holder_name)
                                                    <span class="text-xs font-bold">{{ $device->holder_name }}</span>
                                                    <span
                                                        class="text-[9px] opacity-50 uppercase tracking-tighter">Manual
                                                        Input</span>
                                                @else
                                                    <span class="text-[10px] opacity-30 italic">Belum diset</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2">
                                                {{-- Toggle Status --}}
                                                <button wire:click="toggleStatus({{ $device->id }})"
                                                    class="btn btn-xs {{ $device->status === 'active' ? 'text-error' : 'text-success' }} font-bold"
                                                    wire:loading.attr="disabled">
                                                    <span wire:loading wire:target="toggleStatus({{ $device->id }})"
                                                        class="loading loading-spinner loading-xs"></span>
                                                    <span wire:loading.remove
                                                        wire:target="toggleStatus({{ $device->id }})">
                                                        {{ $device->status === 'active' ? 'Suspend' : 'Aktifkan' }}
                                                    </span>
                                                </button>

                                                {{-- Edit --}}
                                                <button wire:click="edit({{ $device->id }})"
                                                    class="btn btn-xs font-bold" wire:loading.attr="disabled">
                                                    <span wire:loading wire:target="edit({{ $device->id }})"
                                                        class="loading loading-spinner loading-xs"></span>
                                                    <span wire:loading.remove
                                                        wire:target="edit({{ $device->id }})">Edit</span>
                                                </button>

                                                {{-- Delete --}}
                                                <button
                                                    wire:click="confirmDelete({{ $device->id }}, '{{ addslashes($device->name) }}')"
                                                    class="btn btn-xs text-error font-bold"
                                                    wire:loading.attr="disabled">
                                                    <span wire:loading
                                                        wire:target="confirmDelete({{ $device->id }}, '{{ addslashes($device->name) }}')"
                                                        class="loading loading-spinner loading-xs"></span>
                                                    <span wire:loading.remove
                                                        wire:target="confirmDelete({{ $device->id }}, '{{ addslashes($device->name) }}')">Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-20 opacity-30 italic font-medium">
                                            Tidak
                                            ada perangkat ditemukan</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Mobile View --}}
                <div class="block md:hidden divide-y divide-base-200">
                    @if (!$readyToLoad)
                        @for ($i = 0; $i < 3; $i++)
                            <div class="p-4 space-y-3 animate-pulse">
                                <div class="h-4 bg-base-200 rounded w-3/4"></div>
                                <div class="h-3 bg-base-200 rounded w-1/2"></div>
                                <div class="h-8 bg-base-200 rounded"></div>
                            </div>
                        @endfor
                    @else
                        @forelse($this->devices as $device)
                            <div class="p-4 space-y-4">
                                {{-- Header: Name & Status --}}
                                <div class="flex justify-between items-start">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-base">{{ $device->name }}</span>
                                        <span
                                            class="text-[10px] opacity-50 uppercase font-black tracking-widest">{{ $device->opd->name }}</span>
                                    </div>
                                    @if ($device->status === 'active')
                                        <span
                                            class="badge badge-success badge-sm font-bold text-[10px] uppercase">Aktif</span>
                                    @elseif($device->status === 'suspended')
                                        <span
                                            class="badge badge-error badge-sm font-bold text-[10px] uppercase">Suspended</span>
                                    @else
                                        <span
                                            class="badge badge-warning badge-sm font-bold text-[10px] uppercase">Menunggu</span>
                                    @endif
                                </div>

                                {{-- Details Grid --}}
                                <div
                                    class="grid grid-cols-2 gap-3 bg-base-200/50 p-3 rounded-lg border border-base-200">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] uppercase opacity-40 font-bold mb-1">License Key</span>
                                        <code
                                            class="text-xs font-black text-secondary">{{ $device->license_key }}</code>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] uppercase opacity-40 font-bold mb-1">Pemegang</span>
                                        <span class="text-xs font-bold truncate">
                                            {{ $device->user?->name ?? ($device->holder_name ?? 'Belum diset') }}
                                        </span>
                                    </div>
                                    @if ($device->brand)
                                        <div class="col-span-2 flex flex-col pt-1 border-t border-base-200/50">
                                            <span class="text-[9px] uppercase opacity-40 font-bold mb-1">Info
                                                Perangkat</span>
                                            <span class="text-[10px] font-bold text-primary truncate">
                                                {{ $device->brand }} {{ $device->model }} (Android
                                                {{ $device->android_version }})
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex gap-2">
                                    <button wire:click="toggleStatus({{ $device->id }})"
                                        class="btn btn-outline btn-sm flex-1 {{ $device->status === 'active' ? 'btn-error' : 'btn-success' }} text-xs font-bold"
                                        wire:loading.attr="disabled">
                                        <span wire:loading wire:target="toggleStatus({{ $device->id }})"
                                            class="loading loading-spinner loading-xs"></span>
                                        <span wire:loading.remove wire:target="toggleStatus({{ $device->id }})">
                                            {{ $device->status === 'active' ? 'Suspend' : 'Aktifkan' }}
                                        </span>
                                    </button>
                                    <button wire:click="edit({{ $device->id }})"
                                        class="btn btn-neutral btn-sm flex-1 text-xs font-bold"
                                        wire:loading.attr="disabled">
                                        <span wire:loading wire:target="edit({{ $device->id }})"
                                            class="loading loading-spinner loading-xs"></span>
                                        <span wire:loading.remove wire:target="edit({{ $device->id }})">Edit</span>
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $device->id }}, '{{ addslashes($device->name) }}')"
                                        class="btn btn-sm text-error text-xs font-bold" wire:loading.attr="disabled">
                                        <span wire:loading
                                            wire:target="confirmDelete({{ $device->id }}, '{{ addslashes($device->name) }}')"
                                            class="loading loading-spinner loading-xs"></span>
                                        <span wire:loading.remove
                                            wire:target="confirmDelete({{ $device->id }}, '{{ addslashes($device->name) }}')">Hapus</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-10 text-center opacity-30 italic font-medium">Tidak ada perangkat ditemukan
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>

            @if ($readyToLoad)
                <div class="p-6 border-t border-base-200 bg-base-200/30">
                    {{ $this->devices->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Device Modal --}}
    <dialog id="device-modal" class="modal backdrop-blur-xs modal-bottom sm:modal-middle" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'device-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'device-modal' && $el.close()">
        <div class="modal-box p-0 shadow max-h-[80vh] max-w-2xl overflow-y-auto relative">
            <div class="p-6 border-b border-base-200 bg-base-200 flex justify-between items-center sticky top-0 z-50">
                <h3 class="font-bold text-lg">
                    {{ $deviceId ? 'Edit Perangkat' : 'Tambah Perangkat' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('device-modal').close()">✕</button>
            </div>

            <form wire:submit="save">
                <div class="space-y-4 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- OPD --}}
                        <div class="form-control w-full md:col-span-2" wire:key="field-opd">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">OPD / Instansi <span
                                        class="text-error">*</span></span>
                            </label>
                            <select wire:model.live="opd_id"
                                class="select select-bordered focus:select-primary w-full transition-all @error('opd_id') select-error @enderror">
                                <option value="">-- Pilih OPD --</option>
                                @foreach ($this->opds as $opd)
                                    <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tipe Pemegang --}}
                        <div class="form-control w-full md:col-span-2 pt-2 border-t border-base-200"
                            wire:key="field-holder-type">
                            <label class="label mb-1 px-1">
                                <span
                                    class="label-text text-sm font-medium text-base-content uppercase tracking-wider opacity-50">Konfigurasi
                                    Pemegang</span>
                            </label>
                            <div class="flex gap-6 px-1 py-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model.live="holder_type" value="existing"
                                        class="radio radio-primary radio-sm">
                                    <span
                                        class="text-sm font-semibold group-hover:text-primary transition-colors">Ambil
                                        dari Users</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model.live="holder_type" value="manual"
                                        class="radio radio-primary radio-sm">
                                    <span
                                        class="text-sm font-semibold group-hover:text-primary transition-colors">Ketik
                                        Baru</span>
                                </label>
                            </div>
                        </div>

                        @if ($holder_type === 'existing')
                            <div class="form-control w-full md:col-span-2" wire:key="field-user-id">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Pilih User <span
                                            class="text-error">*</span></span>
                                </label>
                                <select wire:model="user_id"
                                    class="select select-bordered focus:select-primary w-full transition-all @error('user_id') select-error @enderror">
                                    <option value="">-- Pilih User dari Tabel Users --</option>
                                    @foreach ($this->usersList as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}
                                            ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @else
                            <div class="form-control w-full md:col-span-2" wire:key="field-holder-name">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Nama Pemegang <span
                                            class="text-error">*</span></span>
                                </label>
                                <input type="text" wire:model="holder_name"
                                    placeholder="Masukkan nama pemegang perangkat..."
                                    class="input input-bordered placeholder:text-base-content/40 focus:input-primary w-full transition-all @error('holder_name') input-error @enderror" />
                                @error('holder_name')
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="form-control w-full md:col-span-2" wire:key="field-device-name">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Nama Perangkat <span
                                        class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="name"
                                placeholder="Cth: Operasional Regu A - Samsung A54"
                                class="input input-bordered placeholder:text-base-content/40 focus:input-primary w-full transition-all @error('name') input-error @enderror" />
                            @error('name')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control w-full md:col-span-2" wire:key="field-license-key">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">License Key <span
                                        class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="license_key"
                                class="input input-bordered w-full font-mono font-black text-secondary uppercase bg-base-200 mb-2"
                                readonly />
                            <div class="flex justify-end">
                                <button type="button" wire:click="generateLicense"
                                    class="btn btn-xs btn-neutral gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-3">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Generate Ulang
                                </button>
                            </div>
                            @error('license_key')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control w-full md:col-span-2" wire:key="field-status">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Status Awal</span>
                            </label>
                            <select wire:model="status"
                                class="select select-bordered focus:select-primary w-full transition-all">
                                <option value="inactive">Non-Aktif (Menunggu Aktivasi)</option>
                                <option value="active">Aktif (Bisa Langsung Pakai)</option>
                                <option value="suspended">Suspended (Diblokir)</option>
                            </select>
                        </div>

                        <div class="form-control w-full md:col-span-2" wire:key="field-notes">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Keterangan
                                    Tambahan</span>
                            </label>
                            <textarea wire:model="notes" class="textarea textarea-bordered focus:textarea-primary w-full transition-all h-24"
                                placeholder="Catatan opsional..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-action px-6 pb-6">
                    <button type="button" class="btn btn-ghost"
                        onclick="document.getElementById('device-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="save">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    {{-- Delete Confirmation Modal --}}
    <dialog id="device-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'device-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'device-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error text-center uppercase tracking-widest">Konfirmasi Hapus</h3>
            <p class="text-sm text-center opacity-70">
                Apakah Anda yakin ingin menghapus data perangkat
                <span class="font-black text-base-content">{{ $deleteName }}</span>?
            </p>
            <p class="text-[10px] text-center text-error mt-4 italic font-medium">
                *Tindakan ini akan mematikan lisensi pada aplikasi mobile yang sudah terhubung.
            </p>
            <div class="modal-action flex justify-center gap-2">
                <button type="button" class="btn btn-ghost"
                    x-on:click="document.getElementById('device-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error px-8" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Ya, Hapus</span>
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>
