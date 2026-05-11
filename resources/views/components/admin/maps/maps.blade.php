<div wire:init="load">
    {{-- ─── Main Content ────────────────────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row gap-4 h-[calc(100vh-12rem)]">
        {{-- Sidebar --}}
        <div class="w-full lg:w-80 bg-base-100 rounded-box shadow-sm p-4 flex flex-col gap-4">
            <div class="form-control w-full">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama personnel..."
                        class="input input-bordered w-full ps-10" />
                    <div class="absolute inset-y-0 left-0 flex items-center ps-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-base-content/40" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="form-control w-full" wire:key="filter-opd-container">
                <label class="label"><span class="label-text font-bold text-base-content mb-1">OPD</span></label>
                <select wire:model.live="filterOpd" class="select select-bordered w-full">
                    <option value="">Semua OPD</option>
                    @foreach ($this->opds as $o)
                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control w-full" wire:key="filter-penugasan-container">
                <label class="label"><span class="label-text font-bold text-base-content mb-1">Penugasan</span></label>
                <select wire:model.live="filterPenugasan" class="select select-bordered w-full">
                    <option value="">Semua Penugasan</option>
                    @foreach ($this->penugasans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="divider my-1"></div>

            <div class="flex-1 overflow-y-auto space-y-2">
                @forelse($this->devices as $d)
                    <div class="p-2 hover:bg-base-200 rounded-lg cursor-pointer flex items-center gap-2"
                        onclick="focusMarker({{ $d->last_latitude }}, {{ $d->last_longitude }})">
                        <div class="avatar">
                            <div class="w-10 rounded-full">
                                <img
                                    src="{{ $d->personnel?->foto ? asset('storage/' . $d->personnel->foto) : ($d->personnel ? 'https://ui-avatars.com/api/?name=' . urlencode($d->personnel->name) : asset('assets/logo/trc-logo.webp')) }}" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold truncate">
                                {{ $d->personnel?->name ?? ($d->name ?? 'Perangkat Global') }}
                            </div>
                            <div class="text-xs opacity-60 truncate">{{ $d->opd?->name ?? 'Global (Admin)' }}</div>
                            <div class="text-[10px] opacity-60 flex items-center gap-1 mt-0.5">
                                <span id="status-dot-{{ $d->personnel_id ?? 'd' . $d->id }}"
                                    class="w-1.5 h-1.5 rounded-full {{ $d->last_seen_at && $d->last_seen_at->diffInMinutes() < 30 ? 'bg-success' : 'bg-base-300' }}"></span>
                                <span id="status-text-{{ $d->personnel_id ?? 'd' . $d->id }}">Aktif:
                                    {{ $d->last_seen_human }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-base-content/60 py-4">Tidak ada data lokasi</div>
                @endforelse
            </div>
        </div>

        {{-- Map Container --}}
        <div class="flex-1 bg-base-100 rounded-box shadow-sm overflow-hidden relative" wire:ignore>
            <div id="map" class="absolute inset-0 w-full h-full"></div>
        </div>
    </div>

    {{-- ─── Styles & Scripts ────────────────────────────────────────────── --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" data-navigate-once />
    <style>
        .custom-div-icon {
            background: none;
            border: none;
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css"
        data-navigate-once />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css"
        data-navigate-once />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" data-navigate-once></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js" data-navigate-once></script>
    <script>
        window.initialDevices = @json($this->devices);
        window.initialKantors = @json($this->kantors);
    </script>
    <script>
        {!! file_get_contents(resource_path('views/components/admin/maps/maps.js')) !!}
    </script>
</div>
