<main class="relative z-10 max-w-7xl mx-auto px-6 py-12 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
        <div class="flex flex-col space-y-8">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 w-fit">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-400">Siaga 24 Jam
                    Penuh</span>
            </div>

            <h1 class="text-5xl lg:text-7xl font-black text-white leading-[1.05] tracking-tighter">
                PEKANBARU <span
                    class="text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-cyan-300">AMAN</span> DALAM
                SATU GENGGAMAN.
            </h1>

            <p class="text-slate-400 text-lg lg:text-xl font-medium leading-relaxed max-w-xl">
                Transformasi layanan publik Kota Pekanbaru melalui Tim Reaksi Cepat (TRC) 112. Penanganan berbagai
                kejadian darurat kini lebih cepat, terpadu, dan profesional.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                <a href="tel:112"
                    class="w-full sm:w-auto px-10 py-5 bg-linear-to-br from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white text-lg font-black rounded-2xl shadow-xl transition-all neon-glow-red text-center">
                    HUBUNGI 112
                </a>
                <div class="flex items-center gap-4 px-6 py-5 glass-panel rounded-2xl border-white/5">
                    <div class="flex -space-x-4">
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-kominfo-logo-only.png') }}" alt="DISKOMINFO"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-bpbd.jpg') }}" alt="BPBD"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-damkar.png') }}" alt="DAMKAR"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-kemenkes.png') }}" alt="KEMENKES"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-polresta-pku.jpg') }}" alt="POLRESTA"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-satpol-pp.png') }}" alt="SATPOL PP"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="h-8 w-8 rounded-full border-2 border-white overflow-hidden bg-white">
                            <img src="{{ asset('assets/logo/logo-tni.png') }}" alt="TNI"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-300">Terintegrasi Lintas Sektoral</span>
                </div>
            </div>

            <div class="pt-8 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">24/7</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Respon Aktif</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-blue-400">112</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nomor Tunggal</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">FAST</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Response Time</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">ALL</span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">City Coverage</span>
                </div>
            </div>
        </div>

        {{-- Visual HUD Element: 3D Holographic Carousel --}}
        <div class="hidden lg:flex justify-center items-center relative min-h-125 w-full" x-data="{
            active: 0,
            total: 7,
            images: [
                '{{ asset('assets/images/carousel/1.jpg') }}',
                '{{ asset('assets/images/carousel/2.jpg') }}',
                '{{ asset('assets/images/carousel/3.jpg') }}',
                '{{ asset('assets/images/carousel/4.jpg') }}',
                '{{ asset('assets/images/carousel/5.jpg') }}',
                '{{ asset('assets/images/carousel/6.jpg') }}',
                '{{ asset('assets/images/carousel/7.jpg') }}'
            ],
            next() { this.active = (this.active + 1) % this.total },
            prev() { this.active = (this.active - 1 + this.total) % this.total },
            getCardClass(index) {
                if (index === this.active) return 'card-active';
                if (index === (this.active - 1 + this.total) % this.total) return 'card-prev';
                if (index === (this.active + 1) % this.total) return 'card-next';
                if (index < this.active) return 'card-far-left';
                return 'card-far-right';
            }
        }"
            x-init="setInterval(() => next(), 5000)">

            {{-- HUD Background Decorations --}}
            <div
                class="absolute w-140 h-140 rounded-full border-2 border-blue-500/5 animate-[spin_40s_linear_infinite]">
            </div>
            <div
                class="absolute w-110 h-110 rounded-full border border-blue-400/5 animate-[spin_25s_linear_infinite_reverse]">
            </div>

            {{-- 3D Carousel Container --}}
            <div class="relative w-full h-100 carousel-view flex items-center justify-center">
                <template x-for="(img, index) in images" :key="index">
                    <div class="absolute w-[320px] h-112.5 carousel-card rounded-3xl overflow-hidden border border-white/10 shadow-2xl"
                        :class="getCardClass(index)">

                        {{-- Image with HUD Overlay --}}
                        <div class="relative w-full h-full">
                            <img :src="img" class="w-full h-full object-cover" alt="Carousel Image">

                            {{-- Holographic HUD Overlay --}}
                            <div class="absolute inset-0 hud-overlay opacity-40"></div>
                            <div class="absolute inset-0 bg-linear-to-t from-slate-900 via-transparent to-transparent">
                            </div>
                            <div class="scan-line"></div>

                            {{-- Digital Corner Labels --}}
                            <div class="absolute top-4 left-4 flex gap-1">
                                <div class="h-1 w-4 bg-blue-500/50"></div>
                                <div class="h-4 w-1 bg-blue-500/50"></div>
                            </div>
                            <div class="absolute bottom-4 right-4 flex flex-col items-end">
                                <span class="text-[8px] font-black text-blue-400/70 tracking-widest uppercase"
                                    x-text="'CAM-' + (index + 1).toString().padStart(3, '0')"></span>
                                <span class="text-[10px] font-bold text-white/50"
                                    x-text="new Date().toLocaleTimeString()"></span>
                            </div>

                            {{-- Central Target Reticle --}}
                            <div
                                class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
                                <div
                                    class="w-12 h-12 border border-blue-400/30 rounded-full flex items-center justify-center">
                                    <div class="w-1 h-1 bg-blue-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Technical Pagination dots --}}
            <div class="absolute -bottom-8 flex gap-3">
                <template x-for="i in total" :key="i - 1">
                    <button @click="active = i-1" class="h-1 transition-all duration-500 rounded-full"
                        :class="active === i - 1 ? 'w-8 bg-blue-500 shadow-lg shadow-blue-500/50' :
                            'w-2 bg-slate-700 hover:bg-slate-500'">
                    </button>
                </template>
            </div>
        </div>
    </main>
