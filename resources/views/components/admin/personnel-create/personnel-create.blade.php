<div wire:init="load">
    <div x-data="personnelCamera()">
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-base-200">
            <div>
                <h3 class="font-black uppercase text-xl">
                    Tambah Personnel
                </h3>
                <p class="text-sm text-base-content/60 mt-1">Lengkapi data profil dan foto personnel di bawah ini.</p>
            </div>
            <a wire:navigate href="{{ route('personnel') }}" class="btn btn-ghost btn-sm" x-data="{ loading: false }"
                @click="stopCamera(); loading = true" :class="loading ? 'pointer-events-none opacity-50' : ''">
                <span x-show="loading" class="loading loading-spinner loading-xs mr-1"></span>
                <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span x-text="loading ? 'Memuat...' : 'Kembali'"></span>
            </a>
        </div>

        @if ($readyToLoad)
            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Kiri: Data Profil --}}
                    <div class="card bg-base-100 shadow-sm border border-base-200 md:col-span-2">
                        <div class="card-body">
                            <h4 class="font-bold text-lg mb-4">Informasi Pribadi</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6 md:gap-y-4">

                                {{-- Nama --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Nama Lengkap
                                            <span class="text-error">*</span></span>
                                    </label>
                                    <input type="text" wire:model="name"
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('name') input-error @enderror"
                                        placeholder="Cth: John Doe">
                                    @error('name')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- NIK --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">NIK (No Induk
                                            Kependudukan) <span class="text-error">*</span></span>
                                    </label>
                                    <input type="text" wire:model="nik" maxlength="16" pattern="[0-9]*"
                                        inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('nik') input-error @enderror"
                                        placeholder="16 digit NIK personel...">
                                    @error('nik')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                {{-- Nomor HP --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Nomor HP</span>
                                    </label>
                                    <input type="tel" wire:model="nomor_hp" maxlength="13"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('nomor_hp') input-error @enderror"
                                        placeholder="Cth: 08123456789">
                                    @error('nomor_hp')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>


                                {{-- OPD Induk --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Pilih OPD Induk
                                            <span class="text-error">*</span></span>
                                    </label>
                                    <select wire:model="opd_id"
                                        class="select select-bordered focus:select-primary w-full transition-all @error('opd_id') select-error @enderror"
                                        @if (!auth()->user()->hasRole('super-admin')) disabled @endif>
                                        <option value="">-- Pilih OPD --</option>
                                        @foreach ($this->opds as $opd)
                                            <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('opd_id')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Penugasan --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Penugasan <span
                                                class="text-error">*</span></span>
                                    </label>
                                    <select wire:model="penugasan_id"
                                        class="select select-bordered focus:select-primary w-full transition-all @error('penugasan_id') select-error @enderror">
                                        <option value="">-- Pilih Penugasan --</option>
                                        @foreach ($this->penugasans as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('penugasan_id')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- PIN --}}
                                <div class="form-control w-full" x-data="{ show: false }">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">PIN (6 Digit)
                                            <span class="text-error">*</span>
                                        </span>
                                    </label>
                                    <div class="join w-full">
                                        <div class="relative flex-1">
                                            <input x-bind:type="show ? 'text' : 'password'" wire:model="pin"
                                                maxlength="6" pattern="[0-9]*" inputmode="numeric"
                                                class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full pr-10 transition-all join-item @error('pin') input-error @enderror"
                                                placeholder="6 digit PIN otomatis...">
                                            <button type="button" @click="show = !show"
                                                class="absolute inset-y-0 right-0 px-3 flex items-center text-base-content/50 hover:text-base-content focus:outline-none">
                                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5 hidden" :class="{ 'hidden': !show }">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                </svg>
                                            </button>
                                        </div>
                                        <button type="button" wire:click="regeneratePin"
                                            class="btn btn-neutral join-item" title="Generate Ulang"
                                            wire:loading.attr="disabled">
                                            <svg wire:loading.remove wire:target="regeneratePin"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            <span wire:loading wire:target="regeneratePin"
                                                class="loading loading-spinner loading-xs"></span>
                                        </button>
                                    </div>
                                    @error('pin')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Attendance Type --}}
                                <div class="form-control w-full md:col-span-2">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Mode Absensi
                                            <span class="text-error">*</span></span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <label
                                            class="label cursor-pointer bg-base-200/50 p-4 rounded-xl border border-base-300 transition-all hover:bg-base-200">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" wire:model="attendance_type" value="SCHEDULED"
                                                    class="radio radio-primary radio-sm">
                                                <div>
                                                    <span
                                                        class="label-text font-bold block text-base-content/80">Jadwal
                                                        Tetap</span>
                                                    <span
                                                        class="text-[10px] opacity-60 text-wrap text-base-content">Wajib
                                                        mengikuti
                                                        shift
                                                        yang telah diatur.</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label
                                            class="label cursor-pointer bg-base-200/50 p-4 rounded-xl border border-base-300 transition-all hover:bg-base-200">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" wire:model="attendance_type" value="FLEXIBLE"
                                                    class="radio radio-primary radio-sm">
                                                <div>
                                                    <span
                                                        class="label-text font-bold block text-base-content/80">Fleksibel</span>
                                                    <span
                                                        class="text-[10px] opacity-60 text-wrap text-base-content">Bisa
                                                        absen kapan
                                                        saja
                                                        tanpa terikat
                                                        shift.</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    @error('attendance_type')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-control w-full md:col-span-2">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Pilih
                                            Kantor</span>
                                    </label>
                                    <select wire:model.live="kantor_id"
                                        class="select select-bordered focus:select-primary w-full transition-all @error('kantor_id') select-error @enderror">
                                        <option value="">-- Tidak Terikat Kantor --</option>
                                        @foreach ($this->kantors as $k)
                                            <option value="{{ $k->id }}">{{ $k->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('kantor_id')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if ($kantor_id)
                                    <div class="form-control w-full md:col-span-2">
                                        <label
                                            class="label w-full cursor-pointer justify-start gap-4 bg-base-200/50 p-4 rounded-xl border border-base-300">
                                            <input type="checkbox" wire:model="wajib_absen_di_lokasi"
                                                class="checkbox checkbox-md checkbox-primary">
                                            <div class="flex-1 min-w-0">
                                                <span
                                                    class="label-text font-bold block uppercase text-xs whitespace-normal text-base-content/70">Wajib
                                                    Absen di Lokasi
                                                    Kantor</span>
                                                <span
                                                    class="text-[10px] text-base-content opacity-60 block whitespace-normal wrap-break-word">Jika
                                                    dicentang, personil tidak bisa absen jika
                                                    berada di luar radius kantor.</span>
                                            </div>
                                        </label>
                                    </div>
                                @endif

                                <div class="form-control w-full md:col-span-2">
                                    <label
                                        class="label w-full cursor-pointer justify-start gap-4 bg-base-200/50 p-4 rounded-xl border border-base-300">
                                        <input type="checkbox" wire:model="face_recognition"
                                            class="checkbox checkbox-md checkbox-secondary">
                                        <div class="flex-1 min-w-0">
                                            <span
                                                class="label-text font-bold block uppercase text-xs whitespace-normal text-base-content/70">Aktifkan
                                                Face Recognition</span>
                                            <span
                                                class="text-[10px] text-base-content opacity-60 block whitespace-normal wrap-break-word">Jika
                                                aktif, personil wajib scan wajah saat absen. Jika tidak, hanya ambil
                                                foto
                                                biasa.</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-control w-full md:col-span-2">
                                    <label
                                        class="label w-full cursor-pointer justify-start gap-4 bg-primary/5 p-4 rounded-xl border border-primary/20">
                                        <input type="checkbox" wire:model="auto_create_device"
                                            class="checkbox checkbox-md checkbox-primary">
                                        <div class="flex-1 min-w-0">
                                            <span
                                                class="label-text font-bold block uppercase text-xs whitespace-normal text-primary">Otomatis
                                                Buat Lisensi Perangkat</span>
                                            <span
                                                class="text-[10px] text-base-content opacity-60 block whitespace-normal wrap-break-word">Daftarkan
                                                perangkat personal untuk personnel ini secara otomatis dan buatkan
                                                license
                                                key.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-t border-base-200 p-6 flex justify-end gap-3">
                            <a wire:navigate href="{{ route('personnel') }}" class="btn btn-ghost"
                                x-data="{ loading: false }" @click="stopCamera(); loading = true"
                                :class="loading ? 'pointer-events-none opacity-50' : ''">
                                <span x-show="loading" class="loading loading-spinner loading-xs mr-1"></span>
                                <span x-text="loading ? 'Memuat...' : 'Batal'"></span>
                            </a>
                            <button type="submit" class="btn btn-secondary px-8" wire:loading.attr="disabled"
                                wire:target="save">
                                <span wire:loading wire:target="save"
                                    class="loading loading-spinner loading-xs"></span>
                                <span wire:loading.remove wire:target="save">Simpan Data</span>
                            </button>
                        </div>
                    </div>

                    {{-- Kanan: Foto Autentikasi --}}
                    <div class="card bg-base-100 shadow-sm border border-base-200 md:col-span-1 h-fit">
                        <div class="card-body p-6">
                            <div class="flex flex-col items-center text-center">
                                <h4 class="font-bold text-lg mb-1">Foto Autentikasi</h4>
                                <p class="text-xs text-base-content/60 mb-4">Gunakan kamera atau upload foto</p>
                            </div>

                            <div class="form-control w-full">
                                <div class="flex flex-col gap-4 items-center">
                                    {{-- Preview Foto --}}
                                    <div
                                        class="relative w-full max-w-70 aspect-5/6 bg-base-200 rounded-xl overflow-hidden border-2 border-base-300 shadow-inner flex items-center justify-center">
                                        {{-- Preview Client-Side Instan --}}
                                        <template x-if="capturedPhotoPreview">
                                            <img :src="capturedPhotoPreview" alt="Preview Foto"
                                                class="w-full h-full object-cover">
                                        </template>

                                        {{-- Preview Server Livewire / Database --}}
                                        <div x-show="!capturedPhotoPreview"
                                            class="w-full h-full flex items-center justify-center">
                                            @if ($foto && !$errors->has('foto'))
                                                <img x-ref="previewImage" src="{{ $foto->temporaryUrl() }}"
                                                    alt="Preview Foto" class="w-full h-full object-cover">
                                            @else
                                                <div
                                                    class="flex flex-col items-center justify-center gap-2 text-base-content/40 p-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-14 h-14">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                    </svg>
                                                    <span class="text-xs font-medium">Belum ada foto</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Overlay Bounding Box untuk upload file --}}
                                        <div x-show="!isCameraOpen && uploadedFaceBox.found"
                                            class="absolute pointer-events-none transition-all duration-150 ease-out border-2 rounded-lg z-20 border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.45)]"
                                            :style="`left: ${uploadedFaceBox.left}; top: ${uploadedFaceBox.top}; width: ${uploadedFaceBox.width}; height: ${uploadedFaceBox.height};`">
                                            <span
                                                class="absolute text-white text-[9px] font-bold px-2 py-0.5 rounded shadow flex items-center gap-1 whitespace-nowrap bg-emerald-500 z-30 pointer-events-none"
                                                style="bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Wajah Terdeteksi</span>
                                            </span>
                                        </div>

                                        {{-- Processing Image Overlay saat Upload File --}}
                                        <div x-show="isUploadingFile"
                                            class="absolute inset-0 bg-base-100/80 backdrop-blur-sm flex flex-col items-center justify-center text-base-content z-20">
                                            <span class="loading loading-spinner loading-md mb-2 text-primary"></span>
                                            <span class="text-[10px] uppercase font-bold tracking-widest">Memproses
                                                Wajah...</span>
                                        </div>
                                    </div>

                                    {{-- Tombol Aksi di Card Kanan --}}
                                    <div class="flex flex-col gap-2.5 w-full max-w-70">
                                        {{-- Tombol Buka Kamera (Memicu Modal Pop-up di Tengah Atas) --}}
                                        <button type="button" @click="startCamera()"
                                            class="btn btn-primary btn-sm w-full gap-2 font-semibold shadow-sm"
                                            :disabled="isStartingCamera">
                                            <span x-show="isStartingCamera"
                                                class="loading loading-spinner loading-xs"></span>
                                            <svg x-show="!isStartingCamera" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                            </svg>
                                            <span
                                                x-text="isStartingCamera ? 'Membuka Kamera...' : 'Buka Kamera'"></span>
                                        </button>

                                        {{-- Tombol Upload File --}}
                                        <div class="relative w-full">
                                            <input type="file" x-ref="fileInput" class="hidden" accept="image/*"
                                                @change="handleFileUpload($event)" :disabled="isUploadingFile">
                                            <button type="button" @click="$refs.fileInput.click()"
                                                class="btn btn-outline btn-sm w-full gap-2"
                                                :disabled="isUploadingFile">
                                                <span x-show="isUploadingFile"
                                                    class="loading loading-spinner loading-xs"></span>
                                                <svg x-show="!isUploadingFile" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.5V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                                </svg>
                                                <span x-text="isUploadingFile ? 'Memproses...' : 'Upload File'"></span>
                                            </button>
                                        </div>

                                        <p
                                            class="text-[10px] text-base-content/50 leading-relaxed italic text-center mt-1">
                                            Direkomendasikan mengambil foto langsung agar AI dapat mendeteksi wajah
                                            dengan lebih akurat.
                                        </p>

                                        @error('foto')
                                            <span
                                                class="text-red-500 text-xs mt-1 block text-center">{{ $message }}</span>
                                        @enderror

                                        {{-- Biometric Status Badges (Dual-Stack 128D / 512D) --}}
                                        <div class="flex flex-wrap justify-center gap-1.5 pt-1">
                                            {{-- 128D Web Status --}}
                                            @if ($face_descriptor)
                                                <span
                                                    class="badge badge-success badge-xs gap-1 py-1.5 px-2 text-[10px]"
                                                    title="128-D Vector dari web face-api.js">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    128D Ready
                                                </span>
                                            @else
                                                <span class="badge badge-ghost badge-xs gap-1 py-1.5 px-2 text-[10px]"
                                                    title="128-D belum diekstrak">
                                                    128D Kosong
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Kamera Live AI (Muncul di tengah layar agak ke top) --}}
                <div x-show="isCameraOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-9999 flex items-start justify-center pt-6 sm:pt-10 px-4 bg-black/75 backdrop-blur-sm overflow-y-auto"
                    @keydown.escape.window="stopCamera()">

                    {{-- Backdrop Click to Close --}}
                    <div class="fixed inset-0" @click="stopCamera()"></div>

                    {{-- Modal Card --}}
                    <div class="relative bg-base-100 dark:bg-base-200 text-base-content rounded-2xl shadow-2xl border border-base-300 w-full max-w-md p-5 flex flex-col gap-4 z-10 animate-in fade-in zoom-in-95 duration-200"
                        @click.stop>

                        {{-- Header Modal --}}
                        <div class="flex items-center justify-between border-b border-base-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                <h4 class="font-bold text-base text-base-content"
                                    x-text="capturedImage ? 'Review Hasil Foto' : 'Perekaman Wajah'"></h4>
                            </div>
                            <button type="button" @click="stopCamera()"
                                class="btn btn-circle btn-ghost btn-sm text-base-content/60 hover:text-base-content"
                                :disabled="isSavingPhoto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Camera Viewport / Preview Container --}}
                        <div
                            class="relative w-full max-w-72 sm:max-w-80 aspect-5/6 mx-auto bg-black overflow-hidden border-2 border-base-300 shadow-inner">
                            <video x-show="!capturedImage" x-ref="video" autoplay muted playsinline
                                class="w-full h-full object-cover" style="transform: scaleX(-1);"></video>
                            <canvas x-ref="canvas" class="hidden"></canvas>

                            {{-- Preview Foto Hasil Jepret --}}
                            <template x-if="capturedImage">
                                <img :src="capturedImage" alt="Hasil Foto"
                                    class="w-full h-full object-cover animate-in fade-in duration-200">
                            </template>

                            {{-- Dynamic Face Bounding Box Overlay (Garis Pelacak Wajah Real-Time - hanya saat kamera aktif) --}}
                            <div x-show="!capturedImage && detectedFaceBox.found"
                                class="absolute pointer-events-none transition-all duration-150 ease-out border-2 rounded-none z-20"
                                :class="poseStatus === 'PERFECT' ?
                                    'border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.55)]' : (
                                        poseStatus === 'ERROR' ?
                                        'border-rose-500 shadow-[0_0_15px_rgba(244,63,94,0.55)]' : (
                                            poseStatus === 'WARNING' ?
                                            'border-amber-400 shadow-[0_0_15px_rgba(251,191,36,0.5)]' :
                                            'border-emerald-400 shadow-[0_0_15px_rgba(52,211,153,0.45)]'))"
                                :style="`left: ${detectedFaceBox.left}; top: ${detectedFaceBox.top}; width: ${detectedFaceBox.width}; height: ${detectedFaceBox.height};`">

                                <!-- Tag Status Wajah (Tampil Tepat di Atas Garis HUD) -->
                                <span
                                    class="absolute text-white text-[9px] font-bold px-2 py-0.5 rounded-none shadow flex items-center gap-1 whitespace-nowrap transition-colors duration-150 z-30 pointer-events-none"
                                    style="bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);"
                                    :class="poseStatus === 'PERFECT' ? 'bg-emerald-500' : (poseStatus === 'ERROR' ?
                                        'bg-rose-500' : (poseStatus === 'WARNING' ?
                                            'bg-amber-500 text-neutral-900' : 'bg-emerald-500'))">
                                    <template x-if="poseStatus === 'PERFECT'">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    <template x-if="poseStatus === 'WARNING'">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </template>
                                    <template x-if="poseStatus === 'ERROR'">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </template>
                                    <span
                                        x-text="poseStatus === 'PERFECT' ? 'Wajah Sempurna' : (poseStatus === 'ERROR' ? (faceCount > 1 ? '> 1 Wajah' : 'Posisi Salah') : (poseStatus === 'WARNING' ? (posePills.tilt !== 'Tegak' && posePills.tilt !== '-' ? posePills.tilt : (posePills.yaw !== 'Lurus' && posePills.yaw !== '-' ? posePills.yaw : posePills.pitch)) : 'Wajah Terdeteksi'))"></span>
                                </span>
                            </div>

                            {{-- Face Guide Overlay (Circle HUD - hanya saat kamera aktif) --}}
                            <div x-show="!capturedImage"
                                class="absolute inset-0 pointer-events-none flex items-center justify-center z-10 transition-all duration-300">
                                <svg class="w-full h-full transition-all duration-300" viewBox="0 0 160 192"
                                    fill="none" xmlns="http://www.w3.org/2000/svg"
                                    :style="poseStatus === 'PERFECT'
                                        ?
                                        'filter: drop-shadow(0 0 8px rgba(52,211,153,0.7));' :
                                        (poseStatus === 'ERROR' ?
                                            'filter: drop-shadow(0 0 8px rgba(244,63,94,0.7));' :
                                            (poseStatus === 'WARNING' ?
                                                'filter: drop-shadow(0 0 6px rgba(251,191,36,0.6));' :
                                                ''))">
                                    <circle cx="80" cy="88" r="52"
                                        :stroke="poseStatus === 'PERFECT'
                                            ?
                                            'rgba(52, 211, 153, 0.95)' :
                                            (poseStatus === 'ERROR' ?
                                                'rgba(244, 63, 94, 0.95)' :
                                                (poseStatus === 'WARNING' ?
                                                    'rgba(251, 191, 36, 0.9)' :
                                                    'rgba(255, 255, 255, 0.6)'))"
                                        stroke-width="2" stroke-dasharray="6 6"
                                        class="transition-colors duration-300" />
                                </svg>
                            </div>

                            {{-- Loading Models Overlay --}}
                            <div x-show="isLoadingModels"
                                class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white z-10">
                                <span class="loading loading-spinner loading-xs mb-2"></span>
                                <span class="text-[8px] uppercase font-bold tracking-widest">AI Engine...</span>
                            </div>

                            {{-- Processing Image Overlay saat Jepret --}}
                            <div x-show="isCapturing"
                                class="absolute inset-0 bg-base-100/80 backdrop-blur-sm flex flex-col items-center justify-center text-base-content z-20">
                                <span class="loading loading-spinner loading-md mb-2 text-primary"></span>
                                <span class="text-[10px] uppercase font-bold tracking-widest">Memproses Wajah...</span>
                            </div>

                            {{-- Processing Simpan Foto Overlay --}}
                            <div x-show="isSavingPhoto"
                                class="absolute inset-0 bg-base-100/80 backdrop-blur-sm flex flex-col items-center justify-center text-base-content z-30">
                                <span class="loading loading-spinner loading-md mb-2 text-primary"></span>
                                <span class="text-[10px] uppercase font-bold tracking-widest">Menyimpan Foto...</span>
                            </div>
                        </div>

                        {{-- Mode 1: Live HUD Telemetry Bar (Saat Kamera Aktif) --}}
                        <div x-show="!capturedImage"
                            class="w-full max-w-72 sm:max-w-80 mx-auto rounded-none p-2.5 flex flex-col gap-2 border transition-all duration-200"
                            :class="poseStatus === 'PERFECT'
                                ?
                                'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400 shadow-sm shadow-emerald-500/10' :
                                (poseStatus === 'ERROR' ?
                                    'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400 shadow-sm shadow-rose-500/10' :
                                    (poseStatus === 'WARNING' ?
                                        'bg-amber-500/10 border-amber-500/30 text-amber-700 dark:text-amber-400 shadow-sm shadow-amber-500/10' :
                                        'bg-base-200/60 border-base-300 text-base-content/70'))">

                            {{-- Status Utama & Instruksi Cerdas --}}
                            <div class="flex items-center gap-2">
                                <span
                                    class="shrink-0 flex items-center justify-center size-6 rounded-none transition-colors"
                                    :class="poseStatus === 'PERFECT'
                                        ?
                                        'bg-emerald-500 text-white' :
                                        (poseStatus === 'ERROR' ?
                                            'bg-rose-500 text-white' :
                                            (poseStatus === 'WARNING' ?
                                                'bg-amber-500 text-neutral-900' :
                                                'bg-base-300 text-base-content/50'))">
                                    <template x-if="poseStatus === 'PERFECT'">
                                        <svg class="size-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    <template x-if="poseStatus === 'ERROR'">
                                        <svg class="size-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </template>
                                    <template x-if="poseStatus === 'WARNING'">
                                        <svg class="size-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </template>
                                    <template x-if="poseStatus === 'IDLE'">
                                        <svg class="size-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </template>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-bold leading-tight truncate" x-text="poseMessage"></p>
                                </div>
                            </div>

                            {{-- Telemetry Pills (4 Metrik Geometri Wajah) --}}
                            <div class="grid grid-cols-4 gap-1 text-[9px] font-bold">
                                {{-- Jumlah Wajah --}}
                                <div class="px-1.5 py-1 rounded-none flex flex-col items-center justify-center text-center transition-colors"
                                    :class="faceCount === 1 ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : (
                                        faceCount > 1 ?
                                        'bg-rose-500/20 text-rose-600 dark:text-rose-400 font-extrabold animate-pulse' :
                                        'bg-base-300/40 text-base-content/40')">
                                    <span
                                        class="text-[7.5px] uppercase opacity-70 font-semibold tracking-tighter">Wajah</span>
                                    <span
                                        x-text="faceCount > 0 ? (faceCount > 1 ? faceCount + ' Wajah' : '1 Orang') : '-'"></span>
                                </div>

                                {{-- Kemiringan / Tilt --}}
                                <div class="px-1.5 py-1 rounded-none flex flex-col items-center justify-center text-center transition-colors"
                                    :class="posePills.tilt === 'Tegak' ?
                                        'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : (posePills
                                            .tilt !== '-' ?
                                            'bg-amber-500/20 text-amber-700 dark:text-amber-400' :
                                            'bg-base-300/40 text-base-content/40')">
                                    <span
                                        class="text-[7.5px] uppercase opacity-70 font-semibold tracking-tighter">Kemiringan</span>
                                    <span x-text="posePills.tilt"></span>
                                </div>

                                {{-- Arah / Yaw --}}
                                <div class="px-1.5 py-1 rounded-none flex flex-col items-center justify-center text-center transition-colors"
                                    :class="posePills.yaw === 'Lurus' ?
                                        'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : (posePills
                                            .yaw !== '-' ?
                                            'bg-amber-500/20 text-amber-700 dark:text-amber-400' :
                                            'bg-base-300/40 text-base-content/40')">
                                    <span
                                        class="text-[7.5px] uppercase opacity-70 font-semibold tracking-tighter">Pandangan</span>
                                    <span x-text="posePills.yaw"></span>
                                </div>

                                {{-- Sudut Dagu / Pitch --}}
                                <div class="px-1.5 py-1 rounded-none flex flex-col items-center justify-center text-center transition-colors"
                                    :class="posePills.pitch === 'Pas' ?
                                        'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : (posePills
                                            .pitch !== '-' ?
                                            'bg-amber-500/20 text-amber-700 dark:text-amber-400' :
                                            'bg-base-300/40 text-base-content/40')">
                                    <span
                                        class="text-[7.5px] uppercase opacity-70 font-semibold tracking-tighter">Sudut
                                        Dagu</span>
                                    <span x-text="posePills.pitch"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Mode 2: Info Banner Preview Hasil Foto --}}
                        <div x-show="capturedImage"
                            class="w-full max-w-72 sm:max-w-80 mx-auto rounded-none p-2.5 flex items-center gap-2.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400">
                            <span
                                class="shrink-0 flex items-center justify-center size-6 rounded-none bg-emerald-500 text-white">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold leading-tight">Foto Berhasil Diambil</p>
                                <p class="text-[10px] text-base-content/70">Wajah siap disimpan sebagai data biometrik
                                    autentikasi.</p>
                            </div>
                        </div>

                        {{-- Action Buttons Mode 1: Saat Kamera Aktif (!capturedImage) --}}
                        <div x-show="!capturedImage"
                            class="flex items-center gap-3 w-full max-w-72 sm:max-w-80 mx-auto pt-1">
                            <button type="button" @click="stopCamera()" class="btn btn-sm btn-ghost flex-1">
                                Batal
                            </button>
                            <button type="button" @click="capture()"
                                class="btn btn-sm flex-1 transition-all duration-200"
                                :class="isPoseValid
                                    ?
                                    'btn-success text-white shadow-lg shadow-emerald-500/30 font-black' :
                                    'btn-primary'"
                                :disabled="isCapturing">
                                <span x-show="isCapturing" class="loading loading-spinner loading-xs"></span>
                                <span x-show="!isCapturing" class="flex items-center justify-center gap-1.5">
                                    <svg x-show="isPoseValid" class="w-3.5 h-3.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                    </svg>
                                    <span
                                        x-text="isCapturing ? 'Memproses...' : (isPoseValid ? 'Jepret Pas!' : 'Jepret')"></span>
                                </span>
                            </button>
                        </div>

                        {{-- Action Buttons Mode 2: Saat Review Hasil Foto (capturedImage) --}}
                        <div x-show="capturedImage"
                            class="flex items-center gap-3 w-full max-w-72 sm:max-w-80 mx-auto pt-1">
                            <button type="button" @click="retakePhoto()"
                                class="btn btn-sm btn-outline flex-1 gap-1.5" :disabled="isSavingPhoto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Ulangi Foto</span>
                            </button>
                            <button type="button" @click="savePhoto()"
                                class="btn btn-sm btn-success text-white flex-1 gap-1.5 font-bold shadow-md shadow-emerald-500/20"
                                :disabled="isSavingPhoto">
                                <span x-show="isSavingPhoto" class="loading loading-spinner loading-xs"></span>
                                <svg x-show="!isSavingPhoto" class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="isSavingPhoto ? 'Menyimpan...' : 'Simpan Foto'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Kiri: Data Profil --}}
                <div class="card bg-base-100 shadow-sm border border-base-200 md:col-span-2">
                    <div class="card-body">
                        <div class="skeleton h-6 w-40 mb-4"></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6 md:gap-y-4">
                            @for ($i = 0; $i < 8; $i++)
                                <div class="form-control w-full">
                                    <div class="skeleton h-4 w-24 mb-2"></div>
                                    <div class="skeleton h-12 w-full"></div>
                                </div>
                            @endfor
                            <div class="form-control w-full md:col-span-2 mt-2">
                                <div class="skeleton h-4 w-32 mb-2"></div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="skeleton h-24 w-full rounded-xl"></div>
                                    <div class="skeleton h-24 w-full rounded-xl"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer border-t border-base-200 p-6 flex justify-end gap-3">
                        <div class="skeleton h-10 w-24 rounded-lg"></div>
                        <div class="skeleton h-10 w-36 rounded-lg"></div>
                    </div>
                </div>

                {{-- Kanan: Foto --}}
                <div class="card bg-base-100 md:col-span-1 h-fit shadow-sm border border-base-200">
                    <div class="card-body p-6 flex flex-col items-center">
                        <div class="skeleton h-6 w-40 mb-4"></div>
                        <div class="skeleton w-full max-w-70 aspect-5/6 rounded-lg mb-4"></div>
                        <div class="flex gap-2 w-full max-w-70">
                            <div class="skeleton h-8 flex-1 rounded-lg"></div>
                            <div class="skeleton h-8 flex-1 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <script src="{{ asset('assets/js/face-api.min.js') }}"></script>
        <script>
            (function() {
                const initPersonnelCamera = () => {
                    if (window.Alpine && !Alpine.data('personnelCamera')) {
                        Alpine.data('personnelCamera', () => ({
                            isCameraOpen: false,
                            isLoadingModels: false,
                            isCapturing: false,
                            isStartingCamera: false,
                            isUploadingFile: false,
                            isSavingPhoto: false,
                            capturedImage: null,
                            capturedPhotoPreview: null,
                            pendingCroppedFile: null,
                            pendingDescriptor: null,
                            stream: null,
                            faceApiLoaded: false,
                            detectedFaceBox: {
                                left: '0%',
                                top: '0%',
                                width: '0%',
                                height: '0%',
                                found: false
                            },
                            uploadedFaceBox: {
                                left: '0%',
                                top: '0%',
                                width: '0%',
                                height: '0%',
                                found: false
                            },
                            trackingInterval: null,
                            faceCount: 0,
                            poseStatus: 'IDLE',
                            poseMessage: 'Posisikan wajah di dalam lingkaran',
                            posePills: {
                                count: '-',
                                tilt: '-',
                                yaw: '-',
                                pitch: '-'
                            },
                            isPoseValid: false,

                            async startCamera() {
                                this.isStartingCamera = true;
                                this.capturedImage = null;
                                this.pendingCroppedFile = null;
                                this.pendingDescriptor = null;
                                try {
                                    if (!this.faceApiLoaded) {
                                        await this.loadModels();
                                    }

                                    this.stream = await navigator.mediaDevices.getUserMedia({
                                        video: {
                                            width: {
                                                ideal: 640
                                            },
                                            height: {
                                                ideal: 480
                                            },
                                            facingMode: "user"
                                        }
                                    });
                                    this.isCameraOpen = true;
                                    this.$nextTick(async () => {
                                        if (this.$refs.video) {
                                            this.$refs.video.srcObject = this.stream;
                                            await this.$refs.video.play().catch(() => {});
                                            this.startFaceTracking();
                                        }
                                    });
                                } catch (err) {
                                    console.error("Error accessing camera: ", err);
                                    alert("Tidak dapat mengakses kamera: " + (err.message ||
                                        "Pastikan izin akses kamera telah diberikan di browser."
                                    ));
                                    this.isCameraOpen = false;
                                } finally {
                                    this.isStartingCamera = false;
                                }
                            },

                            startFaceTracking() {
                                this.stopFaceTracking();
                                this.trackingInterval = setInterval(async () => {
                                    if (!this.isCameraOpen || !this.faceApiLoaded || this
                                        .isCapturing) return;
                                    try {
                                        const video = this.$refs.video;
                                        if (!video || video.paused || video.ended || !video
                                            .videoWidth) return;

                                        const detections = await faceapi.detectAllFaces(
                                            video,
                                            new faceapi.TinyFaceDetectorOptions({
                                                inputSize: 224,
                                                scoreThreshold: 0.5
                                            })
                                        ).withFaceLandmarks();

                                        if (!detections || detections.length === 0) {
                                            this.detectedFaceBox = {
                                                left: '0%',
                                                top: '0%',
                                                width: '0%',
                                                height: '0%',
                                                found: false
                                            };
                                            this.faceCount = 0;
                                            this.poseStatus = 'IDLE';
                                            this.poseMessage =
                                                'Posisikan wajah di dalam lingkaran';
                                            this.posePills = {
                                                count: '-',
                                                tilt: '-',
                                                yaw: '-',
                                                pitch: '-'
                                            };
                                            this.isPoseValid = false;
                                            return;
                                        }

                                        this.faceCount = detections.length;

                                        if (detections.length > 1) {
                                            const box = detections[0].detection.box;
                                            const vw = video.videoWidth;
                                            const vh = video.videoHeight;
                                            const mirroredX = vw - (box.x + box.width);
                                            this.detectedFaceBox = {
                                                left: (mirroredX / vw * 100).toFixed(1) +
                                                    '%',
                                                top: Math.max(6.5, (box.y / vh * 100))
                                                    .toFixed(1) + '%',
                                                width: (box.width / vw * 100).toFixed(1) +
                                                    '%',
                                                height: (box.height / vh * 100).toFixed(1) +
                                                    '%',
                                                found: true
                                            };
                                            this.poseStatus = 'ERROR';
                                            this.poseMessage = 'Terdeteksi ' + detections
                                                .length + ' wajah! Harap 1 orang saja';
                                            this.posePills = {
                                                count: detections.length + ' Orang',
                                                tilt: '-',
                                                yaw: '-',
                                                pitch: '-'
                                            };
                                            this.isPoseValid = false;
                                            return;
                                        }

                                        // 1 Wajah Terdeteksi
                                        const primary = detections[0];
                                        const box = primary.detection.box;
                                        const landmarks = primary.landmarks;
                                        const vw = video.videoWidth;
                                        const vh = video.videoHeight;
                                        const mirroredX = vw - (box.x + box.width);
                                        this.detectedFaceBox = {
                                            left: (mirroredX / vw * 100).toFixed(1) + '%',
                                            top: Math.max(6.5, (box.y / vh * 100)).toFixed(
                                                1) + '%',
                                            width: (box.width / vw * 100).toFixed(1) + '%',
                                            height: (box.height / vh * 100).toFixed(1) +
                                                '%',
                                            found: true
                                        };

                                        // Hitung Titik Landmark (Mata, Hidung, Dagu)
                                        const leftEye = landmarks.getLeftEye();
                                        const rightEye = landmarks.getRightEye();
                                        const leftEyeCenter = {
                                            x: leftEye.reduce((s, p) => s + p.x, 0) /
                                                leftEye.length,
                                            y: leftEye.reduce((s, p) => s + p.y, 0) /
                                                leftEye.length,
                                        };
                                        const rightEyeCenter = {
                                            x: rightEye.reduce((s, p) => s + p.x, 0) /
                                                rightEye.length,
                                            y: rightEye.reduce((s, p) => s + p.y, 0) /
                                                rightEye.length,
                                        };

                                        // 1. Roll / Kemiringan Kepala (Tilt)
                                        const dY = rightEyeCenter.y - leftEyeCenter.y;
                                        const dX = rightEyeCenter.x - leftEyeCenter.x;
                                        const rollAngle = Math.atan2(dY, dX) * (180 / Math.PI);
                                        const isTiltValid = Math.abs(rollAngle) <= 7.5;
                                        let tiltText = 'Tegak';
                                        if (!isTiltValid) {
                                            tiltText = rollAngle > 0 ? 'Miring Kiri' :
                                                'Miring Kanan';
                                        }

                                        // 2. Yaw / Menoleh (Jarak Ujung Hidung ke Mata)
                                        const noseTip = landmarks.positions[30];
                                        const distLeft = Math.abs(noseTip.x - leftEyeCenter.x);
                                        const distRight = Math.abs(rightEyeCenter.x - noseTip
                                            .x);
                                        const yawRatio = distLeft / (distRight || 0.001);
                                        const isYawValid = yawRatio >= 0.60 && yawRatio <= 1.65;
                                        let yawText = 'Lurus';
                                        if (!isYawValid) {
                                            yawText = yawRatio < 0.60 ? 'Menoleh Kiri' :
                                                'Menoleh Kanan';
                                        }

                                        // 3. Pitch / Menunduk vs Mendongak
                                        const eyeLevelY = (leftEyeCenter.y + rightEyeCenter.y) /
                                            2;
                                        const chinTip = landmarks.positions[8];
                                        const eyeToNose = noseTip.y - eyeLevelY;
                                        const noseToChin = chinTip.y - noseTip.y;
                                        const pitchRatio = eyeToNose / (noseToChin || 0.001);
                                        const isPitchValid = pitchRatio >= 0.35 && pitchRatio <=
                                            0.85;
                                        let pitchText = 'Pas';
                                        if (pitchRatio > 0.85) {
                                            pitchText = 'Menunduk';
                                        } else if (pitchRatio < 0.35) {
                                            pitchText = 'Mendongak';
                                        }

                                        // 4. Jarak Wajah ke Kamera
                                        const faceScale = box.width / vw;
                                        const isDistanceValid = faceScale >= 0.22 &&
                                            faceScale <= 0.65;

                                        // 5. Centering (Pusat Wajah)
                                        const boxCenterX = (box.x + box.width / 2) / vw;
                                        const isCentered = Math.abs(boxCenterX - 0.5) <= 0.22;

                                        this.posePills = {
                                            count: '1 Orang',
                                            tilt: isTiltValid ? 'Tegak' : tiltText,
                                            yaw: isYawValid ? 'Lurus' : yawText,
                                            pitch: isPitchValid ? 'Pas' : pitchText
                                        };

                                        if (!isDistanceValid) {
                                            this.poseStatus = 'WARNING';
                                            this.poseMessage = faceScale < 0.22 ?
                                                'Wajah terlalu jauh, mendekatlah' :
                                                'Wajah terlalu dekat, mundurlah sedikit';
                                            this.isPoseValid = false;
                                        } else if (!isCentered) {
                                            this.poseStatus = 'WARNING';
                                            this.poseMessage =
                                                'Posisikan wajah tepat di tengah lingkaran';
                                            this.isPoseValid = false;
                                        } else if (!isTiltValid) {
                                            this.poseStatus = 'WARNING';
                                            this.poseMessage =
                                                'Tegakkan kepala (Wajah masih miring)';
                                            this.isPoseValid = false;
                                        } else if (!isYawValid) {
                                            this.poseStatus = 'WARNING';
                                            this.poseMessage =
                                                'Hadap lurus ke depan (Jangan menoleh)';
                                            this.isPoseValid = false;
                                        } else if (!isPitchValid) {
                                            this.poseStatus = 'WARNING';
                                            this.poseMessage = pitchText === 'Menunduk' ?
                                                'Angkat dagu sedikit (Jangan menunduk)' :
                                                'Tundukkan kepala sedikit (Jangan mendongak)';
                                            this.isPoseValid = false;
                                        } else {
                                            this.poseStatus = 'PERFECT';
                                            this.poseMessage =
                                                'Posisi Wajah Sempurna — Siap Dipotret!';
                                            this.isPoseValid = true;
                                        }
                                    } catch (e) {
                                        // Abaikan error transisi frame
                                    }
                                }, 150);
                            },

                            stopFaceTracking() {
                                if (this.trackingInterval) {
                                    clearInterval(this.trackingInterval);
                                    this.trackingInterval = null;
                                }
                                this.detectedFaceBox = {
                                    left: '0%',
                                    top: '0%',
                                    width: '0%',
                                    height: '0%',
                                    found: false
                                };
                                this.faceCount = 0;
                                this.poseStatus = 'IDLE';
                                this.poseMessage = 'Posisikan wajah di dalam lingkaran';
                                this.posePills = {
                                    count: '-',
                                    tilt: '-',
                                    yaw: '-',
                                    pitch: '-'
                                };
                                this.isPoseValid = false;
                            },

                            stopCamera() {
                                this.stopFaceTracking();
                                if (this.stream) {
                                    this.stream.getTracks().forEach(track => track.stop());
                                    this.stream = null;
                                }
                                if (this.$refs.video) {
                                    this.$refs.video.srcObject = null;
                                }
                                this.capturedImage = null;
                                this.pendingCroppedFile = null;
                                this.pendingDescriptor = null;
                                this.isCapturing = false;
                                this.isSavingPhoto = false;
                                this.isCameraOpen = false;
                            },

                            async loadModels() {
                                this.isLoadingModels = true;
                                const MODEL_URL = '/models';
                                try {
                                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                                    this.faceApiLoaded = true;
                                } catch (err) {
                                    console.error("Error loading face-api models: ", err);
                                } finally {
                                    this.isLoadingModels = false;
                                }
                            },

                            async compressImage(file) {
                                return new Promise((resolve, reject) => {
                                    if (!file.type.startsWith('image/')) {
                                        resolve(file);
                                        return;
                                    }

                                    const reader = new FileReader();
                                    reader.readAsDataURL(file);
                                    reader.onload = (event) => {
                                        const img = new Image();
                                        img.src = event.target.result;
                                        img.onload = () => {
                                            const canvas = document.createElement(
                                                'canvas');
                                            let width = img.width;
                                            let height = img.height;
                                            const MAX_SIZE = 1000;

                                            if (width > MAX_SIZE || height > MAX_SIZE) {
                                                if (width > height) {
                                                    height *= MAX_SIZE / width;
                                                    width = MAX_SIZE;
                                                } else {
                                                    width *= MAX_SIZE / height;
                                                    height = MAX_SIZE;
                                                }
                                            }

                                            canvas.width = width;
                                            canvas.height = height;
                                            const ctx = canvas.getContext('2d');
                                            ctx.drawImage(img, 0, 0, width, height);

                                            canvas.toBlob((blob) => {
                                                if (!blob) {
                                                    resolve(file);
                                                    return;
                                                }

                                                let filename = file.name;
                                                const dotIndex = filename
                                                    .lastIndexOf('.');
                                                if (dotIndex !== -1) {
                                                    filename = filename
                                                        .substring(0,
                                                            dotIndex) + '.jpg';
                                                } else {
                                                    filename = filename +
                                                        '.jpg';
                                                }

                                                const compressedFile = new File(
                                                    [blob], filename, {
                                                        type: 'image/jpeg',
                                                        lastModified: Date
                                                            .now()
                                                    });

                                                resolve(compressedFile);
                                            }, 'image/jpeg', 0.9);
                                        };
                                        img.onerror = (err) => reject(err);
                                    };
                                    reader.onerror = (err) => reject(err);
                                });
                            },

                            // Helper untuk memotong (crop) hanya area wajah dengan margin proporsional (mengeliminasi background & baju berlebih)
                            async cropFace(source, box, filename = 'foto_personnel.jpg') {
                                const marginX = box.width * 0.25;
                                const marginYTop = box.height * 0.35; // margin dahi/rambut
                                const marginYBottom = box.height * 0.25; // margin dagu/leher

                                const cropX = Math.max(0, box.x - marginX);
                                const cropY = Math.max(0, box.y - marginYTop);
                                const cropW = Math.min(source.width - cropX, box.width + (marginX * 2));
                                const cropH = Math.min(source.height - cropY, box.height + marginYTop +
                                    marginYBottom);

                                const cropCanvas = document.createElement('canvas');
                                cropCanvas.width = cropW;
                                cropCanvas.height = cropH;
                                const ctx = cropCanvas.getContext('2d');
                                ctx.drawImage(source, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

                                const dataUrl = cropCanvas.toDataURL('image/jpeg', 0.92);

                                return new Promise((resolve, reject) => {
                                    cropCanvas.toBlob((blob) => {
                                        if (!blob) {
                                            reject(new Error(
                                                "Gagal membuat blob gambar hasil crop."
                                            ));
                                            return;
                                        }
                                        const croppedFile = new File([blob], filename, {
                                            type: 'image/jpeg',
                                            lastModified: Date.now()
                                        });
                                        resolve({
                                            file: croppedFile,
                                            dataUrl: dataUrl
                                        });
                                    }, 'image/jpeg', 0.92);
                                });
                            },

                            async handleFileUpload(event) {
                                const rawFile = event.target.files[0];
                                if (!rawFile) return;

                                this.isUploadingFile = true;
                                try {
                                    if (!this.faceApiLoaded) await this.loadModels();

                                    const img = await faceapi.bufferToImage(rawFile);

                                    // Quality Gate 1: Deteksi seluruh wajah dalam foto
                                    const detections = await faceapi.detectAllFaces(
                                        img,
                                        new faceapi.TinyFaceDetectorOptions({
                                            inputSize: 416,
                                            scoreThreshold: 0.5
                                        })
                                    ).withFaceLandmarks().withFaceDescriptors();

                                    if (detections.length === 0) {
                                        alert(
                                            "Wajah tidak terdeteksi pada file tersebut. Harap gunakan foto dengan wajah yang terlihat jelas dan menghadap ke kamera."
                                        );
                                        @this.set('face_descriptor', '');
                                        event.target.value = '';
                                        return;
                                    }

                                    if (detections.length > 1) {
                                        alert("Terdeteksi " + detections.length +
                                            " wajah pada foto! Harap unggah foto sendiri tanpa ada orang lain di latar belakang."
                                        );
                                        @this.set('face_descriptor', '');
                                        event.target.value = '';
                                        return;
                                    }

                                    const primaryDetection = detections[0];
                                    const box = primaryDetection.detection.box;
                                    const minDimension = Math.min(img.width, img.height);

                                    // Quality Gate 2: Ukuran wajah memadai (tidak terlalu jauh)
                                    if (box.width < minDimension * 0.15) {
                                        alert(
                                            "Ukuran wajah terlalu kecil atau jauh pada foto. Harap gunakan foto portrait atau pasfoto yang lebih dekat."
                                        );
                                        @this.set('face_descriptor', '');
                                        event.target.value = '';
                                        return;
                                    }

                                    // Visualisasi kotak deteksi wajah pada foto
                                    this.uploadedFaceBox = {
                                        left: (box.x / img.width * 100).toFixed(1) + '%',
                                        top: (box.y / img.height * 100).toFixed(1) + '%',
                                        width: (box.width / img.width * 100).toFixed(1) + '%',
                                        height: (box.height / img.height * 100).toFixed(1) + '%',
                                        found: true
                                    };

                                    // Auto-crop area wajah (menghilangkan background & baju yang tidak perlu)
                                    let filename = rawFile.name.replace(/\.[^/.]+$/, "") + ".jpg";
                                    const cropped = await this.cropFace(img, box, filename);
                                    const croppedFile = cropped.file;
                                    this.capturedPhotoPreview = cropped.dataUrl;

                                    // Upload foto hasil crop ke Livewire
                                    await new Promise((resolve, reject) => {
                                        @this.upload('foto', croppedFile,
                                            (uploadedVal) => resolve(uploadedVal),
                                            (err) => reject(err)
                                        );
                                    });

                                    // Simpan descriptor 128D (web)
                                    const descriptor = JSON.stringify(Array.from(primaryDetection
                                        .descriptor));
                                    @this.set('face_descriptor', descriptor);
                                } catch (err) {
                                    console.error("Error processing file upload: ", err);
                                    alert("Gagal memproses file foto: " + (err.message || err));
                                } finally {
                                    this.isUploadingFile = false;
                                }
                            },

                            async capture() {
                                const video = this.$refs.video;
                                const canvas = this.$refs.canvas;
                                if (!video || !canvas) return;

                                this.isCapturing = true;
                                this.stopFaceTracking();
                                try {
                                    canvas.width = video.videoWidth;
                                    canvas.height = video.videoHeight;

                                    const context = canvas.getContext('2d');
                                    context.save();
                                    context.translate(canvas.width, 0);
                                    context.scale(-1, 1);
                                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                                    context.restore();

                                    if (!this.faceApiLoaded) await this.loadModels();

                                    // Quality Gate 1: Deteksi seluruh wajah pada frame kamera
                                    const detections = await faceapi.detectAllFaces(
                                        canvas,
                                        new faceapi.TinyFaceDetectorOptions({
                                            inputSize: 416,
                                            scoreThreshold: 0.5
                                        })
                                    ).withFaceLandmarks().withFaceDescriptors();

                                    if (detections.length === 0) {
                                        alert(
                                            "Wajah tidak terdeteksi! Pastikan wajah terlihat jelas di depan kamera dan pencahayaan memadai."
                                        );
                                        this.startFaceTracking();
                                        return;
                                    }

                                    if (detections.length > 1) {
                                        alert("Terdeteksi " + detections.length +
                                            " wajah! Harap pastikan hanya ada 1 orang di depan kamera."
                                        );
                                        this.startFaceTracking();
                                        return;
                                    }

                                    const primaryDetection = detections[0];
                                    const box = primaryDetection.detection.box;
                                    const minDimension = Math.min(canvas.width, canvas.height);

                                    // Quality Gate 2: Ukuran wajah memadai (tidak terlalu jauh)
                                    if (box.width < minDimension * 0.20) {
                                        alert(
                                            "Wajah terlalu jauh dari kamera. Harap mendekat ke kamera dan ulangi pemotretan."
                                        );
                                        this.startFaceTracking();
                                        return;
                                    }

                                    // Quality Gate 3: Konfirmasi jika posisi wajah belum ideal
                                    if (!this.isPoseValid) {
                                        const konfirmasi = confirm("Posisi wajah belum ideal: " + this
                                            .poseMessage +
                                            ".\n\nApakah Anda tetap ingin mengambil foto ini?");
                                        if (!konfirmasi) {
                                            this.startFaceTracking();
                                            return;
                                        }
                                    }

                                    // Auto-crop area wajah langsung dari canvas
                                    const cropped = await this.cropFace(canvas, box, "capture.jpg");

                                    // Jeda video kamera saat menampilkan hasil foto di modal
                                    if (this.$refs.video) {
                                        this.$refs.video.pause();
                                    }

                                    // Simpan ke state untuk review dalam modal
                                    this.pendingCroppedFile = cropped.file;
                                    this.pendingDescriptor = JSON.stringify(Array.from(primaryDetection
                                        .descriptor));
                                    this.capturedImage = cropped.dataUrl;
                                } catch (err) {
                                    console.error("Error capturing/processing image: ", err);
                                    alert("Gagal memproses hasil pemotretan: " + (err.message || err));
                                    this.startFaceTracking();
                                } finally {
                                    this.isCapturing = false;
                                }
                            },

                            retakePhoto() {
                                this.capturedImage = null;
                                this.pendingCroppedFile = null;
                                this.pendingDescriptor = null;
                                if (this.$refs.video) {
                                    this.$refs.video.play().catch(() => {});
                                }
                                this.startFaceTracking();
                            },

                            async savePhoto() {
                                if (!this.pendingCroppedFile) return;

                                this.isSavingPhoto = true;
                                try {
                                    // Upload foto hasil crop ke Livewire
                                    await new Promise((resolve, reject) => {
                                        @this.upload('foto', this.pendingCroppedFile,
                                            () => resolve(),
                                            (err) => reject(err)
                                        );
                                    });

                                    // Simpan descriptor 128D (web)
                                    if (this.pendingDescriptor) {
                                        @this.set('face_descriptor', this.pendingDescriptor);
                                    }

                                    // Tampilkan foto di card kanan secara instan
                                    this.capturedPhotoPreview = this.capturedImage;

                                    // Tutup modal dan matikan kamera
                                    this.stopCamera();
                                } catch (err) {
                                    console.error("Error saving photo: ", err);
                                    alert("Gagal menyimpan foto: " + (err.message || err));
                                } finally {
                                    this.isSavingPhoto = false;
                                }
                            }
                        }));
                    }
                };

                if (window.Alpine) {
                    initPersonnelCamera();
                } else {
                    document.addEventListener('alpine:init', initPersonnelCamera);
                }
            })();
        </script>
    </div>
