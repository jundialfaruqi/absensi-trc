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
                            <div class="font-bold truncate">{{ $d->personnel?->name ?? $d->name ?? 'Perangkat Global' }}</div>
                            <div class="text-xs opacity-60 truncate">{{ $d->opd?->name ?? 'Global (Admin)' }}</div>
                            <div class="text-[10px] opacity-60 flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $d->last_seen_at && $d->last_seen_at->diffInMinutes() < 30 ? 'bg-success' : 'bg-base-300' }}"></span>
                                Aktif: {{ $d->last_seen_human }}
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" data-navigate-once />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" data-navigate-once />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" data-navigate-once></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js" data-navigate-once></script>
    <script>
        (function() {
            const initMapsHandler = () => {
                if (window.mapsInitialized) return;

                const mapEl = document.getElementById('map');
                if (!mapEl) return;

                if (typeof L === 'undefined') {
                    setTimeout(initMapsHandler, 100);
                    return;
                }

                const map = L.map('map', {
                    zoomSnap: 0.1
                }).setView([0.55, 101.447], 11.7); // Zoom out sangat sedikit (11.7)

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                const markerCluster = L.markerClusterGroup();
                map.addLayer(markerCluster);

                // Fetch Pekanbaru boundary
                fetch('https://nominatim.openstreetmap.org/search?city=Pekanbaru&format=json&polygon_geojson=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0 && data[0].geojson) {
                            const geojson = data[0].geojson;

                            // World boundary in GeoJSON format [lng, lat]
                            const worldCoords = [
                                [-180, -90],
                                [180, -90],
                                [180, 90],
                                [-180, 90],
                                [-180, -90]
                            ];

                            let maskedGeoJSON = geojson;

                            if (geojson.type === 'Polygon') {
                                maskedGeoJSON = {
                                    "type": "Polygon",
                                    "coordinates": [
                                        worldCoords,
                                        geojson.coordinates[0]
                                    ]
                                };
                            } else if (geojson.type === 'MultiPolygon') {
                                const holes = geojson.coordinates.map(poly => poly[0]);
                                maskedGeoJSON = {
                                    "type": "Polygon",
                                    "coordinates": [
                                        worldCoords,
                                        ...holes
                                    ]
                                };
                            }

                            L.geoJSON(maskedGeoJSON, {
                                style: {
                                    color: '#ef4444',
                                    weight: 2,
                                    fillOpacity: 0.2, // Opacity merah untuk luar batas
                                    fillColor: '#ef4444'
                                }
                            }).addTo(map);
                        }
                    })
                    .catch(e => console.error('Failed to load boundary', e));

                let markers = {};

                function updateMarkers(devices) {
                    // Clear existing markers from cluster
                    markerCluster.clearLayers();
                    markers = {};

                    devices.forEach(d => {
                        if (!d.last_latitude || !d.last_longitude) return;

                        const iconUrl = d.personnel 
                            ? (d.personnel.foto ? '/storage/'+d.personnel.foto : 'https://ui-avatars.com/api/?name='+encodeURIComponent(d.personnel.name))
                            : '/assets/logo/trc-logo.webp';

                        const icon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div class="relative w-10 h-10">
                                        <div class="avatar">
                                            <div class="w-10 rounded-full border-2 border-primary shadow-lg">
                                                <img src="${iconUrl}" />
                                            </div>
                                        </div>
                                   </div>`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40]
                        });

                        const marker = L.marker([d.last_latitude, d.last_longitude], {
                                icon: icon
                            });

                        const name = d.personnel ? d.personnel.name : (d.name || 'Perangkat Global');
                        const opdName = d.opd ? d.opd.name : 'Global (Admin)';
                        const penugasan = d.personnel && d.personnel.penugasan ? d.personnel.penugasan.name : '-';

                        const popupContent = `
                            <div class="p-2 max-w-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="avatar">
                                        <div class="w-12 rounded-full">
                                            <img src="${iconUrl}" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">${name}</div>
                                        <div class="text-xs opacity-60">${opdName}</div>
                                    </div>
                                </div>
                                <div class="text-xs mb-1"><strong>Penugasan:</strong> ${penugasan}</div>
                                <div class="text-xs mb-1" id="address-${d.id}"><strong>Lokasi:</strong> Memuat alamat...</div>
                                <div class="text-xs opacity-60"><strong>Terakhir Terlihat:</strong> ${d.last_seen_human}</div>
                            </div>
                        `;

                        marker.bindPopup(popupContent);

                        marker.on('popupopen', () => {
                            fetchAddress(d.last_latitude, d.last_longitude, d.id);
                        });

                        markerCluster.addLayer(marker);
                        markers[d.id] = marker;
                    });

                    // Auto fit bounds disabled per user request
                }

                function fetchAddress(lat, lng, id) {
                    const el = document.getElementById(`address-${id}`);
                    if (!el) return;

                    fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
                        )
                        .then(response => response.json())
                        .then(data => {
                            el.innerHTML = `<strong>Lokasi:</strong> ${data.display_name}`;
                        })
                        .catch(error => {
                            el.innerHTML = `<strong>Lokasi:</strong> Gagal memuat alamat.`;
                        });
                }

                window.focusMarker = function(lat, lng) {
                    if (map) {
                        map.setView([lat, lng], 16);
                    }
                };

                window.updateMarkers = updateMarkers;

                Livewire.on('devices-updated', (data) => {
                    updateMarkers(data.devices);
                });

                updateMarkers(@json($this->devices));

                window.mapsInitialized = true;
            };

            // Initial load
            if (window.Livewire) {
                initMapsHandler();
            }

            // On navigation
            document.addEventListener('livewire:navigated', () => {
                window.mapsInitialized = false; // Allow re-init for new DOM
                initMapsHandler();
            }, {
                once: true
            });
        })();
    </script>
</div>
