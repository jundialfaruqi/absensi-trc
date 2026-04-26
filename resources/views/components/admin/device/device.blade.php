<div wire:init="load" class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-tight uppercase">Manajemen Perangkat</h2>
            <p class="text-sm text-base-content/60">Kelola lisensi dan akses perangkat mobile personel TRC</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary shadow-lg shadow-primary/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Perangkat
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <div class="stat-value text-2xl text-success">{{ $readyToLoad ? \App\Models\Device::where('status', 'active')->count() : '...' }}</div>
                <div class="stat-desc">Bisa melakukan absensi</div>
            </div>
        </div>
        <div class="stats shadow bg-base-100 border border-base-200">
            <div class="stat">
                <div class="stat-title text-[10px] uppercase font-bold opacity-50 text-error">Suspended</div>
                <div class="stat-value text-2xl text-error">{{ $readyToLoad ? \App\Models\Device::where('status', 'suspended')->count() : '...' }}</div>
                <div class="stat-desc">Akses diblokir</div>
            </div>
        </div>
        <div class="stats shadow bg-base-100 border border-base-200">
            <div class="stat">
                <div class="stat-title text-[10px] uppercase font-bold opacity-50 text-warning">Belum Aktivasi</div>
                <div class="stat-value text-2xl text-warning">{{ $readyToLoad ? \App\Models\Device::where('status', 'inactive')->count() : '...' }}</div>
                <div class="stat-desc">Menunggu input user</div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-0">
            <div class="p-6 border-b border-base-200 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="relative w-full md:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, lisensi, atau ID..." 
                           class="input input-bordered w-full pl-10 bg-base-200/50 focus:bg-base-100 transition-all border-none" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="select select-sm select-bordered bg-base-200 border-none">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto min-h-[400px]">
                <table class="table table-md">
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
                        @if(!$readyToLoad)
                            @for($i=0; $i<5; $i++)
                            <tr>
                                <td colspan="6"><div class="h-12 bg-base-200 animate-pulse rounded-lg"></div></td>
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
                                        <span class="text-[10px] opacity-50 uppercase font-black tracking-widest">{{ $device->opd->name }}</span>
                                        @if($device->unique_device_id)
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="badge badge-ghost badge-xs text-[9px] font-mono opacity-60">{{ $device->unique_device_id }}</span>
                                                @if($device->brand)
                                                    <span class="text-[9px] font-bold text-primary">{{ $device->brand }} {{ $device->model }} (v{{ $device->android_version }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <code class="text-sm font-black text-secondary">{{ $device->license_key }}</code>
                                        @if($device->activated_at)
                                            <span class="text-[9px] opacity-50">Aktif: {{ $device->activated_at->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="text-[9px] text-warning italic font-medium">Belum digunakan</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($device->status === 'active')
                                            <div class="status status-success"></div>
                                            <span class="text-xs font-bold uppercase text-success">Aktif</span>
                                        @elseif($device->status === 'suspended')
                                            <div class="status status-error"></div>
                                            <span class="text-xs font-bold uppercase text-error">Suspended</span>
                                        @else
                                            <div class="status status-warning"></div>
                                            <span class="text-xs font-bold uppercase text-warning">Menunggu</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        @if($device->user)
                                            <span class="text-xs font-bold">{{ $device->user->name }}</span>
                                            <span class="text-[9px] opacity-50 uppercase tracking-tighter">System User</span>
                                        @elseif($device->holder_name)
                                            <span class="text-xs font-bold">{{ $device->holder_name }}</span>
                                            <span class="text-[9px] opacity-50 uppercase tracking-tighter">Manual Input</span>
                                        @else
                                            <span class="text-[10px] opacity-30 italic">Belum diset</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="toggleStatus({{ $device->id }})" class="btn btn-ghost btn-xs btn-square" title="{{ $device->status === 'active' ? 'Suspend' : 'Aktifkan' }}">
                                            @if($device->status === 'active')
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 text-error">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 text-success">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                        </button>
                                        <button wire:click="edit({{ $device->id }})" class="btn btn-ghost btn-xs btn-square">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $device->id }})" wire:confirm="Hapus perangkat ini?" class="btn btn-ghost btn-xs btn-square text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-1.123c0-.796-.623-1.457-1.423-1.485a46.242 46.242 0 0 0-3.255 0c-.8.028-1.423.689-1.423 1.485V3.5m7.5 0h-7.5" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-20 opacity-30 italic font-medium">Tidak ada perangkat ditemukan</td>
                            </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if($readyToLoad)
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
                                <span class="label-text text-sm font-medium text-base-content">OPD / Instansi <span class="text-error">*</span></span>
                            </label>
                            <select wire:model.live="opd_id" class="select select-bordered focus:select-primary w-full transition-all @error('opd_id') select-error @enderror">
                                <option value="">-- Pilih OPD --</option>
                                @foreach($this->opds as $opd)
                                    <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                                @endforeach
                            </select>
                            @error('opd_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipe Pemegang --}}
                        <div class="form-control w-full md:col-span-2 pt-2 border-t border-base-200" wire:key="field-holder-type">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content uppercase tracking-wider opacity-50">Konfigurasi Pemegang</span>
                            </label>
                            <div class="flex gap-6 px-1 py-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model.live="holder_type" value="existing" class="radio radio-primary radio-sm">
                                    <span class="text-sm font-semibold group-hover:text-primary transition-colors">Ambil dari Users</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model.live="holder_type" value="manual" class="radio radio-primary radio-sm">
                                    <span class="text-sm font-semibold group-hover:text-primary transition-colors">Ketik Baru</span>
                                </label>
                            </div>
                        </div>

                        @if($holder_type === 'existing')
                            <div class="form-control w-full md:col-span-2" wire:key="field-user-id">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Pilih User <span class="text-error">*</span></span>
                                </label>
                                <select wire:model="user_id" class="select select-bordered focus:select-primary w-full transition-all @error('user_id') select-error @enderror">
                                    <option value="">-- Pilih User dari Tabel Users --</option>
                                    @foreach($this->usersList as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="form-control w-full md:col-span-2" wire:key="field-holder-name">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Nama Pemegang <span class="text-error">*</span></span>
                                </label>
                                <input type="text" wire:model="holder_name" placeholder="Masukkan nama pemegang perangkat..." 
                                    class="input input-bordered placeholder:text-base-content/40 focus:input-primary w-full transition-all @error('holder_name') input-error @enderror" />
                                @error('holder_name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="form-control w-full md:col-span-2" wire:key="field-device-name">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Nama Perangkat <span class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="name" placeholder="Cth: Operasional Regu A - Samsung A54" 
                                class="input input-bordered placeholder:text-base-content/40 focus:input-primary w-full transition-all @error('name') input-error @enderror" />
                            @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control w-full md:col-span-2" wire:key="field-license-key">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">License Key <span class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="license_key" class="input input-bordered w-full font-mono font-black text-secondary uppercase bg-base-200 mb-2" readonly />
                            <div class="flex justify-end">
                                <button type="button" wire:click="generateLicense" class="btn btn-xs btn-neutral gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Generate Ulang
                                </button>
                            </div>
                            @error('license_key') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control w-full md:col-span-2" wire:key="field-status">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Status Awal</span>
                            </label>
                            <select wire:model="status" class="select select-bordered focus:select-primary w-full transition-all">
                                <option value="inactive">Non-Aktif (Menunggu Aktivasi)</option>
                                <option value="active">Aktif (Bisa Langsung Pakai)</option>
                                <option value="suspended">Suspended (Diblokir)</option>
                            </select>
                        </div>

                        <div class="form-control w-full md:col-span-2" wire:key="field-notes">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">Keterangan Tambahan</span>
                            </label>
                            <textarea wire:model="notes" class="textarea textarea-bordered focus:textarea-primary w-full transition-all h-24" placeholder="Catatan opsional..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-action px-6 pb-6">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('device-modal').close()">Batal</button>
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
</div>
