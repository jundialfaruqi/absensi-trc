<div data-theme="winter" wire:init="load" x-data="{ showPassword: false }"
    class="min-h-screen flex items-center justify-center bg-[#f0f4f8] p-4 md:p-8 font-sans">

    @if ($ready)
        <div
            class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-150">
            <!-- Left Side: Illustration & Branding (Hidden on mobile) -->
            <div
                class="hidden md:flex md:w-1/2 bg-linear-to-br from-info/10 to-primary/5 p-12 flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center gap-0 mb-8">
                        <img src="{{ asset('assets/logo/logo-pemko.webp') }}" alt="Logo Pemko"
                            class="h-10 w-10 object-contain opacity-80">
                        <div class="flex flex-col">
                            <span
                                class="text-2xl font-black tracking-widest uppercase text-base-content/80 leading-none">DISKOMINFO</span>
                            <span class="text-[10px] font-bold text-base-content/60 uppercase tracking-tighter">Dinas
                                Komunikasi, Informatika & Statistik</span>
                        </div>
                    </div>

                    <h1 class="text-4xl font-bold text-base-content leading-tight">
                        Digitalisasi Tata Kelola <span class="text-primary italic">Absensi Pegawai</span> secara online!
                    </h1>
                </div>

                <!-- Abstract Illustration Placeholder -->
                <div class="relative flex-1 flex items-center justify-center py-10">
                    <div class="absolute w-64 h-64 bg-primary/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 w-full max-w-sm">
                        <!-- Mimic the image's 3D vibe with layered shapes -->
                        <div
                            class="bg-white p-6 rounded-2xl shadow-xl border border-base-200 transform -rotate-3 translate-x-4">
                            <div class="h-4 w-2/3 bg-base-200 rounded-full mb-4"></div>
                            <div class="h-4 w-1/2 bg-base-200 rounded-full mb-8"></div>
                            <div class="flex justify-end">
                                <div
                                    class="px-4 py-2 bg-success/20 text-success rounded-lg text-xs font-bold uppercase tracking-widest">
                                    Accept</div>
                            </div>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl shadow-2xl border border-base-200 absolute inset-0 transform rotate-3 -translate-y-4">
                            <div class="flex gap-4 items-center mb-6">
                                <div class="w-12 h-12 bg-info/20 rounded-full"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 w-full bg-base-200 rounded-full"></div>
                                    <div class="h-3 w-2/3 bg-base-200 rounded-full"></div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="h-2 w-full bg-base-100 rounded-full"></div>
                                <div class="h-2 w-full bg-base-100 rounded-full"></div>
                                <div class="h-2 w-3/4 bg-base-100 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10">
                    <p class="text-sm font-medium opacity-60">© {{ date('Y') }} DISKOMINFO Kota Pekanbaru. All
                        rights
                        reserved.</p>
                </div>

                <!-- Decorative Circles -->
                <div class="absolute -bottom-12.5 -left-12.5 w-48 h-48 bg-info/10 rounded-full"></div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="w-full md:w-1/2 p-8 md:p-16 flex flex-col justify-center bg-white">
                <div class="max-w-md mx-auto w-full">
                    <!-- Mobile Logo (Visible only on mobile) -->
                    <div class="md:hidden flex flex-col items-center mb-8 text-center">
                        <img src="{{ asset('assets/logo/logo-pemko.webp') }}" alt="Logo"
                            class="h-10 w-10 mb-2 opacity-50">
                        <h3 class="text-2xl font-black tracking-wider uppercase text-base-content/80 leading-none">
                            DISKOMINFO
                        </h3>
                        <span class="text-[10px] font-bold text-base-content/60 uppercase tracking-tighter">Dinas
                            Komunikasi, Informatika & Statistik</span>
                    </div>

                    <div class="text-center md:text-left mb-16">
                        <h2 class="text-3xl font-bold text-base-content mb-2">Hai, selamat datang kembali</h2>
                        <p class="text-base-content/60 text-sm">
                            Masuk untuk mulai menggunakan layanan
                        </p>
                    </div>

                    <form class="space-y-5" wire:submit.prevent="authenticate">
                        @if ($errors->has('loginError'))
                            <div class="alert alert-error rounded-xl py-3 text-xs font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5"
                                    fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $errors->first('loginError') }}</span>
                            </div>
                        @endif

                        <div class="form-control">
                            <label class="label mb-1">
                                <span
                                    class="label-text font-bold text-xs uppercase tracking-wider flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-mail text-primary">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" />
                                        <path d="M3 7l9 6l9 -6" />
                                    </svg>
                                    Alamat Email
                                </span>
                            </label>
                            <input type="text" wire:model="email" placeholder="Contoh: email@example.com"
                                class="input input-bordered bg-base-100/50 focus:border-primary focus:outline-none rounded-xl h-12 w-full @error('email') border-error @enderror"
                                autofocus />
                            @error('email')
                                <label class="label p-0 mt-1">
                                    <span class="label-text-alt text-error italic text-xs">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label mb-1">
                                <span
                                    class="label-text font-bold text-xs uppercase tracking-wider flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-lock text-primary">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6" />
                                        <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
                                        <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
                                    </svg>
                                    Kata Sandi
                                </span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" wire:model="password"
                                    placeholder="Masukkan kata sandi kamu"
                                    class="input input-bordered bg-base-100/50 focus:border-primary focus:outline-none rounded-xl h-12 w-full @error('password') border-error @enderror" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-base-content/30 hover:text-primary transition-colors">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <label class="label p-0 mt-1">
                                    <span class="label-text-alt text-error italic text-xs">{{ $message }}</span>
                                </label>
                            @enderror
                            <div class="flex items-center justify-between mt-4 mb-1">
                                <label class="label cursor-pointer justify-start gap-2 p-0">
                                    <input type="checkbox" wire:model="remember"
                                        class="checkbox checkbox-primary checkbox-xs rounded-md" />
                                    <span class="label-text font-semibold text-xs text-base-content/70">Ingat
                                        saya</span>
                                </label>
                                <a href="#" class="text-primary text-xs font-semibold hover:underline">Lupa kata
                                    sandi?</a>
                            </div>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="btn btn-primary w-full rounded-xl h-12 font-bold text-white normal-case text-base shadow-lg shadow-primary/20">
                            <span wire:loading.remove>Masuk</span>
                            <span wire:loading>Harap tunggu...</span>
                        </button>

                        <div
                            class="pt-2 pb-6 text-center text-[11px] text-base-content/40 leading-relaxed uppercase tracking-wider">
                            Dengan melanjutkan, kamu menerima <a href="#" class="text-primary font-bold">Syarat
                                Penggunaan</a> dan <a href="#" class="text-primary font-bold">Kebijakan
                                Privasi</a> kami.
                        </div>
                    </form>

                    <div class="mt-12 flex items-center justify-center gap-4 opacity-80">
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
