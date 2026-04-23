<div class="max-w-3xl mx-auto space-y-8 animate-in slide-in-from-bottom-4 duration-700">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="space-y-1">
            <a href="{{ url('/personnel/dashboard') }}"
                class="group inline-flex items-center gap-2 text-slate-500 hover:text-blue-400 text-[10px] font-black uppercase tracking-widest transition-colors mb-2">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
            <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter">Pengaturan Profil</h1>
            <p class="text-slate-400 font-medium text-sm">Perbarui kredensial keamanan akun Anda.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div
            class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3 animate-bounce-short">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 shrink-0" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            <span
                class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-panel p-8 md:p-10 rounded-[2.5rem] border-white/5 relative overflow-hidden">
        {{-- High-tech Accents --}}
        <div class="absolute top-0 right-0 h-32 w-32 bg-blue-600/5 blur-3xl pointer-events-none"></div>

        <form wire:submit.prevent="updateProfile" class="space-y-8 relative z-10">
            {{-- Email Group --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-black text-white uppercase tracking-widest italic">Kontak & Kredensial</h3>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Alamat Email
                        Aktif</label>
                    <input type="email" wire:model="email"
                        class="w-full bg-slate-900/50 border border-white/5 rounded-2xl h-14 px-5 text-sm font-medium text-white focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none" />
                    @error('email')
                        <span
                            class="text-[9px] font-bold text-red-500 uppercase tracking-widest ml-1 italic">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="h-px w-full bg-white/5"></div>

            {{-- Security Group --}}
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-black text-white uppercase tracking-widest italic">Keamanan Akun</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Password
                            Baru</label>
                        <input type="password" wire:model="password" placeholder="Kosongkan jika tidak diubah"
                            class="w-full bg-slate-900/50 border border-white/5 rounded-2xl h-14 px-5 text-sm font-medium text-white focus:border-blue-500/50 transition-all outline-none" />
                        @error('password')
                            <span
                                class="text-[9px] font-bold text-red-500 uppercase tracking-widest ml-1 italic">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Konfirmasi
                            Password</label>
                        <input type="password" wire:model="password_confirmation" placeholder="Ulangi password baru"
                            class="w-full bg-slate-900/50 border border-white/5 rounded-2xl h-14 px-5 text-sm font-medium text-white focus:border-blue-500/50 transition-all outline-none" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-1">Update Kode PIN
                        (4 Digit)</label>
                    <div class="relative group">
                        <input type="password" wire:model="pin" maxlength="4" placeholder="Masukkan 4 digit PIN baru"
                            class="w-full bg-slate-910/50 border border-white/5 rounded-2xl h-14 px-5 text-center text-xl font-black text-white tracking-[1em] focus:border-red-500/50 transition-all outline-none placeholder:text-xs placeholder:tracking-normal placeholder:font-medium placeholder:text-slate-600" />
                    </div>
                    @error('pin')
                        <span
                            class="text-[9px] font-bold text-red-500 uppercase tracking-widest ml-1 italic">{{ $message }}</span>
                    @enderror
                    <p class="text-[8px] font-bold text-slate-500 uppercase tracking-widest italic ml-1">PIN digunakan
                        untuk absensi (4 Digit).</p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-4">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full h-15 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl shadow-xl shadow-blue-600/20 transition-all grid place-items-center group relative overflow-hidden">
                    <div
                        class="absolute inset-0 bg-linear-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                    </div>

                    <span wire:loading.remove class="col-start-1 row-start-1">Simpan Perubahan Data</span>

                    <div wire:loading class="col-start-1 row-start-1">
                        <div class="flex items-center gap-3">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-xs">Menyimpan...</span>
                        </div>
                    </div>
                </button>
            </div>
        </form>
    </div>

    <style>
        @keyframes bounce-short {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px);
            }
        }

        .animate-bounce-short {
            animation: bounce-short 1.5s ease-in-out infinite;
        }
    </style>
</div>
