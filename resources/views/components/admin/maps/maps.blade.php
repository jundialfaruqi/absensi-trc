<div wire:init="load">
    {{-- ─── Main Content ────────────────────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row gap-4 lg:h-[calc(100vh-12rem)]">
        {{-- Sidebar --}}
        <div class="w-full lg:w-80 bg-base-100 rounded-box shadow-sm p-4 flex flex-col gap-4 lg:overflow-hidden">
            <div class="form-control w-full">
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama personnel..."
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

            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                <span class="text-sm font-semibold text-base-content">
                    Online : <span id="online-count">{{ $this->totalOnline }}</span>
                </span>
            </div>

            <div id="device-list" class="flex-1 overflow-y-auto space-y-2">
                @forelse($this->devices as $d)
                    @php $markerKey = $d->personnel_id ? ('p' . $d->personnel_id) : ('d' . $d->id); @endphp
                    @php $itemId = $d->personnel_id ?? 'd' . $d->id; @endphp
                    <div id="device-item-{{ $itemId }}" class="p-2 hover:bg-base-200 rounded-lg cursor-pointer flex items-center gap-2"
                        onclick="focusMarker('{{ $markerKey }}', {{ $d->last_latitude }}, {{ $d->last_longitude }})">
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
                                    class="w-2.5 h-2.5 rounded-full {{ $d->last_seen_at && $d->last_seen_at->diffInMinutes() < 1 ? 'bg-emerald-500' : 'bg-base-300' }}"></span>
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
        <div class="w-full min-h-[60vh] lg:min-h-0 lg:flex-1 bg-base-100 rounded-box shadow-sm overflow-hidden relative"
            wire:ignore>
            <div id="map" class="absolute inset-0 w-full h-full z-0"></div>
        </div>
    </div>

    {{-- ─── Styles & Scripts ────────────────────────────────────────────── --}}
    <style>
        .custom-div-icon {
            background: none;
            border: none;
        }
    </style>


    <script>
        (function() {
            const initMapsHandler = () => {
                if (window.mapsInitialized) return;

                const mapEl = document.getElementById('map');
                if (!mapEl) return;

                // Jika elemen kontainer sudah memiliki peta Leaflet, batalkan inisialisasi ulang
                if (mapEl._leaflet_id) return;

                if (typeof L === 'undefined' || typeof L.markerClusterGroup !== 'function') {
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
                                            <div class="w-10 h-10 rounded-full border-2 border-primary shadow-lg overflow-hidden">
                                                <img src="${iconUrl}" class="w-full h-full object-cover rounded-full" style="width: 100%; height: 100%; object-fit: cover;" />
                                            </div>
                                        </div>
                                   </div>`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 20]
                        });

                        const marker = L.marker([d.last_latitude, d.last_longitude], {
                            icon: icon
                        });

                        const name = d.personnel ? d.personnel.name : (d.name || 'Perangkat Global');
                        const opdName = d.opd ? d.opd.name : 'Global (Admin)';
                        const penugasan = d.personnel && d.personnel.penugasan ? d.personnel.penugasan
                            .name : '-';

                        let statusPopup = 'Online';
                        let statusColorClass = 'text-success';

                        if (d.last_seen_human && !d.last_seen_human.includes('detik')) {
                            statusPopup = `Aktif: ${d.last_seen_human}`;
                            statusColorClass = 'opacity-60';
                        }

                        const popupContent = `
                            <div class="p-2 max-w-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="avatar">
                                        <div class="w-12 h-12 rounded-full overflow-hidden">
                                            <img src="${iconUrl}" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">${name}</div>
                                        <div class="text-xs opacity-60">${opdName}</div>
                                    </div>
                                </div>
                                <div class="text-xs mb-1"><strong>Penugasan:</strong> ${penugasan}</div>
                                <div class="text-xs mb-1" id="address-${d.id}"><strong>Lokasi:</strong> Memuat alamat...</div>
                                <div class="text-xs ${statusColorClass}" id="popup-status-${d.personnel_id || 'd'+d.id}">${statusPopup}</div>
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
                    const markerKey = e.personnel_id ? ('p' + e.personnel_id) : ('d' + e.device_id);
                    const marker = markers[markerKey];
                    const elementId = e.personnel_id || ('d' + e.device_id);

                    if (marker) {
                        marker.setLatLng([e.latitude, e.longitude]);

                        // Update status in popup if it's open
                        const popupStatusEl = document.getElementById(`popup-status-${elementId}`);
                        if (popupStatusEl) {
                            popupStatusEl.innerText = 'Online';
                            popupStatusEl.className = 'text-xs text-success';
                        }

                        // Update the stored popup content so it persists when closed and reopened
                        if (marker.getPopup()) {
                            const currentPopupContent = marker.getPopup().getContent();
                            if (typeof currentPopupContent === 'string') {
                                const regex = new RegExp(
                                    `<div class="text-xs [^"]*" id="popup-status-${elementId}">[^<]*</div>`);
                                const newDiv =
                                    `<div class="text-xs text-success" id="popup-status-${elementId}">Online</div>`;
                                marker.setPopupContent(currentPopupContent.replace(regex, newDiv));
                            }
                        }
                    }

                    // Update status dot and text in sidebar
                    const dotEl = document.getElementById(`status-dot-${elementId}`);
                    const textEl = document.getElementById(`status-text-${elementId}`);

                    if (dotEl) {
                        dotEl.classList.remove('bg-base-300');
                        dotEl.classList.add('bg-emerald-500');
                        dotEl.classList.add('animate-pulse');
                    }
                    if (textEl) {
                        textEl.innerText = 'Online';
                        textEl.classList.remove('opacity-60');
                    }

                    // Move this item to top of sidebar list
                    const listItem = document.getElementById(`device-item-${elementId}`);
                    const listContainer = document.getElementById('device-list');
                    if (listItem && listContainer && listContainer.firstElementChild !== listItem) {
                        listContainer.prepend(listItem);
                    }

                    // Update online counter
                    updateOnlineCount();

                    // Set timer to clear online status after 70 seconds
                    const timerKey = `statusTimer_${elementId}`;
                    if (window[timerKey]) {
                        clearTimeout(window[timerKey]);
                    }

                    window[timerKey] = setTimeout(() => {
                        const dotEl = document.getElementById(`status-dot-${elementId}`);
                        const textEl = document.getElementById(`status-text-${elementId}`);
                        if (dotEl) {
                            dotEl.classList.remove('bg-emerald-500');
                            dotEl.classList.remove('animate-pulse');
                            dotEl.classList.add('bg-base-300');
                        }
                        if (textEl) {
                            textEl.innerText = 'Aktif: 1 menit yang lalu';
                        }

                        // Update status in popup too
                        const popupStatusEl = document.getElementById(`popup-status-${elementId}`);
                        if (popupStatusEl) {
                            popupStatusEl.innerText = 'Aktif: 1 menit yang lalu';
                            popupStatusEl.className = 'text-xs opacity-60';
                        }

                        // Update the stored popup content to Offline too
                        if (marker && marker.getPopup()) {
                            const currentPopupContent = marker.getPopup().getContent();
                            if (typeof currentPopupContent === 'string') {
                                const regex = new RegExp(
                                    `<div class="text-xs [^"]*" id="popup-status-${elementId}">[^<]*</div>`
                                );
                                const newDiv =
                                    `<div class="text-xs opacity-60" id="popup-status-${elementId}">Aktif: 1 menit yang lalu</div>`;
                                marker.setPopupContent(currentPopupContent.replace(regex, newDiv));
                            }
                        }

                        // Update online counter
                        updateOnlineCount();
                    }, 70000); // 1 minute 10 seconds
                };

                function updateOnlineCount() {
                    const count = document.querySelectorAll('#device-list .bg-emerald-500').length;
                    const el = document.getElementById('online-count');
                    if (el) { el.textContent = count; }
                }



                function fetchAddress(lat, lng, id) {
                    const el = document.getElementById(`address-${id}`);
                    if (!el) return;

                    fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
                        )
                        .then(response => response.json())
                        .then(data => {
                            const address = data.display_name;
                            el.innerHTML = `<strong>Lokasi:</strong> ${address}`;

                            // Update stored popup content to persist address
                            const marker = markers['p' + id] || markers['d' + id];
                            if (marker && marker.getPopup()) {
                                const currentPopupContent = marker.getPopup().getContent();
                                if (typeof currentPopupContent === 'string') {
                                    const regex = new RegExp(
                                        `<div class="text-xs mb-1" id="address-${id}">[\\s\\S]*?</div>`);
                                    const newDiv =
                                        `<div class="text-xs mb-1" id="address-${id}"><strong>Lokasi:</strong> ${address}</div>`;
                                    marker.setPopupContent(currentPopupContent.replace(regex, newDiv));
                                }
                            }
                        })
                        .catch(error => {
                            const failText = `Gagal memuat alamat.`;
                            el.innerHTML = `<strong>Lokasi:</strong> ${failText}`;

                            const marker = markers['p' + id] || markers['d' + id];
                            if (marker && marker.getPopup()) {
                                const currentPopupContent = marker.getPopup().getContent();
                                if (typeof currentPopupContent === 'string') {
                                    const regex = new RegExp(
                                        `<div class="text-xs mb-1" id="address-${id}">[\\s\\S]*?</div>`);
                                    const newDiv =
                                        `<div class="text-xs mb-1" id="address-${id}"><strong>Lokasi:</strong> ${failText}</div>`;
                                    marker.setPopupContent(currentPopupContent.replace(regex, newDiv));
                                }
                            }
                        });
                }

                window.focusMarker = function(key, lat, lng) {
                    if (map) {
                        const marker = markers[key];
                        if (marker) {
                            map.setView(marker.getLatLng(), 16);
                            marker.openPopup(); // Opsi: langsung buka popupnya juga
                        } else if (lat && lng) {
                            map.setView([lat, lng], 16);
                        }
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
                // Gunakan _EchoHandler (Constructor) yang ditangkap oleh shim di app.blade.php
                const EchoConstructor = window._EchoHandler || window.Echo;

                if (!window.CustomEcho && typeof EchoConstructor === 'function') {
                    window.Pusher = Pusher;
                    const reverbHost = '{{ env('REVERB_HOST') }}';
                    const wsHost = (reverbHost === '127.0.0.1' || reverbHost === 'localhost' || !reverbHost) ?
                        window.location.hostname : reverbHost;

                    const isSecure = window.location.protocol === 'https:';
                    window.CustomEcho = new EchoConstructor({
                        broadcaster: 'reverb',
                        key: '{{ env('REVERB_APP_KEY') }}',
                        wsHost: wsHost,
                        wsPort: window.location.port || (isSecure ? 443 : 80),
                        wssPort: window.location.port || (isSecure ? 443 : 80),
                        forceTLS: isSecure,
                        enabledTransports: ['ws', 'wss'],
                    });

                    // Set ke global Echo agar Livewire bisa mendeteksi jika diperlukan,
                    // tapi shim kita akan menjamin socketId() ada.
                    window.Echo = window.CustomEcho;
                }

                try {
                    const echoInstance = window.CustomEcho || window.Echo;

                    if (echoInstance) {
                        window.EchoInstance = echoInstance;

                        if (typeof window.EchoInstance.connect === 'function') {
                            window.EchoInstance.connect();
                        }

                        window.EchoInstance.channel('personnel-locations')
                            .stopListening('PersonnelLocationUpdated')
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
                            if (typeof window.EchoInstance.disconnect === 'function') {
                                window.EchoInstance.disconnect();
                            }
                            window.EchoInstance = null;
                            window.CustomEcho = null;
                            window.Echo =
                                null; // Penting: Set null agar shim mengosongkan actualEcho dan tidak mengganggu Livewire di halaman lain
                        }
                    });
                    window.hasWsDisconnectListener = true;
                }
            };

            // Initial load
            if (window.Livewire) {
                initMapsHandler();
            } else {
                document.addEventListener('livewire:init', initMapsHandler);
            }

            // On navigation
            document.addEventListener('livewire:navigated', () => {
                window.mapsInitialized = false; // Allow re-init for new DOM
                initMapsHandler();
            });
        })();
    </script>
</div>
