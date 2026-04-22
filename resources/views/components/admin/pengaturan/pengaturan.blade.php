<div class="space-y-6">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Pengaturan Sistem</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola Pengaturan Sistem</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-black uppercase italic">Pendaftaran Personnel</h2>
                </div>

                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between p-4 rounded-xl bg-base-200/50 border border-base-200 group transition-all duration-300 hover:border-primary/30">
                        <div class="space-y-1">
                            <span class="text-sm font-bold block uppercase tracking-tight">Aktifkan Halaman
                                Register</span>
                            <p class="text-[10px] text-base-content/50 uppercase font-medium leading-relaxed max-w-md">
                                Jika dinonaktifkan, personel tidak dapat mendaftar sendiri melalui halaman portal.
                            </p>
                        </div>
                        <input type="checkbox" wire:model.live="registrationEnabled" class="toggle toggle-primary" />
                    </div>

                    <div class="alert alert-info shadow-sm border border-info/20 py-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="stroke-current shrink-0 w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-[10px] font-bold uppercase tracking-wide">
                            Link Registrasi: <span
                                class="select-all underline decoration-dotted">{{ route('personnel.register') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
