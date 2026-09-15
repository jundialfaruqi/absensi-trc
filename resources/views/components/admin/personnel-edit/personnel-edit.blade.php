<div wire:init="load">
    <div x-data="personnelCamera()">
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-base-200">
            <div>
                <h3 class="font-black uppercase text-xl">
                    Edit Personnel
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

                                {{-- Email --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Alamat Email
                                            <span class="text-error">*</span></span>
                                    </label>
                                    <input type="email" wire:model="email"
                                        class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full transition-all @error('email') input-error @enderror"
                                        placeholder="Cth: john@example.com">
                                    @error('email')
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

                                {{-- Password --}}
                                <div class="form-control w-full" x-data="{ show: false }">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Password</span>
                                    </label>
                                    <div class="relative flex items-center">
                                        <input x-bind:type="show ? 'text' : 'password'" wire:model="password"
                                            class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full pr-10 transition-all @error('password') input-error @enderror"
                                            placeholder="(Kosongkan jika tidak diubah)">
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
                                    @error('password')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Password Confirmation --}}
                                <div class="form-control w-full" x-data="{ show: false }">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Ketik Ulang
                                            Password</span>
                                    </label>
                                    <div class="relative flex items-center">
                                        <input x-bind:type="show ? 'text' : 'password'"
                                            wire:model="password_confirmation"
                                            class="input input-bordered focus:input-primary placeholder:text-base-content/60 w-full pr-10 transition-all"
                                            placeholder="(Kosongkan jika tidak diubah)">
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
                                </div>

                                {{-- OPD Induk --}}
                                <div class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">Pilih OPD Induk
                                            <span class="text-error">*</span></span>
                                    </label>
                                    <select wire:model="opd_id"
                                        class="select select-bordered focus:select-primary w-full transition-all @error('opd_id') select-error @enderror"
                                        @if (!auth()->user()->hasRole('super-admin') && !auth()->user()->can('edit-personel-all-opd')) disabled @endif>
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
                                                        mengikuti shift
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
                                                        absen kapan saja
                                                        tanpa terikat shift.</span>
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

                                <div class="form-control w-full">
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
                                    @if ($has_personal_device)
                                        <div
                                            class="flex items-center gap-4 bg-success/5 p-4 rounded-xl border border-success/20">
                                            <div class="bg-success/10 p-2 rounded-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="w-5 h-5 text-success">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span
                                                    class="label-text font-bold block uppercase text-xs text-success">Perangkat
                                                    Terdaftar</span>
                                                <span
                                                    class="text-[10px] text-base-content opacity-60 block truncate italic">
                                                    {{ $existing_device_name }}
                                                </span>
                                            </div>
                                        </div>
                                    @else
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
                                                    license key.</span>
                                            </div>
                                        </label>
                                    @endif
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
                                            @elseif ($oldFoto)
                                                <img x-ref="previewImage" src="{{ Storage::url($oldFoto) }}"
                                                    alt="Preview Foto Lama" class="w-full h-full object-cover">
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
                                            :disabled="isStartingCamera || isStarting3DCamera">
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

                                        @can('rekam-wajah-3d')
                                            {{-- Tombol Perekaman Wajah 3D Multi-Angle (BARU) --}}
                                            <button type="button" @click="open3DModal()"
                                                class="btn btn-neutral text-white btn-sm w-full gap-2 font-semibold shadow-sm border-none"
                                                :disabled="isStartingCamera">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                                                    class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m9-5.25v9" />
                                                </svg>
                                                <span>Perekaman Wajah 3D</span>
                                            </button>
                                        @endcan

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

                                        {{-- Biometric Status Badges (Dual-Stack 128D / 192D & 3D Multi-Angle) --}}
                                        <div class="flex flex-wrap justify-center gap-1.5 pt-1">
                                            @if ($has_3d_faces)
                                                <span
                                                    class="badge badge-secondary badge-xs gap-1 py-1.5 px-2 text-[10px] font-semibold text-white"
                                                    title="4 Sudut Wajah (3D) Lengkap">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    3D (4 Sudut) Ready
                                                </span>
                                            @endif

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

                                            {{-- 192D Mobile Status (Real-time synced via Reverb) --}}
                                            <template x-if="isSyncingMobile">
                                                <span
                                                    class="badge badge-warning badge-xs gap-1 py-1.5 px-2 text-[10px] animate-pulse"
                                                    title="Menunggu ekstraksi 192-D otomatis dari HP Admin...">
                                                    <span class="loading loading-spinner loading-xs scale-75"></span>
                                                    Syncing 192D...
                                                </span>
                                            </template>
                                            <template x-if="!isSyncingMobile && has192D">
                                                <span class="badge badge-info badge-xs gap-1 py-1.5 px-2 text-[10px]"
                                                    title="192-D Vector dari MobileFaceNet HP siap">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    192D Mobile Ready
                                                </span>
                                            </template>
                                            <template x-if="!isSyncingMobile && !has192D">
                                                <span class="badge badge-ghost badge-xs gap-1 py-1.5 px-2 text-[10px]"
                                                    title="192-D belum disinkron dari mobile">
                                                    192D Belum Sync
                                                </span>
                                            </template>
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

                {{-- Modal Kamera 3D Multi-Angle Otomatis (BARU & TERISOLASI) --}}
                <div x-show="is3DCameraOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[99999] flex items-start justify-center pt-4 sm:pt-8 px-4 bg-black/85 backdrop-blur-md overflow-y-auto"
                    @keydown.escape.window="stop3DCamera()">

                    {{-- Backdrop (Tidak tertutup jika klik luar tanpa sengaja) --}}
                    <div class="fixed inset-0"></div>

                    {{-- Modal Card --}}
                    <div class="relative bg-base-100 dark:bg-base-200 text-base-content rounded-2xl shadow-2xl border border-base-300 w-full max-w-xl p-5 flex flex-col gap-4 z-10 animate-in fade-in zoom-in-95 duration-200"
                        @click.stop>

                        {{-- Header Modal --}}
                        <div class="flex items-center justify-between border-b border-base-200 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m9-5.25v9" />
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-bold text-sm sm:text-base leading-tight"
                                        x-text="current3DStage === 'TUTORIAL' ? 'Panduan Perekaman Wajah 3D' : 'Perekaman Wajah 3D'">
                                    </h4>
                                    <p class="text-[11px] text-base-content/60"
                                        x-text="current3DStage === 'TUTORIAL' ? 'Pelajari 4 posisi wajah sebelum memulai perekaman otomatis' : 'Otomatis menangkap 4 sudut wajah untuk akurasi optimal'">
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="stop3DCamera()"
                                class="btn btn-sm btn-circle btn-ghost text-base-content/70 hover:bg-base-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- 4-Stage Progress Tracker Pills (Hanya saat proses rekam / review) --}}
                        <div x-show="current3DStage !== 'TUTORIAL'" class="grid grid-cols-4 gap-2">
                            {{-- Step 1: Depan --}}
                            <div class="flex flex-col items-center p-2 rounded-xl border text-center transition-all duration-200"
                                :class="poses3D.FRONT ?
                                    'border-emerald-500/50 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (
                                        current3DStage === 'FRONT' ?
                                        'border-primary bg-primary/10 text-primary font-bold shadow-sm' :
                                        'border-base-200 opacity-60')">
                                <div class="flex items-center gap-1 text-[11px]">
                                    <template x-if="poses3D.FRONT">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <span>1. Depan</span>
                                </div>
                            </div>

                            {{-- Step 2: Kanan --}}
                            <div class="flex flex-col items-center p-2 rounded-xl border text-center transition-all duration-200"
                                :class="poses3D.RIGHT ?
                                    'border-emerald-500/50 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (
                                        current3DStage === 'RIGHT' ?
                                        'border-primary bg-primary/10 text-primary font-bold shadow-sm' :
                                        'border-base-200 opacity-60')">
                                <div class="flex items-center gap-1 text-[11px]">
                                    <template x-if="poses3D.RIGHT">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <span>2. Kanan</span>
                                </div>
                            </div>

                            {{-- Step 3: Kiri --}}
                            <div class="flex flex-col items-center p-2 rounded-xl border text-center transition-all duration-200"
                                :class="poses3D.LEFT ?
                                    'border-emerald-500/50 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (
                                        current3DStage === 'LEFT' ?
                                        'border-primary bg-primary/10 text-primary font-bold shadow-sm' :
                                        'border-base-200 opacity-60')">
                                <div class="flex items-center gap-1 text-[11px]">
                                    <template x-if="poses3D.LEFT">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <span>3. Kiri</span>
                                </div>
                            </div>

                            {{-- Step 4: Atas --}}
                            <div class="flex flex-col items-center p-2 rounded-xl border text-center transition-all duration-200"
                                :class="poses3D.UP ?
                                    'border-emerald-500/50 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (
                                        current3DStage === 'UP' ?
                                        'border-primary bg-primary/10 text-primary font-bold shadow-sm' :
                                        'border-base-200 opacity-60')">
                                <div class="flex items-center gap-1 text-[11px]">
                                    <template x-if="poses3D.UP">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <span>4. Atas</span>
                                </div>
                            </div>
                        </div>

                        {{-- Mode 0: Tutorial & Panduan Perekaman Wajah 3D (Tampil Saat Modal Dibuka) --}}
                        <div x-show="current3DStage === 'TUTORIAL'" class="flex flex-col gap-3.5">
                            {{-- Info Banner --}}
                            <div class="bg-primary/5 border border-primary/20 rounded-xl p-3 flex items-start gap-3">
                                <span class="p-1.5 rounded-lg bg-primary/10 text-primary shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <div class="text-xs">
                                    <span class="font-bold text-base-content">Perekaman Otomatis 4 Sudut:</span>
                                    <span class="text-base-content/75 block mt-0.5 leading-relaxed">
                                        Kamera AI akan mengambil 4 posisi wajah secara otomatis tanpa perlu menekan
                                        tombol. Ikuti urutan gerakan berikut:
                                    </span>
                                </div>
                            </div>

                            {{-- 4 Cards Panduan dengan Gambar Ilustrasi --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                {{-- Card 1: Tampak Depan --}}
                                <div
                                    class="bg-base-200/60 dark:bg-base-300/40 rounded-xl p-2.5 border border-base-300/70 flex flex-col items-center text-center relative hover:border-emerald-500/50 transition-all">
                                    <span
                                        class="badge badge-xs bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold mb-1.5 border-emerald-500/30">1.
                                        Depan</span>
                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center mb-1.5 bg-base-100 dark:bg-base-200/80 rounded-lg p-1 shadow-sm">
                                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Outer Reticle Box -->
                                            <rect x="10" y="10" width="80" height="80" rx="14"
                                                class="fill-emerald-500/5 stroke-emerald-500/30" stroke-width="1.5"
                                                stroke-dasharray="3 3" />
                                            <path d="M 20 12 H 12 V 20" class="stroke-emerald-500" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M 80 12 H 88 V 20" class="stroke-emerald-500" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M 20 88 H 12 V 80" class="stroke-emerald-500" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M 80 88 H 88 V 80" class="stroke-emerald-500" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Body -->
                                            <path d="M 26 88 C 26 74 36 68 50 68 C 64 68 74 74 74 88"
                                                class="fill-base-content/10 stroke-base-content/30"
                                                stroke-width="1.5" />
                                            <!-- Neck -->
                                            <rect x="44" y="54" width="12" height="13" rx="3"
                                                class="fill-amber-100 dark:fill-amber-900/40 stroke-base-content/30"
                                                stroke-width="1.2" />
                                            <!-- Face -->
                                            <ellipse cx="50" cy="42" rx="18" ry="22"
                                                class="fill-amber-50 dark:fill-base-200 stroke-base-content/70"
                                                stroke-width="2" />
                                            <!-- Hair -->
                                            <path
                                                d="M 32 38 C 32 26 40 22 50 22 C 60 22 68 26 68 38 C 65 31 59 30 50 30 C 41 30 35 32 32 38 Z"
                                                class="fill-base-content/70" />
                                            <!-- Eyes -->
                                            <circle cx="43" cy="40" r="2.5"
                                                class="fill-base-content/80" />
                                            <circle cx="57" cy="40" r="2.5"
                                                class="fill-base-content/80" />
                                            <!-- Nose -->
                                            <path d="M 50 39 V 45 H 52" class="stroke-base-content/50"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Smile -->
                                            <path d="M 45 49 Q 50 53 55 49" class="stroke-base-content/70"
                                                stroke-width="1.8" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-xs text-base-content leading-tight">Hadap Depan</span>
                                    <p class="text-[10px] text-base-content/60 mt-0.5 leading-tight">Lurus ke tengah
                                        lingkaran kamera</p>
                                </div>

                                {{-- Card 2: Menoleh Kanan --}}
                                <div
                                    class="bg-base-200/60 dark:bg-base-300/40 rounded-xl p-2.5 border border-base-300/70 flex flex-col items-center text-center relative hover:border-emerald-500/50 transition-all">
                                    <span
                                        class="badge badge-xs bg-blue-500/15 text-blue-600 dark:text-blue-400 font-bold mb-1.5 border-blue-500/30">2.
                                        Kanan</span>
                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center mb-1.5 bg-base-100 dark:bg-base-200/80 rounded-lg p-1 shadow-sm">
                                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Outer Reticle Box -->
                                            <rect x="10" y="10" width="80" height="80" rx="14"
                                                class="fill-blue-500/5 stroke-blue-500/30" stroke-width="1.5"
                                                stroke-dasharray="3 3" />
                                            <!-- Direction Arrow (Right) -->
                                            <path d="M 22 22 Q 50 14 74 22" class="stroke-blue-500" stroke-width="2.5"
                                                stroke-linecap="round" fill="none" />
                                            <path d="M 68 17 L 76 22 L 68 27" class="stroke-blue-500 fill-blue-500"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Body turned -->
                                            <path d="M 28 88 C 28 75 38 69 52 68 C 65 67 74 74 76 88"
                                                class="fill-base-content/10 stroke-base-content/30"
                                                stroke-width="1.5" />
                                            <!-- Neck -->
                                            <rect x="46" y="54" width="12" height="13" rx="3"
                                                class="fill-amber-100 dark:fill-amber-900/40 stroke-base-content/30"
                                                stroke-width="1.2" />
                                            <!-- Face Turned Right -->
                                            <path
                                                d="M 40 22 C 55 22 66 28 66 42 C 66 46 64 51 60 56 C 54 62 42 63 36 58 C 30 52 30 40 32 34 C 34 26 38 22 40 22 Z"
                                                class="fill-amber-50 dark:fill-base-200 stroke-base-content/70"
                                                stroke-width="2" />
                                            <!-- Hair -->
                                            <path
                                                d="M 32 34 C 32 24 38 21 44 21 C 52 21 62 25 64 36 C 60 30 52 28 44 28 C 37 28 34 30 32 34 Z"
                                                class="fill-base-content/70" />
                                            <!-- Eyes shifted right -->
                                            <circle cx="48" cy="40" r="2.5"
                                                class="fill-base-content/80" />
                                            <circle cx="60" cy="39" r="2"
                                                class="fill-base-content/80" />
                                            <!-- Nose pointing right -->
                                            <path d="M 57 40 L 63 45 L 59 47" class="stroke-base-content/60"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Smile shifted right -->
                                            <path d="M 50 51 Q 56 54 60 50" class="stroke-base-content/70"
                                                stroke-width="1.8" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-xs text-base-content leading-tight">Menoleh
                                        Kanan</span>
                                    <p class="text-[10px] text-base-content/60 mt-0.5 leading-tight">Putar kepala
                                        perlahan ke kanan Anda</p>
                                </div>

                                {{-- Card 3: Menoleh Kiri --}}
                                <div
                                    class="bg-base-200/60 dark:bg-base-300/40 rounded-xl p-2.5 border border-base-300/70 flex flex-col items-center text-center relative hover:border-emerald-500/50 transition-all">
                                    <span
                                        class="badge badge-xs bg-blue-500/15 text-blue-600 dark:text-blue-400 font-bold mb-1.5 border-blue-500/30">3.
                                        Kiri</span>
                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center mb-1.5 bg-base-100 dark:bg-base-200/80 rounded-lg p-1 shadow-sm">
                                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Outer Reticle Box -->
                                            <rect x="10" y="10" width="80" height="80" rx="14"
                                                class="fill-blue-500/5 stroke-blue-500/30" stroke-width="1.5"
                                                stroke-dasharray="3 3" />
                                            <!-- Direction Arrow (Left) -->
                                            <path d="M 78 22 Q 50 14 26 22" class="stroke-blue-500" stroke-width="2.5"
                                                stroke-linecap="round" fill="none" />
                                            <path d="M 32 17 L 24 22 L 32 27" class="stroke-blue-500 fill-blue-500"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Body turned -->
                                            <path d="M 24 88 C 26 74 35 67 48 68 C 62 69 72 75 72 88"
                                                class="fill-base-content/10 stroke-base-content/30"
                                                stroke-width="1.5" />
                                            <!-- Neck -->
                                            <rect x="42" y="54" width="12" height="13" rx="3"
                                                class="fill-amber-100 dark:fill-amber-900/40 stroke-base-content/30"
                                                stroke-width="1.2" />
                                            <!-- Face Turned Left -->
                                            <path
                                                d="M 60 22 C 45 22 34 28 34 42 C 34 46 36 51 40 56 C 46 62 58 63 64 58 C 70 52 70 40 68 34 C 66 26 62 22 60 22 Z"
                                                class="fill-amber-50 dark:fill-base-200 stroke-base-content/70"
                                                stroke-width="2" />
                                            <!-- Hair -->
                                            <path
                                                d="M 68 34 C 68 24 62 21 56 21 C 48 21 38 25 36 36 C 40 30 48 28 56 28 C 63 28 66 30 68 34 Z"
                                                class="fill-base-content/70" />
                                            <!-- Eyes shifted left -->
                                            <circle cx="52" cy="40" r="2.5"
                                                class="fill-base-content/80" />
                                            <circle cx="40" cy="39" r="2"
                                                class="fill-base-content/80" />
                                            <!-- Nose pointing left -->
                                            <path d="M 43 40 L 37 45 L 41 47" class="stroke-base-content/60"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Smile shifted left -->
                                            <path d="M 40 50 Q 44 54 50 51" class="stroke-base-content/70"
                                                stroke-width="1.8" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-xs text-base-content leading-tight">Menoleh Kiri</span>
                                    <p class="text-[10px] text-base-content/60 mt-0.5 leading-tight">Putar kepala
                                        perlahan ke kiri Anda</p>
                                </div>

                                {{-- Card 4: Mendongak Atas --}}
                                <div
                                    class="bg-base-200/60 dark:bg-base-300/40 rounded-xl p-2.5 border border-base-300/70 flex flex-col items-center text-center relative hover:border-emerald-500/50 transition-all">
                                    <span
                                        class="badge badge-xs bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold mb-1.5 border-emerald-500/30">4.
                                        Atas</span>
                                    <div
                                        class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center mb-1.5 bg-base-100 dark:bg-base-200/80 rounded-lg p-1 shadow-sm">
                                        <svg viewBox="0 0 100 100" class="w-full h-full" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Outer Reticle Box -->
                                            <rect x="10" y="10" width="80" height="80" rx="14"
                                                class="fill-emerald-500/5 stroke-emerald-500/30" stroke-width="1.5"
                                                stroke-dasharray="3 3" />
                                            <!-- Direction Arrow (Up) -->
                                            <path d="M 50 25 V 13" class="stroke-emerald-500" stroke-width="2.5"
                                                stroke-linecap="round" />
                                            <path d="M 44 19 L 50 13 L 56 19"
                                                class="stroke-emerald-500 fill-emerald-500" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <!-- Body -->
                                            <path d="M 26 88 C 26 76 36 71 50 71 C 64 71 74 76 74 88"
                                                class="fill-base-content/10 stroke-base-content/30"
                                                stroke-width="1.5" />
                                            <!-- Extended Neck -->
                                            <path d="M 43 50 L 41 68 L 59 68 L 57 50 Z"
                                                class="fill-amber-100 dark:fill-amber-900/40 stroke-base-content/30"
                                                stroke-width="1.2" />
                                            <!-- Face Tilted Upwards -->
                                            <ellipse cx="50" cy="38" rx="18" ry="20"
                                                class="fill-amber-50 dark:fill-base-200 stroke-base-content/70"
                                                stroke-width="2" />
                                            <!-- Hair -->
                                            <path
                                                d="M 33 34 C 33 22 41 18 50 18 C 59 18 67 22 67 34 C 64 27 58 26 50 26 C 42 26 36 27 33 34 Z"
                                                class="fill-base-content/70" />
                                            <!-- Eyes looking up -->
                                            <circle cx="43" cy="35" r="2.5"
                                                class="fill-base-content/80" />
                                            <circle cx="57" cy="35" r="2.5"
                                                class="fill-base-content/80" />
                                            <!-- Nostrils / Under-nose -->
                                            <path d="M 47 43 Q 50 40 53 43" class="stroke-base-content/60"
                                                stroke-width="1.5" stroke-linecap="round" fill="none" />
                                            <!-- Smile -->
                                            <path d="M 45 47 Q 50 50 55 47" class="stroke-base-content/70"
                                                stroke-width="1.8" stroke-linecap="round" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-xs text-base-content leading-tight">Mendongak
                                        Atas</span>
                                    <p class="text-[10px] text-base-content/60 mt-0.5 leading-tight">Dongakkan wajah
                                        atau dagu sedikit ke atas</p>
                                </div>
                            </div>

                            {{-- Tips Box --}}
                            <div
                                class="bg-base-200/50 dark:bg-base-300/30 rounded-xl p-3 border border-base-300/60 text-xs flex flex-col gap-1">
                                <div class="flex items-center gap-1.5 font-bold text-base-content/90 text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Hal yang Perlu Diperhatikan:</span>
                                </div>
                                <ul
                                    class="text-[11px] text-base-content/70 space-y-0.5 pl-5 list-disc leading-relaxed">
                                    <li>Pencahayaan cukup terang & hindari lampu langsung dari belakang (backlight).
                                    </li>
                                    <li>Lepaskan masker atau kacamata hitam agar seluruh wajah terlihat jelas.</li>
                                    <li>Pastikan kedua mata terbuka dengan jelas (tidak terpejam / berkedip).</li>
                                    <li>Saat posisi wajah pas & frame menjadi hijau, tahan posisi ~1 detik hingga bunyi
                                        bip / flash.</li>
                                </ul>
                            </div>

                            {{-- Footer Tombol Batal & Mulai Rekam Wajah --}}
                            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-base-200">
                                <button type="button" @click="stop3DCamera()"
                                    class="btn btn-ghost hover:bg-base-300 text-base-content/70 font-medium">
                                    Batal
                                </button>
                                <button type="button" @click="start3DCamera()"
                                    class="btn bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold shadow-md shadow-emerald-500/25 border-none gap-2 px-5"
                                    :disabled="isStarting3DCamera">
                                    <span x-show="isStarting3DCamera"
                                        class="loading loading-spinner loading-xs"></span>
                                    <svg x-show="!isStarting3DCamera" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <span
                                        x-text="isStarting3DCamera ? 'Menyiapkan Kamera & AI...' : 'Mulai Rekam Wajah'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Mode 1: Kamera Aktif Melakukan Scan Real-Time --}}
                        <div x-show="current3DStage !== 'TUTORIAL' && current3DStage !== 'REVIEW'"
                            class="flex flex-col gap-3">
                            {{-- Viewport Container --}}
                            <div
                                class="relative w-full max-w-sm aspect-4/5 mx-auto bg-black rounded-2xl overflow-hidden border-2 border-base-300 shadow-inner">
                                <video x-ref="video3D" autoplay muted playsinline class="w-full h-full object-cover"
                                    style="transform: scaleX(-1);"></video>
                                <canvas x-ref="canvas3D" class="hidden"></canvas>

                                {{-- Visual Flash Effect saat Auto-Capture --}}
                                <div x-show="show3DFlash" x-cloak
                                    class="absolute inset-0 bg-white z-50 pointer-events-none transition-opacity duration-150">
                                </div>

                                {{-- Circle Guide HUD Statis dengan Donut Loading Berkesinambungan --}}
                                <div
                                    class="absolute inset-0 pointer-events-none flex items-center justify-center z-10">
                                    <div class="relative w-56 h-56 sm:w-60 sm:h-60 flex items-center justify-center">
                                        {{-- SVG Donut Progress Ring --}}
                                        <svg class="w-full h-full" viewBox="0 0 240 240">
                                            {{-- Base Circle Track (Garis Putus-putus) --}}
                                            <circle cx="120" cy="120" r="100"
                                                class="transition-colors duration-300"
                                                :class="isPoseMatched3D ? 'stroke-emerald-500/30' : 'stroke-white/25'"
                                                stroke-width="3.5" stroke-dasharray="6 6" fill="none" />

                                            {{-- Subtle Tint Fill saat Posisi Pas --}}
                                            <circle cx="120" cy="120" r="97"
                                                class="transition-all duration-300"
                                                :class="isPoseMatched3D ? 'fill-emerald-500/10' : 'fill-transparent'" />

                                            {{-- Donut Loading Progress Arc (Mulai dari Atas / Jam 12, Berkesinambungan) --}}
                                            <circle cx="120" cy="120" r="100"
                                                class="stroke-emerald-400 transition-all duration-150 ease-out"
                                                stroke-width="6" stroke-linecap="round" stroke-dasharray="628.32"
                                                :stroke-dashoffset="628.32 - (628.32 * total3DProgress / 100)"
                                                transform="rotate(-90 120 120)" fill="none"
                                                style="filter: drop-shadow(0 0 6px rgba(52, 211, 153, 0.7));" />

                                            {{-- 4 Divider Dots pada Sudut 4 Tahap --}}
                                            {{-- 1. Depan (Top / 0°) --}}
                                            <circle cx="120" cy="20" r="3.5"
                                                :class="total3DProgress >= 100 ? 'fill-emerald-400' : 'fill-white/40'" />
                                            {{-- 2. Kanan (Right / 90°) --}}
                                            <circle cx="220" cy="120" r="3.5"
                                                :class="total3DProgress >= 25 ? 'fill-emerald-400' : 'fill-white/40'" />
                                            {{-- 3. Kiri (Bottom / 180°) --}}
                                            <circle cx="120" cy="220" r="3.5"
                                                :class="total3DProgress >= 50 ? 'fill-emerald-400' : 'fill-white/40'" />
                                            {{-- 4. Atas (Left / 270°) --}}
                                            <circle cx="20" cy="120" r="3.5"
                                                :class="total3DProgress >= 75 ? 'fill-emerald-400' : 'fill-white/40'" />
                                        </svg>

                                        {{-- Indikator Persentase Kecil di Bawah Lingkaran --}}
                                        <div x-show="total3DProgress > 0" x-cloak
                                            class="absolute bottom-3 px-2.5 py-0.5 rounded-full bg-black/75 backdrop-blur-md border border-emerald-500/30 text-[11px] font-bold text-emerald-400 shadow-md">
                                            <span x-text="total3DProgress + '%'"></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Instruction Floating Pill --}}
                                <div class="absolute top-3 inset-x-3 z-30 flex justify-center">
                                    <div class="px-4 py-2 rounded-full backdrop-blur-md text-xs font-semibold shadow-lg text-center transition-all duration-200"
                                        :class="isPoseMatched3D ? 'bg-emerald-600/90 text-white' :
                                            'bg-black/70 text-white/90 border border-white/10'">
                                        <span x-text="stageInstruction"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-base-content/60 px-1">
                                <button type="button" @click="backTo3DTutorial()"
                                    class="btn btn-ghost btn-xs text-base-content/60 hover:text-base-content gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <span>Lihat Panduan</span>
                                </button>
                                <span class="italic text-[11px]">Foto otomatis mirror tanpa konversi ulang.</span>
                            </div>
                        </div>

                        {{-- Mode 2: Review Hasil 4 Sudut Wajah --}}
                        <div x-show="current3DStage === 'REVIEW'" class="flex flex-col gap-4">
                            <div class="text-center">
                                <div class="inline-flex p-2 rounded-full bg-emerald-500/10 text-emerald-500 mb-1">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h5 class="font-bold text-sm">4 Sudut Wajah Berhasil Ditangkap!</h5>
                                <p class="text-xs text-base-content/60">Semua foto telah tersimpan secara presisi
                                    dalam format mirror.</p>
                            </div>

                            {{-- Grid 4 Foto Thumbnail --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                {{-- Thumbnail Depan --}}
                                <div
                                    class="flex flex-col items-center bg-base-200 p-2 rounded-xl border border-base-300">
                                    <div
                                        class="w-full aspect-square rounded-lg overflow-hidden bg-black mb-1.5 shadow-inner">
                                        <template x-if="poses3D.FRONT">
                                            <img :src="poses3D.FRONT.dataUrl" alt="Depan"
                                                class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <span class="text-[11px] font-bold">Depan</span>
                                    <span
                                        class="badge badge-success badge-xs mt-0.5 text-[9px] text-white">Lengkap</span>
                                </div>

                                {{-- Thumbnail Kanan --}}
                                <div
                                    class="flex flex-col items-center bg-base-200 p-2 rounded-xl border border-base-300">
                                    <div
                                        class="w-full aspect-square rounded-lg overflow-hidden bg-black mb-1.5 shadow-inner">
                                        <template x-if="poses3D.RIGHT">
                                            <img :src="poses3D.RIGHT.dataUrl" alt="Kanan"
                                                class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <span class="text-[11px] font-bold">Kanan</span>
                                    <span
                                        class="badge badge-success badge-xs mt-0.5 text-[9px] text-white">Lengkap</span>
                                </div>

                                {{-- Thumbnail Kiri --}}
                                <div
                                    class="flex flex-col items-center bg-base-200 p-2 rounded-xl border border-base-300">
                                    <div
                                        class="w-full aspect-square rounded-lg overflow-hidden bg-black mb-1.5 shadow-inner">
                                        <template x-if="poses3D.LEFT">
                                            <img :src="poses3D.LEFT.dataUrl" alt="Kiri"
                                                class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <span class="text-[11px] font-bold">Kiri</span>
                                    <span
                                        class="badge badge-success badge-xs mt-0.5 text-[9px] text-white">Lengkap</span>
                                </div>

                                {{-- Thumbnail Atas --}}
                                <div
                                    class="flex flex-col items-center bg-base-200 p-2 rounded-xl border border-base-300">
                                    <div
                                        class="w-full aspect-square rounded-lg overflow-hidden bg-black mb-1.5 shadow-inner">
                                        <template x-if="poses3D.UP">
                                            <img :src="poses3D.UP.dataUrl" alt="Atas"
                                                class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <span class="text-[11px] font-bold">Atas</span>
                                    <span
                                        class="badge badge-success badge-xs mt-0.5 text-[9px] text-white">Lengkap</span>
                                </div>
                            </div>

                            {{-- Tombol Aksi Review --}}
                            <div class="flex items-center gap-3 pt-2">
                                <button type="button" @click="restart3DScan()"
                                    class="btn btn-sm btn-outline flex-1 gap-1.5" :disabled="isSaving3D">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Ulangi Scan</span>
                                </button>
                                <button type="button" @click="save3DPoses()"
                                    class="btn btn-sm btn-success text-white flex-1 gap-1.5 font-bold shadow-md shadow-emerald-500/20"
                                    :disabled="isSaving3D">
                                    <span x-show="isSaving3D" class="loading loading-spinner loading-xs"></span>
                                    <svg x-show="!isSaving3D" class="w-4 h-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="isSaving3D ? 'Menyimpan 3D...' : 'Gunakan Wajah 3D Ini'"></span>
                                </button>
                            </div>
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

                            // State 3D Multi-Angle Camera (BARU)
                            is3DCameraOpen: false,
                            isStarting3DCamera: false,
                            current3DStage: 'TUTORIAL',
                            stageHoldProgress: 0,
                            stageHoldStart: null,
                            stageInstruction: 'Posisikan wajah menghadap depan di dalam lingkaran...',
                            poses3D: {
                                FRONT: null,
                                RIGHT: null,
                                LEFT: null,
                                UP: null
                            },
                            get total3DProgress() {
                                let base = 0;
                                if (this.poses3D && this.poses3D.FRONT) base += 25;
                                if (this.poses3D && this.poses3D.RIGHT) base += 25;
                                if (this.poses3D && this.poses3D.LEFT) base += 25;
                                if (this.poses3D && this.poses3D.UP) base += 25;

                                if (this.current3DStage === 'REVIEW') return 100;

                                const currentHold = (this.stageHoldProgress / 100) * 25;
                                return Math.min(100, Math.round(base + currentHold));
                            },
                            stream3D: null,
                            trackingInterval3D: null,
                            isSaving3D: false,
                            show3DFlash: false,
                            detected3DFaceBox: {
                                left: '0%',
                                top: '0%',
                                width: '0%',
                                height: '0%',
                                found: false
                            },
                            isPoseMatched3D: false,
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
                            has192D: {{ !empty($face_descriptor_mobile) ? 'true' : 'false' }},
                            isSyncingMobile: false,
                            syncTimeout: null,

                            init() {
                                const EchoConstructor = window._EchoHandler || window.Echo;
                                if (typeof EchoConstructor === 'function' && !window.Echo) {
                                    try {
                                        const reverbHost = '{{ env('REVERB_HOST') }}';
                                        const wsHost = (reverbHost === '127.0.0.1' || reverbHost ===
                                                'localhost' || !reverbHost) ?
                                            window.location.hostname : reverbHost;
                                        const isSecure = window.location.protocol === 'https:';
                                        window.Echo = new EchoConstructor({
                                            broadcaster: 'reverb',
                                            key: '{{ env('REVERB_APP_KEY') }}',
                                            wsHost: wsHost,
                                            wsPort: window.location.port || (isSecure ? 443 : 80),
                                            wssPort: window.location.port || (isSecure ? 443 : 80),
                                            forceTLS: isSecure,
                                            enabledTransports: ['ws', 'wss'],
                                        });
                                    } catch (e) {
                                        console.warn("Could not init Echo in personnel-edit: ", e);
                                    }
                                }

                                if (window.Echo && typeof window.Echo.channel === 'function') {
                                    try {
                                        window.Echo.channel('personnel-biometrics')
                                            .listen('PersonnelVectorUpdated', (e) => {
                                                if (e.personnel_id == {{ $personnelId }}) {
                                                    this.has192D = true;
                                                    this.isSyncingMobile = false;
                                                    if (this.syncTimeout) {
                                                        clearTimeout(this.syncTimeout);
                                                        this.syncTimeout = null;
                                                    }
                                                }
                                            });
                                    } catch (e) {
                                        console.warn("Could not listen to Echo channel: ", e);
                                    }
                                }
                            },

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

                                        const leftEar = this.calculateEyeAspectRatio(leftEye);
                                        const rightEar = this.calculateEyeAspectRatio(rightEye);
                                        const areEyesOpen = leftEar >= 0.19 && rightEar >= 0.19;

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
                                        } else if (!areEyesOpen) {
                                            this.poseStatus = 'WARNING';
                                            this.poseMessage =
                                                'Harap buka kedua mata dengan jelas';
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

                                    // Quality Gate 3: Kedua mata harus terbuka (tidak terpejam / berkedip)
                                    const leftEye = primaryDetection.landmarks.getLeftEye();
                                    const rightEye = primaryDetection.landmarks.getRightEye();
                                    const leftEar = this.calculateEyeAspectRatio(leftEye);
                                    const rightEar = this.calculateEyeAspectRatio(rightEye);
                                    if (leftEar < 0.18 || rightEar < 0.18) {
                                        alert(
                                            "Mata terdeteksi tertutup atau terpejam pada foto! Harap unggah foto dengan kedua mata terbuka dengan jelas."
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
                                    await @this.call('saveFotoDirectly', descriptor);

                                    this.has192D = false;
                                    if (window.Echo && typeof window.Echo.channel === 'function') {
                                        this.isSyncingMobile = true;
                                        if (this.syncTimeout) clearTimeout(this.syncTimeout);
                                        this.syncTimeout = setTimeout(() => {
                                            this.isSyncingMobile = false;
                                        }, 6000);
                                    } else {
                                        this.isSyncingMobile = false;
                                    }
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
                                    // Simpan gambar hasil kamera dalam bentuk raw (tanpa mirror)
                                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

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

                                    // Quality Gate 3: Kedua mata harus terbuka (tidak terpejam / berkedip)
                                    const leftEye = primaryDetection.landmarks.getLeftEye();
                                    const rightEye = primaryDetection.landmarks.getRightEye();
                                    const leftEar = this.calculateEyeAspectRatio(leftEye);
                                    const rightEar = this.calculateEyeAspectRatio(rightEye);
                                    if (leftEar < 0.18 || rightEar < 0.18) {
                                        alert(
                                            "Mata terdeteksi tertutup atau terpejam! Harap pastikan kedua mata terbuka dengan jelas saat pemotretan."
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

                                    // Simpan descriptor dan simpan langsung ke database
                                    if (this.pendingDescriptor) {
                                        @this.set('face_descriptor', this.pendingDescriptor);
                                    }
                                    await @this.call('saveFotoDirectly', this.pendingDescriptor);

                                    // Tampilkan foto di card kanan
                                    this.capturedPhotoPreview = this.capturedImage;
                                    this.has192D = false;

                                    // Jika Echo terhubung, tunggu sinkronisasi mobile maksimal 6 detik
                                    if (window.Echo && typeof window.Echo.channel === 'function') {
                                        this.isSyncingMobile = true;
                                        if (this.syncTimeout) clearTimeout(this.syncTimeout);
                                        this.syncTimeout = setTimeout(() => {
                                            this.isSyncingMobile = false;
                                        }, 6000);
                                    } else {
                                        this.isSyncingMobile = false;
                                    }

                                    // Tutup modal dan matikan kamera
                                    this.stopCamera();
                                } catch (err) {
                                    console.error("Error saving photo: ", err);
                                    alert("Gagal menyimpan foto: " + (err.message || err));
                                } finally {
                                    this.isSavingPhoto = false;
                                }
                            },

                            playBeep(freq = 660, duration = 120) {
                                try {
                                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                                    if (!AudioContext) return;
                                    const audioCtx = new AudioContext();
                                    const osc = audioCtx.createOscillator();
                                    const gain = audioCtx.createGain();
                                    osc.type = 'sine';
                                    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                                    gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime +
                                        duration / 1000);
                                    osc.connect(gain);
                                    gain.connect(audioCtx.destination);
                                    osc.start();
                                    osc.stop(audioCtx.currentTime + duration / 1000);
                                } catch (e) {}
                            },

                            open3DModal() {
                                this.current3DStage = 'TUTORIAL';
                                this.is3DCameraOpen = true;
                                this.stageHoldProgress = 0;
                                this.stageHoldStart = null;
                                this.isPoseMatched3D = false;
                            },

                            backTo3DTutorial() {
                                if (this.trackingInterval3D) {
                                    clearInterval(this.trackingInterval3D);
                                    this.trackingInterval3D = null;
                                }
                                if (this.stream3D) {
                                    this.stream3D.getTracks().forEach(track => track.stop());
                                    this.stream3D = null;
                                }
                                this.current3DStage = 'TUTORIAL';
                                this.stageHoldProgress = 0;
                                this.stageHoldStart = null;
                                this.isPoseMatched3D = false;
                                this.detected3DFaceBox.found = false;
                            },

                            async start3DCamera() {
                                this.isStarting3DCamera = true;
                                try {
                                    if (!this.faceApiLoaded) {
                                        await this.loadModels();
                                    }

                                    const stream = await navigator.mediaDevices.getUserMedia({
                                        video: {
                                            width: {
                                                ideal: 1280
                                            },
                                            height: {
                                                ideal: 720
                                            },
                                            facingMode: 'user'
                                        },
                                        audio: false
                                    });

                                    this.stream3D = stream;
                                    this.is3DCameraOpen = true;
                                    this.current3DStage = 'FRONT';
                                    this.stageHoldProgress = 0;
                                    this.stageHoldStart = null;
                                    this.stageInstruction =
                                        'Posisikan wajah menghadap depan di dalam lingkaran...';
                                    this.poses3D = {
                                        FRONT: null,
                                        RIGHT: null,
                                        LEFT: null,
                                        UP: null
                                    };
                                    this.isPoseMatched3D = false;

                                    this.$nextTick(() => {
                                        const video = this.$refs.video3D;
                                        if (video) {
                                            video.srcObject = stream;
                                            video.onloadedmetadata = () => {
                                                video.play().catch(e => console.error(
                                                    "Error playing 3D video:", e));
                                                this.start3DFaceTracking();
                                            };
                                        }
                                    });
                                } catch (err) {
                                    console.error("Error starting 3D camera: ", err);
                                    alert("Gagal mengakses kamera: " + (err.message || err));
                                    this.current3DStage = 'TUTORIAL';
                                } finally {
                                    this.isStarting3DCamera = false;
                                }
                            },

                            stop3DCamera() {
                                if (this.trackingInterval3D) {
                                    clearInterval(this.trackingInterval3D);
                                    this.trackingInterval3D = null;
                                }
                                if (this.stream3D) {
                                    this.stream3D.getTracks().forEach(track => track.stop());
                                    this.stream3D = null;
                                }
                                this.is3DCameraOpen = false;
                                this.current3DStage = 'TUTORIAL';
                                this.detected3DFaceBox.found = false;
                                this.stageHoldProgress = 0;
                                this.stageHoldStart = null;
                            },

                            async cropFaceFromMirrored(canvas, box, filename) {
                                const cropCanvas = document.createElement('canvas');
                                const padX = box.width * 0.25;
                                const padY = box.height * 0.35;
                                const startX = Math.max(0, box.x - padX);
                                const startY = Math.max(0, box.y - padY);
                                const width = Math.min(canvas.width - startX, box.width + (padX * 2));
                                const height = Math.min(canvas.height - startY, box.height + (padY *
                                    2));

                                cropCanvas.width = width;
                                cropCanvas.height = height;
                                const cropCtx = cropCanvas.getContext('2d');
                                cropCtx.drawImage(canvas, startX, startY, width, height, 0, 0, width,
                                    height);

                                const dataUrl = cropCanvas.toDataURL('image/jpeg', 0.92);
                                const blob = await (await fetch(dataUrl)).blob();
                                const file = new File([blob], filename, {
                                    type: 'image/jpeg'
                                });
                                return {
                                    dataUrl,
                                    file
                                };
                            },

                            calculateEyeAspectRatio(eyePoints) {
                                if (!eyePoints || eyePoints.length < 6) return 0;
                                const v1 = Math.hypot(eyePoints[1].x - eyePoints[5].x, eyePoints[1].y -
                                    eyePoints[5].y);
                                const v2 = Math.hypot(eyePoints[2].x - eyePoints[4].x, eyePoints[2].y -
                                    eyePoints[4].y);
                                const h = Math.hypot(eyePoints[0].x - eyePoints[3].x, eyePoints[0].y -
                                    eyePoints[3].y);
                                return (v1 + v2) / (2 * (h || 0.001));
                            },

                            start3DFaceTracking() {
                                if (this.trackingInterval3D) {
                                    clearInterval(this.trackingInterval3D);
                                }

                                let isProcessing = false;

                                this.trackingInterval3D = setInterval(async () => {
                                    if (isProcessing || !this.is3DCameraOpen || this
                                        .current3DStage === 'REVIEW') return;
                                    const video = this.$refs.video3D;
                                    const canvas = this.$refs.canvas3D;
                                    if (!video || !canvas || video.paused || video.ended ||
                                        video.videoWidth === 0) return;

                                    isProcessing = true;
                                    try {
                                        canvas.width = video.videoWidth;
                                        canvas.height = video.videoHeight;
                                        const ctx = canvas.getContext('2d');

                                        // Rendering langsung mirror ke canvas:
                                        ctx.save();
                                        ctx.translate(canvas.width, 0);
                                        ctx.scale(-1, 1);
                                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                                        ctx.restore();

                                        const detections = await faceapi.detectAllFaces(
                                            canvas,
                                            new faceapi.TinyFaceDetectorOptions({
                                                inputSize: 320,
                                                scoreThreshold: 0.5
                                            })
                                        ).withFaceLandmarks().withFaceDescriptors();

                                        if (detections.length === 0) {
                                            this.detected3DFaceBox.found = false;
                                            this.isPoseMatched3D = false;
                                            this.stageHoldProgress = 0;
                                            this.stageHoldStart = null;
                                            this.stageInstruction =
                                                'Arahkan wajah ke dalam lingkaran kamera...';
                                            return;
                                        }

                                        if (detections.length > 1) {
                                            this.detected3DFaceBox.found = false;
                                            this.isPoseMatched3D = false;
                                            this.stageHoldProgress = 0;
                                            this.stageHoldStart = null;
                                            this.stageInstruction =
                                                'Harap hanya 1 orang di depan kamera!';
                                            return;
                                        }

                                        const primary = detections[0];
                                        const box = primary.detection.box;
                                        const landmarks = primary.landmarks;
                                        const vw = canvas.width;
                                        const vh = canvas.height;

                                        // Koordinat box langsung selaras karena canvas sudah berorientasi mirror
                                        this.detected3DFaceBox = {
                                            left: (box.x / vw * 100).toFixed(1) + '%',
                                            top: (box.y / vh * 100).toFixed(1) + '%',
                                            width: (box.width / vw * 100).toFixed(1) + '%',
                                            height: (box.height / vh * 100).toFixed(1) +
                                                '%',
                                            found: true
                                        };

                                        const leftEye = landmarks.getLeftEye();
                                        const rightEye = landmarks.getRightEye();
                                        const leftEyeCenter = {
                                            x: leftEye.reduce((s, p) => s + p.x, 0) /
                                                leftEye.length,
                                            y: leftEye.reduce((s, p) => s + p.y, 0) /
                                                leftEye.length
                                        };
                                        const rightEyeCenter = {
                                            x: rightEye.reduce((s, p) => s + p.x, 0) /
                                                rightEye.length,
                                            y: rightEye.reduce((s, p) => s + p.y, 0) /
                                                rightEye.length
                                        };

                                        const dY = rightEyeCenter.y - leftEyeCenter.y;
                                        const dX = rightEyeCenter.x - leftEyeCenter.x;
                                        const rollAngle = Math.atan2(dY, dX) * (180 / Math.PI);

                                        const noseTip = landmarks.positions[30];
                                        const distLeft = Math.abs(noseTip.x - leftEyeCenter.x);
                                        const distRight = Math.abs(rightEyeCenter.x - noseTip
                                            .x);
                                        const yawRatio = distLeft / (distRight || 0.001);

                                        const eyeLevelY = (leftEyeCenter.y + rightEyeCenter.y) /
                                            2;
                                        const chinTip = landmarks.positions[8];
                                        const eyeToNose = noseTip.y - eyeLevelY;
                                        const noseToChin = chinTip.y - noseTip.y;
                                        const pitchRatio = eyeToNose / (noseToChin || 0.001);

                                        const faceScale = box.width / vw;
                                        const boxCenterX = (box.x + box.width / 2) / vw;
                                        const isCentered = Math.abs(boxCenterX - 0.5) <= 0.25;
                                        const isDistanceValid = faceScale >= 0.20 &&
                                            faceScale <= 0.65;

                                        let isCurrentPoseValid = false;
                                        let targetInstruction = '';
                                        let holdRequiredMs = 700;

                                        const leftEar = this.calculateEyeAspectRatio(leftEye);
                                        const rightEar = this.calculateEyeAspectRatio(rightEye);

                                        if (this.current3DStage === 'FRONT') {
                                            holdRequiredMs = 800;
                                            const isRollOk = Math.abs(rollAngle) <= 10;
                                            const isYawOk = yawRatio >= 0.70 && yawRatio <=
                                            1.40;
                                            const isPitchOk = pitchRatio >= 0.42 &&
                                                pitchRatio <= 0.85;
                                            const areEyesOpen = leftEar >= 0.19 && rightEar >=
                                                0.19;

                                            if (!isDistanceValid) {
                                                targetInstruction = faceScale < 0.20 ?
                                                    'Mendekatlah ke kamera' :
                                                    'Mundurlah sedikit';
                                            } else if (!isCentered) {
                                                targetInstruction =
                                                    'Posisikan wajah di tengah lingkaran';
                                            } else if (!areEyesOpen) {
                                                targetInstruction =
                                                    'Harap buka kedua mata dengan jelas';
                                            } else if (!isRollOk) {
                                                targetInstruction = 'Tegakkan kepala';
                                            } else if (!isYawOk) {
                                                targetInstruction = 'Tatap lurus ke depan';
                                            } else if (!isPitchOk) {
                                                targetInstruction = pitchRatio > 0.85 ?
                                                    'Angkat kepala sedikit' :
                                                    'Jangan terlalu mendongak';
                                            } else {
                                                isCurrentPoseValid = true;
                                                targetInstruction =
                                                    'Posisi sempurna! Tahan sejenak...';
                                            }
                                        } else if (this.current3DStage === 'RIGHT') {
                                            holdRequiredMs = 700;
                                            const isYawRight = yawRatio >= 1.35;
                                            const isRollOk = Math.abs(rollAngle) <= 14;
                                            const areEyesOpen = leftEar >= 0.16 && rightEar >=
                                                0.16;

                                            if (!areEyesOpen) {
                                                targetInstruction =
                                                    'Harap buka kedua mata saat menoleh';
                                            } else if (!isYawRight) {
                                                targetInstruction =
                                                    'Tengok perlahan sedikit ke KANAN (~15°-20°)...';
                                            } else if (!isRollOk) {
                                                targetInstruction =
                                                    'Jaga kepala tetap tegak saat menoleh';
                                            } else {
                                                isCurrentPoseValid = true;
                                                targetInstruction =
                                                    'Sudut kanan pas! Tahan sejenak...';
                                            }
                                        } else if (this.current3DStage === 'LEFT') {
                                            holdRequiredMs = 700;
                                            const isYawLeft = yawRatio <= 0.72;
                                            const isRollOk = Math.abs(rollAngle) <= 14;
                                            const areEyesOpen = leftEar >= 0.16 && rightEar >=
                                                0.16;

                                            if (!areEyesOpen) {
                                                targetInstruction =
                                                    'Harap buka kedua mata saat menoleh';
                                            } else if (!isYawLeft) {
                                                targetInstruction =
                                                    'Tengok perlahan sedikit ke KIRI (~15°-20°)...';
                                            } else if (!isRollOk) {
                                                targetInstruction =
                                                    'Jaga kepala tetap tegak saat menoleh';
                                            } else {
                                                isCurrentPoseValid = true;
                                                targetInstruction =
                                                    'Sudut kiri pas! Tahan sejenak...';
                                            }
                                        } else if (this.current3DStage === 'UP') {
                                            holdRequiredMs = 700;
                                            const isPitchUp = pitchRatio <= 0.44;
                                            const isRollOk = Math.abs(rollAngle) <= 14;
                                            const areEyesOpen = leftEar >= 0.16 && rightEar >=
                                                0.16;

                                            if (!areEyesOpen) {
                                                targetInstruction =
                                                    'Harap tetap buka kedua mata saat mendongak';
                                            } else if (!isPitchUp) {
                                                targetInstruction =
                                                    'Angkat dagu / mendongak sedikit ke ATAS (~10°-15°)...';
                                            } else if (!isRollOk) {
                                                targetInstruction =
                                                    'Jaga kepala tidak miring saat mendongak';
                                            } else {
                                                isCurrentPoseValid = true;
                                                targetInstruction =
                                                    'Sudut mendongak pas! Tahan sejenak...';
                                            }
                                        }

                                        this.stageInstruction = targetInstruction;
                                        this.isPoseMatched3D = isCurrentPoseValid;

                                        const now = Date.now();
                                        if (isCurrentPoseValid) {
                                            if (!this.stageHoldStart) {
                                                this.stageHoldStart = now;
                                            }
                                            const elapsed = now - this.stageHoldStart;
                                            this.stageHoldProgress = Math.min(100, Math.round((
                                                elapsed / holdRequiredMs) * 100));

                                            if (elapsed >= holdRequiredMs) {
                                                // AUTO CAPTURE STAGE
                                                const stageCaptured = this.current3DStage;
                                                const cropped = await this.cropFaceFromMirrored(
                                                    canvas,
                                                    box,
                                                    `pose_${stageCaptured.toLowerCase()}.jpg`
                                                );

                                                this.poses3D[stageCaptured] = {
                                                    dataUrl: cropped.dataUrl,
                                                    file: cropped.file,
                                                    descriptor: JSON.stringify(Array.from(
                                                        primary.descriptor))
                                                };

                                                // Trigger flash
                                                this.show3DFlash = true;
                                                setTimeout(() => {
                                                    this.show3DFlash = false;
                                                }, 150);

                                                // Reset hold
                                                this.stageHoldStart = null;
                                                this.stageHoldProgress = 0;
                                                this.isPoseMatched3D = false;

                                                // Sound and transition
                                                if (stageCaptured === 'FRONT') {
                                                    this.playBeep(650, 100);
                                                    this.current3DStage = 'RIGHT';
                                                } else if (stageCaptured === 'RIGHT') {
                                                    this.playBeep(650, 100);
                                                    this.current3DStage = 'LEFT';
                                                } else if (stageCaptured === 'LEFT') {
                                                    this.playBeep(650, 100);
                                                    this.current3DStage = 'UP';
                                                } else if (stageCaptured === 'UP') {
                                                    this.playBeep(880, 120);
                                                    setTimeout(() => this.playBeep(1100, 180),
                                                        140);
                                                    this.current3DStage = 'REVIEW';
                                                    if (video) video.pause();
                                                }
                                            }
                                        } else {
                                            this.stageHoldStart = null;
                                            this.stageHoldProgress = 0;
                                        }
                                    } catch (err) {
                                        console.error("Error in 3D face tracking: ", err);
                                    } finally {
                                        isProcessing = false;
                                    }
                                }, 100);
                            },

                            restart3DScan() {
                                this.poses3D = {
                                    FRONT: null,
                                    RIGHT: null,
                                    LEFT: null,
                                    UP: null
                                };
                                this.current3DStage = 'FRONT';
                                this.stageHoldProgress = 0;
                                this.stageHoldStart = null;
                                this.isPoseMatched3D = false;
                                const video = this.$refs.video3D;
                                if (video) {
                                    video.play().catch(() => {});
                                }
                            },

                            async save3DPoses() {
                                if (!this.poses3D.FRONT || !this.poses3D.RIGHT || !this.poses3D.LEFT ||
                                    !this.poses3D.UP) {
                                    alert("Perekaman 4 sudut belum lengkap!");
                                    return;
                                }

                                this.isSaving3D = true;
                                try {
                                    const uploadFile = (field, file) => {
                                        return new Promise((resolve, reject) => {
                                            @this.upload(
                                                field,
                                                file,
                                                () => resolve(),
                                                (err) => reject(err)
                                            );
                                        });
                                    };

                                    // Upload 4 sudut pose
                                    await uploadFile('foto_front', this.poses3D.FRONT.file);
                                    await uploadFile('foto_right', this.poses3D.RIGHT.file);
                                    await uploadFile('foto_left', this.poses3D.LEFT.file);
                                    await uploadFile('foto_up', this.poses3D.UP.file);

                                    // Simpan descriptor untuk masing-masing sudut
                                    @this.set('descriptor_front', this.poses3D.FRONT.descriptor);
                                    @this.set('descriptor_right', this.poses3D.RIGHT.descriptor);
                                    @this.set('descriptor_left', this.poses3D.LEFT.descriptor);
                                    @this.set('descriptor_up', this.poses3D.UP.descriptor);
                                    @this.set('has_3d_faces', true);

                                    // Set foto utama & descriptor utama (FRONT) agar kompatibel 100% dengan sistem lama
                                    await uploadFile('foto', this.poses3D.FRONT.file);
                                    @this.set('face_descriptor', this.poses3D.FRONT.descriptor);

                                    this.capturedPhotoPreview = this.poses3D.FRONT.dataUrl;

                                    this.stop3DCamera();
                                } catch (err) {
                                    console.error("Error saving 3D faces: ", err);
                                    alert("Gagal menyimpan data wajah 3D: " + (err.message || err));
                                } finally {
                                    this.isSaving3D = false;
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
