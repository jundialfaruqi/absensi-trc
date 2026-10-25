<div class="min-h-[80vh] flex items-center justify-center px-2 py-4 md:py-10 relative z-10">
    <div class="w-full max-w-md">
        <div class="glass-panel p-6 md:p-10 rounded-[2.5rem] border-white/10 shadow-2xl relative overflow-hidden group">
            <!-- Animated background accent -->
            <div
                class="absolute -top-24 -right-24 w-48 h-48 bg-blue-600/10 rounded-full blur-3xl group-hover:bg-blue-600/20 transition-all duration-700">
            </div>

            <div class="relative z-10 flex flex-col items-center text-center space-y-4">
                <!-- Icon -->
                <div class="flex items-center justify-center p-4">
                    <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo TRC" class="w-20 h-20 object-contain" />
                </div>

                <div class="space-y-2">
                    <h2 class="text-3xl font-black text-white tracking-tighter uppercase">Download APK</h2>
                    <p class="text-slate-400 text-sm font-medium">Silakan masukkan 6-digit PIN Personnel Anda untuk
                        mengunduh aplikasi.</p>
                </div>

                <form wire:submit.prevent="download" class="w-full space-y-6">
                    <div class="form-control w-full" x-data="{ show: false }">
                        <div class="relative">
                            <input x-bind:type="show ? 'text' : 'password'" wire:model.live="pin" maxlength="6"
                                placeholder="••••••"
                                class="w-full bg-slate-900/50 border-2 border-white/5 rounded-2xl px-4 py-4 md:px-5 md:py-5 text-center text-xl md:text-3xl font-black tracking-[0.5em] text-white focus:border-blue-500/50 focus:ring-0 transition-all placeholder:text-white/50">
                            <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('pin')
                            <span
                                class="text-red-500 text-xs font-bold mt-3 uppercase tracking-wider">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (!app()->isLocal())
                        <div class="flex justify-center min-h-78px bg-transparent" wire:ignore>
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"
                                data-callback="onRecaptchaSuccess"></div>
                        </div>

                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <script>
                            function onRecaptchaSuccess(token) {
                                @this.set('recaptchaToken', token);
                            }

                            window.addEventListener('reset-captcha', () => {
                                if (typeof grecaptcha !== 'undefined') {
                                    grecaptcha.reset();
                                }
                            });
                        </script>
                    @endif

                    <button type="submit"
                        class="w-full h-12 bg-linear-to-br from-blue-600 to-blue-700 text-white font-black rounded-2xl shadow-xl neon-glow-blue flex items-center justify-center gap-3 group/btn relative overflow-hidden cursor-pointer"
                        wire:loading.attr="disabled">

                        <!-- Spinner -->
                        <div wire:loading.flex wire:target="download" class="items-center justify-center">
                            <span class="loading loading-spinner loading-md"></span>
                        </div>

                        <!-- Button Content (Hidden when loading) -->
                        <div wire:loading.remove wire:target="download" class="items-center">
                            <span class="text-white uppercase tracking-wider">Lanjutkan Download</span>
                        </div>
                    </button>
                </form>

                <div class="pt-4 border-t border-white/5 w-full">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Hanya untuk personil resmi TRC Kota Pekanbaru.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilihan Jalur Download (Google Drive vs Direct Server) -->
    @if ($showOptionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-fadeIn"
            x-data x-trap.noscroll="true">
            <div
                class="w-full max-w-lg glass-panel p-6 sm:p-8 rounded-[2.5rem] border border-white/10 shadow-2xl relative overflow-hidden bg-slate-950/95">
                <!-- Background Accent Glow -->
                <div
                    class="absolute -top-24 -right-24 w-48 h-48 bg-blue-600/20 rounded-full blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/15 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="relative z-10 space-y-6">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3.5">
                            <div>
                                <span class="text-[10px] font-black uppercase text-emerald-400">PIN
                                    Terverifikasi</span>
                                <h3 class="text-xl font-black text-white tracking-tight uppercase">Pilih Jalur Unduhan
                                </h3>
                            </div>
                        </div>

                        <button type="button" wire:click="closeOptionModal"
                            class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-all cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if ($personnelName)
                        <div class="p-3 bg-white/5 rounded-2xl border border-white/5 flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400 shrink-0"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs text-slate-300 font-medium truncate">Personil: <strong
                                    class="text-white font-bold">{{ $personnelName }}</strong></span>
                        </div>
                    @endif

                    <p class="text-slate-400 text-xs leading-relaxed">
                        Silakan pilih jalur unduhan APK berikut. Disarankan menggunakan <strong>Google Drive</strong>
                        untuk kecepatan unduh maksimal di jaringan seluler/HP:
                    </p>

                    <!-- Options List -->
                    <div class="space-y-3">
                        <!-- Option 1: Google Drive (Recommended) -->
                        <a href="{{ $gdriveUrl }}" target="_blank" rel="noopener noreferrer"
                            class="group block p-4.5 rounded-full bg-linear-to-r from-blue-600/20 via-blue-500/10 to-transparent hover:from-blue-600/30 hover:via-blue-500/20 border-2 border-blue-500/40 hover:border-blue-400 transition-all duration-300 relative overflow-hidden shadow-lg shadow-blue-500/10">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3.5">
                                    <div
                                        class="w-12 h-12 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                        <!-- Cloud/Drive Rocket Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="flex items-center gap-2">
                                            <h4
                                                class="text-sm font-black text-white uppercase tracking-tight group-hover:text-blue-300 transition-colors">
                                                Download via Google Drive
                                            </h4>
                                        </div>
                                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                            Kecepatan tinggi (selesai dalam hitungan detik)
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:translate-x-1 transition-transform shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </div>
                            </div>
                        </a>

                        <!-- Option 2: Direct Server Web -->
                        <button type="button" wire:click="triggerDirectDownload"
                            class="w-full group p-4.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 transition-all duration-300 text-left relative overflow-hidden cursor-pointer">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3.5">
                                    <div
                                        class="w-12 h-12 rounded-full bg-white/5 text-slate-300 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-6 h-6 text-slate-400 group-hover:text-white transition-colors"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-white uppercase tracking-tight">
                                                Download Langsung (Server Web)
                                            </h4>
                                        </div>
                                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                            Unduh langsung dari server lokal TRC Pekanbaru
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-white group-hover:translate-x-1 transition-transform shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Footer note -->
                    <div class="pt-2 text-center">
                        <button type="button" wire:click="closeOptionModal"
                            class="text-xs text-slate-500 hover:text-slate-300 font-bold uppercase tracking-wider transition-colors cursor-pointer">
                            ← Kembali ke Input PIN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.addEventListener('trigger-download', (event) => {
            const link = document.createElement('a');
            link.href = event.detail.url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
</div>
