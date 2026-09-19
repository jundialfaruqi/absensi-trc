<!-- Leaflet Resources -->
{{-- <link rel="stylesheet" href="{{ asset('assets/leaflet/dist/leaflet.css') }}" />
<script src="{{ asset('assets/leaflet/dist/leaflet-global.js') }}"></script> --}}

<style>
    /* Custom scrollbar for sidebar if needed */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
    (function() {
        function setIcons(theme) {
            var sun = document.getElementById('sun-icon');
            var moon = document.getElementById('moon-icon');
            if (!sun || !moon) return;
            if (theme === 'winter') {
                sun.classList.add('hidden');
                moon.classList.remove('hidden');
            } else {
                sun.classList.remove('hidden');
                moon.classList.add('hidden');
            }
        }

        function initTheme() {
            var saved = localStorage.getItem('adminTheme') || 'winter';
            // Apply to HTML tag to prevent flash/glitch during wire:navigate
            document.documentElement.setAttribute('data-theme', saved);
            setIcons(saved);

            var btn = document.getElementById('theme-toggle');
            if (btn && !btn.__bound) {
                btn.addEventListener('click', function() {
                    var now = document.documentElement.getAttribute('data-theme') || 'winter';
                    var next = now === 'winter' ? 'dracula' : 'winter';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('adminTheme', next);
                    setIcons(next);
                });
                btn.__bound = true;
            }
        }

        function restoreSidebarScroll() {
            // Target the specific scrollable container in sidebar
            var sidebar = document.querySelector('.drawer-side aside .overflow-y-auto');
            if (!sidebar) return;

            // Restore from session
            var savedPos = sessionStorage.getItem('sidebar-scroll-pos');
            if (savedPos) {
                sidebar.scrollTop = savedPos;
            }

            // Save on scroll
            sidebar.addEventListener('scroll', function() {
                sessionStorage.setItem('sidebar-scroll-pos', sidebar.scrollTop);
            }, {
                passive: true
            });
        }

        function initAll() {
            initTheme();
            restoreSidebarScroll();
        }

        // Run immediately
        initAll();

        // Run on every Livewire navigation
        document.addEventListener('livewire:navigated', initAll);
        document.addEventListener('DOMContentLoaded', initAll);
    })();
</script>

<script>
    (function() {
        function showToast(detail) {
            var container = document.getElementById('global-toast');
            if (!container) return;
            var payload = detail;
            if (payload && typeof payload === 'object' && 'detail' in payload) {
                payload = payload.detail;
            }
            if (Array.isArray(payload)) {
                payload = payload[0] || {};
            }
            var type = (typeof payload === 'object' && payload && payload.type) || 'success';
            var title = (typeof payload === 'object' && payload && payload.title) || (type === 'error' ? 'Gagal' : (
                type === 'info' ? 'Info' : 'Berhasil'));
            var message = (typeof payload === 'string') ? payload : ((payload && (payload.message || payload
                .text)) || '');
            var key = [type, title, message].join('|');
            var now = Date.now();
            if (window.__toastLastKey === key && (now - (window.__toastLastAt || 0)) < 800) {
                return;
            }
            window.__toastLastKey = key;
            window.__toastLastAt = now;
            var alert = document.createElement('div');
            alert.className = 'alert bg-white text-black shadow-xl';
            var iconWrap = document.createElement('div');
            iconWrap.className = (type === 'error' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white') +
                ' rounded-full p-1.5 flex items-center justify-center';
            var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            icon.setAttribute('viewBox', '0 0 24 24');
            icon.setAttribute('class', 'w-5 h-5');
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', 'currentColor');
            icon.setAttribute('stroke-width', '1.5');
            if (type === 'error') {
                var p1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                p1.setAttribute('d', 'M18 6 6 18');
                p1.setAttribute('stroke-linecap', 'round');
                p1.setAttribute('stroke-linejoin', 'round');
                var p2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                p2.setAttribute('d', 'm6 6 12 12');
                p2.setAttribute('stroke-linecap', 'round');
                p2.setAttribute('stroke-linejoin', 'round');
                icon.appendChild(p1);
                icon.appendChild(p2);
            } else if (type === 'info') {
                var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', '12');
                circle.setAttribute('cy', '12');
                circle.setAttribute('r', '9');
                circle.setAttribute('stroke-linecap', 'round');
                circle.setAttribute('stroke-linejoin', 'round');
                var line1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                line1.setAttribute('d', 'M12 8h.01');
                var line2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                line2.setAttribute('d', 'M11 12h2v4h-2z');
                icon.appendChild(circle);
                icon.appendChild(line1);
                icon.appendChild(line2);
            } else {
                var ok = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                ok.setAttribute('d', 'M20 6 9 17l-5-5');
                ok.setAttribute('stroke-linecap', 'round');
                ok.setAttribute('stroke-linejoin', 'round');
                icon.appendChild(ok);
            }
            var textWrap = document.createElement('div');
            textWrap.className = 'flex flex-col';
            var titleEl = document.createElement('span');
            titleEl.className = 'font-bold';
            titleEl.textContent = title;
            var msgEl = document.createElement('span');
            msgEl.textContent = message;
            textWrap.appendChild(titleEl);
            if (message) {
                textWrap.appendChild(msgEl);
            }
            iconWrap.appendChild(icon);
            alert.appendChild(iconWrap);
            alert.appendChild(textWrap);
            container.appendChild(alert);
            setTimeout(function() {
                alert.classList.add('transition', 'duration-300', 'opacity-0');
                alert.addEventListener('transitionend', function() {
                    alert.remove();
                }, {
                    once: true
                });
            }, 2500);
        }

        function bindToast() {
            if (window.__toastBound) return;
            document.addEventListener('toast', function(e) {
                showToast(e.detail || {});
            }, {
                passive: true
            });
            if (window.Livewire && window.Livewire.on && !window.__toastLWBound) {
                window.Livewire.on('toast', function(payload) {
                    try {
                        var ev = new CustomEvent('toast', {
                            detail: payload,
                            bubbles: true
                        });
                        document.dispatchEvent(ev);
                    } catch (_) {
                        showToast(payload);
                    }
                });
                window.Livewire.on('set-pending-toast', function(payload) {
                    try {
                        localStorage.setItem('pendingToast', JSON.stringify(payload));
                    } catch (_) {}
                });
                window.__toastLWBound = true;
            }
            window.__toastBound = true;
            checkPendingToast();
        }

        function checkPendingToast() {
            try {
                var pending = localStorage.getItem('pendingToast');
                if (pending) {
                    localStorage.removeItem('pendingToast');
                    var payloadObj;
                    try {
                        payloadObj = JSON.parse(pending);
                    } catch (_) {
                        payloadObj = pending;
                    }
                    showToast(payloadObj);
                }
            } catch (_) {}
        }

        function registerNotificationManager() {
            if (!window.Alpine) return;
            if (window._adminNotifMgrRegistered) return;
            window._adminNotifMgrRegistered = true;

            Alpine.data('adminNotificationManager', function(config) {
                return {
                    items: config.initialItems || [],
                    isSuperAdmin: config.isSuperAdmin,
                    userOpdId: config.userOpdId,

                    get count() {
                        return this.items.length;
                    },

                    init() {
                        this.initEcho();
                    },

                    initEcho() {
                        var self = this;
                        var EchoConstructor = window._EchoHandler || window.Echo;
                        if (!EchoConstructor) {
                            setTimeout(function() { self.initEcho(); }, 1000);
                            return;
                        }

                        var echo = window.EchoInstance || window.CustomEcho;
                        if (!echo && typeof EchoConstructor === 'function') {
                            try {
                                echo = new EchoConstructor({
                                    broadcaster: 'reverb',
                                    key: '{{ config('reverb.apps.apps.0.key', env('REVERB_APP_KEY', 'zv7x8huegls10mbb45sk')) }}',
                                    wsHost: window.location.hostname,
                                    wsPort: {{ config('reverb.apps.apps.0.options.port', env('REVERB_PORT', 8080)) }},
                                    wssPort: {{ config('reverb.apps.apps.0.options.port', env('REVERB_PORT', 443)) }},
                                    forceTLS: window.location.protocol === 'https:',
                                    enabledTransports: ['ws', 'wss'],
                                    disableStats: true,
                                });
                                window.EchoInstance = echo;
                            } catch (e) {
                                console.warn('[RealtimeNotif] Gagal init Echo:', e);
                            }
                        }

                        if (!echo) return;

                        var handleEnrollment = function(data) {
                            var personnelId = data.personnel_id;
                            var opdId = data.opd_id;
                            var name = data.name || 'Personel TRC';
                            var opdName = data.opd_name || 'OPD TRC';

                            // Validasi wewenang OPD
                            if (!self.isSuperAdmin && self.userOpdId && opdId && parseInt(self.userOpdId) !== parseInt(opdId)) {
                                return;
                            }

                            // Cek duplikasi
                            var exists = self.items.some(function(item) {
                                return item.category === 'VERIFIKASI WAJAH' && item.url.indexOf('/' + personnelId + '/edit') !== -1;
                            });

                            if (!exists) {
                                self.items.unshift({
                                    url: '/page/personnel/' + personnelId + '/edit',
                                    color: 'error',
                                    icon: 'face',
                                    category: 'VERIFIKASI WAJAH',
                                    type: 'PENDING',
                                    title: name,
                                    message: 'Meminta verifikasi rekaman wajah 3D (' + opdName + ')',
                                    created_at: new Date().toISOString()
                                });

                                // Tampilkan Toast popup di Web
                                showToast({
                                    type: 'info',
                                    title: 'Verifikasi Wajah Masuk',
                                    message: name + ' (' + opdName + ') meminta verifikasi biometrik wajah 3D.'
                                });
                            }
                        };

                        var handleProcessed = function(data) {
                            var personnelId = data.personnel_id;
                            self.items = self.items.filter(function(item) {
                                return !(item.category === 'VERIFIKASI WAJAH' && item.url.indexOf('/' + personnelId + '/edit') !== -1);
                            });
                        };

                        try {
                            echo.channel('admin-notifications')
                                .listen('.FaceEnrollmentSubmitted', handleEnrollment)
                                .listen('FaceEnrollmentSubmitted', handleEnrollment)
                                .listen('.FaceVerificationProcessed', handleProcessed)
                                .listen('FaceVerificationProcessed', handleProcessed);

                            echo.channel('personnel-biometrics')
                                .listen('.FaceEnrollmentSubmitted', handleEnrollment)
                                .listen('FaceEnrollmentSubmitted', handleEnrollment);
                        } catch (err) {
                            console.warn('[RealtimeNotif] Error binding:', err);
                        }
                    }
                };
            });
        }

        if (window.Alpine) {
            registerNotificationManager();
        } else {
            document.addEventListener('alpine:init', registerNotificationManager);
        }

        document.addEventListener('DOMContentLoaded', bindToast);
        document.addEventListener('livewire:navigated', function() {
            checkPendingToast();
        });
        if (document.readyState !== 'loading') {
            bindToast();
        }
    })();
</script>
