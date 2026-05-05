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
                                class="w-full bg-slate-900/50 border-2 border-white/5 rounded-2xl px-4 py-4 md:px-6 md:py-5 text-center text-xl md:text-3xl font-black tracking-[0.5em] text-white focus:border-blue-500/50 focus:ring-0 transition-all placeholder:text-base-content/30">
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

                    <button type="submit"
                        class="w-full py-5 bg-linear-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-black rounded-2xl shadow-xl transition-all neon-glow-blue flex items-center justify-center gap-3 group/btn relative overflow-hidden"
                        wire:loading.attr="disabled">

                        <!-- Spinner Overlay -->
                        <div wire:loading wire:target="download"
                            class="absolute inset-0 flex items-center justify-center bg-blue-600/50 backdrop-blur-sm z-20">
                            <span class="loading loading-spinner loading-md"></span>
                        </div>

                        <!-- Button Content -->
                        <span class="flex items-center gap-3 relative z-10">
                            DOWNLOAD
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2.5" stroke="currentColor"
                                class="w-5 h-5 group-hover/btn:translate-y-1 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </span>
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
</div>
