<div>
    <div x-data="personnelCamera()">
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-base-200">
            <div>
                <h3 class="font-black uppercase text-xl">
                    Tambah Personnel
                </h3>
                <p class="text-sm text-base-content/60 mt-1">Lengkapi data profil dan foto personnel di bawah ini.</p>
            </div>
            <a wire:navigate href="{{ route('personnel') }}" class="btn btn-ghost btn-sm" @click="stopCamera()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4 mr-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali
            </a>
        </div>
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
                                    <span class="label-text text-sm font-medium text-base-content">Nama Lengkap <span
                                            class="text-error">*</span></span>
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
                                    <span class="label-text text-sm font-medium text-base-content">Pilih OPD Induk <span
                                            class="text-error">*</span></span>
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

                            {{-- Attendance Type --}}
                            <div class="form-control w-full md:col-span-2">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Mode Absensi <span
                                            class="text-error">*</span></span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label
                                        class="label cursor-pointer bg-base-200/50 p-4 rounded-xl border border-base-300 transition-all hover:bg-base-200">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" wire:model="attendance_type" value="SCHEDULED"
                                                class="radio radio-primary radio-sm">
                                            <div>
                                                <span class="label-text font-bold block">Jadwal Tetap</span>
                                                <span class="text-[10px] opacity-60 text-wrap">Wajib mengikuti shift
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
                                                <span class="label-text font-bold block">Fleksibel</span>
                                                <span class="text-[10px] opacity-60 text-wrap">Bisa absen kapan saja
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

                            <div class="form-control w-full md:col-span-2">
                                <label class="label mb-1 px-1">
                                    <span class="label-text text-sm font-medium text-base-content">Pilih Kantor</span>
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
                                            aktif, personil wajib scan wajah saat absen. Jika tidak, hanya ambil foto
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
                                            perangkat personal untuk personnel ini secara otomatis dan buatkan license
                                            key.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer border-t border-base-200 p-6 flex justify-end gap-3">
                        <a wire:navigate href="{{ route('personnel') }}" class="btn btn-ghost"
                            @click="stopCamera()">Batal</a>
                        <button type="submit" class="btn btn-secondary px-8" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                            <span wire:loading.remove wire:target="save">Simpan Data</span>
                        </button>
                    </div>
                </div>

                {{-- Kanan: Foto --}}
                <div class="card md:col-span-1 h-fit">
                    <div class="card-body p-6">
                        <div class="flex flex-col items-center">
                            <h4 class="font-bold text-lg mb-4">Foto Autentikasi</h4>
                        </div>
                        <div class="form-control w-full">
                            <div class="flex flex-col gap-4 items-center">
                                {{-- Preview & Camera View --}}
                                <div class="flex flex-col gap-4 items-center w-full">
                                    {{-- Camera / Current Foto --}}
                                    <div
                                        class="relative w-full max-w-70 aspect-5/6 bg-base-300 rounded-lg overflow-hidden border-2 border-base-200">
                                        <video x-ref="video" x-show="isCameraOpen" autoplay muted playsinline
                                            class="w-full h-full object-cover"></video>
                                        <canvas x-ref="canvas" class="hidden"></canvas>

                                        <div x-show="!isCameraOpen"
                                            class="w-full h-full flex items-center justify-center">
                                            @if ($foto && !$errors->has('foto'))
                                                <img src="{{ $foto->temporaryUrl() }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-12 h-12 opacity-20">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                </svg>
                                            @endif
                                        </div>

                                        {{-- Face Guide Overlay --}}
                                        <div x-show="isCameraOpen"
                                            class="absolute inset-0 pointer-events-none flex items-center justify-center z-10">
                                            <svg class="w-full h-full" viewBox="0 0 160 192" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <!-- Dashed Oval (More narrow and slightly higher) -->
                                                <ellipse cx="80" cy="86" rx="42" ry="60"
                                                    stroke="rgba(255, 255, 255, 0.6)" stroke-width="2"
                                                    stroke-dasharray="6 6" />
                                                <!-- Top Left -->
                                                <path d="M 50 35 L 35 35 L 35 50" stroke="rgba(255, 255, 255, 0.8)"
                                                    stroke-width="2" fill="none" />
                                                <!-- Top Right -->
                                                <path d="M 110 35 L 125 35 L 125 50" stroke="rgba(255, 255, 255, 0.8)"
                                                    stroke-width="2" fill="none" />
                                                <!-- Bottom Left -->
                                                <path d="M 50 145 L 35 145 L 35 130" stroke="rgba(255, 255, 255, 0.8)"
                                                    stroke-width="2" fill="none" />
                                                <!-- Bottom Right -->
                                                <path d="M 110 145 L 125 145 L 125 130"
                                                    stroke="rgba(255, 255, 255, 0.8)" stroke-width="2"
                                                    fill="none" />
                                            </svg>
                                        </div>

                                        {{-- Loading Models Overlay --}}
                                        <div x-show="isLoadingModels"
                                            class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white z-10">
                                            <span class="loading loading-spinner loading-xs mb-2"></span>
                                            <span class="text-[8px] uppercase font-bold tracking-widest">AI
                                                Engine...</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 w-full max-w-70">
                                        <div class="flex flex-row gap-2 w-full">
                                            {{-- Toggle Camera --}}
                                            <button type="button"
                                                @click="isCameraOpen ? stopCamera() : startCamera()"
                                                class="btn btn-sm flex-1"
                                                :class="isCameraOpen ? 'btn-error' : 'btn-neutral'">
                                                <svg x-show="!isCameraOpen" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" class="w-4 h-4 hidden sm:block">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                                </svg>
                                                <span x-text="isCameraOpen ? 'Tutup' : 'Kamera'"></span>
                                            </button>

                                            {{-- Capture Button --}}
                                            <button x-show="isCameraOpen" type="button" @click="capture()"
                                                class="btn btn-sm btn-primary flex-1">
                                                Jepret
                                            </button>

                                            {{-- File Upload --}}
                                            <div class="relative flex-1" x-show="!isCameraOpen">
                                                <input type="file" x-ref="fileInput" class="hidden"
                                                    accept="image/*" @change="handleFileUpload($event)">
                                                <button type="button" @click="$refs.fileInput.click()"
                                                    class="btn btn-sm btn-outline w-full">
                                                    Upload File
                                                </button>
                                            </div>
                                        </div>

                                        <p class="text-[10px] text-base-content/50 leading-relaxed italic">
                                            Direkomendasikan mengambil foto langsung agar AI dapat mendeteksi wajah
                                            dengan lebih akurat.
                                        </p>

                                        @error('foto')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror

                                        @if ($face_descriptor)
                                            <div class="badge badge-success badge-xs gap-1 py-2 px-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Face Code Ready
                                            </div>
                                        @else
                                            <div class="badge badge-warning badge-xs gap-1 py-2 px-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                </svg>
                                                Face Code Not Extracted
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </form>
        <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
        <script>
            (function() {
                const initPersonnelCamera = () => {
                    if (window.Alpine && !Alpine.data('personnelCamera')) {
                        Alpine.data('personnelCamera', () => ({
                            isCameraOpen: false,
                            isLoadingModels: false,
                            stream: null,
                            faceApiLoaded: false,

                            async startCamera() {
                                this.isCameraOpen = true;
                                if (!this.faceApiLoaded) {
                                    await this.loadModels();
                                }

                                try {
                                    this.stream = await navigator.mediaDevices.getUserMedia({
                                        video: true
                                    });
                                    this.$refs.video.srcObject = this.stream;
                                } catch (err) {
                                    console.error("Error accessing camera: ", err);
                                    alert("Tidak dapat mengakses kamera.");
                                    this.isCameraOpen = false;
                                }
                            },

                            stopCamera() {
                                if (this.stream) {
                                    this.stream.getTracks().forEach(track => track.stop());
                                }
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

                            async handleFileUpload(event) {
                                const file = event.target.files[0];
                                if (!file) return;

                                // Preview & Upload to Livewire
                                @this.upload('foto', file);

                                // Extract descriptor
                                if (!this.faceApiLoaded) await this.loadModels();

                                const img = await faceapi.bufferToImage(file);
                                const detection = await faceapi.detectSingleFace(img, new faceapi
                                        .TinyFaceDetectorOptions()).withFaceLandmarks()
                                    .withFaceDescriptor();

                                if (detection) {
                                    @this.set('face_descriptor', JSON.stringify(Array.from(detection
                                        .descriptor)));
                                } else {
                                    alert(
                                        "Wajah tidak terdeteksi pada file tersebut. Silakan coba foto lain."
                                    );
                                    @this.set('face_descriptor', '');
                                }
                            },

                            async capture() {
                                const video = this.$refs.video;
                                const canvas = this.$refs.canvas;
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;

                                const context = canvas.getContext('2d');
                                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                                // Extract descriptor from canvas
                                if (!this.faceApiLoaded) await this.loadModels();
                                const detection = await faceapi.detectSingleFace(canvas, new faceapi
                                        .TinyFaceDetectorOptions()).withFaceLandmarks()
                                    .withFaceDescriptor();

                                if (detection) {
                                    @this.set('face_descriptor', JSON.stringify(Array.from(detection
                                        .descriptor)));

                                    // Convert to Blob and upload
                                    canvas.toBlob((blob) => {
                                        const file = new File([blob], "capture.jpg", {
                                            type: "image/jpeg"
                                        });
                                        @this.upload('foto', file);
                                        this.stopCamera();
                                    }, 'image/jpeg', 0.9);
                                } else {
                                    alert(
                                        "Wajah tidak terdeteksi! Pastikan wajah terlihat jelas di depan kamera."
                                    );
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

        <script>
            function handlePersonnelImageUpload(input) {
                const file = input.files[0];
                if (!file) return;

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
                    console.log('File personnel terlalu besar, melakukan kompresi...');
                    resizePersonnelImage(file, 1200, 1200, 0.85, (resizedFile) => {
                        @this.upload('foto', resizedFile);
                    });
                } else {
                    @this.upload('foto', file);
                }
            }

            function resizePersonnelImage(file, maxWidth, maxHeight, quality, callback) {
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
    </div>
