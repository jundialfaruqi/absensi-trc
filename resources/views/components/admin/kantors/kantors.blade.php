<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Manajemen Kantor</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola lokasi kantor dan radius absensi</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data</li>
                <li>
                    <a href="{{ route('kantor') }}">
                        <span class="text-base-content font-bold">Kantor</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar: Search + Buttons ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
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

            <div class="relative w-full sm:w-64">
                <input type="text" placeholder="Cari nama kantor..." wire:model.live.debounce.400ms="search"
                    class="input input-bordered w-full pl-10 pr-10 bg-base-100" />
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
            </div>

            @if (auth()->user()->hasRole('super-admin'))
                <select wire:model.live="filterOpd" class="select select-bordered w-full sm:w-48">
                    <option value="">Semua OPD</option>
                    @foreach ($this->opds as $o)
                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="flex gap-2">
            <button type="button" wire:click="openAddModal" class="btn btn-neutral gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Kantor
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
                            <th>Nama Kantor</th>
                            <th>OPD</th>
                            <th>Radius</th>
                            <th>Personel</th>
                            <th>Status</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->kantors as $r)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center font-bold">{{ $this->kantors->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="font-bold">{{ $r->name }}</div>
                                    <div class="text-xs opacity-50 truncate max-w-xs">{{ $r->alamat }}</div>
                                    <div class="text-[10px] text-primary font-mono mt-1">{{ $r->latitude }},
                                        {{ $r->longitude }}</div>
                                </td>
                                <td>
                                    <div class="text-xs font-medium">{{ $r->opd->name }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $r->radius_meter }}m</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4 opacity-50">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        <span class="text-sm">{{ $r->personnels_count }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($r->is_active)
                                        <div class="badge badge-success badge-sm text-white">Aktif</div>
                                    @else
                                        <div class="badge badge-ghost badge-sm opacity-50">Non-aktif</div>
                                    @endif
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
                                            <li><button type="button"
                                                    wire:click="openEditModal({{ $r->id }})">Edit</button></li>
                                            <li><button type="button" class="text-error"
                                                    wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">Delete</button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-sm text-base-content/60 py-8">Tidak ada data
                                    Kantor</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->kantors->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Modal: Form Kantor ────────────────────────────────────────────────── --}}
    <dialog id="kantor-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'kantor-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'kantor-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-5xl p-0 overflow-y-auto md:overflow-hidden">
            <div class="p-6 border-b border-base-200 bg-base-200/30 flex justify-between items-center sticky top-0 z-50 backdrop-blur-md">
                <h3 class="font-bold text-lg">
                    {{ $kantorId ? 'Edit Kantor' : 'Tambah Kantor Baru' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('kantor-modal').close()">✕</button>
            </div>
 
            <form wire:submit="save" class="flex flex-col md:flex-row h-auto md:h-150">
                {{-- Left Side: Form --}}
                <div class="w-full md:w-1/3 p-6 space-y-4 md:overflow-y-auto border-b md:border-b-0 md:border-r border-base-200">
                    <div class="form-control">
                        <label class="label"><span class="label-text text-sm font-medium">Nama Kantor</span></label>
                        <input type="text" wire:model="name" class="input input-bordered w-full"
                            placeholder="Cth: Kantor Pusat TRC">
                        @error('name')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text text-sm font-medium">OPD Induk</span></label>
                        <select wire:model="opd_id" class="select select-bordered w-full"
                            @disabled(!auth()->user()->hasRole('super-admin'))>
                            <option value="">-- Pilih OPD --</option>
                            @foreach ($this->opds as $o)
                                <option value="{{ $o->id }}">{{ $o->name }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text text-sm font-medium">Alamat</span></label>
                        <textarea wire:model="alamat" class="textarea textarea-bordered h-20" placeholder="Masukkan alamat lengkap..."></textarea>
                        @error('alamat')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="form-control">
                            <label class="label"><span
                                    class="label-text font-bold text-[10px] uppercase">Latitude</span></label>
                            <input type="number" step="any" wire:model.live.debounce.500ms="latitude"
                                class="input input-bordered input-sm font-mono">
                            @error('latitude')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span
                                    class="label-text font-bold text-[10px] uppercase">Longitude</span></label>
                            <input type="number" step="any" wire:model.live.debounce.500ms="longitude"
                                class="input input-bordered input-sm font-mono">
                            @error('longitude')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label flex justify-between">
                            <span class="label-text text-sm font-medium">Radius Absensi</span>
                            <span class="text-xs font-black text-primary">{{ $radius_meter }} Meter</span>
                        </label>
                        <input type="range" min="50" max="1000" step="10"
                            wire:model.live="radius_meter" class="range range-primary range-xs">
                        <div class="w-full flex justify-between text-[10px] px-2 mt-1 opacity-50 uppercase font-bold">
                            <span>50m</span>
                            <span>1km</span>
                        </div>
                        @error('radius_meter')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-1">
                            <input type="checkbox" wire:model="is_active"
                                class="checkbox checkbox-primary checkbox-sm">
                            <span class="label-text text-sm font-medium">Kantor Aktif</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-base-200 sticky bottom-0 bg-base-100 flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-1">
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                            Simpan Data
                        </button>
                    </div>
                </div>

                {{-- Right Side: Map --}}
                <div class="w-full md:w-2/3 h-100 md:h-full relative bg-base-300" wire:ignore>
                    <div id="map-selection" class="w-full h-full"></div>
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 z-1000 pointer-events-none">
                        <div
                            class="badge badge-neutral p-4 shadow-xl border-none opacity-90 text-[10px] uppercase font-black tracking-widest gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="size-4 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            Klik atau Seret Marker Untuk Pilih Lokasi
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="kantor-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'kantor-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'kantor-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error uppercase">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus kantor
                <span class="font-black text-base-content">{{ $deleteName }}</span>?
                Semua personel yang terhubung akan kehilangan referensi kantor mereka.
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    onclick="document.getElementById('kantor-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error text-white" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus Sekarang</span>
                </button>
            </div>
        </div>
    </dialog>

    {{-- ─── Scripts ─────────────────────────────────────────────────────── --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let map, marker, circle;

            Livewire.on('init-map', (data) => {
                const {
                    lat,
                    lng,
                    radius
                } = data;

                // Give time for modal to render
                setTimeout(() => {
                    if (!map) {
                        map = L.map('map-selection').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        marker = L.marker([lat, lng], {
                            draggable: true
                        }).addTo(map);
                        circle = L.circle([lat, lng], {
                            radius: radius,
                            color: '#1d4ed8',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.2
                        }).addTo(map);

                        marker.on('dragend', function(e) {
                            const position = marker.getLatLng();
                            updateCoords(position.lat, position.lng);
                            circle.setLatLng(position);
                        });

                        map.on('click', function(e) {
                            marker.setLatLng(e.latlng);
                            circle.setLatLng(e.latlng);
                            updateCoords(e.latlng.lat, e.latlng.lng);
                        });
                    } else {
                        const newPos = [lat, lng];
                        map.setView(newPos, 15);
                        marker.setLatLng(newPos);
                        circle.setLatLng(newPos);
                        circle.setRadius(radius);

                        // Force redraw
                        map.invalidateSize();
                    }
                }, 300);
            });

            // Watch for radius changes
            Livewire.on('radius_updated', (newRadius) => {
                if (circle) circle.setRadius(newRadius);
            });

            function updateCoords(lat, lng) {
                @this.set('latitude', lat);
                @this.set('longitude', lng);
            }

            // Sync coordinates and radius when property changes (Typed or Slider)
            Livewire.hook('commit', ({
                component,
                commit,
                respond,
                succeed,
                fail
            }) => {
                succeed(({
                    snapshot,
                    effect
                }) => {
                    const snap = snapshot.memo.data;
                    if (map && marker && circle) {
                        const newLat = parseFloat(snap.latitude);
                        const newLng = parseFloat(snap.longitude);
                        const newRad = parseInt(snap.radius_meter);

                        if (!isNaN(newLat) && !isNaN(newLng)) {
                            const newPos = [newLat, newLng];
                            // Only update if marker position is actually different to avoid snap loop
                            if (marker.getLatLng().lat !== newLat || marker.getLatLng().lng !== newLng) {
                                marker.setLatLng(newPos);
                                circle.setLatLng(newPos);
                                map.panTo(newPos);
                            }
                        }

                        if (!isNaN(newRad)) {
                            circle.setRadius(newRad);
                        }
                    }
                });
            });
        });
    </script>
</div>
