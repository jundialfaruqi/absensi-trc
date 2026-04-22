<div class="min-h-screen flex items-center justify-center p-4 md:p-8">
    <div
        class="w-full max-w-5xl glass-card rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col md:flex-row min-h-[700px]">

        {{-- Left Side: Info & Aesthetic --}}
        <div
            class="md:w-5/12 bg-primary/20 p-8 md:p-12 flex flex-col justify-between border-r border-white/5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/20 blur-[100px] -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-secondary/20 blur-[100px] -ml-32 -mb-32"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-10" style="animation: floating 5s ease-in-out infinite;">
                    <img src="{{ asset('assets/logo/trc-logo.webp') }}" class="w-12 h-12 object-contain" alt="Logo">
                    <div class="h-8 w-px bg-white/20"></div>
                    <div class="flex flex-col leading-none">
                        <span class="text-lg font-black uppercase tracking-tighter italic">Personnel Portal</span>
                        <span class="text-[8px] font-bold text-blue-400 tracking-[0.2em] uppercase">Emergency 112</span>
                    </div>
                </div>

                <h2 class="text-4xl md:text-5xl font-black leading-tight italic uppercase tracking-tighter mb-6">
                    Mulai <span
                        class="text-transparent bg-clip-text bg-linear-to-r from-blue-400 to-cyan-300">Tugas</span> Anda
                    Disini.
                </h2>
                <p class="text-slate-400 font-medium leading-relaxed mb-8">
                    Daftarkan akun personel Anda untuk mengakses sistem absensi digital dan manajemen penugasan TRC
                    Pekanbaru.
                </p>
            </div>

            <div class="relative z-10 space-y-6">
                <div class="flex items-center gap-4 group">
                    <div
                        class="w-10 h-10 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 group-hover:border-blue-400/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold uppercase tracking-wide italic text-slate-300">Validasi Data
                        Akurat</span>
                </div>
                <div class="flex items-center gap-4 group">
                    <div
                        class="w-10 h-10 rounded-full bg-cyan-500/10 flex items-center justify-center text-cyan-400 border border-cyan-500/20 group-hover:border-cyan-400/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold uppercase tracking-wide italic text-slate-300">Enkripsi PIN
                        4-Digit</span>
                </div>
                <div class="mt-8 text-[10px] font-black text-slate-500 uppercase tracking-widest leading-loose">
                    &copy; {{ date('Y') }} DISKOMINFOTIKSAN Pekanbaru.<br>Developed by DISKOMINFOTIKSAN Pekanbaru
                </div>
            </div>
        </div>

        {{-- Right Side: Form or Disabled Message --}}
        <div class="md:w-7/12 p-8 md:p-12 overflow-y-auto max-h-[800px] no-scrollbar bg-slate-900/40">
            @if ($registrationEnabled)
                <div class="mb-10 flex justify-between items-end">
                    <div>
                        <h3 class="text-2xl font-black uppercase italic tracking-tighter text-white">Registrasi Akun
                        </h3>
                        <p class="text-[10px] font-bold text-blue-400 uppercase tracking-[0.2em] mt-1">Lengkapi data
                            diri Anda</p>
                    </div>
                    <a href="{{ route('personnel.login') }}" wire:navigate
                        class="text-xs font-black uppercase tracking-widest text-blue-400 hover:text-cyan-300 transition-colors underline decoration-blue-500/30 underline-offset-4">Masuk</a>
                </div>

                <form wire:submit="register" class="space-y-6">
                    @if ($errors->has('registration'))
                        <div
                            class="alert alert-error bg-red-500/10 border border-red-500/20 text-red-500 rounded-2xl py-3 px-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span
                                class="text-xs font-bold uppercase tracking-tight">{{ $errors->first('registration') }}</span>
                        </div>
                    @endif

                    {{-- Photo Section --}}
                    <div class="grid grid-cols-1 gap-6 py-4 border-b border-white/5">
                        {{-- Example Side --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <div class="w-1 h-3 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Contoh Pas
                                    Foto</span>
                            </div>
                            <div
                                class="flex items-center gap-5 p-4 rounded-3xl bg-blue-500/5 border border-blue-500/10 min-h-[140px]">
                                <div
                                    class="w-20 h-28 rounded-xl overflow-hidden shrink-0 shadow-2xl border border-white/10">
                                    <img src="{{ asset('assets/images/contoh-foto-profil.png') }}"
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="space-y-2">
                                    <p class="text-[11px] font-black uppercase italic text-blue-400">Ketentuan:</p>
                                    <ul class="space-y-1.5">
                                        <li
                                            class="flex items-center gap-2 text-[9px] font-bold text-slate-400 uppercase tracking-wide">
                                            <div class="w-1 h-1 rounded-full bg-blue-500/50 shrink-0"></div>
                                            Foto Terbaru
                                        </li>
                                        <li
                                            class="flex items-center gap-2 text-[9px] font-bold text-slate-400 uppercase tracking-wide">
                                            <div class="w-1 h-1 rounded-full bg-blue-500/50 shrink-0"></div>
                                            Background Polos
                                        </li>
                                        <li
                                            class="flex items-center gap-2 text-[9px] font-bold text-slate-400 uppercase tracking-wide">
                                            <div class="w-1 h-1 rounded-full bg-blue-500/50 shrink-0"></div>
                                            JPG / PNG / JPEG
                                        </li>
                                        <li
                                            class="flex items-center gap-2 text-[9px] font-bold text-slate-400 uppercase tracking-wide">
                                            <div class="w-1 h-1 rounded-full bg-blue-500/50 shrink-0"></div>
                                            Maksimal 2 MB
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Upload Side --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <div class="w-1 h-3 bg-cyan-500 rounded-full shadow-[0_0_10px_rgba(6,182,212,0.5)]">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Upload
                                    Foto Anda</span>
                            </div>
                            <div
                                class="flex flex-row items-center justify-start gap-5 p-4 rounded-3xl bg-cyan-500/5 border border-cyan-500/10 border-dashed hover:border-cyan-500/30 transition-all group min-h-[140px] relative">
                                <div class="relative shrink-0">
                                    <div
                                        class="w-20 h-28 rounded-xl overflow-hidden border border-cyan-500/20 shadow-2xl relative transition-transform duration-500 group-hover:scale-105 bg-slate-800">
                                        @if ($foto && !$errors->has('foto'))
                                            <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-10 h-10">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <label for="foto-input"
                                        class="absolute -bottom-2 -right-2 btn btn-circle btn-sm w-8 h-8 min-h-0 btn-info border-2 border-slate-900 shadow-xl cursor-pointer hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </label>
                                    <input type="file" id="foto-input" class="hidden"
                                        accept="image/png, image/jpeg, image/jpg" onchange="handleImageUpload(this)">
                                </div>
                                <div class="flex flex-col justify-center">
                                    @if ($foto)
                                        <span
                                            class="text-[10px] font-black uppercase text-info tracking-widest italic flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                                                class="w-3 h-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            Siap Unggah
                                        </span>
                                    @else
                                        <span
                                            class="text-[10px] font-black uppercase text-slate-500 tracking-[0.15em] group-hover:text-cyan-400 transition-colors italic">Pilih
                                            Foto</span>
                                        <p class="text-[8px] text-slate-600 uppercase mt-1">Ketuk ikon tambah</p>
                                    @endif
                                </div>
                            </div>
                            @error('foto')
                                <span
                                    class="text-[9px] font-bold text-red-500 uppercase tracking-tight block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Nama
                                    Lengkap</span></label>
                            <input type="text" wire:model="name"
                                class="input input-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white"
                                placeholder="Contoh: Jundi Al Faruqi">
                            @error('name')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Email
                                    Aktif</span></label>
                            <input type="email" wire:model="email"
                                class="input input-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white"
                                placeholder="jundi@mail.com">
                            @error('email')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nomor HP --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Nomor
                                    HP</span></label>
                            <input type="text" wire:model="nomor_hp"
                                class="input input-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white"
                                placeholder="08xxxxxx">
                            @error('nomor_hp')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>

                        {{-- OPD --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Pilih
                                    OPD</span></label>
                            <select wire:model="opd_id"
                                class="select select-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white">
                                <option value="">Pilih Organisasi</option>
                                @foreach ($opds as $opd)
                                    <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Penugasan --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Pilih
                                    Penugasan</span></label>
                            <select wire:model="penugasan_id"
                                class="select select-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white">
                                <option value="">Pilih Jenis Tugas</option>
                                @foreach ($penugasans as $penugasan)
                                    <option value="{{ $penugasan->id }}">{{ $penugasan->name }}</option>
                                @endforeach
                            </select>
                            @error('penugasan_id')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>

                        {{-- PIN --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">PIN
                                    (4 Digit)</span></label>
                            <input type="password" maxlength="4" wire:model="pin"
                                class="input input-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-center tracking-[1em] text-white"
                                placeholder="0000">
                            @error('pin')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Password --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Password
                                    Akun</span></label>
                            <input type="password" wire:model="password"
                                class="input input-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white"
                                placeholder="••••••••">
                            @error('password')
                                <label class="label"><span
                                        class="label-text-alt text-red-500 font-bold uppercase text-[9px]">{{ $message }}</span></label>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="form-control w-full">
                            <label class="label pb-1"><span
                                    class="label-text text-[10px] font-black uppercase tracking-widest text-slate-500 italic">Konfirmasi
                                    Password</span></label>
                            <input type="password" wire:model="password_confirmation"
                                class="input input-bordered bg-white/5 border-white/10 rounded-2xl h-12 focus:border-blue-500 transition-all text-sm font-medium text-white"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" wire:loading.attr="disabled"
                            class="btn bg-linear-to-br from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 border-none btn-block rounded-2xl h-14 font-black uppercase tracking-widest italic group overflow-hidden relative shadow-[0_10px_20px_rgba(59,130,246,0.3)] text-white transition-all">
                            <span wire:loading.remove class="relative z-10">Daftarkan Sekarang</span>
                            <div wire:loading class="flex items-center gap-2 relative z-10">
                                <span class="loading loading-spinner loading-sm"></span>
                                <span>Memproses...</span>
                            </div>
                            <div
                                class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                            </div>
                        </button>
                    </div>
                </form>
            @else
                <div class="h-full flex flex-col items-center justify-center text-center py-20 px-6">
                    <div
                        class="w-24 h-24 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500 border border-amber-500/20 mb-8 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>

                    <h3 class="text-3xl font-black uppercase italic tracking-tighter text-white mb-4">Pendaftaran
                        Ditutup</h3>
                    <p class="text-slate-400 font-medium leading-relaxed max-w-md mb-10">
                        Maaf, halaman registrasi mandiri personel saat ini sedang tidak aktif. Silakan hubungi
                        administrator sistem untuk informasi lebih lanjut mengenai pendaftaran akun.
                    </p>

                    <a href="/" wire:navigate
                        class="btn btn-outline border-white/10 hover:bg-white/5 text-white rounded-2xl h-14 px-10 font-black uppercase tracking-widest italic flex items-center gap-3 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Image Compression Script --}}
    <script>
        function handleImageUpload(input) {
            const file = input.files[0];
            if (!file) return;

            // Robust client-side validation
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const allowedExtensions = ['jpg', 'jpeg', 'png'];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedTypes.includes(file.type) || !allowedExtensions.includes(extension)) {
                alert('File tidak valid! Hanya format JPG, JPEG, dan PNG yang diperbolehkan.');
                input.value = '';
                return;
            }

            const maxSize = 2000 * 1024; // 2000KB

            if (file.size > maxSize) {
                console.log('File too large, resizing...');
                resizeImage(file, 1200, 1200, 0.85, (resizedFile) => {
                    console.log('Resized to:', (resizedFile.size / 1024).toFixed(2), 'KB');
                    // Upload the resized file using Livewire
                    @this.upload('foto', resizedFile);
                });
            } else {
                @this.upload('foto', file);
            }
        }

        function resizeImage(file, maxWidth, maxHeight, quality, callback) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width *= maxHeight / height;
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        const resizedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now()
                        });
                        callback(resizedFile);
                    }, file.type, quality);
                };
            };
        }
    </script>

    {{-- Modals Section --}}
    <div x-data="{
        showSuccess: false,
        showError: false,
        errorMessage: '',
        countdown: 5,
        startCountdown() {
            let timer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = '{{ route('personnel.login') }}';
                }
            }, 1000);
        }
    }" x-on:registration-success.window="showSuccess = true; startCountdown()"
        x-on:registration-failed.window="showError = true; errorMessage = $event.detail.message"
        class="relative z-[100]">

        {{-- Success Modal --}}
        <div x-show="showSuccess" x-cloak
            class="fixed inset-0 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-opacity duration-300">
            <div x-show="showSuccess" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="w-full max-w-md bg-slate-900 border border-white/10 rounded-[2.5rem] p-10 text-center shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-success/20 blur-[60px] -mr-16 -mt-16"></div>

                <div
                    class="w-20 h-20 bg-success/20 text-success rounded-full flex items-center justify-center mx-auto mb-6 border border-success/30 relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>

                <h3 class="text-2xl font-black italic uppercase tracking-tighter mb-2 relative z-10">Pendaftaran
                    Berhasil!</h3>
                <p class="text-slate-400 text-sm font-medium leading-relaxed mb-8 relative z-10">
                    Akun Anda telah berhasil dibuat. Anda akan dialihkan ke halaman login secara otomatis.
                </p>

                <div class="space-y-4 relative z-10">
                    <a href="{{ route('personnel.login') }}" wire:navigate
                        class="btn btn-success btn-block rounded-2xl h-12 font-black uppercase tracking-widest italic shadow-lg shadow-success/20">
                        Ke Halaman Login (<span x-text="countdown"></span>)
                    </a>
                </div>
            </div>
        </div>

        {{-- Error Modal --}}
        <div x-show="showError" x-cloak
            class="fixed inset-0 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-opacity duration-300">
            <div x-show="showError" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="w-full max-w-md bg-slate-900 border border-white/10 rounded-[2.5rem] p-10 text-center shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-error/20 blur-[60px] -mr-16 -mt-16"></div>

                <div
                    class="w-20 h-20 bg-error/20 text-error rounded-full flex items-center justify-center mx-auto mb-6 border border-error/30 relative z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </div>

                <h3 class="text-2xl font-black italic uppercase tracking-tighter mb-2 relative z-10">Pendaftaran Gagal
                </h3>
                <p class="text-slate-400 text-sm font-medium leading-relaxed mb-8 relative z-10"
                    x-text="errorMessage"></p>

                <div class="space-y-4 relative z-10">
                    <button @click="showError = false"
                        class="btn btn-error btn-block rounded-2xl h-12 font-black uppercase tracking-widest italic shadow-lg shadow-error/20">
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Transition for selects and inputs */
        input:focus,
        select:focus {
            box-shadow: 0 0 0 2px rgba(var(--p), 0.2);
        }

        option {
            background-color: #1e293b;
            color: white;
        }
    </style>
</div>
