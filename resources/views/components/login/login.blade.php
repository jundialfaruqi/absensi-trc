<div wire:init="load" x-data="{ showPassword: false }"
    class="min-h-screen flex items-center justify-center bg-[#0a192f] p-4 md:p-8 relative overflow-hidden font-sans antialiased">

    {{-- ─── Background Decoration Layers ────────────────────────────────────── --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        {{-- Base Gradient --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,#1a3a8a_0%,#0a192f_70%)]"></div>

        {{-- Digital Plexus Cluster --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
            <svg class="absolute top-[-10%] right-[-10%] w-[800px] h-[800px] text-blue-400 animate-float"
                viewBox="0 0 100 100">
                <circle cx="20" cy="20" r="1" fill="currentColor" />
                <circle cx="50" cy="10" r="1.2" fill="currentColor" />
                <circle cx="80" cy="30" r="1" fill="currentColor" />
                <circle cx="40" cy="40" r="1.5" fill="currentColor" />
                <circle cx="90" cy="60" r="1" fill="currentColor" />
                <line x1="20" y1="20" x2="50" y2="10" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="50" y1="10" x2="80" y2="30" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="80" y1="30" x2="40" y2="40" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="40" y1="40" x2="20" y2="20" stroke="currentColor"
                    stroke-width="0.3" />
                <line x1="90" y1="60" x2="80" y2="30" stroke="currentColor"
                    stroke-width="0.3" />
            </svg>
        </div>

        {{-- Neon Tech Lines --}}
        <div
            class="absolute top-1/4 left-0 w-full h-px bg-gradient-to-r from-transparent via-blue-500/20 to-transparent rotate-6">
        </div>
        <div
            class="absolute bottom-1/3 right-0 w-full h-px bg-gradient-to-r from-transparent via-red-500/10 to-transparent -rotate-12">
        </div>
    </div>

    <style>
        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            50% {
                transform: translate(15px, 20px) rotate(3deg);
            }
        }

        .animate-float {
            animation: float 20s ease-in-out infinite;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .neon-glow-red {
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.4), 0 0 40px rgba(220, 38, 38, 0.1);
        }

        .input-tech {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .input-tech:focus {
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.15);
            background: rgba(15, 23, 42, 0.6);
        }
    </style>

    @if ($ready)
        <div
            class="relative z-10 w-full max-w-6xl glass-panel rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">

            {{-- Left Side: Illustration & Branding --}}
            <div
                class="hidden md:flex md:w-1/2 p-16 flex-col justify-between relative overflow-hidden border-r border-white/5">

                <div class="relative z-10 space-y-12">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 p-2 bg-white/5 rounded-xl border border-white/10 backdrop-blur-md">
                            <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC"
                                class="h-full w-full object-contain">
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-xl font-black tracking-tighter text-white uppercase leading-none italic">TRC
                                PEKANBARU</span>
                            <span class="text-[9px] font-bold text-blue-400 uppercase tracking-[0.3em]">Emergency 112
                                Center</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h1 class="text-5xl font-black text-white leading-[1.1] tracking-tighter uppercase text-wrap">
                            SISTEM <br />
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 italic">REAKSI
                                CEPAT</span> <br />
                            TERINTEGRASI.
                        </h1>
                        <p class="text-slate-400 font-medium leading-relaxed max-w-sm">
                            Platform pengelolaan data dan absensi personil TRC 112 Kota Pekanbaru dalam ekosistem
                            proteksi publik digital.
                        </p>
                    </div>
                </div>

                {{-- Digital Illustration HUD Style --}}
                <div
                    class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex items-center justify-center opacity-20 pointer-events-none scale-150">
                    <div class="relative">
                        <div
                            class="w-64 h-64 rounded-full border-2 border-dashed border-blue-500/30 animate-[spin_40s_linear_infinite]">
                        </div>
                        <div
                            class="absolute inset-0 w-48 h-48 m-auto rounded-full border border-blue-400/20 animate-[spin_20s_linear_infinite_reverse]">
                        </div>
                        <div class="absolute inset-0 w-32 h-32 m-auto bg-blue-600/5 blur-2xl animate-pulse"></div>
                    </div>
                </div>

                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">
                        &copy; {{ date('Y') }} DISKOMINFO Kota Pekanbaru <br />
                        Dinas Komunikasi, Informatika & Statistik
                    </p>
                </div>
            </div>

            {{-- Right Side: Login Form --}}
            <div class="w-full md:w-1/2 p-8 md:p-20 flex flex-col justify-center relative">
                <div class="max-w-md mx-auto w-full relative z-10">
                    {{-- Mobile Branding --}}
                    <div class="md:hidden flex flex-col items-center mb-12 text-center">
                        <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo" class="h-16 w-16 mb-4">
                        <h3 class="text-2xl font-black tracking-tighter text-white uppercase italic">TRC PEKANBARU</h3>
                        <span class="text-[10px] font-bold text-blue-400 uppercase tracking-[0.3em]">112
                            EMERGENCY</span>
                    </div>

                    <div class="mb-10 text-center md:text-left">
                        <h2 class="text-3xl font-black text-white italic tracking-tighter uppercase mb-2">OTENTIKASI
                        </h2>
                        <p class="text-slate-400 text-xs font-semibold uppercase tracking-widest">Akses Portal
                            Pengelolaan TRC</p>
                    </div>

                    <form class="space-y-6" wire:submit.prevent="authenticate">
                        @if ($errors->has('loginError'))
                            <div
                                class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-[11px] font-black text-red-500 uppercase tracking-wider">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>{{ $errors->first('loginError') }}</span>
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Kredensial
                                Email</label>
                            <div class="relative group">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg>
                                </span>
                                <input type="text" wire:model="email" placeholder="nama@trcpekanbaru.go.id"
                                    class="input-tech w-full h-14 pl-12 pr-4 rounded-2xl text-sm font-medium text-white placeholder-slate-600 outline-none" />
                            </div>
                            @error('email')
                                <span
                                    class="text-[10px] font-bold text-red-500 italic ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Kunci
                                Keamanan</label>
                            <div class="relative group">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" />
                                        <circle cx="12" cy="16" r="1" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" wire:model="password"
                                    placeholder="••••••••••••"
                                    class="input-tech w-full h-14 pl-12 pr-12 rounded-2xl text-sm font-medium text-white placeholder-slate-600 outline-none" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z" />
                                    </svg>
                                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span
                                    class="text-[10px] font-bold text-red-500 italic ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" wire:model="remember"
                                    class="w-4 h-4 rounded-md border-white/10 bg-slate-900 focus:ring-blue-500 focus:ring-offset-slate-900 transition-all cursor-pointer" />
                                <span
                                    class="text-[10px] font-bold text-slate-400 group-hover:text-slate-300 transition-colors uppercase tracking-widest">Ingat
                                    Akses Saya</span>
                            </label>
                            <a href="#"
                                class="text-[10px] font-black text-blue-400 hover:text-blue-300 transition-colors uppercase tracking-widest">Bantuan
                                Kunci?</a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="relative w-full h-14 bg-red-600 hover:bg-red-500 text-white font-black uppercase tracking-[0.2em] rounded-2xl transition-all transform hover:scale-[1.01] active:scale-[0.98] neon-glow-red grid place-items-center overflow-hidden group">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                            </div>

                            <span wire:loading.remove class="col-start-1 row-start-1">Masuk Sekarang</span>

                            <span wire:loading.flex
                                class="col-start-1 row-start-1 flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="tracking-widest">MEMPROSES...</span>
                            </span>
                        </button>

                        <div class="mt-4">
                            <a href="/"
                                class="btn btn-ghost btn-xs btn-block text-white/30 font-bold uppercase tracking-widest rounded-xl hover:bg-transparent hover:text-white/30 hover:shadow-none hover:border-none">Kembali</a>
                        </div>

                        <div class="pt-4 text-center">
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">
                                Dengan melanjutkan, kamu menerima dan menyetujui<br />
                                <a href="#" class="text-blue-400 hover:text-blue-300 transition-colors">Syarat &
                                    Ketentuan</a>
                            </p>
                        </div>
                    </form>

                    <div
                        class="mt-12 flex items-center justify-center gap-6 opacity-60 grayscale hover:grayscale-0 transition-all duration-700">
                        <img src="{{ asset('assets/logo/aman.webp') }}" alt="AMAN" class="h-6">
                        <img src="{{ asset('assets/logo/bangun-negeri.webp') }}" alt="Bangun Negeri" class="h-6">
                        <img src="{{ asset('assets/logo/logo-diskominfo-pekanbaru.webp') }}" alt="Diskominfo"
                            class="h-6">
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
