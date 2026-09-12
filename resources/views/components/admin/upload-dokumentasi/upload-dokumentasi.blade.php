<div wire:init="load">
    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-base-200">
        <div>
            <h3 class="font-black uppercase text-xl">
                Upload Dokumentasi
            </h3>
            <p class="text-sm text-base-content/60 mt-1">
                Lengkapi tanggal, shift, jumlah porsi, dan unggah foto dokumentasi konsumsi harian.
            </p>
        </div>
    </div>

    @if ($readyToLoad)
        <form wire:submit="save" x-data="uploadDokumentasiUploader()">
            <div class="max-w-4xl">
                {{-- Card Informasi & Upload Dokumentasi --}}
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-lg">Dokumentasi</h4>
                            @if ($shift)
                                <span
                                    class="border-b-2 border-primary text-xs font-bold text-primary pb-0.5 uppercase tracking-wide">
                                    Shift {{ $shift === 'siang' ? 'Siang' : 'Malam' }} Aktif
                                </span>
                            @else
                                <span
                                    class="border-b border-base-content/30 text-xs font-medium text-base-content/60 pb-0.5">
                                    Belum Memilih Shift
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6 md:gap-y-4">

                            {{-- Tanggal --}}
                            <div class="form-control w-full">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">
                                        Tanggal Dokumentasi <span class="text-error">*</span>
                                    </span>
                                </label>
                                <input type="date" wire:model.live="tanggal"
                                    class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('tanggal') input-error @enderror">
                                @error('tanggal')
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Shift --}}
                            <div class="form-control w-full">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">
                                        Pilih Shift <span class="text-error">*</span>
                                    </span>
                                </label>
                                <select wire:model.live="shift"
                                    class="select select-bordered focus:select-primary w-full transition-all @error('shift') select-error @enderror">
                                    <option value="">-- Pilih Shift --</option>
                                    <option value="siang">Siang</option>
                                    <option value="malam">Malam</option>
                                </select>
                                @error('shift')
                                    <span class="text-error text-xs mt-1.5 flex items-start gap-1 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 mt-0.5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ $message }}</span>
                                    </span>
                                @enderror
                            </div>

                            {{-- Jumlah Porsi --}}
                            <div class="form-control w-full md:col-span-2">
                                <label class="label mb-1 px-1 flex justify-between items-center">
                                    <span class="label-text text-sm font-medium text-base-content">
                                        Jumlah Porsi Nasi <span class="text-error">*</span>
                                    </span>
                                </label>

                                {{-- Skeleton input porsi saat shift sedang di-load --}}
                                <div wire:loading wire:target="shift" class="w-full">
                                    <div class="h-12 w-full bg-base-300/80 rounded-lg animate-pulse"></div>
                                </div>

                                {{-- Input porsi & stepper saat shift sudah selesai dimuat --}}
                                <div wire:loading.remove wire:target="shift" class="w-full"
                                    wire:key="porsi-stepper-container-{{ $shift }}-{{ (int) $this->maxPorsi }}"
                                    x-data="{
                                        porsi: @entangle('jumlah_porsi'),
                                        max: {{ (int) $this->maxPorsi }},
                                        isBlocked: {{ $this->isShiftAlreadyDocumented || !$shift ? 'true' : 'false' }},
                                        get shortcuts() {
                                            const m = parseInt(this.max, 10) || 0;
                                            if (m <= 0) return [];
                                            if (m <= 5) {
                                                let res = [];
                                                for (let i = 1; i <= m; i++) res.push(i);
                                                return res;
                                            }
                                            let base = m <= 25 ? [5, 10, 15, 20] : (m <= 50 ? [10, 20, 30, 40] : [10, 25, 50, 75, 100]);
                                            let res = base.filter(n => n < m);
                                            if (res.length === 0 && m > 2) {
                                                res.push(Math.floor(m / 2));
                                            }
                                            if (!res.includes(m)) {
                                                res.push(m);
                                            }
                                            return res;
                                        },
                                        syncToLivewire() {
                                            const val = this.porsi === '' ? 0 : (parseInt(this.porsi, 10) || 0);
                                            if (typeof this.$wire !== 'undefined') {
                                                this.$wire.set('jumlah_porsi', val, false);
                                            }
                                        },
                                        increment() {
                                            if (this.isBlocked) return;
                                            let val = parseInt(this.porsi, 10) || 0;
                                            if (this.max > 0 && val >= this.max) return;
                                            this.porsi = val + 1;
                                            this.syncToLivewire();
                                        },
                                        decrement() {
                                            if (this.isBlocked) return;
                                            let val = parseInt(this.porsi, 10) || 0;
                                            if (val <= 0) return;
                                            this.porsi = val - 1;
                                            this.syncToLivewire();
                                        },
                                        setShortcut(val) {
                                            if (this.isBlocked) return;
                                            let target = parseInt(val, 10) || 0;
                                            if (this.max > 0 && target > this.max) target = this.max;
                                            this.porsi = target;
                                            this.syncToLivewire();
                                        },
                                        handleInput(e) {
                                            let raw = e.target.value;
                                            let clean = raw.replace(/[^0-9]/g, '');
                                            if (clean !== raw) {
                                                e.target.value = clean;
                                            }
                                            if (clean === '') {
                                                this.porsi = '';
                                                this.syncToLivewire();
                                                return;
                                            }
                                            let num = parseInt(clean, 10);
                                            if (isNaN(num)) num = 0;
                                            if (this.max > 0 && num > this.max) {
                                                num = this.max;
                                                e.target.value = num;
                                            }
                                            this.porsi = num;
                                            this.syncToLivewire();
                                        },
                                        handleKeydown(e) {
                                            if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) {
                                                return;
                                            }
                                            if (e.key === 'ArrowUp') {
                                                e.preventDefault();
                                                this.increment();
                                                return;
                                            }
                                            if (e.key === 'ArrowDown') {
                                                e.preventDefault();
                                                this.decrement();
                                                return;
                                            }
                                            if (e.ctrlKey || e.metaKey) {
                                                return;
                                            }
                                            if (!/^[0-9]$/.test(e.key)) {
                                                e.preventDefault();
                                            }
                                        },
                                        handlePaste(e) {
                                            e.preventDefault();
                                            let text = (e.clipboardData || window.clipboardData).getData('text') || '';
                                            let clean = text.replace(/[^0-9]/g, '');
                                            if (clean) {
                                                let num = parseInt(clean, 10);
                                                if (this.max > 0 && num > this.max) num = this.max;
                                                this.porsi = num;
                                                this.syncToLivewire();
                                            }
                                        },
                                        handleBlur() {
                                            if (this.porsi === '' || isNaN(this.porsi) || this.porsi === null) {
                                                this.porsi = 0;
                                                this.syncToLivewire();
                                            }
                                        }
                                    }">

                                    {{-- Stepper Input Group: Form Input + Tombol Kurang & Tambah di samping kanan --}}
                                    <div class="join w-full shadow-xs">
                                        <input type="text" inputmode="numeric" pattern="[0-9]*" x-model="porsi"
                                            @input="handleInput($event)" @keydown="handleKeydown($event)"
                                            @paste="handlePaste($event)" @blur="handleBlur()" @drop.prevent
                                            @disabled(!$shift || $this->isShiftAlreadyDocumented)
                                            class="input input-bordered focus:input-primary join-item flex-1 font-medium transition-all @error('jumlah_porsi') input-error @enderror"
                                            placeholder="0">

                                        {{-- Tombol Kurang (-) --}}
                                        <button type="button" @click="decrement()" :disabled="isBlocked || porsi <= 0"
                                            title="Kurangi 1 Porsi"
                                            class="btn join-item btn-outline border-base-300 hover:border-base-content/20 hover:bg-base-200 px-3.5 transition-transform active:scale-95 disabled:opacity-40">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                            </svg>
                                        </button>

                                        {{-- Tombol Tambah (+) --}}
                                        <button type="button" @click="increment()"
                                            :disabled="isBlocked || (max > 0 && porsi >= max)" title="Tambah 1 Porsi"
                                            class="btn join-item btn-outline border-base-300 hover:border-primary hover:bg-primary hover:text-white px-3.5 transition-transform active:scale-95 disabled:opacity-40">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Shortcut Pilihan Cepat Jumlah Porsi --}}
                                    <div x-show="max > 0 && !isBlocked && shortcuts.length > 0" x-cloak
                                        class="flex flex-wrap items-center gap-1.5 mt-2">
                                        <span
                                            class="text-xs text-base-content/60 mr-1 flex items-center gap-1 font-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.8" stroke="currentColor" class="size-3.5 text-primary">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                            </svg>
                                            Pilihan Cepat:
                                        </span>
                                        <template x-for="item in shortcuts" :key="item">
                                            <button type="button" @click="setShortcut(item)"
                                                :class="porsi == item ? 'btn-primary text-white font-bold shadow-xs' :
                                                    'btn-ghost bg-base-200 hover:bg-base-300 text-base-content/80 font-medium'"
                                                class="btn btn-xs rounded-md transition-all active:scale-95"
                                                x-text="item + ' Porsi'">
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-1 px-1">
                                    <div wire:loading wire:target="shift" class="w-full">
                                        <div class="h-3 w-64 bg-base-300 rounded animate-pulse mt-0.5"></div>
                                    </div>
                                    <div wire:loading.remove wire:target="shift"
                                        class="w-full flex items-center justify-between">
                                        @if ($shift)
                                            <span class="text-[11px] text-base-content/60">
                                                Kalkulasi dari absensi hadir dan jadwal shift
                                                {{ $shift === 'siang' ? 'siang' : 'malam' }}.
                                            </span>
                                        @else
                                            <span class="text-[11px] text-base-content/50">
                                                Pilih shift terlebih dahulu untuk menentukan kuota porsi.
                                            </span>
                                        @endif
                                        @error('jumlah_porsi')
                                            <span class="text-error text-xs font-semibold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Foto Dokumentasi --}}
                            <div class="form-control w-full md:col-span-2 pt-2 border-t border-base-200">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-4">
                                    <div>
                                        <h5 class="font-bold text-base text-base-content">
                                            Foto Dokumentasi <span class="text-error">*</span>
                                        </h5>
                                        <p class="text-xs text-base-content/60 mt-0.5">
                                            @if ($shift === 'siang')
                                                Foto Konsumsi Shift Siang (WebP, maks. 100KB)
                                            @elseif ($shift === 'malam')
                                                Foto Konsumsi Shift Malam (WebP, maks. 100KB)
                                            @else
                                                Pilih shift untuk mulai mengunggah foto dokumentasi
                                            @endif
                                        </p>
                                    </div>
                                    @if ($shift)
                                        <div wire:loading wire:target="shift">
                                            <div class="h-4 w-24 bg-base-300 rounded animate-pulse"></div>
                                        </div>
                                        <span wire:loading.remove wire:target="shift"
                                            class="border-b-2 border-primary text-xs font-bold text-primary pb-0.5 uppercase tracking-wide self-start sm:self-auto">
                                            Shift {{ $shift === 'siang' ? 'Siang' : 'Malam' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Skeleton Loading saat user baru memilih shift --}}
                                <div wire:loading wire:target="shift" class="w-full">
                                    <div
                                        class="p-6 bg-base-200/40 border border-base-300/80 rounded-2xl animate-pulse space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="h-4 w-44 bg-base-300 rounded"></div>
                                            <div class="h-4 w-24 bg-base-300 rounded"></div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div
                                                class="h-56 bg-base-300/70 rounded-xl flex flex-col items-center justify-center p-4">
                                                <div class="w-12 h-12 rounded-full bg-base-300 mb-3"></div>
                                                <div class="h-3.5 w-32 bg-base-300 rounded mb-2"></div>
                                                <div class="h-2.5 w-44 bg-base-300 rounded"></div>
                                            </div>
                                            <div
                                                class="h-56 bg-base-300/70 rounded-xl flex flex-col items-center justify-center p-4">
                                                <div class="w-12 h-12 rounded-full bg-base-300 mb-3"></div>
                                                <div class="h-3.5 w-32 bg-base-300 rounded mb-2"></div>
                                                <div class="h-2.5 w-44 bg-base-300 rounded"></div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-center pt-2 gap-2 text-xs text-base-content/60">
                                            <span class="loading loading-spinner loading-xs text-primary"></span>
                                            <span>Memeriksa dokumentasi & kuota shift...</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Konten Form Foto setelah shift selesai dimuat --}}
                                <div wire:loading.remove wire:target="shift">
                                    @if (!$shift)
                                        {{-- Placeholder jika shift belum dipilih --}}
                                        <div
                                            class="flex flex-col items-center justify-center p-8 text-center text-base-content/40 border-2 border-dashed border-base-300 rounded-xl my-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-12 h-12 mb-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                            <span class="text-sm font-semibold">Pilih Shift Terlebih Dahulu</span>
                                            <span class="text-xs mt-1">Field upload foto akan aktif setelah shift
                                                dipilih.</span>
                                        </div>
                                    @else
                                        {{-- Banner Peringatan Inline jika dokumentasi shift sudah ada --}}
                                        @if ($this->isShiftAlreadyDocumented)
                                            <div
                                                class="alert alert-error bg-error/10 border border-error/20 text-error rounded-xl p-4 mb-4 flex items-start gap-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mt-0.5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div class="flex-1 text-xs">
                                                    <span class="font-bold text-sm block text-error mb-0.5">Dokumentasi
                                                        Sudah Ada</span>
                                                    <p class="text-base-content/80 text-xs">
                                                        Data dokumentasi konsumsi untuk <strong>Shift
                                                            {{ ucfirst($shift) }}</strong> pada tanggal
                                                        <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
                                                        sudah pernah diunggah ke sistem.
                                                    </p>
                                                    @php
                                                        $currentSavedFoto1 =
                                                            $shift === 'siang'
                                                                ? $this->existingRecord?->foto_siang
                                                                : $this->existingRecord?->foto_malam;
                                                        $currentSavedFoto2 =
                                                            $shift === 'siang'
                                                                ? $this->existingRecord?->foto_siang_2
                                                                : $this->existingRecord?->foto_malam_2;
                                                        $savedPorsi =
                                                            $shift === 'siang'
                                                                ? $this->existingRecord?->jumlah_siang
                                                                : $this->existingRecord?->jumlah_malam;
                                                    @endphp
                                                    <div
                                                        class="mt-2.5 flex flex-wrap items-center gap-3 text-[11px] text-base-content/70">
                                                        <span class="text-neutral font-medium">Tersimpan:
                                                            {{ $savedPorsi ?? 0 }} Porsi</span>
                                                        @if ($currentSavedFoto1)
                                                            <a href="{{ asset('storage/' . $currentSavedFoto1) }}"
                                                                target="_blank"
                                                                class="text-primary hover:underline font-semibold flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="size-3.5" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                </svg>
                                                                Lihat Foto Utama Tersimpan
                                                            </a>
                                                        @endif
                                                        @if ($currentSavedFoto2)
                                                            <a href="{{ asset('storage/' . $currentSavedFoto2) }}"
                                                                target="_blank"
                                                                class="text-primary hover:underline font-semibold flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="size-3.5" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                </svg>
                                                                Lihat Foto 2 Tersimpan
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">

                                            {{-- Foto 1 (Wajib) --}}
                                            <div class="form-control w-full">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-base-content">
                                                        Foto Utama (1) <span class="text-error">*</span>
                                                    </span>
                                                    <span
                                                        class="border-b-2 border-primary text-xs font-bold text-primary pb-0.5">
                                                        Wajib
                                                    </span>
                                                </div>

                                                @if ($foto1 && (!method_exists($foto1, 'isPreviewable') || $foto1->isPreviewable()))
                                                    {{-- Preview Foto 1 Baru (Terkompresi WebP) --}}
                                                    <div wire:key="foto1-preview-container-{{ $uploadIteration }}">
                                                        <div
                                                            class="w-full aspect-4/3 bg-base-200/80 rounded-xl overflow-hidden border border-base-300 shadow-inner relative group flex items-center justify-center">
                                                            <img src="{{ $foto1->temporaryUrl() }}"
                                                                alt="Preview Foto 1"
                                                                class="w-full h-full object-contain">
                                                            <div class="absolute top-2 right-2">
                                                                <span
                                                                    class="badge badge-sm badge-success text-[10px] font-bold text-white shadow-xs">
                                                                    WebP &le; 100KB
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 flex items-center justify-between text-xs">
                                                            <span class="text-success font-medium text-[11px]"
                                                                x-show="compressInfo1" x-text="compressInfo1"></span>
                                                            <button type="button" wire:click="removeFoto(1)"
                                                                @click="resetSlot(1)"
                                                                class="text-error hover:text-error/80 text-xs font-semibold flex items-center gap-1 transition-colors ml-auto">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="2"
                                                                    stroke="currentColor" class="size-4">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                                </svg>
                                                                <span>Hapus Foto Utama</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Area Pemilihan Foto 1 (Dengan 2 Tombol: Dokumen & Kamera HP) --}}
                                                    <div wire:key="foto1-dropzone-container-{{ $uploadIteration }}"
                                                        class="relative flex flex-col items-center justify-center w-full min-h-55 aspect-4/3 border-2 border-dashed rounded-xl transition-all p-4 text-center bg-base-200/40"
                                                        :class="{
                                                            'border-primary/60 bg-primary/5': isProcessing1,
                                                            'border-base-300 hover:border-primary/50 hover:bg-base-200/60':
                                                                !isProcessing1
                                                        }"
                                                        @dragover.prevent
                                                        @drop.prevent="if (!isProcessing1 && $event.dataTransfer.files && $event.dataTransfer.files[0]) processFile($event.dataTransfer.files[0], 1)">

                                                        {{-- State Loading: Kompresi & Upload --}}
                                                        <div x-show="isProcessing1" x-cloak
                                                            class="flex flex-col items-center justify-center w-full py-3 px-2 my-auto">
                                                            <span
                                                                class="loading loading-spinner loading-lg text-primary mb-2.5"></span>
                                                            <span class="text-xs font-bold text-base-content"
                                                                x-text="statusText1"></span>
                                                            <span class="text-[11px] text-base-content/60 mt-1"
                                                                x-show="compressInfo1" x-text="compressInfo1"></span>

                                                            <div class="w-full max-w-50 mt-3">
                                                                <div
                                                                    class="w-full bg-base-300 rounded-full h-2 overflow-hidden shadow-inner">
                                                                    <div class="bg-primary h-2 rounded-full transition-all duration-200"
                                                                        :style="`width: ${progress1}%`"></div>
                                                                </div>
                                                                <div
                                                                    class="flex justify-between text-[10px] text-base-content/50 mt-1 font-medium">
                                                                    <span>Progress</span>
                                                                    <span x-text="progress1 + '%'"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- State Normal: Pilihan Dokumen atau Kamera HP --}}
                                                        <div x-show="!isProcessing1"
                                                            class="flex flex-col items-center justify-center w-full my-auto">
                                                            <div
                                                                class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.6"
                                                                    stroke="currentColor" class="size-6">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                                </svg>
                                                            </div>
                                                            <span class="text-xs font-bold text-base-content">Pilih
                                                                atau
                                                                Ambil Foto Utama</span>
                                                            <span
                                                                class="text-[10px] text-base-content/60 text-center max-w-55 mt-0.5">
                                                                Maks. 10MB dari HP/PC, otomatis dikompres ke WebP
                                                                (&le;100KB)
                                                            </span>

                                                            {{-- 2 Tombol: Dokumen & Kamera HP --}}
                                                            <div
                                                                class="flex flex-wrap items-center justify-center gap-2 mt-3">
                                                                <button type="button"
                                                                    @click="$refs.docInput1.click()"
                                                                    class="btn btn-sm btn-outline btn-primary gap-1.5 rounded-lg transition-transform active:scale-95">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.8" stroke="currentColor"
                                                                        class="size-4">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                    </svg>
                                                                    <span>Pilih Dokumen</span>
                                                                </button>

                                                                <button type="button" @click="openCamera(1)"
                                                                    class="btn btn-sm btn-primary gap-1.5 rounded-lg shadow-xs transition-transform active:scale-95">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.8" stroke="currentColor"
                                                                        class="size-4">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316z" />
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5z" />
                                                                    </svg>
                                                                    <span>Kamera HP</span>
                                                                </button>
                                                            </div>

                                                            {{-- Hidden Inputs Foto 1 --}}
                                                            <input type="file" x-ref="docInput1"
                                                                @change="handleFileSelect($event, 1)"
                                                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                                                class="hidden">
                                                            <input type="file" x-ref="camInput1"
                                                                @change="handleFileSelect($event, 1)" accept="image/*"
                                                                capture="environment" class="hidden">
                                                        </div>

                                                        {{-- Pesan Error Client-side --}}
                                                        <div x-show="errorMsg1" x-cloak x-transition
                                                            class="mt-2 text-error text-xs font-semibold text-center bg-error/10 border border-error/20 py-1.5 px-2.5 rounded-lg w-full"
                                                            x-text="errorMsg1"></div>
                                                    </div>

                                                    {{-- Informasi jika foto sudah tersimpan sebelumnya --}}
                                                    @php
                                                        $currentSavedFoto1 =
                                                            $shift === 'siang'
                                                                ? $this->existingRecord?->foto_siang
                                                                : $this->existingRecord?->foto_malam;
                                                    @endphp
                                                    @if ($currentSavedFoto1)
                                                        <div
                                                            class="mt-2 flex items-center justify-between text-xs text-base-content/60">
                                                            <span class="border-b border-base-content/30 pb-0.5">Foto
                                                                tersimpan di database</span>
                                                            <a href="{{ asset('storage/' . $currentSavedFoto1) }}"
                                                                target="_blank"
                                                                class="text-primary hover:underline font-medium text-[11px]">
                                                                Lihat Foto
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endif

                                                @error('foto1')
                                                    <span class="text-error text-xs mt-1.5">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Foto 2 (Opsional) --}}
                                            <div class="form-control w-full">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-base-content">
                                                        Foto Tambahan (2)
                                                    </span>
                                                    <span
                                                        class="border-b border-base-content/30 text-xs font-medium text-base-content/60 pb-0.5">
                                                        Opsional
                                                    </span>
                                                </div>

                                                @if ($foto2 && (!method_exists($foto2, 'isPreviewable') || $foto2->isPreviewable()))
                                                    {{-- Preview Foto 2 Baru (Terkompresi WebP) --}}
                                                    <div wire:key="foto2-preview-container-{{ $uploadIteration }}">
                                                        <div
                                                            class="w-full aspect-4/3 bg-base-200/80 rounded-xl overflow-hidden border border-base-300 shadow-inner relative group flex items-center justify-center">
                                                            <img src="{{ $foto2->temporaryUrl() }}"
                                                                alt="Preview Foto 2"
                                                                class="w-full h-full object-contain">
                                                            <div class="absolute top-2 right-2">
                                                                <span
                                                                    class="badge badge-sm badge-success text-[10px] font-bold text-white shadow-xs">
                                                                    WebP &le; 100KB
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 flex items-center justify-between text-xs">
                                                            <span class="text-success font-medium text-[11px]"
                                                                x-show="compressInfo2" x-text="compressInfo2"></span>
                                                            <button type="button" wire:click="removeFoto(2)"
                                                                @click="resetSlot(2)"
                                                                class="text-error hover:text-error/80 text-xs font-semibold flex items-center gap-1 transition-colors ml-auto">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="2"
                                                                    stroke="currentColor" class="size-4">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                                </svg>
                                                                <span>Hapus Foto Tambahan</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Area Pemilihan Foto 2 (Dengan 2 Tombol: Dokumen & Kamera HP) --}}
                                                    <div wire:key="foto2-dropzone-container-{{ $uploadIteration }}"
                                                        class="relative flex flex-col items-center justify-center w-full min-h-55 aspect-4/3 border-2 border-dashed rounded-xl transition-all p-4 text-center bg-base-200/40"
                                                        :class="{
                                                            'border-primary/60 bg-primary/5': isProcessing2,
                                                            'border-base-300 hover:border-primary/50 hover:bg-base-200/60':
                                                                !isProcessing2
                                                        }"
                                                        @dragover.prevent
                                                        @drop.prevent="if (!isProcessing2 && $event.dataTransfer.files && $event.dataTransfer.files[0]) processFile($event.dataTransfer.files[0], 2)">

                                                        {{-- State Loading: Kompresi & Upload --}}
                                                        <div x-show="isProcessing2" x-cloak
                                                            class="flex flex-col items-center justify-center w-full py-3 px-2 my-auto">
                                                            <span
                                                                class="loading loading-spinner loading-lg text-primary mb-2.5"></span>
                                                            <span class="text-xs font-bold text-base-content"
                                                                x-text="statusText2"></span>
                                                            <span class="text-[11px] text-base-content/60 mt-1"
                                                                x-show="compressInfo2" x-text="compressInfo2"></span>

                                                            <div class="w-full max-w-50 mt-3">
                                                                <div
                                                                    class="w-full bg-base-300 rounded-full h-2 overflow-hidden shadow-inner">
                                                                    <div class="bg-primary h-2 rounded-full transition-all duration-200"
                                                                        :style="`width: ${progress2}%`"></div>
                                                                </div>
                                                                <div
                                                                    class="flex justify-between text-[10px] text-base-content/50 mt-1 font-medium">
                                                                    <span>Progress</span>
                                                                    <span x-text="progress2 + '%'"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- State Normal: Pilihan Dokumen atau Kamera HP --}}
                                                        <div x-show="!isProcessing2"
                                                            class="flex flex-col items-center justify-center w-full my-auto">
                                                            <div
                                                                class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.6"
                                                                    stroke="currentColor" class="size-6">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                                </svg>
                                                            </div>
                                                            <span class="text-xs font-bold text-base-content">Pilih
                                                                atau
                                                                Ambil Foto Tambahan</span>
                                                            <span
                                                                class="text-[10px] text-base-content/60 text-center max-w-55 mt-0.5">
                                                                Maks. 10MB dari HP/PC, otomatis dikompres ke WebP
                                                                (&le;100KB)
                                                            </span>

                                                            {{-- 2 Tombol: Dokumen & Kamera HP --}}
                                                            <div
                                                                class="flex flex-wrap items-center justify-center gap-2 mt-3">
                                                                <button type="button"
                                                                    @click="$refs.docInput2.click()"
                                                                    class="btn btn-sm btn-outline btn-primary gap-1.5 rounded-lg transition-transform active:scale-95">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.8" stroke="currentColor"
                                                                        class="size-4">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                    </svg>
                                                                    <span>Pilih Dokumen</span>
                                                                </button>

                                                                <button type="button" @click="openCamera(2)"
                                                                    class="btn btn-sm btn-primary gap-1.5 rounded-lg shadow-xs transition-transform active:scale-95">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.8" stroke="currentColor"
                                                                        class="size-4">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316z" />
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5z" />
                                                                    </svg>
                                                                    <span>Kamera HP</span>
                                                                </button>
                                                            </div>

                                                            {{-- Hidden Inputs Foto 2 --}}
                                                            <input type="file" x-ref="docInput2"
                                                                @change="handleFileSelect($event, 2)"
                                                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                                                class="hidden">
                                                            <input type="file" x-ref="camInput2"
                                                                @change="handleFileSelect($event, 2)" accept="image/*"
                                                                capture="environment" class="hidden">
                                                        </div>

                                                        {{-- Pesan Error Client-side --}}
                                                        <div x-show="errorMsg2" x-cloak x-transition
                                                            class="mt-2 text-error text-xs font-semibold text-center bg-error/10 border border-error/20 py-1.5 px-2.5 rounded-lg w-full"
                                                            x-text="errorMsg2"></div>
                                                    </div>

                                                    {{-- Informasi jika foto kedua sudah tersimpan sebelumnya --}}
                                                    @php
                                                        $currentSavedFoto2 =
                                                            $shift === 'siang'
                                                                ? $this->existingRecord?->foto_siang_2
                                                                : $this->existingRecord?->foto_malam_2;
                                                    @endphp
                                                    @if ($currentSavedFoto2)
                                                        <div
                                                            class="mt-2 flex items-center justify-between text-xs text-base-content/60">
                                                            <span class="border-b border-base-content/30 pb-0.5">Foto 2
                                                                tersimpan di database</span>
                                                            <a href="{{ asset('storage/' . $currentSavedFoto2) }}"
                                                                target="_blank"
                                                                class="text-primary hover:underline font-medium text-[11px]">
                                                                Lihat Foto
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endif

                                                @error('foto2')
                                                    <span class="text-error text-xs mt-1.5">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Keterangan --}}
                            <div class="form-control w-full md:col-span-2">
                                <label class="label mb-1 px-1 flex justify-between items-center">
                                    <span class="label-text text-sm font-medium text-base-content">
                                        Keterangan
                                    </span>
                                    <span
                                        class="border-b border-base-content/30 text-xs font-normal text-base-content/50 pb-0.5">
                                        Opsional
                                    </span>
                                </label>
                                <textarea wire:model="keterangan" rows="3"
                                    class="textarea textarea-bordered focus:textarea-primary placeholder:text-base-content/60 w-full transition-all @error('keterangan') textarea-error @enderror"
                                    placeholder="Tuliskan catatan atau keterangan tambahan (opsional)..."></textarea>
                                @error('keterangan')
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Footer Form --}}
                    <div class="card-footer border-t border-base-200 p-6 flex justify-end gap-3">
                        <button type="button" wire:click="resetForm" @click="resetAll()" class="btn btn-ghost"
                            wire:loading.attr="disabled" :disabled="isProcessing1 || isProcessing2">
                            Reset Form
                        </button>
                        <button type="submit" class="btn btn-secondary px-8" wire:loading.attr="disabled"
                            :disabled="isProcessing1 || isProcessing2 || {{ $this->isShiftAlreadyDocumented ? 'true' : 'false' }}"
                            wire:target="save, foto1, foto2, shift">
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                            <span wire:loading.remove wire:target="save">Simpan Data</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Kamera Live WebRTC (Dapat digunakan di HP maupun PC/Laptop) --}}
            <div x-show="isCameraModalOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-9999 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-xs"
                @keydown.escape.window="stopCamera()">

                {{-- Backdrop Click to Close --}}
                <div class="fixed inset-0" @click="stopCamera()"></div>

                {{-- Modal Card --}}
                <div class="relative bg-base-100 text-base-content rounded-2xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden z-10 flex flex-col animate-in fade-in zoom-in-95 duration-200"
                    @click.stop>

                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-base-200 bg-base-200/50">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                            </span>
                            <span class="font-bold text-sm text-base-content">
                                Ambil Foto via Kamera
                            </span>
                            <span class="badge badge-sm badge-neutral text-[10px] font-semibold"
                                x-text="activeCameraSlot === 1 ? 'Foto Utama' : 'Foto Tambahan'"></span>
                        </div>
                        <button type="button" @click="stopCamera()"
                            class="btn btn-circle btn-ghost btn-xs text-base-content/60 hover:text-base-content">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Bar Pemilihan Orientasi Hasil Foto (Landscape vs Portrait) --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2 border-b border-base-200 bg-base-100 text-xs">
                        <div class="flex items-center gap-1.5 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                stroke="currentColor" class="size-4 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                            <span class="font-semibold">Orientasi Foto:</span>
                        </div>
                        <div class="join shadow-2xs">
                            <button type="button" @click="cameraOrientation = 'auto'"
                                :class="cameraOrientation === 'auto' ? 'btn-primary text-white font-bold shadow-xs' : 'btn-ghost text-base-content/70 hover:bg-base-200'"
                                class="btn btn-xs join-item transition-all" title="Otomatis mendeteksi orientasi sensor kamera">
                                Otomatis
                            </button>
                            <button type="button" @click="cameraOrientation = 'landscape'"
                                :class="cameraOrientation === 'landscape' ? 'btn-primary text-white font-bold shadow-xs' : 'btn-ghost text-base-content/70 hover:bg-base-200'"
                                class="btn btn-xs join-item transition-all gap-1" title="Foto mendatar (Landscape 4:3)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                </svg>
                                Landscape
                            </button>
                            <button type="button" @click="cameraOrientation = 'portrait'"
                                :class="cameraOrientation === 'portrait' ? 'btn-primary text-white font-bold shadow-xs' : 'btn-ghost text-base-content/70 hover:bg-base-200'"
                                class="btn btn-xs join-item transition-all gap-1" title="Foto tegak (Portrait 3:4)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="2" width="14" height="20" rx="2" />
                                </svg>
                                Portrait
                            </button>
                        </div>
                    </div>

                    {{-- Video Viewport --}}
                    <div class="relative w-full bg-black flex items-center justify-center overflow-hidden transition-all duration-300 mx-auto"
                        :class="effectiveOrientation() === 'portrait' ? 'aspect-3/4 max-h-[58vh]' : 'aspect-4/3 max-h-[50vh]'">
                        {{-- Loading State --}}
                        <div x-show="isStartingCamera"
                            class="absolute inset-0 flex flex-col items-center justify-center text-white gap-2 bg-black/60 z-20">
                            <span class="loading loading-spinner loading-md text-primary"></span>
                            <span class="text-xs font-medium">Menghubungkan ke kamera...</span>
                        </div>

                        {{-- Error State --}}
                        <div x-show="cameraError" x-cloak
                            class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-black/90 z-20">
                            <div
                                class="w-12 h-12 rounded-full bg-error/20 text-error flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-error mb-1">Kamera Tidak Tersedia</span>
                            <p class="text-xs text-white/80 max-w-xs mb-4" x-text="cameraError"></p>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    @click="stopCamera(); $refs['camInput' + activeCameraSlot].click()"
                                    class="btn btn-xs btn-primary">
                                    Gunakan File Kamera HP
                                </button>
                                <button type="button" @click="initCameraStream()"
                                    class="btn btn-xs btn-ghost text-white">
                                    Coba Lagi
                                </button>
                            </div>
                        </div>

                        {{-- Badge Status Orientasi Aktif --}}
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-xs text-[11px] font-medium text-white border border-white/10 z-10 pointer-events-none">
                            <span x-text="effectiveOrientation() === 'portrait' ? '📱 Portrait' : '🖥️ Landscape'"></span>
                            <span class="opacity-60" x-text="effectiveOrientation() === 'portrait' ? '(3:4)' : '(4:3)'"></span>
                            <span x-show="cameraOrientation === 'auto'" class="badge badge-2xs badge-primary ml-0.5 text-[9px] text-white">Auto</span>
                        </div>

                        {{-- Video Element --}}
                        <video x-ref="cameraVideo" autoplay playsinline muted class="w-full h-full object-cover"
                            :class="cameraFacingMode === 'user' ? 'scale-x-[-1]' : ''"></video>

                        {{-- Framing Overlay --}}
                        <div
                            class="absolute inset-0 pointer-events-none border-2 border-white/20 m-4 rounded-xl flex items-center justify-center">
                            <div
                                class="w-8 h-8 border-t-2 border-l-2 border-primary absolute top-0 left-0 rounded-tl-lg">
                            </div>
                            <div
                                class="w-8 h-8 border-t-2 border-r-2 border-primary absolute top-0 right-0 rounded-tr-lg">
                            </div>
                            <div
                                class="w-8 h-8 border-b-2 border-l-2 border-primary absolute bottom-0 left-0 rounded-bl-lg">
                            </div>
                            <div
                                class="w-8 h-8 border-b-2 border-r-2 border-primary absolute bottom-0 right-0 rounded-br-lg">
                            </div>
                        </div>

                        {{-- Switch Camera Button (floating top-right) --}}
                        <button type="button" x-show="hasMultipleCameras" @click="switchCamera()"
                            class="absolute top-3 right-3 btn btn-circle btn-sm bg-black/50 hover:bg-black/80 text-white border-0 backdrop-blur-xs z-10 transition-transform active:rotate-180"
                            title="Ganti Kamera Depan/Belakang">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.8" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </button>
                    </div>

                    {{-- Footer Controls --}}
                    <div class="p-4 bg-base-100 flex items-center justify-between">
                        <button type="button" @click="stopCamera()"
                            class="btn btn-sm btn-ghost text-base-content/70">
                            Batal
                        </button>

                        {{-- Tombol Shutter Ambil Foto --}}
                        <button type="button" @click="capturePhoto()" :disabled="isStartingCamera || !!cameraError"
                            class="btn btn-circle btn-lg btn-primary shadow-lg p-1.5 transition-transform active:scale-90 hover:scale-105"
                            title="Jepret Foto">
                            <div class="w-full h-full rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-6 text-primary-content">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5z" />
                                </svg>
                            </div>
                        </button>

                        {{-- Spacer / Switch button for balance --}}
                        <div class="w-16 flex justify-end">
                            <button type="button" x-show="hasMultipleCameras" @click="switchCamera()"
                                class="btn btn-sm btn-circle btn-ghost text-base-content/70" title="Ganti Kamera">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        {{-- Skeleton Loading --}}
        <div class="max-w-4xl">
            <div class="card bg-base-100 shadow-sm border border-base-200 animate-pulse">
                <div class="card-body p-6 space-y-4">
                    <div class="h-6 bg-base-300 rounded w-1/3 mb-4"></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded"></div>
                        <div class="h-12 bg-base-300 rounded md:col-span-2"></div>
                        <div class="h-44 bg-base-300 rounded md:col-span-2"></div>
                        <div class="h-24 bg-base-300 rounded md:col-span-2"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Alpine.js Image Compression & Upload Handler --}}
    <script>
        (function() {
            function registerUploadHandler() {
                if (window.Alpine && !window._uploadDokumentasiHandlerRegistered) {
                    window._uploadDokumentasiHandlerRegistered = true;
                    Alpine.data('uploadDokumentasiUploader', () => ({
                        isProcessing1: false,
                        isProcessing2: false,
                        progress1: 0,
                        progress2: 0,
                        statusText1: '',
                        statusText2: '',
                        compressInfo1: '',
                        compressInfo2: '',
                        errorMsg1: '',
                        errorMsg2: '',

                        // State Kamera Live WebRTC
                        isCameraModalOpen: false,
                        activeCameraSlot: 1,
                        cameraStream: null,
                        isStartingCamera: false,
                        cameraFacingMode: 'environment',
                        hasMultipleCameras: false,
                        cameraError: '',
                        cameraOrientation: 'auto', // 'auto', 'landscape', 'portrait'
                        detectedOrientation: 'landscape', // 'landscape' atau 'portrait'

                        effectiveOrientation() {
                            if (this.cameraOrientation === 'landscape') return 'landscape';
                            if (this.cameraOrientation === 'portrait') return 'portrait';
                            return this.detectedOrientation;
                        },

                        async openCamera(slot) {
                            this.activeCameraSlot = slot;
                            this.cameraError = '';

                            // Cek ketersediaan navigator.mediaDevices
                            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                const inputRef = slot === 1 ? this.$refs.camInput1 : this.$refs
                                    .camInput2;
                                if (inputRef) {
                                    inputRef.click();
                                } else {
                                    const msg = 'Browser tidak mendukung akses kamera langsung.';
                                    this.$dispatch('toast', {
                                        type: 'error',
                                        message: msg
                                    });
                                }
                                return;
                            }

                            this.isCameraModalOpen = true;
                            this.isStartingCamera = true;
                            await this.initCameraStream();
                        },

                        async initCameraStream() {
                            this.isStartingCamera = true;
                            this.cameraError = '';

                            if (this.cameraStream) {
                                this.cameraStream.getTracks().forEach(track => track.stop());
                                this.cameraStream = null;
                            }

                            try {
                                try {
                                    const devices = await navigator.mediaDevices.enumerateDevices();
                                    const videoDevices = devices.filter(d => d.kind === 'videoinput');
                                    this.hasMultipleCameras = videoDevices.length > 1;
                                } catch (e) {
                                    this.hasMultipleCameras = false;
                                }

                                const constraints = {
                                    video: {
                                        facingMode: {
                                            ideal: this.cameraFacingMode
                                        },
                                        width: {
                                            ideal: 1920,
                                            min: 640
                                        },
                                        height: {
                                            ideal: 1080,
                                            min: 480
                                        }
                                    },
                                    audio: false
                                };

                                this.cameraStream = await navigator.mediaDevices.getUserMedia(
                                    constraints);

                                this.$nextTick(() => {
                                    if (this.$refs.cameraVideo) {
                                        this.$refs.cameraVideo.srcObject = this.cameraStream;
                                        this.$refs.cameraVideo.onloadedmetadata = () => {
                                            const vw = this.$refs.cameraVideo.videoWidth || 1280;
                                            const vh = this.$refs.cameraVideo.videoHeight || 720;
                                            this.detectedOrientation = vh > vw ? 'portrait' : 'landscape';
                                        };
                                        this.$refs.cameraVideo.play().catch(() => {});
                                    }
                                });
                            } catch (err) {
                                console.error('Gagal mengakses kamera:', err);
                                if (err.name === 'NotAllowedError' || err.name ===
                                    'PermissionDeniedError') {
                                    this.cameraError =
                                        'Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
                                } else if (err.name === 'NotFoundError' || err.name ===
                                    'DevicesNotFoundError') {
                                    this.cameraError = 'Kamera tidak ditemukan di perangkat Anda.';
                                } else {
                                    this.cameraError = 'Gagal membuka kamera: ' + (err.message ||
                                        'Periksa izin browser.');
                                }
                            } finally {
                                this.isStartingCamera = false;
                            }
                        },

                        async switchCamera() {
                            this.cameraFacingMode = this.cameraFacingMode === 'environment' ? 'user' :
                                'environment';
                            await this.initCameraStream();
                        },

                        stopCamera() {
                            if (this.cameraStream) {
                                this.cameraStream.getTracks().forEach(track => track.stop());
                                this.cameraStream = null;
                            }
                            if (this.$refs.cameraVideo) {
                                this.$refs.cameraVideo.srcObject = null;
                            }
                            this.isCameraModalOpen = false;
                            this.isStartingCamera = false;
                            this.cameraError = '';
                        },

                        capturePhoto() {
                            if (!this.$refs.cameraVideo || this.isStartingCamera || this.cameraError)
                                return;

                            const video = this.$refs.cameraVideo;
                            const vw = video.videoWidth || 1280;
                            const vh = video.videoHeight || 720;

                            const isStreamPortrait = vh > vw;
                            const orientation = this.effectiveOrientation();

                            let sourceX = 0;
                            let sourceY = 0;
                            let sourceW = vw;
                            let sourceH = vh;
                            let targetW = vw;
                            let targetH = vh;

                            if (orientation === 'portrait') {
                                if (!isStreamPortrait) {
                                    // Sensor adalah landscape (misal webcam PC), potong tengah ke 3:4 portrait
                                    targetH = vh;
                                    targetW = Math.round(vh * (3 / 4));
                                    if (targetW > vw) {
                                        targetW = vw;
                                        targetH = Math.round(vw * (4 / 3));
                                    }
                                } else {
                                    // Sensor sudah portrait (misal HP), sesuaikan ke 3:4 portrait
                                    targetW = vw;
                                    targetH = Math.round(vw * (4 / 3));
                                    if (targetH > vh) {
                                        targetH = vh;
                                        targetW = Math.round(vh * (3 / 4));
                                    }
                                }
                                sourceW = targetW;
                                sourceH = targetH;
                                sourceX = Math.round((vw - targetW) / 2);
                                sourceY = Math.round((vh - targetH) / 2);
                            } else if (orientation === 'landscape') {
                                if (isStreamPortrait) {
                                    // Sensor adalah portrait (misal HP tegak), potong tengah ke 4:3 landscape
                                    targetW = vw;
                                    targetH = Math.round(vw * (3 / 4));
                                    if (targetH > vh) {
                                        targetH = vh;
                                        targetW = Math.round(vh * (4 / 3));
                                    }
                                } else {
                                    // Sensor sudah landscape, sesuaikan ke 4:3 landscape
                                    targetH = vh;
                                    targetW = Math.round(vh * (4 / 3));
                                    if (targetW > vw) {
                                        targetW = vw;
                                        targetH = Math.round(vw * (3 / 4));
                                    }
                                }
                                sourceW = targetW;
                                sourceH = targetH;
                                sourceX = Math.round((vw - targetW) / 2);
                                sourceY = Math.round((vh - targetH) / 2);
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = targetW;
                            canvas.height = targetH;
                            const ctx = canvas.getContext('2d');

                            // Jika kamera depan, balik secara horizontal agar preview cermin alami
                            if (this.cameraFacingMode === 'user') {
                                ctx.translate(targetW, 0);
                                ctx.scale(-1, 1);
                            }

                            ctx.drawImage(video, sourceX, sourceY, sourceW, sourceH, 0, 0, targetW, targetH);

                            const slot = this.activeCameraSlot;
                            this.stopCamera();

                            canvas.toBlob((blob) => {
                                if (!blob) {
                                    this.$dispatch('toast', {
                                        type: 'error',
                                        message: 'Gagal mengambil gambar dari kamera.'
                                    });
                                    return;
                                }
                                const fileName = `kamera_slot${slot}_${Date.now()}.jpg`;
                                const capturedFile = new File([blob], fileName, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });

                                this.processFile(capturedFile, slot);
                            }, 'image/jpeg', 0.92);
                        },

                        resetSlot(slot) {
                            if (slot === 1) {
                                if (this.$refs.docInput1) this.$refs.docInput1.value = '';
                                if (this.$refs.camInput1) this.$refs.camInput1.value = '';
                                this.isProcessing1 = false;
                                this.progress1 = 0;
                                this.statusText1 = '';
                                this.compressInfo1 = '';
                                this.errorMsg1 = '';
                            } else {
                                if (this.$refs.docInput2) this.$refs.docInput2.value = '';
                                if (this.$refs.camInput2) this.$refs.camInput2.value = '';
                                this.isProcessing2 = false;
                                this.progress2 = 0;
                                this.statusText2 = '';
                                this.compressInfo2 = '';
                                this.errorMsg2 = '';
                            }
                        },

                        resetAll() {
                            this.stopCamera();
                            this.resetSlot(1);
                            this.resetSlot(2);
                        },

                        handleFileSelect(event, slot) {
                            const file = event.target.files && event.target.files[0];
                            if (!file) return;

                            // Reset value agar input change tetap terpicu jika memilih file yang sama lagi
                            event.target.value = '';

                            this.processFile(file, slot);
                        },

                        async processFile(file, slot) {
                            if (!file) return;

                            // 1. Validasi tipe file
                            if (!file.type || !file.type.startsWith('image/')) {
                                const msg =
                                    'File yang dipilih harus berupa gambar (JPEG, JPG, PNG, atau WebP).';
                                if (slot === 1) this.errorMsg1 = msg;
                                else this.errorMsg2 = msg;
                                this.$dispatch('toast', {
                                    type: 'error',
                                    message: msg
                                });
                                return;
                            }

                            // 2. Validasi maksimal ukuran file asli: 10MB (10 * 1024 * 1024 bytes)
                            const maxOriginalBytes = 10 * 1024 * 1024;
                            if (file.size > maxOriginalBytes) {
                                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                                const msg =
                                    `Ukuran foto (${sizeMB}MB) melebihi batas maksimal 10MB. Silakan pilih foto lain.`;
                                if (slot === 1) this.errorMsg1 = msg;
                                else this.errorMsg2 = msg;
                                this.$dispatch('toast', {
                                    type: 'error',
                                    message: msg
                                });
                                return;
                            }

                            if (slot === 1) {
                                this.isProcessing1 = true;
                                this.progress1 = 15;
                                this.statusText1 = 'Membaca gambar...';
                                this.compressInfo1 = '';
                                this.errorMsg1 = '';
                            } else {
                                this.isProcessing2 = true;
                                this.progress2 = 15;
                                this.statusText2 = 'Membaca gambar...';
                                this.compressInfo2 = '';
                                this.errorMsg2 = '';
                            }

                            try {
                                if (slot === 1) {
                                    this.statusText1 = 'Mengompresi ke WebP (maks. 100KB)...';
                                    this.progress1 = 30;
                                } else {
                                    this.statusText2 = 'Mengompresi ke WebP (maks. 100KB)...';
                                    this.progress2 = 30;
                                }

                                // Kompresi ke format WebP maks 100KB di client browser
                                const compressedWebpFile = await this.compressToWebp(
                                    file,
                                    100 * 1024,
                                    (stepPct) => {
                                        const cur = 30 + Math.round(stepPct * 0.25);
                                        if (slot === 1) this.progress1 = cur;
                                        else this.progress2 = cur;
                                    }
                                );

                                const origSizeStr = (file.size >= 1024 * 1024) ?
                                    (file.size / (1024 * 1024)).toFixed(1) + 'MB' :
                                    Math.round(file.size / 1024) + 'KB';
                                const finalSizeStr = Math.round(compressedWebpFile.size / 1024) + 'KB';
                                const infoStr = `${origSizeStr} → ${finalSizeStr} (WebP)`;

                                if (slot === 1) {
                                    this.compressInfo1 = infoStr;
                                    this.statusText1 = 'Mengunggah ke server...';
                                    this.progress1 = 60;
                                } else {
                                    this.compressInfo2 = infoStr;
                                    this.statusText2 = 'Mengunggah ke server...';
                                    this.progress2 = 60;
                                }

                                // Upload file yang sudah terkompresi ke Livewire
                                const targetProp = slot === 1 ? 'foto1' : 'foto2';

                                await new Promise((resolve, reject) => {
                                    this.$wire.upload(
                                        targetProp,
                                        compressedWebpFile,
                                        () => {
                                            if (slot === 1) {
                                                this.progress1 = 100;
                                                this.statusText1 = 'Selesai!';
                                                this.isProcessing1 = false;
                                            } else {
                                                this.progress2 = 100;
                                                this.statusText2 = 'Selesai!';
                                                this.isProcessing2 = false;
                                            }
                                            resolve();
                                        },
                                        (error) => {
                                            reject(new Error(error ||
                                                'Gagal mengunggah foto ke server.'));
                                        },
                                        (event) => {
                                            if (event.detail && event.detail.progress) {
                                                const uploadPct = event.detail.progress;
                                                const totalPct = 60 + Math.round(uploadPct *
                                                    0.4);
                                                if (slot === 1) {
                                                    this.progress1 = totalPct;
                                                    this.statusText1 =
                                                        `Mengunggah... ${uploadPct}%`;
                                                } else {
                                                    this.progress2 = totalPct;
                                                    this.statusText2 =
                                                        `Mengunggah... ${uploadPct}%`;
                                                }
                                            }
                                        }
                                    );
                                });

                            } catch (err) {
                                console.error('Error compression/upload:', err);
                                const errMsg = err.message || 'Gagal memproses gambar.';
                                if (slot === 1) {
                                    this.errorMsg1 = errMsg;
                                    this.isProcessing1 = false;
                                } else {
                                    this.errorMsg2 = errMsg;
                                    this.isProcessing2 = false;
                                }
                                this.$dispatch('toast', {
                                    type: 'error',
                                    message: errMsg
                                });
                            }
                        },

                        compressToWebp(file, maxBytes = 100 * 1024, onProgress = null) {
                            return new Promise((resolve, reject) => {
                                const reader = new FileReader();
                                reader.onerror = () => reject(new Error(
                                    'Gagal membaca file gambar dari perangkat.'));
                                reader.onload = (e) => {
                                    const img = new Image();
                                    img.onerror = () => reject(new Error(
                                        'Gagal memuat gambar ke browser.'));
                                    img.onload = async () => {
                                        try {
                                            const origW = img.naturalWidth || img.width;
                                            const origH = img.naturalHeight || img
                                                .height;

                                            // Batasi dimensi awal jika foto sangat besar (misal 12MP/4K)
                                            let destW = origW;
                                            let destH = origH;
                                            const maxDim = 1280;
                                            if (destW > maxDim || destH > maxDim) {
                                                if (destW > destH) {
                                                    destH = Math.round((destH *
                                                        maxDim) / destW);
                                                    destW = maxDim;
                                                } else {
                                                    destW = Math.round((destW *
                                                        maxDim) / destH);
                                                    destH = maxDim;
                                                }
                                            }

                                            const canvas = document.createElement(
                                                'canvas');
                                            canvas.width = destW;
                                            canvas.height = destH;
                                            const ctx = canvas.getContext('2d');
                                            ctx.drawImage(img, 0, 0, destW, destH);

                                            let currentCanvas = canvas;
                                            let quality = 0.82;
                                            let iterations = 0;
                                            const maxIterations = 15;

                                            while (iterations < maxIterations) {
                                                iterations++;
                                                if (onProgress) onProgress(Math.min(100,
                                                    iterations * 8));

                                                const blob = await new Promise((
                                                    res) => {
                                                    currentCanvas.toBlob(res,
                                                        'image/webp',
                                                        quality);
                                                });

                                                if (!blob) {
                                                    throw new Error(
                                                        'Browser tidak mendukung konversi WebP.'
                                                    );
                                                }

                                                if (blob.size <= maxBytes) {
                                                    const baseName = (file.name ||
                                                            'foto')
                                                        .replace(/\.[^/.]+$/, '')
                                                        .replace(/[^a-zA-Z0-9_-]/g,
                                                            '_');
                                                    const webpFile = new File([blob],
                                                        `${baseName}.webp`, {
                                                            type: 'image/webp',
                                                            lastModified: Date.now()
                                                        });
                                                    resolve(webpFile);
                                                    return;
                                                }

                                                // Jika masih melebihi 100KB, kurangi quality atau resolusi canvas
                                                if (quality > 0.35) {
                                                    quality = Math.max(0.2, quality -
                                                        0.12);
                                                } else {
                                                    if (currentCanvas.width > 400 &&
                                                        currentCanvas.height > 300) {
                                                        const nextW = Math.round(
                                                            currentCanvas.width *
                                                            0.78);
                                                        const nextH = Math.round(
                                                            currentCanvas.height *
                                                            0.78);
                                                        const scaledCvs = document
                                                            .createElement('canvas');
                                                        scaledCvs.width = nextW;
                                                        scaledCvs.height = nextH;
                                                        const sCtx = scaledCvs
                                                            .getContext('2d');
                                                        sCtx.drawImage(currentCanvas, 0,
                                                            0, nextW, nextH);
                                                        currentCanvas = scaledCvs;
                                                        quality = 0.75;
                                                    } else {
                                                        quality = Math.max(0.05,
                                                            quality - 0.05);
                                                        if (quality <= 0.05) {
                                                            const baseName = (file
                                                                    .name || 'foto')
                                                                .replace(/\.[^/.]+$/,
                                                                    '')
                                                                .replace(
                                                                    /[^a-zA-Z0-9_-]/g,
                                                                    '_');
                                                            const webpFile = new File([
                                                                    blob
                                                                ],
                                                                `${baseName}.webp`, {
                                                                    type: 'image/webp',
                                                                    lastModified: Date
                                                                        .now()
                                                                });
                                                            resolve(webpFile);
                                                            return;
                                                        }
                                                    }
                                                }
                                            }

                                            // Fallback
                                            const finalBlob = await new Promise(res =>
                                                currentCanvas.toBlob(res,
                                                    'image/webp', quality));
                                            const baseName = (file.name || 'foto')
                                                .replace(/\.[^/.]+$/, '')
                                                .replace(/[^a-zA-Z0-9_-]/g, '_');
                                            resolve(new File([finalBlob || blob],
                                                `${baseName}.webp`, {
                                                    type: 'image/webp'
                                                }));

                                        } catch (err) {
                                            reject(err);
                                        }
                                    };
                                    img.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            });
                        }
                    }));
                }
            }

            if (window.Alpine) {
                registerUploadHandler();
            } else {
                document.addEventListener('alpine:init', registerUploadHandler);
            }
        })();
    </script>
</div>
