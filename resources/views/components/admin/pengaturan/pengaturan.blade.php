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
        {{-- Download App Card (PREVIEW) --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div class="p-0 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-black uppercase">Download APK</h2>
                    </div>
                    <button type="button" wire:click="openApkModal" class="btn btn-xs btn-neutral gap-1.5 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                        </svg>
                        Edit
                    </button>
                </div>

                <div class="flex flex-col gap-6">
                    {{-- App Info --}}
                    <div class="p-4 rounded-xl bg-info/5 border border-info/10 flex items-center gap-4">
                        <div class="text-primary p-3 bg-white rounded-lg shadow-sm border border-base-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                <line x1="12" y1="18" x2="12.01" y2="18" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xs font-black uppercase tracking-tight">Android Application</h3>
                                <span
                                    class="badge badge-primary badge-sm text-[10px] font-black h-4 px-1.5">{{ $apkVersion }}</span>
                                @if ($apkReleaseDate)
                                    <span
                                        class="text-[9px] font-bold opacity-40 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($apkReleaseDate)->format('d M Y') }}</span>
                                @endif
                            </div>
                            <p class="text-[10px] text-base-content/50 uppercase font-bold leading-relaxed">
                                {{ $apkDescription }}
                            </p>
                        </div>
                    </div>

                    {{-- What's New List --}}
                    <div class="space-y-4">
                        <h4
                            class="text-[10px] font-black uppercase tracking-widest text-base-content/40 flex items-center gap-2">
                            <span class="w-4 h-[1px] bg-base-content/20"></span>
                            Apa yang baru
                        </h4>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($apkWhatsNew as $line)
                                @if (trim($line))
                                    @php
                                        $parts = explode(':', $line, 2);
                                        $title = trim($parts[0]);
                                        $desc = isset($parts[1]) ? trim($parts[1]) : '';
                                    @endphp
                                    <div class="flex gap-3">
                                        <div
                                            class="size-6 rounded bg-success/10 text-success flex items-center justify-center shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[11px] font-bold text-base-content uppercase tracking-tight">
                                                {{ $title }}</p>
                                            @if ($desc)
                                                <p class="text-[10px] text-base-content/60 leading-normal">
                                                    {{ $desc }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if ($apkOptionalMessage)
                        <div class="p-3 rounded-lg bg-warning/5 border border-warning/10 italic">
                            <p class="text-[10px] text-warning-content/70 leading-relaxed font-medium">
                                "{{ $apkOptionalMessage }}"
                            </p>
                        </div>
                    @endif
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

        {{-- Riwayat Rilis APK Card --}}
        <div class="card bg-base-100 border border-base-200 overflow-hidden">
            <div class="card-body p-0">
                <div class="p-6 pb-0 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-0 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-black uppercase">Riwayat Versi APK</h2>
                    </div>
                    <span
                        class="badge badge-neutral badge-sm text-[10px] font-black uppercase">{{ $apkReleases->total() }}
                        Rilis</span>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="table table-xs w-full">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="text-[9px] font-black uppercase py-3">Versi</th>
                                <th class="text-[9px] font-black uppercase py-3">Tanggal</th>
                                <th class="text-[9px] font-black uppercase py-3">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            @forelse($apkReleases as $release)
                                <tr class="hover:bg-base-50/50 transition-colors group">
                                    <td class="py-3">
                                        <span
                                            class="badge badge-primary badge-sm text-[9px] font-black px-1.5 h-4">{{ $release->version }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="text-[10px] font-bold text-base-content/60 uppercase">{{ $release->release_date->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-3">
                                        <p
                                            class="text-[10px] leading-relaxed line-clamp-1 group-hover:line-clamp-none transition-all duration-300">
                                            {{ $release->description }}
                                        </p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-10 opacity-30">
                                        <p class="text-[10px] font-black uppercase">Belum ada riwayat rilis</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($apkReleases->hasPages())
                    <div class="p-4 border-t border-base-200 bg-base-50/30">
                        {{ $apkReleases->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Modal: Update APK Info ────────────────────────────────────────── --}}
    <dialog id="apk-modal" class="modal backdrop-blur-xs modal-bottom sm:modal-middle" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'apk-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'apk-modal' && $el.close()">
        <div class="modal-box shadow p-0 max-h-[90vh] max-w-2xl overflow-y-auto relative">
            <div class="p-6 border-b border-base-200 bg-base-200 flex justify-between items-center sticky top-0 z-50">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-primary text-primary-content flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-sm uppercase tracking-tight">Update Informasi APK</h3>
                        <p class="text-[10px] text-base-content/50 uppercase font-bold">Sesuaikan detail rilis aplikasi
                            mobile</p>
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('apk-modal').close()">✕</button>
            </div>

            <form wire:submit="saveApkSettings">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Versi --}}
                        <div class="form-control w-full">
                            <label class="label py-1">
                                <span class="label-text text-[10px] font-black uppercase text-base-content">Versi
                                    Aplikasi <span class="text-error">*</span></span>
                            </label>
                            <input type="text" wire:model="apkVersion"
                                class="input input-bordered focus:input-primary font-bold placeholder:text-base-content/40 w-full transition-all"
                                placeholder="Cth: v1.2.0">
                        </div>

                        {{-- Tanggal Rilis --}}
                        <div class="form-control w-full">
                            <label class="label py-1">
                                <span class="label-text text-[10px] font-black uppercase text-base-content">Tanggal
                                    Rilis <span class="text-error">*</span></span>
                            </label>
                            <input type="date" wire:model="apkReleaseDate"
                                class="input input-bordered focus:input-primary font-bold w-full transition-all">
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="form-control w-full">
                        <label class="label py-1">
                            <span class="label-text text-[10px] font-black uppercase text-base-content">Deskripsi
                                Singkat</span>
                        </label>
                        <textarea wire:model="apkDescription"
                            class="textarea textarea-bordered focus:textarea-primary placeholder:text-base-content/40 w-full transition-all h-20 text-xs"
                            placeholder="Cth: Perbaikan bug dan peningkatan performa"></textarea>
                    </div>

                    {{-- Whats New --}}
                    <div class="form-control w-full">
                        <div class="flex items-center justify-between mb-2">
                            <label class="label py-0">
                                <span class="label-text text-[10px] font-black uppercase text-base-content">Poin Fitur
                                    Baru</span>
                            </label>
                            <button type="button" wire:click="addWhatsNewPoint"
                                class="btn btn-xs btn-primary gap-1 text-[9px] font-black uppercase px-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Tambah Poin
                            </button>
                        </div>

                        <div class="space-y-4 bg-base-200/50 p-4 rounded-xl border border-base-200">
                            @forelse($apkWhatsNew as $index => $point)
                                <div class="flex gap-3 group items-start">
                                    <div
                                        class="size-8 rounded-lg bg-white border border-base-200 flex items-center justify-center shrink-0 text-success shadow-sm mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="3"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <textarea wire:model="apkWhatsNew.{{ $index }}"
                                            class="textarea textarea-bordered w-full text-xs focus:textarea-primary transition-all h-20"
                                            placeholder="Cth: Keamanan: Penjelasan singkat fitur baru..."></textarea>
                                    </div>
                                    <button type="button" wire:click="removeWhatsNewPoint({{ $index }})"
                                        class="btn btn-sm btn-ghost text-error p-1 h-8 min-h-8 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" />
                                        </svg>
                                    </button>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-[10px] text-base-content/40 font-bold uppercase">Belum ada poin
                                        fitur baru</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Pesan Khusus --}}
                    <div class="form-control w-full">
                        <label class="label py-1">
                            <span class="label-text text-[10px] font-black uppercase text-base-content">Pesan Khusus
                                (Opsional)</span>
                        </label>
                        <textarea wire:model="apkOptionalMessage"
                            class="textarea textarea-bordered focus:textarea-primary placeholder:text-base-content/40 w-full transition-all h-24 text-xs"
                            placeholder="Tuliskan pesan khusus untuk ditampilkan di Dashboard..."></textarea>
                    </div>
                </div>

                <div class="modal-action mt-0 p-6 border-t border-base-200 bg-base-100 sticky bottom-0 z-50">
                    <button type="button" class="btn btn-ghost btn-sm"
                        onclick="document.getElementById('apk-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm text-white px-8" wire:click="closeApkModal">
                        <span wire:loading wire:target="saveApkSettings"
                            class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="saveApkSettings">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>
</div>
