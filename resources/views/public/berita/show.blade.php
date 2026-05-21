<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $berita->judul }} | TRC Pekanbaru Aman 112</title>
    <meta name="description" content="{{ Str::limit(strip_tags($berita->isi), 160) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(10px, 15px) rotate(2deg); }
        }

        .animate-float {
            animation: float 15s ease-in-out infinite;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .neon-glow-blue {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5), 0 0 40px rgba(59, 130, 246, 0.2);
        }

        /* Article Content Styles */
        .berita-content p {
            margin-bottom: 0rem;
            line-height: 1.8;
            color: #cbd5e1;
        }
        .berita-content h1, .berita-content h2, .berita-content h3 {
            color: white;
            font-weight: 900;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .berita-content h1 { font-size: 2rem; }
        .berita-content h2 { font-size: 1.75rem; }
        .berita-content h3 { font-size: 1.5rem; }
        .berita-content ul {
            list-style-type: disc;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
            color: #cbd5e1;
        }
        .berita-content ol {
            list-style-type: decimal;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
            color: #cbd5e1;
        }
        .berita-content li { margin-bottom: 0.5rem; }
        .berita-content a { color: #60a5fa; text-decoration: underline; }
        .berita-content a:hover { color: #93c5fd; }
        .berita-content strong, .berita-content b { color: white; font-weight: bold; }
        .berita-content img {
            border-radius: 1rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body class="bg-[#0a192f] text-slate-100 min-h-screen relative overflow-x-hidden antialiased">

    {{-- Background Decoration --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,#1a3a8a_0%,#0a192f_70%)]"></div>
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-10">
            <svg class="absolute top-[-5%] left-[-5%] w-150 h-150 text-blue-400 animate-float" viewBox="0 0 100 100">
                <circle cx="10" cy="10" r="1" fill="currentColor" />
                <circle cx="30" cy="20" r="1.2" fill="currentColor" />
                <circle cx="50" cy="15" r="1.5" fill="currentColor" />
                <line x1="10" y1="10" x2="30" y2="20" stroke="currentColor" stroke-width="0.3" />
                <line x1="30" y1="20" x2="50" y2="15" stroke="currentColor" stroke-width="0.3" />
            </svg>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="relative z-50 px-4 md:px-6 py-4 md:py-6 flex items-center justify-between max-w-7xl mx-auto w-full gap-2">
        <div class="flex items-center gap-1">
            <div class="shrink-0">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                    class="h-10 w-10 md:h-13 md:w-13 object-contain" />
            </div>
            <div class="flex flex-col leading-none">
                <a href="/"
                    class="text-sm md:text-xl font-black tracking-tighter text-white uppercase whitespace-nowrap">TRC
                    PEKANBARU</a>
                <span
                    class="text-[8px] md:text-[10px] font-bold text-blue-400 tracking-widest md:tracking-[0.2em] uppercase whitespace-nowrap">AMAN
                    112</span>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4 shrink-0">
            <a href="{{ url('/') }}"
                class="px-2 md:px-4 py-2 text-xs md:text-sm font-semibold text-slate-300 hover:text-white transition-colors whitespace-nowrap">Beranda</a>

            <a href="tel:112"
                class="px-3 md:px-6 py-2 md:py-2.5 bg-red-600 hover:bg-red-500 text-white font-black rounded-lg transition-all transform hover:scale-105 flex items-center gap-1.5 md:gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path
                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
                <span class="text-[10px] md:text-base">CALL 112</span>
            </a>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="relative z-10">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-12 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                {{-- Left Column: Main Article --}}
                <div class="lg:col-span-8">

                    {{-- Header Section --}}
                    <div class="space-y-6 mb-10">
                        {{-- Breadcrumb --}}
                        <nav class="flex text-[10px] font-black uppercase tracking-[0.2em]" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-2">
                                <li>
                                    <a href="{{ url('/') }}" class="text-slate-500 hover:text-blue-400 transition-colors">HOME</a>
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-3 h-3 text-slate-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    <span class="text-blue-500/80">{{ $berita->kategori ?? 'BERITA' }}</span>
                                </li>
                            </ol>
                        </nav>

                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 w-fit">
                            <span class="text-[10px] font-black uppercase tracking-widest text-blue-400">
                                {{ \Carbon\Carbon::parse($berita->created_at)->format('d M Y, H:i') }}
                            </span>
                        </div>

                        <h1 class="text-3xl lg:text-5xl font-black text-white leading-tight tracking-tight">
                            {{ $berita->judul }}
                        </h1>
                    </div>

                    {{-- Featured Image --}}
                    @if($berita->gambar)
                        <div class="w-full rounded-3xl overflow-hidden glass-panel border border-white/10 mb-12 shadow-2xl relative">
                            <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}" class="w-full h-auto object-cover max-h-[500px]">
                            <div class="absolute inset-0 bg-linear-to-t from-slate-900/20 via-transparent to-transparent pointer-events-none"></div>
                        </div>
                    @endif

                    {{-- Article Content --}}
                    <article class="berita-content" style="background-color: rgba(255,255,255,0.05); padding: 2rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1);">
                        <div style="color: #cbd5e1; font-size: 1.125rem; line-height: 1.8;">
                            {!! $berita->isi !!}
                        </div>
                    </article>

                    {{-- Related News Section --}}
                    @if($beritaTerkait->count() > 0)
                        <div class="mt-16 space-y-8">
                            <h3 class="text-xl font-black text-white flex items-center gap-2">
                                <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                                BERITA TERKAIT
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($beritaTerkait as $terkait)
                                    <a href="{{ route('public.berita.show', $terkait->slug) }}" class="group block glass-panel p-4 rounded-[2rem] border border-white/5 hover:border-blue-500/30 transition-all">
                                        <div class="flex items-center gap-4">
                                            @if($terkait->gambar)
                                                <div class="shrink-0 w-16 h-16 rounded-2xl overflow-hidden border border-white/10">
                                                    <img src="{{ asset('storage/' . $terkait->gambar) }}" alt="{{ $terkait->judul }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                                </div>
                                            @else
                                                <div class="shrink-0 w-16 h-16 rounded-2xl bg-blue-500/10 flex items-center justify-center border border-white/10">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <span class="text-[9px] font-bold text-blue-400 uppercase tracking-widest block mb-1">
                                                    {{ \Carbon\Carbon::parse($terkait->created_at)->format('d M Y') }}
                                                </span>
                                                <h4 class="text-sm font-bold text-slate-200 group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">
                                                    {{ $terkait->judul }}
                                                </h4>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Sidebar --}}
                <div class="lg:col-span-4">
                    <div class="sticky top-10 space-y-8">
                        {{-- Call to Action Widget (Optional but good for layout balance) --}}
                        <div class="bg-linear-to-br from-blue-600 to-blue-800 p-8 rounded-3xl shadow-xl relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="relative z-10 space-y-4">
                                <h3 class="text-xl font-black text-white leading-tight">Butuh Bantuan Darurat?</h3>
                                <p class="text-blue-100 text-sm leading-relaxed">
                                    Hubungi layanan darurat TRC Pekanbaru AMAN 112 untuk bantuan dan penanganan cepat dalam kondisi darurat. Tim siaga siap melayani masyarakat selama 24 jam penuh.
                                </p>
                                <a href="tel:112" class="inline-flex items-center gap-2 bg-white text-blue-700 px-6 py-3 rounded-xl font-black text-sm hover:bg-blue-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                    </svg>
                                    HUBUNGI 112
                                </a>
                            </div>
                        </div>
                        {{-- Latest News Widget --}}
                        <div class="glass-panel p-6 rounded-3xl border border-white/10">
                            <h3 class="text-xl font-black text-white mb-6 flex items-center gap-2">
                                <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                                BERITA TERBARU
                            </h3>
                            <div class="space-y-6">
                                @forelse($beritaTerbaru as $item)
                                    <a href="{{ route('public.berita.show', $item->slug) }}" class="group block">
                                        <div class="flex gap-4">
                                            @if($item->gambar)
                                                <div class="shrink-0 w-20 h-20 rounded-xl overflow-hidden border border-white/5">
                                                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                                </div>
                                            @endif
                                            <div class="space-y-1">
                                                <span class="text-[9px] font-bold text-blue-400 uppercase tracking-widest">
                                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                                                </span>
                                                <h4 class="text-sm font-bold text-white group-hover:text-blue-400 transition-colors line-clamp-2 leading-snug">
                                                    {{ $item->judul }}
                                                </h4>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-xs text-slate-500 italic">Tidak ada berita terbaru lainnya.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="relative z-10 border-t border-white/5 mt-6 py-8 px-6 text-center">
        <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">
            &copy; 2026 Pemerintah Kota Pekanbaru. Seluruh Hak Cipta Dilindungi.
        </p>
    </footer>

    @livewireScripts
</body>

</html>
