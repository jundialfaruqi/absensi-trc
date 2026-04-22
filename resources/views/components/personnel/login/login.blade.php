<div wire:init="load" x-data="{ showPassword: false }"
    class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    {{-- Background Glow --}}
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none">
    </div>

    @if ($ready)
        <div class="w-full max-w-md relative z-10">
            {{-- Branding --}}
            <div class="text-center mb-10 space-y-4">
                <div
                    class="inline-flex p-3 bg-white/5 rounded-2xl border border-white/10 backdrop-blur-xl shadow-2xl animate-pulse">
                    <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="TRC Logo" class="h-16 w-16 object-contain">
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-black text-white tracking-tighter uppercase italic">Portal Personnel</h1>
                    <p class="text-[10px] font-bold text-blue-400 uppercase tracking-[0.4em]">Emergency 112 Ecosystem
                    </p>
                </div>
            </div>

            {{-- Login Card --}}
            <div
                class="glass-panel p-8 md:p-10 rounded-[2.5rem] shadow-2xl border-white/10 relative group overflow-hidden">
                {{-- Decorative Line --}}
                <div
                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent">
                </div>

                <div class="mb-8">
                    <h2 class="text-xl font-black text-white uppercase italic tracking-wider">Akses Keamanan</h2>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Gunakan kredensial resmi
                        Anda</p>
                </div>

                <form wire:submit.prevent="authenticate" class="space-y-6">
                    @if ($errors->has('loginError'))
                        <div
                            class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center gap-3 animate-head-shake">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 shrink-0"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span
                                class="text-[10px] font-black text-red-500 uppercase tracking-wider">{{ $errors->first('loginError') }}</span>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kredensial
                            Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-slate-500 group-focus-within:text-blue-400 transition-colors"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </div>
                            <input type="email" wire:model="email" placeholder="example@trcpekanbaru.go.id"
                                class="w-full bg-slate-900/50 border border-white/5 rounded-2xl h-14 pl-12 pr-4 text-sm font-medium text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none" />
                        </div>
                        @error('email')
                            <span
                                class="text-[9px] font-bold text-red-500 uppercase tracking-widest ml-1 italic">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kunci
                            Keamanan</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-slate-500 group-focus-within:text-blue-400 transition-colors"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" wire:model="password"
                                placeholder="••••••••••••"
                                class="w-full bg-slate-900/50 border border-white/5 rounded-2xl h-14 pl-12 pr-12 text-sm font-medium text-white placeholder-slate-600 focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-white transition-colors">
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span
                                class="text-[9px] font-bold text-red-500 uppercase tracking-widest ml-1 italic">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" wire:model="remember"
                                class="w-4 h-4 rounded border-white/10 bg-slate-800 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900">
                            <span
                                class="text-[10px] font-bold text-slate-500 group-hover:text-slate-300 uppercase tracking-widest">Ingat
                                Sesi Saya</span>
                        </label>
                        <a href="{{ route('personnel.panduan') }}"
                            class="text-[10px] font-black text-blue-400 hover:text-blue-300 uppercase tracking-widest transition-colors">Bantuan?</a>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full h-14 bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-600/20 transition-all transform hover:scale-[1.02] active:scale-[0.98] grid place-items-center group overflow-hidden relative">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                        </div>

                        <span wire:loading.remove class="col-start-1 row-start-1">Masuk</span>

                        <div wire:loading class="col-start-1 row-start-1">
                            <div class="flex items-center gap-3">
                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="text-xs">Otentikasi...</span>
                            </div>
                        </div>
                    </button>
                </form>

                <div class="mt-4">
                    <a href="/"
                        class="btn btn-ghost btn-xs btn-block text-white/30 font-bold uppercase tracking-widest rounded-xl hover:bg-transparent hover:text-white/30 hover:shadow-none hover:border-none">Kembali</a>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-loose">
                        Belum punya akun? <br>
                        <a href="{{ route('personnel.register') }}" wire:navigate
                            class="text-blue-400 font-black hover:text-blue-300 transition-colors underline decoration-blue-500/30 underline-offset-4">Daftar
                            Sekarang</a>
                    </p>
                </div>

                <div class="mt-6 pt-8 border-t border-white/5 text-center">
                    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-[0.2em] leading-relaxed">
                        Sistem Monitoring Personil Terpadu <br />
                        Dinas Kominfo Kota Pekanbaru
                    </p>
                </div>
            </div>
        </div>
    @endif

    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes head-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .animate-head-shake {
            animation: head-shake 0.4s ease-in-out;
        }
    </style>
</div>
