<section class="relative z-10 py-12 lg:py-20 overflow-hidden border-t border-white/5 bg-slate-900/10">
    <div class="max-w-7xl mx-auto px-6 mb-12 text-center">
        <h2 class="text-3xl font-black text-white tracking-widest uppercase">Lintas Sektoral</h2>
        <div class="h-1 w-20 bg-blue-500 mx-auto mt-4 rounded-full"></div>
    </div>

    <div class="flex relative overflow-hidden">
        <div class="flex animate-marquee whitespace-nowrap gap-8 py-8">
            @php
                $partners = [
                    ['logo' => 'logo-kominfo-logo-only.png', 'name' => 'Dinas Kominfo Pekanbaru'],
                    ['logo' => 'logo-bpbd.jpg', 'name' => 'BPBD Pekanbaru'],
                    ['logo' => 'logo-damkar.png', 'name' => 'Damkar Pekanbaru'],
                    ['logo' => 'logo-kemenkes.png', 'name' => 'Dinas Kesehatan Pekanbaru'],
                    ['logo' => 'logo-pemko.webp', 'name' => 'Dinas Sosial Pekanbaru'],
                    ['logo' => 'logo-satpol-pp.png', 'name' => 'Satpol PP Pekanbaru'],
                    ['logo' => 'logo-dishub-pku.png', 'name' => 'Dinas Perhubungan Pekanbaru'],
                    ['logo' => 'logo-dlhk-pku.png', 'name' => 'DLHK Pekanbaru'],
                ];
            @endphp

            @foreach (array_merge($partners, $partners, $partners) as $p)
                <div
                    class="flex flex-col items-center justify-center p-8 bg-white rounded-3xl shadow-2xl w-64 shrink-0 gap-6 border border-slate-200 transform transition-transform hover:scale-105">
                    <div class="h-24 w-24 flex items-center justify-center">
                        <img src="{{ asset('assets/logo/' . $p['logo']) }}" alt="{{ $p['name'] }}"
                            class="max-h-full max-w-full object-contain">
                    </div>
                    <span
                        class="text-sm font-black text-slate-800 uppercase tracking-tighter text-center whitespace-normal leading-tight h-10 flex items-center">{{ $p['name'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Atmospheric Overlays --}}
        <div class="absolute inset-y-0 left-0 w-40 bg-linear-to-r from-[#0a192f] to-transparent z-10"></div>
        <div class="absolute inset-y-0 right-0 w-40 bg-linear-to-l from-[#0a192f] to-transparent z-10"></div>
    </div>
</section>
