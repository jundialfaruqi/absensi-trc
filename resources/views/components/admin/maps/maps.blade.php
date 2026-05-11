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
                                    class="w-1.5 h-1.5 rounded-full {{ $d->last_seen_at && $d->last_seen_at->diffInMinutes() < 1 ? 'bg-success' : 'bg-base-300' }}"></span>
                                <span id="status-text-{{ $d->personnel_id ?? 'd' . $d->id }}">
                                    @if ($d->last_seen_at && $d->last_seen_at->diffInMinutes() < 1)
                                        Online
                                    @else
                                        Aktif: {{ $d->last_seen_human }}
                                    @endif
                                </span>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js" data-navigate-once></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js" data-navigate-once></script>
    <script data-navigate-once>
        // Hack untuk mencegah Livewire 3 error karena mendeteksi global constructor Echo
        if (typeof Echo !== 'undefined' && !Echo.socketId) {
            Echo.socketId = function() {
                return null;
            };
        }
    </script>
    <script>
        (function() {
            const initMapsHandler = () => {
                if (window.mapsInitialized) return;

                const mapEl = document.getElementById('map');
                if (!mapEl) return;

                // Jika elemen kontainer sudah memiliki peta Leaflet, batalkan inisialisasi ulang
                if (mapEl._leaflet_id) return;

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

                const kantorLayer = L.layerGroup().addTo(map);

                function updateKantors(kantors) {
                    kantorLayer.clearLayers();
                    kantors.forEach(k => {
                        if (!k.latitude || !k.longitude || !k.radius_meter) return;

                        const circle = L.circle([k.latitude, k.longitude], {
                            color: '#10b981', // green-500
                            fillColor: '#10b981',
                            fillOpacity: 0.15,
                            radius: parseFloat(k.radius_meter),
                            weight: 1.5,
                            dashArray: '5, 5' // dashed line for better aesthetics
                        });

                        circle.bindPopup(`
                            <div class="p-2">
                                <div class="font-bold text-sm text-primary">${k.name}</div>
                                <div class="text-xs opacity-70 mt-1">Radius Absensi: <strong>${k.radius_meter} meter</strong></div>
                            </div>
                        `);

                        kantorLayer.addLayer(circle);
                    });
                }

                window.updateKantors = updateKantors;

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

                        const iconUrl = d.personnel ?
                            (d.personnel.foto ? '/storage/' + d.personnel.foto :
                                'https://ui-avatars.com/api/?name=' + encodeURIComponent(d.personnel.name)
                            ) :
                            '/assets/logo/trc-logo.webp';

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
                        const penugasan = d.personnel && d.personnel.penugasan ? d.personnel.penugasan
                            .name : '-';

                        let popupLastSeenHTML = '';
                        if (d.last_seen_human && !d.last_seen_human.includes('detik')) {
                            popupLastSeenHTML = `<div class="text-xs opacity-60"><strong>Terakhir Terlihat:</strong> <span id="last-seen-${d.personnel_id || 'd'+d.id}">${d.last_seen_human}</span></div>`;
                        }

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
                                ${popupLastSeenHTML}
                            </div>
                        `;

                        marker.bindPopup(popupContent);

                        marker.on('popupopen', () => {
                            fetchAddress(d.last_latitude, d.last_longitude, d.id);
                        });

                        markerCluster.addLayer(marker);
                        if (d.personnel_id) {
                            markers['p' + d.personnel_id] = marker;
                        } else {
                            markers['d' + d.id] = marker;
                        }
                    });

                    // Auto fit bounds disabled per user request
                }

                window.updateSingleMarker = function(e) {
                    const markerKey = 'p' + e.personnel_id;
                    const marker = markers[markerKey];

                    if (marker) {
                        marker.setLatLng([e.latitude, e.longitude]);

                        // Update last seen in popup if it's open
                        const lastSeenEl = document.getElementById(`last-seen-${e.personnel_id}`);
                        if (lastSeenEl) {
                            lastSeenEl.innerText = e.last_seen;
                        }
                    }

                    // Update status dot and text in sidebar
                    const dotEl = document.getElementById(`status-dot-${e.personnel_id}`);
                    const textEl = document.getElementById(`status-text-${e.personnel_id}`);

                    if (dotEl) {
                        dotEl.classList.remove('bg-base-300');
                        dotEl.classList.add('bg-success');
                        dotEl.classList.add('animate-pulse');
                    }
                    if (textEl) {
                        textEl.innerText = 'Online';
                    }

                    // Set timer to clear online status after 15 seconds
                    if (window[`statusTimer_${e.personnel_id}`]) {
                        clearTimeout(window[`statusTimer_${e.personnel_id}`]);
                    }

                    window[`statusTimer_${e.personnel_id}`] = setTimeout(() => {
                        if (dotEl) {
                            dotEl.classList.remove('bg-success');
                            dotEl.classList.remove('animate-pulse');
                            dotEl.classList.add('bg-base-300');
                        }
                        if (textEl) {
                            textEl.innerText = 'Offline';
                        }
                    }, 70000); // 1 minute 10 seconds
                };

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

                Livewire.on('kantors-updated', (data) => {
                    updateKantors(data.kantors);
                });

                updateMarkers(@json($this->devices));
                updateKantors(@json($this->kantors));

                // Initialize Echo if not exists (using CDN fallback for local dev)
                // Gunakan nama variabel kustom agar tidak bentrok dengan Livewire atau constructor Echo
                if (!window.CustomEcho && typeof Echo === 'function') {
                    window.Pusher = Pusher;
                    window.CustomEcho = new Echo({
                        broadcaster: 'reverb',
                        key: '{{ env('REVERB_APP_KEY') }}',
                        wsHost: '{{ env('REVERB_HOST', '127.0.0.1') }}',
                        wsPort: {{ env('REVERB_PORT', 8080) }},
                        wssPort: {{ env('REVERB_PORT', 8080) }},
                        forceTLS: {{ env('REVERB_SCHEME') === 'https' ? 'true' : 'false' }},
                        enabledTransports: ['ws', 'wss'],
                    });
                }

                try {
                    // Gunakan window.CustomEcho jika ada (hasil CDN), atau window.Echo jika ada (bawaan VPS)
                    const echoInstance = (window.Echo && typeof window.Echo.channel === 'function') ? window.Echo :
                        window.CustomEcho;

                    if (echoInstance && !window.EchoInstance) {
                        window.EchoInstance = echoInstance;
                        window.EchoInstance.channel('personnel-locations')
                            .listen('PersonnelLocationUpdated', (e) => {
                                console.log('Location updated via WebSocket:', e);
                                window.updateSingleMarker(e);
                            });
                        console.log('WebSocket: Berhasil mendaftarkan listener channel.');
                    }
                } catch (e) {
                    console.warn('Echo WebSocket gagal:', e.message);
                }

                window.mapsInitialized = true;

                // Putuskan koneksi saat pindah halaman (Livewire Navigation)
                if (!window.hasWsDisconnectListener) {
                    document.addEventListener('livewire:navigating', () => {
                        if (window.EchoInstance) {
                            console.log('Memutuskan koneksi WebSocket karena pindah halaman...');
                            window.EchoInstance.disconnect();
                            window.EchoInstance = null;
                        }
                    });
                    window.hasWsDisconnectListener = true;
                }
            };

            // Initial load
            if (window.Livewire) {
                initMapsHandler();
            }

            // On navigation
            document.addEventListener('livewire:navigated', () => {
                window.mapsInitialized = false; // Allow re-init for new DOM
                initMapsHandler();
            });
        })();
    </script>
</div>
