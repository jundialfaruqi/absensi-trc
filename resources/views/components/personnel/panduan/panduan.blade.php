<div>
    <x-slot name="title">Panduan Penggunaan - TRC Pekanbaru Aman 112</x-slot>

    <div class="max-w-7xl mx-auto px-6 py-12 lg:py-20 relative z-10">
        {{-- Header --}}
        <div class="mb-16">
            <div class="flex items-center gap-3 mb-6" style="animation: floating 5s ease-in-out infinite;">
                <img src="{{ asset('assets/logo/trc-logo.webp') }}" class="w-16 h-16 object-contain" alt="Logo">
                <div class="h-10 w-px bg-white/20"></div>
                <div class="flex flex-col leading-none">
                    <span class="text-2xl font-black uppercase tracking-tighter italic">Pusat Panduan</span>
                    <span class="text-[10px] font-bold text-blue-400 tracking-[0.2em] uppercase">Sistem Absensi
                        TRC</span>
                </div>
            </div>
            <h1 class="text-4xl lg:text-6xl font-black text-white leading-none uppercase tracking-tighter mb-4">
                PELAJARI <span
                    class="text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-cyan-300">SISTEM</span> KAMI.
            </h1>
            <p class="text-slate-400 text-lg font-medium max-w-2xl">
                Ikuti langkah-langkah di bawah ini untuk memahami cara penggunaan aplikasi absensi TRC Pekanbaru mulai
                dari registrasi hingga pengajuan cuti.
            </p>
        </div>

        <div class="grid lg:grid-cols-4 gap-10 items-start">
            {{-- Navigation Sidebar --}}
            <div class="lg:sticky lg:top-24 space-y-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6 italic">Navigasi Panduan
                </p>
                <a href="#register"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-blue-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">1
                    </div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-blue-400 transition-colors">Registrasi
                        Akun</span>
                </a>
                <a href="#absensi"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-emerald-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        2</div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-emerald-400 transition-colors">Absensi
                        Harian</span>
                </a>
                <a href="#login"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-purple-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400">3
                    </div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-purple-400 transition-colors">Login
                        Dashboard</span>
                </a>
                <a href="#profil"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-amber-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">4
                    </div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-amber-400 transition-colors">Update
                        Profil</span>
                </a>
                <a href="#jadwal"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-cyan-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center text-cyan-400">5
                    </div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-cyan-400 transition-colors">Lihat
                        Jadwal</span>
                </a>
                <a href="#riwayat"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-blue-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">6
                    </div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-blue-400 transition-colors">Riwayat
                        Absensi</span>
                </a>
                <a href="#cuti"
                    class="flex items-center gap-3 p-4 rounded-2xl glass-panel hover:border-red-500/30 transition-all group">
                    <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-400">7</div>
                    <span
                        class="text-xs font-bold uppercase tracking-wide group-hover:text-red-400 transition-colors">Ajukan
                        Cuti/Izin</span>
                </a>
            </div>

            {{-- Content Area --}}
            <div class="lg:col-span-3 space-y-24 pb-20">
                {{-- 1. Register --}}
                <section id="register" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-blue-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">01. Registrasi Akun
                            Personnel</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <p class="text-slate-300 leading-relaxed">Pendaftaran akun hanya dilakukan satu kali oleh
                            personel baru untuk mendapatkan akses ke sistem.</p>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <h4 class="text-xs font-black uppercase tracking-widest text-blue-400">Langkah-langkah:
                                </h4>
                                <ul class="space-y-3">
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-blue-400 font-bold">1.</span>
                                        Kunjungi halaman <code
                                            class="bg-white/5 px-2 rounded text-blue-300">/personnel/register</code>.
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-blue-400 font-bold">2.</span>
                                        Isi data diri lengkap: Nama, Email, No HP, OPD, dan Jenis Penugasan.
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-blue-400 font-bold">3.</span>
                                        Buat password akun dan **PIN 4 Digit** (PIN digunakan untuk setiap kali
                                        absensi).
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-blue-400 font-bold">4.</span>
                                        Upload pas foto terbaru sesuai ketentuan.
                                    </li>
                                </ul>
                            </div>
                            <div class="bg-blue-500/5 rounded-3xl p-6 border border-blue-500/10 space-y-4">
                                <h4 class="text-xs font-black uppercase tracking-widest text-red-500 italic">Aturan
                                    Penting:</h4>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-[11px] font-bold text-slate-300 uppercase">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 shrink-0"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Foto harus format JPG/PNG & Maks 2MB.
                                    </li>
                                    <li class="flex items-start gap-2 text-[11px] font-bold text-slate-300 uppercase">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 shrink-0"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Gunakan latar belakang polos.
                                    </li>
                                    <li class="flex items-start gap-2 text-[11px] font-bold text-slate-300 uppercase">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 shrink-0"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        PIN tidak boleh dibagikan ke orang lain.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 2. Absensi --}}
                <section id="absensi" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-emerald-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">02. Absensi Harian
                            (Kiosk)</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <p class="text-slate-300 leading-relaxed">Absensi dilakukan setiap hari kerja/shift melalui
                            halaman <a href="{{ route('absensi-web') }}"
                                class="text-emerald-400 hover:text-emerald-300 underline">Absensi</a> yang telah
                            disediakan</p>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <h4 class="text-xs font-black uppercase tracking-widest text-emerald-400">
                                    Langkah-langkah:</h4>
                                <ul class="space-y-3">
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-emerald-400 font-bold">1.</span>
                                        Pilih nama Anda pada daftar personel yang muncul.
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-emerald-400 font-bold">2.</span>
                                        Masukkan **PIN 4 Digit** yang telah dibuat saat registrasi.
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-emerald-400 font-bold">3.</span>
                                        Lakukan pengambilan foto (Face Recognition/Verification).
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-emerald-400 font-bold">4.</span>
                                        Klik "Absen Sekarang" dan tunggu hingga notifikasi sukses muncul.
                                    </li>
                                </ul>
                            </div>
                            <div class="bg-emerald-500/5 rounded-3xl p-6 border border-emerald-500/10 space-y-4">
                                <h4 class="text-xs font-black uppercase tracking-widest text-amber-500 italic">Aturan
                                    Absensi:</h4>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-[11px] font-bold text-slate-300 uppercase">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1 shrink-0"></div>
                                        Absen Masuk dilakukan sesuai jam shift.
                                    </li>
                                    <li class="flex items-start gap-2 text-[11px] font-bold text-slate-300 uppercase">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1 shrink-0"></div>
                                        Absen Pulang wajib dilakukan setelah tugas selesai.
                                    </li>
                                    <li class="flex items-start gap-2 text-[11px] font-bold text-slate-300 uppercase">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1 shrink-0"></div>
                                        Wajah harus terlihat jelas saat difoto.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 3. Login Dashboard --}}
                <section id="login" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-purple-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">03. Akses Zona
                            Personel</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <div class="flex items-start gap-6">
                            <div
                                class="hidden md:flex w-32 h-32 rounded-3xl bg-purple-500/10 border border-purple-500/20 items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1" stroke="currentColor" class="w-16 h-16 text-purple-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <div class="space-y-4 flex-1">
                                <p class="text-slate-300 leading-relaxed">Personel dapat melihat data kehadiran dan
                                    profil melalui Dashboard Personel.</p>
                                <ul class="space-y-3">
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-purple-400 font-bold">1.</span>
                                        Akses <code
                                            class="bg-white/5 px-2 rounded text-purple-300">/personnel/login</code>.
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-purple-400 font-bold">2.</span>
                                        Masukkan Email dan Password yang didaftarkan.
                                    </li>
                                    <li class="flex gap-3 text-sm text-slate-400">
                                        <span class="text-purple-400 font-bold">3.</span>
                                        Klik "Masuk" untuk melihat ringkasan tugas dan status Anda hari ini.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 4. Update Profil --}}
                <section id="profil" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-amber-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">04. Manajemen Data
                            Diri</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <div class="space-y-4">
                            <p class="text-slate-300 leading-relaxed">Untuk memperbarui informasi seperti Email,
                                Password
                                & PIN, ikuti langkah berikut:</p>
                            <div class="grid md:grid-cols-2 gap-8">
                                <div class="bg-white/5 p-6 rounded-3xl border border-white/10">
                                    <span
                                        class="text-[10px] font-black text-amber-500 uppercase tracking-widest block mb-4">Langkah:</span>
                                    <p class="text-sm text-slate-400">Pilih menu <b>Ubah Data Akun</b> di Menu Utama
                                        Navigasi. Anda dapat mengubah data seperti Email Password & PIN</p>
                                </div>
                                <div class="bg-white/5 p-6 rounded-3xl border border-white/10">
                                    <span
                                        class="text-[10px] font-black text-amber-500 uppercase tracking-widest block mb-4">Catatan
                                        PIN:</span>
                                    <p class="text-sm text-slate-400 italic">Jika anda lupa PIN, PIN dapat diubah
                                        secara mandiri melalui menu Profil</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 5. Lihat Jadwal --}}
                <section id="jadwal" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-cyan-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">05. Pemantauan
                            Jadwal Shift</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <p class="text-slate-300 leading-relaxed">Jadwal penugasan Anda dapat dilihat secara real-time
                            pada menu Jadwal.</p>
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="md:w-1/2 p-6 rounded-3xl bg-cyan-500/5 border border-cyan-500/10">
                                <h4 class="text-xs font-black uppercase tracking-widest text-cyan-400 mb-3">Tampilan
                                    Kalender:</h4>
                                <p class="text-xs text-slate-400 leading-relaxed">Jadwal ditampilkan dalam bentuk
                                    kalender bulanan. Shift Anda akan ditandai dengan label nama shift
                                    (Pagi/Sore/Malam/Libur).</p>
                            </div>
                            <div class="md:w-1/2 p-6 rounded-3xl bg-cyan-500/5 border border-cyan-500/10">
                                <h4 class="text-xs font-black uppercase tracking-widest text-cyan-400 mb-3">Kepatuhan:
                                </h4>
                                <p class="text-xs text-slate-400 leading-relaxed">Wajib memantau jadwal secara berkala.
                                    Perubahan jadwal mendadak akan diinformasikan oleh Admin OPD masing-masing.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 6. Riwayat Absensi --}}
                <section id="riwayat" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-blue-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">06. Verifikasi
                            Riwayat Absensi</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <p class="text-slate-300 leading-relaxed">Anda dapat memeriksa rekaman waktu masuk dan pulang
                            untuk memastikan tidak ada kekeliruan data.</p>
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                                <p class="text-sm text-slate-400">Data mencakup: Tanggal, Waktu Masuk, Waktu Pulang,
                                    dan Status (Hadir/Terlambat).</p>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                                <p class="text-sm text-slate-400">Klik pada baris riwayat untuk melihat detail foto
                                    absensi yang Anda lakukan.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 7. Ajukan Cuti --}}
                <section id="cuti" class="scroll-mt-24 space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-1 w-12 bg-red-500 rounded-full"></div>
                        <h2 class="text-3xl font-black uppercase italic tracking-tighter text-white">07. Pengajuan Izin
                            & Cuti</h2>
                    </div>
                    <div class="glass-panel p-8 rounded-4xl space-y-6">
                        <p class="text-slate-300 leading-relaxed">Izin tidak hadir wajib diajukan melalui sistem paling
                            lambat 1 hari sebelumnya (kecuali keadaan darurat/sakit).</p>
                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h4 class="text-xs font-black uppercase tracking-widest text-red-400">Cara Mengajukan:
                                </h4>
                                <ul class="space-y-2">
                                    <li class="text-sm text-slate-400">1. Pilih menu **Ajukan Cuti** di Dashboard.</li>
                                    <li class="text-sm text-slate-400">2. Tentukan Tanggal Mulai dan Selesai.</li>
                                    <li class="text-sm text-slate-400">3. Pilih Jenis (Sakit, Cuti, Izin Penting).</li>
                                    <li class="text-sm text-slate-400">4. Upload Lampiran (Surat Dokter/Surat
                                        Pernyataan).</li>
                                    <li class="text-sm text-slate-400">5. Kirim dan tunggu persetujuan Admin.</li>
                                </ul>
                            </div>
                            <div class="bg-red-500/5 rounded-3xl p-6 border border-red-500/10">
                                <span
                                    class="text-[10px] font-black text-red-500 uppercase tracking-widest block mb-4">Ketentuan
                                    Persetujuan:</span>
                                <p class="text-xs text-slate-400 leading-relaxed italic">
                                    Status pengajuan akan berubah dari **Menunggu** menjadi **Disetujui** atau
                                    **Ditolak**. Selama status belum disetujui, personel dianggap wajib hadir sesuai
                                    jadwal.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="relative z-10 border-t border-white/5 py-12 px-6 bg-slate-900/20">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex flex-col items-center md:items-start space-y-2">
                <span class="font-black text-white tracking-tighter uppercase text-lg italic">TRC Pekanbaru Aman
                    112</span>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sistem Absensi Digital
                    Terintegrasi</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('personnel.login') }}"
                    class="px-6 py-2 rounded-xl bg-blue-600 text-white font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition-colors shadow-lg shadow-blue-600/20">Mulai
                    Gunakan Sistem</a>
            </div>
        </div>
    </footer>
</div>
