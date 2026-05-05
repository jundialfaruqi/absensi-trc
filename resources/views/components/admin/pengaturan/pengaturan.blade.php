<div class="space-y-6">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Pengaturan Sistem</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola Pengaturan Sistem</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('pengaturan') }}">
                        <span class="text-base-content font-bold">Pengaturan Sistem</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Settings Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pendaftaran Personnel --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-0 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <line x1="19" y1="8" x2="19" y2="14" />
                            <line x1="22" y1="11" x2="16" y2="11" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black uppercase">Pendaftaran Personnel</h2>
                </div>

                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between p-4 rounded-xl bg-base-200/50 border border-base-200 group transition-all duration-300 hover:border-primary/30">
                        <div class="space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-tight opacity-70">Status
                                Registrasi Mandiri</span>
                            <p class="text-[10px] text-base-content/50 uppercase font-medium leading-relaxed max-w-md">
                                Jika dinonaktifkan, pendaftaran hanya bisa melalui admin.
                            </p>
                        </div>
                        <input type="checkbox" wire:model.live="registrationEnabled" class="toggle toggle-primary" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Web Absensi Status --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-0 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="2" y1="12" x2="22" y2="12" />
                            <path
                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black uppercase">Akses Portal Web Absensi</h2>
                </div>

                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between p-4 rounded-xl bg-base-200/50 border border-base-200 group transition-all duration-300 hover:border-secondary/30">
                        <div class="space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-tight opacity-70">Aktifkan Halaman
                                Absensi Web</span>
                            <p class="text-[10px] text-base-content/50 uppercase font-medium leading-relaxed max-w-md">
                                Matikan jika ingin menutup akses absensi melalui browser.
                            </p>
                        </div>
                        <input type="checkbox" wire:model.live="webAbsensiActive" class="toggle toggle-secondary" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Batasan Waktu Absensi --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-0 rounded-full text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black uppercase">Batasan Waktu Absensi (Global)</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Batasan Masuk --}}
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-base-200/50 border border-base-200 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="badge badge-primary badge-xs"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest">Batasan Absen
                                    Masuk</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-control w-full">
                                    <label class="label pt-0"><span
                                            class="text-[9px] font-bold uppercase opacity-50">Mulai
                                            (Menit)</span></label>
                                    <input type="number" wire:model="masukMulai"
                                        class="input input-sm input-bordered focus:input-primary font-bold text-xs" />
                                </div>
                                <div class="form-control w-full">
                                    <label class="label pt-0"><span
                                            class="text-[9px] font-bold uppercase opacity-50">Selesai
                                            (Menit)</span></label>
                                    <input type="number" wire:model="masukSelesai"
                                        class="input input-sm input-bordered focus:input-primary font-bold text-xs" />
                                </div>
                            </div>
                            <p class="text-[9px] text-base-content/40 font-medium uppercase leading-relaxed italic">
                                * Contoh: 30 menit (Mulai) artinya personel bisa absen 30 menit sebelum jadwal masuk.
                            </p>
                        </div>
                    </div>

                    {{-- Batasan Pulang --}}
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-base-200/50 border border-base-200 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="badge badge-secondary badge-xs"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest">Batasan Absen
                                    Pulang</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-control w-full">
                                    <label class="label pt-0"><span
                                            class="text-[9px] font-bold uppercase opacity-50">Mulai
                                            (Menit)</span></label>
                                    <input type="number" wire:model="pulangMulai"
                                        class="input input-sm input-bordered focus:input-secondary font-bold text-xs" />
                                </div>
                                <div class="form-control w-full">
                                    <label class="label pt-0"><span
                                            class="text-[9px] font-bold uppercase opacity-50">Selesai
                                            (Menit)</span></label>
                                    <input type="number" wire:model="pulangSelesai"
                                        class="input input-sm input-bordered focus:input-secondary font-bold text-xs" />
                                </div>
                            </div>
                            <p class="text-[9px] text-base-content/40 font-medium uppercase leading-relaxed italic">
                                * Contoh: 120 menit (Selesai) artinya batas akhir absen pulang adalah 2 jam setelah
                                jadwal pulang.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-6 pt-6 border-t border-base-200">
                    <button wire:click="saveTimeSettings" wire:loading.attr="disabled"
                        class="btn btn-primary text-white">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Keamanan & Rate Limit --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-0 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black uppercase">Keamanan & Rate Limit PIN</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="form-control w-full">
                        <label class="label pt-0"><span
                                class="text-[9px] font-bold uppercase text-base-content opacity-50">Maksimal
                                Percobaan</span></label>
                        <input type="number" wire:model.live="pinMaxAttempts"
                            class="input input-sm input-bordered focus:input-error font-bold text-xs" />
                        <label class="label"><span
                                class="text-[8px] uppercase opacity-40 font-bold text-base-content tracking-tighter">Batas
                                percobaan
                                salah per sesi</span></label>
                    </div>
                    <div class="form-control w-full">
                        <label class="label pt-0"><span
                                class="text-[9px] font-bold text-base-content uppercase opacity-50">Lock 5
                                Menit</span></label>
                        <input type="number" wire:model="pinLock5"
                            class="input input-sm input-bordered focus:input-error font-bold text-xs" />
                        <label class="label"><span
                                class="text-[8px] uppercase opacity-40 font-bold text-base-content tracking-tighter">Setelah
                                {{ $pinMaxAttempts }} kali salah</span></label>
                    </div>
                    <div class="form-control w-full">
                        <label class="label pt-0"><span
                                class="text-[9px] font-bold text-base-content uppercase opacity-50">Lock 15
                                Menit</span></label>
                        <input type="number" wire:model="pinLock10"
                            class="input input-sm input-bordered focus:input-error font-bold text-xs" />
                        <label class="label"><span
                                class="text-[8px] uppercase opacity-40 font-bold text-base-content tracking-tighter">Setelah
                                {{ $pinMaxAttempts * 2 }} kali salah</span></label>
                    </div>
                </div>

                <div class="card-actions justify-end mt-auto pt-6 border-t border-base-200">
                    <button wire:click="saveSecuritySettings" wire:loading.attr="disabled"
                        class="btn btn-primary text-white">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Download App Card --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-0 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black uppercase">Download Aplikasi (APK)</h2>
                </div>

                <div class="p-4 rounded-xl bg-info/5 border border-info/10 flex items-center gap-2">
                    <div class="text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                            <line x1="12" y1="18" x2="12.01" y2="18" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xs font-bold uppercase tracking-tight">Android Application</h3>
                        <p class="text-[10px] text-base-content/50 uppercase font-medium leading-relaxed">
                            Versi terbaru aplikasi absensi untuk perangkat Android.
                        </p>
                    </div>
                </div>

                <div class="card-actions justify-end mt-6 pt-6 border-t border-base-200">
                    <a href="{{ route('pengaturan.download-apk') }}"
                        class="btn btn-primary w-full sm:w-auto text-white" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 mr-2" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>Download APK</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
