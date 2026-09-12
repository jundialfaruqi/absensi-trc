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
                            <h4 class="font-bold text-lg">Informasi Dokumentasi</h4>
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
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Jumlah Porsi --}}
                            <div class="form-control w-full md:col-span-2">
                                <label class="label mb-1 px-1 flex justify-between items-center">
                                    <span class="label-text text-sm font-medium text-base-content">
                                        Jumlah Porsi Makan <span class="text-error">*</span>
                                    </span>
                                    @if ($shift)
                                        <span class="text-xs text-base-content/70">
                                            Maks:
                                            <span class="border-b-2 border-primary font-bold text-primary pb-0.5">
                                                {{ $this->maxPorsi }} Porsi
                                            </span>
                                        </span>
                                    @endif
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="jumlah_porsi" min="0"
                                        max="{{ $this->maxPorsi }}" @disabled(!$shift)
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('jumlah_porsi') input-error @enderror"
                                        placeholder="0">
                                </div>
                                <div class="flex items-center justify-between mt-1 px-1">
                                    @if ($shift)
                                        <span class="text-[11px] text-base-content/60">
                                            Kalkulasi dari absensi hadir dan jadwal shift
                                            {{ $shift === 'siang' ? 'siang' : 'malam' }}.
                                        </span>
                                    @else
                                        <span class="text-[11px] text-base-content/50">
                                            Pilih shift terlebih dahulu untuk melihat kuota porsi.
                                        </span>
                                    @endif
                                    @error('jumlah_porsi')
                                        <span class="text-error text-xs">{{ $message }}</span>
                                    @enderror
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
                                        <span
                                            class="border-b-2 border-primary text-xs font-bold text-primary pb-0.5 uppercase tracking-wide self-start sm:self-auto">
                                            Shift {{ $shift === 'siang' ? 'Siang' : 'Malam' }}
                                        </span>
                                    @endif
                                </div>

                                @if (!$shift)
                                    {{-- Placeholder jika shift belum dipilih --}}
                                    <div
                                        class="flex flex-col items-center justify-center p-8 text-center text-base-content/40 border-2 border-dashed border-base-300 rounded-xl my-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mb-3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        <span class="text-sm font-semibold">Pilih Shift Terlebih Dahulu</span>
                                        <span class="text-xs mt-1">Field upload foto akan aktif setelah shift
                                            dipilih.</span>
                                    </div>
                                @else
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

                                            @if ($foto1)
                                                {{-- Preview Foto 1 Baru (Terkompresi WebP) --}}
                                                <div
                                                    class="w-full aspect-4/3 bg-base-200 rounded-xl overflow-hidden border border-base-300 shadow-inner relative group">
                                                    <img src="{{ $foto1->temporaryUrl() }}" alt="Preview Foto 1"
                                                        class="w-full h-full object-cover">
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
                                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                            class="size-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                        <span>Hapus Foto Utama</span>
                                                    </button>
                                                </div>
                                            @else
                                                {{-- Area Pemilihan Foto 1 (Dengan 2 Tombol: Dokumen & Kamera HP) --}}
                                                <div class="relative flex flex-col items-center justify-center w-full min-h-[220px] aspect-4/3 border-2 border-dashed rounded-xl transition-all p-4 text-center bg-base-200/40"
                                                    :class="{
                                                        'border-primary/60 bg-primary/5': isProcessing1,
                                                        'border-base-300 hover:border-primary/50 hover:bg-base-200/60':
                                                            !isProcessing1
                                                    }"
                                                    @dragover.prevent
                                                    @drop.prevent="if (!isProcessing1 && $event.dataTransfer.files && $event.dataTransfer.files[0]) processFile($event.dataTransfer.files[0], 1)">

                                                    {{-- State Loading: Kompresi & Upload --}}
                                                    <div x-show="isProcessing1" x-cloak
                                                        class="flex flex-col items-center justify-center w-full py-3 px-2">
                                                        <span
                                                            class="loading loading-spinner loading-lg text-primary mb-2.5"></span>
                                                        <span class="text-xs font-bold text-base-content"
                                                            x-text="statusText1"></span>
                                                        <span class="text-[11px] text-base-content/60 mt-1"
                                                            x-show="compressInfo1" x-text="compressInfo1"></span>

                                                        <div class="w-full max-w-[200px] mt-3">
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
                                                        class="flex flex-col items-center justify-center w-full">
                                                        <div
                                                            class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.6"
                                                                stroke="currentColor" class="size-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                            </svg>
                                                        </div>
                                                        <span class="text-xs font-bold text-base-content">Pilih atau
                                                            Ambil Foto Utama</span>
                                                        <span
                                                            class="text-[10px] text-base-content/60 text-center max-w-220px mt-0.5">
                                                            Maks. 10MB dari HP/PC, otomatis dikompres ke WebP
                                                            (&le;100KB)
                                                        </span>

                                                        {{-- 2 Tombol: Dokumen & Kamera HP --}}
                                                        <div
                                                            class="flex flex-wrap items-center justify-center gap-2 mt-3">
                                                            <button type="button" @click="$refs.docInput1.click()"
                                                                class="btn btn-sm btn-outline btn-primary gap-1.5 rounded-lg transition-transform active:scale-95">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.8"
                                                                    stroke="currentColor" class="size-4">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                </svg>
                                                                <span>Pilih Dokumen</span>
                                                            </button>

                                                            <button type="button" @click="$refs.camInput1.click()"
                                                                class="btn btn-sm btn-primary gap-1.5 rounded-lg shadow-xs transition-transform active:scale-95">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.8"
                                                                    stroke="currentColor" class="size-4">
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

                                            @if ($foto2)
                                                {{-- Preview Foto 2 Baru (Terkompresi WebP) --}}
                                                <div
                                                    class="w-full aspect-4/3 bg-base-200 rounded-xl overflow-hidden border border-base-300 shadow-inner relative group">
                                                    <img src="{{ $foto2->temporaryUrl() }}" alt="Preview Foto 2"
                                                        class="w-full h-full object-cover">
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
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                        <span>Hapus Foto Tambahan</span>
                                                    </button>
                                                </div>
                                            @else
                                                {{-- Area Pemilihan Foto 2 (Dengan 2 Tombol: Dokumen & Kamera HP) --}}
                                                <div class="relative flex flex-col items-center justify-center w-full min-h-220px aspect-4/3 border-2 border-dashed rounded-xl transition-all p-4 text-center bg-base-200/40"
                                                    :class="{
                                                        'border-primary/60 bg-primary/5': isProcessing2,
                                                        'border-base-300 hover:border-primary/50 hover:bg-base-200/60':
                                                            !isProcessing2
                                                    }"
                                                    @dragover.prevent
                                                    @drop.prevent="if (!isProcessing2 && $event.dataTransfer.files && $event.dataTransfer.files[0]) processFile($event.dataTransfer.files[0], 2)">

                                                    {{-- State Loading: Kompresi & Upload --}}
                                                    <div x-show="isProcessing2" x-cloak
                                                        class="flex flex-col items-center justify-center w-full py-3 px-2">
                                                        <span
                                                            class="loading loading-spinner loading-lg text-primary mb-2.5"></span>
                                                        <span class="text-xs font-bold text-base-content"
                                                            x-text="statusText2"></span>
                                                        <span class="text-[11px] text-base-content/60 mt-1"
                                                            x-show="compressInfo2" x-text="compressInfo2"></span>

                                                        <div class="w-full max-w-[200px] mt-3">
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
                                                        class="flex flex-col items-center justify-center w-full">
                                                        <div
                                                            class="w-11 h-11 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.6"
                                                                stroke="currentColor" class="size-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                            </svg>
                                                        </div>
                                                        <span class="text-xs font-bold text-base-content">Pilih atau
                                                            Ambil Foto Tambahan</span>
                                                        <span
                                                            class="text-[10px] text-base-content/60 text-center max-w-220px mt-0.5">
                                                            Maks. 10MB dari HP/PC, otomatis dikompres ke WebP
                                                            (&le;100KB)
                                                        </span>

                                                        {{-- 2 Tombol: Dokumen & Kamera HP --}}
                                                        <div
                                                            class="flex flex-wrap items-center justify-center gap-2 mt-3">
                                                            <button type="button" @click="$refs.docInput2.click()"
                                                                class="btn btn-sm btn-outline btn-primary gap-1.5 rounded-lg transition-transform active:scale-95">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.8"
                                                                    stroke="currentColor" class="size-4">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                </svg>
                                                                <span>Pilih Dokumen</span>
                                                            </button>

                                                            <button type="button" @click="$refs.camInput2.click()"
                                                                class="btn btn-sm btn-primary gap-1.5 rounded-lg shadow-xs transition-transform active:scale-95">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.8"
                                                                    stroke="currentColor" class="size-4">
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
                            :disabled="isProcessing1 || isProcessing2" wire:target="save, foto1, foto2">
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                            <span wire:loading.remove wire:target="save">Simpan Data</span>
                        </button>
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

                        resetSlot(slot) {
                            if (slot === 1) {
                                this.isProcessing1 = false;
                                this.progress1 = 0;
                                this.statusText1 = '';
                                this.compressInfo1 = '';
                                this.errorMsg1 = '';
                            } else {
                                this.isProcessing2 = false;
                                this.progress2 = 0;
                                this.statusText2 = '';
                                this.compressInfo2 = '';
                                this.errorMsg2 = '';
                            }
                        },

                        resetAll() {
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
