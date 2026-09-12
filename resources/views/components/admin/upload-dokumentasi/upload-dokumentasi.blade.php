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
        <form wire:submit="save">
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
                                                Foto Konsumsi Shift Siang
                                            @elseif ($shift === 'malam')
                                                Foto Konsumsi Shift Malam
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
                                                {{-- Preview Foto 1 Baru --}}
                                                <div
                                                    class="w-full aspect-4/3 bg-base-200 rounded-xl overflow-hidden border border-base-300 shadow-inner">
                                                    <img src="{{ $foto1->temporaryUrl() }}" alt="Preview Foto 1"
                                                        class="w-full h-full object-cover">
                                                </div>
                                                <div class="mt-2 flex justify-end">
                                                    <button type="button" wire:click="removeFoto(1)"
                                                        class="text-error hover:text-error/80 text-xs font-semibold flex items-center gap-1 transition-colors">
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
                                                {{-- Input File Foto 1 --}}
                                                <label
                                                    class="relative flex flex-col items-center justify-center w-full aspect-4/3 border-2 border-dashed border-base-300 hover:border-primary rounded-xl cursor-pointer bg-base-200/40 hover:bg-base-200/70 transition-all p-4 text-center">
                                                    <input type="file" wire:model="foto1"
                                                        key="foto1-{{ $uploadIteration }}"
                                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                                        class="hidden">
                                                    <div wire:loading.remove wire:target="foto1"
                                                        class="flex flex-col items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                            class="w-8 h-8 text-base-content/50 mb-2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                        </svg>
                                                        <span class="text-xs font-semibold text-base-content/70">Klik
                                                            untuk unggah</span>
                                                        <span class="text-[10px] text-base-content/40 mt-0.5">JPG, PNG,
                                                            WebP (Maks. 5MB)</span>
                                                    </div>
                                                    <div wire:loading wire:target="foto1"
                                                        class="flex flex-col items-center">
                                                        <span
                                                            class="loading loading-spinner loading-md text-primary mb-2"></span>
                                                        <span class="text-xs text-base-content/60">Mengunggah...</span>
                                                    </div>
                                                </label>

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
                                                {{-- Preview Foto 2 Baru --}}
                                                <div
                                                    class="w-full aspect-4/3 bg-base-200 rounded-xl overflow-hidden border border-base-300 shadow-inner">
                                                    <img src="{{ $foto2->temporaryUrl() }}" alt="Preview Foto 2"
                                                        class="w-full h-full object-cover">
                                                </div>
                                                <div class="mt-2 flex justify-end">
                                                    <button type="button" wire:click="removeFoto(2)"
                                                        class="text-error hover:text-error/80 text-xs font-semibold flex items-center gap-1 transition-colors">
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
                                                {{-- Input File Foto 2 --}}
                                                <label
                                                    class="relative flex flex-col items-center justify-center w-full aspect-4/3 border-2 border-dashed border-base-300 hover:border-primary rounded-xl cursor-pointer bg-base-200/40 hover:bg-base-200/70 transition-all p-4 text-center">
                                                    <input type="file" wire:model="foto2"
                                                        key="foto2-{{ $uploadIteration }}"
                                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                                        class="hidden">
                                                    <div wire:loading.remove wire:target="foto2"
                                                        class="flex flex-col items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor"
                                                            class="w-8 h-8 text-base-content/50 mb-2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                        </svg>
                                                        <span class="text-xs font-semibold text-base-content/70">Klik
                                                            untuk unggah</span>
                                                        <span class="text-[10px] text-base-content/40 mt-0.5">JPG, PNG,
                                                            WebP (Maks. 5MB)</span>
                                                    </div>
                                                    <div wire:loading wire:target="foto2"
                                                        class="flex flex-col items-center">
                                                        <span
                                                            class="loading loading-spinner loading-md text-primary mb-2"></span>
                                                        <span class="text-xs text-base-content/60">Mengunggah...</span>
                                                    </div>
                                                </label>

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
                        <button type="button" wire:click="resetForm" class="btn btn-ghost"
                            wire:loading.attr="disabled">
                            Reset Form
                        </button>
                        <button type="submit" class="btn btn-secondary px-8" wire:loading.attr="disabled"
                            wire:target="save, foto1, foto2">
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
</div>
