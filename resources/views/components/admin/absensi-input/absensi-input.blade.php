<div>
    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-base-200">
        <div>
            <h3 class="font-black uppercase text-xl">
                Input Absen
            </h3>
            <p class="text-sm text-base-content/60 mt-1">
                Catat atau lengkapi data kehadiran personel ke sistem absensi.
            </p>
        </div>
    </div>

    <form wire:submit="save"
        x-data="absensiInputForm({
            personnels: @js($this->personnelsData),
            shifts: @js($this->shiftsData),
            kantors: @js($this->kantorsData),
            toleransiMenit: {{ (int) $this->toleransiMenit }}
        })"
        @absensi-context-updated.window="applyServerContext($event.detail)"
        @absensi-input-reset.window="resetClientState()">
        <div class="max-w-4xl">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6 md:gap-y-4">

                        {{-- Personel (Client-side searchable select) --}}
                        <div class="form-control w-full relative" @click.outside="dropdownOpen = false">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Personel <span class="text-error">*</span>
                                </span>
                            </label>

                            <div class="relative">
                                <input type="text"
                                    x-model="searchQuery"
                                    @focus="dropdownOpen = true"
                                    @input="dropdownOpen = true"
                                    @keydown.escape="dropdownOpen = false"
                                    placeholder="Cari nama atau NIK personel..."
                                    autocomplete="off"
                                    class="input input-bordered w-full pr-9 @error('personnelId') input-error @enderror">

                                <button type="button"
                                    x-show="selectedPersonnel"
                                    x-cloak
                                    @click="clearPersonnel()"
                                    title="Hapus pilihan personel"
                                    class="btn btn-ghost btn-xs btn-circle absolute right-2 top-1/2 -translate-y-1/2 text-base-content/60 hover:text-base-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Dropdown hasil pencarian instant --}}
                            <div x-show="dropdownOpen"
                                x-cloak
                                class="absolute left-0 right-0 top-full mt-1 z-50 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-64 overflow-y-auto">
                                <template x-if="filteredPersonnels.length === 0">
                                    <div class="px-4 py-3 text-xs text-base-content/60">
                                        Personel tidak ditemukan.
                                    </div>
                                </template>
                                <template x-for="p in filteredPersonnels" :key="p.id">
                                    <button type="button"
                                        @click="choosePersonnel(p)"
                                        class="w-full text-left px-4 py-2.5 hover:bg-base-200 transition-colors border-b border-base-200/70 last:border-b-0 flex items-center justify-between gap-2"
                                        :class="selectedPersonnel && selectedPersonnel.id === p.id ? 'bg-base-200 font-semibold' : ''">
                                        <div class="min-w-0">
                                            <div class="text-sm text-base-content truncate" x-text="p.name"></div>
                                            <div class="text-xs text-base-content/50 truncate"
                                                x-text="(p.nik || '-') + ' · ' + p.opd_name"></div>
                                        </div>
                                        <span class="text-xs text-base-content/50 shrink-0"
                                            x-text="p.kantor_name || '-'"></span>
                                    </button>
                                </template>
                            </div>

                            @error('personnelId')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Tanggal Absensi --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Tanggal Absensi <span class="text-error">*</span>
                                </span>
                            </label>
                            <input type="date"
                                wire:model.live="tanggal"
                                class="input input-bordered w-full @error('tanggal') input-error @enderror">
                            @error('tanggal')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Info Ringkas Data Eksisting (jika sudah ada di tanggal tersebut) --}}
                        @if ($personnelId && $existingSummary && ($existingSummary['jam_masuk'] || $existingSummary['jam_pulang'] || !in_array($existingSummary['status'], ['ALPA', null, ''])))
                            <div class="md:col-span-2 px-3.5 py-2.5 rounded-lg border border-base-300 bg-base-200/40 text-xs text-base-content/80 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" class="size-4 shrink-0 text-base-content/60">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <span>
                                    Data tanggal ini sudah ada (Status: {{ $existingSummary['status'] ?? '-' }}
                                    @if ($existingSummary['jam_masuk'])
                                        · Masuk {{ $existingSummary['jam_masuk'] }}
                                    @endif
                                    @if ($existingSummary['jam_pulang'])
                                        · Pulang {{ $existingSummary['jam_pulang'] }}
                                    @endif
                                    ) dan akan diperbarui otomatis.
                                </span>
                            </div>
                        @endif

                        {{-- Shift Section: Info Jadwal atau Pilih Shift Wajib jika belum ada jadwal / OFF --}}
                        @if ($personnelId)
                            @if ($needsShiftSelection)
                                <div class="form-control w-full md:col-span-2 p-4 rounded-xl border border-base-300 bg-base-200/30">
                                    <div class="flex items-start gap-2 text-xs text-error font-medium mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="size-4 shrink-0 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        <span>
                                            @if ($activeJadwalStatus === 'OFF')
                                                Jadwal personel pada tanggal ini berstatus {{ $activeShiftName ?: 'OFF/LIBUR' }}. Pilih shift kerja terlebih dahulu untuk melanjutkan.
                                            @else
                                                Personel belum memiliki jadwal shift pada tanggal ini. Pilih shift terlebih dahulu untuk melanjutkan.
                                            @endif
                                        </span>
                                    </div>

                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">
                                            Pilih Shift <span class="text-error">*</span>
                                        </span>
                                    </label>
                                    <select x-model="shiftId"
                                        @change="onShiftSelected($event.target.value)"
                                        class="select select-bordered w-full @error('shiftId') select-error @enderror">
                                        <option value="">-- Pilih Shift Kerja --</option>
                                        <template x-for="s in shifts" :key="s.id">
                                            <option :value="s.id"
                                                x-text="s.name + (s.start_time && s.end_time ? ' (' + s.start_time + ' - ' + s.end_time + ')' : '')"></option>
                                        </template>
                                    </select>
                                    @error('shiftId')
                                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @else
                                <div class="form-control w-full md:col-span-2">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-sm font-medium text-base-content">
                                            Jadwal Shift
                                        </span>
                                    </label>
                                    <input type="text" readonly
                                        value="{{ $activeJadwalStatus === 'FLEXIBLE' ? ($activeShiftName ?: 'Fleksibel') : ($activeShiftName . ($activeShiftStart && $activeShiftEnd ? " ({$activeShiftStart} - {$activeShiftEnd})" : '')) }}"
                                        class="input input-bordered w-full bg-base-200/50 text-base-content/80 cursor-default">
                                </div>
                            @endif
                        @endif

                        {{-- Sesi Absen --}}
                        <div class="form-control w-full md:col-span-2">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Sesi Absen <span class="text-error">*</span>
                                </span>
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="sesi = 'keduanya'"
                                    :class="sesi === 'keduanya' ? 'btn-neutral' : 'btn-outline border-base-300 text-base-content/80'"
                                    class="btn btn-sm font-medium">
                                    Masuk &amp; Pulang
                                </button>
                                <button type="button" @click="sesi = 'masuk'"
                                    :class="sesi === 'masuk' ? 'btn-neutral' : 'btn-outline border-base-300 text-base-content/80'"
                                    class="btn btn-sm font-medium">
                                    Masuk Saja
                                </button>
                                <button type="button" @click="sesi = 'pulang'"
                                    :class="sesi === 'pulang' ? 'btn-neutral' : 'btn-outline border-base-300 text-base-content/80'"
                                    class="btn btn-sm font-medium">
                                    Pulang Saja
                                </button>
                            </div>
                            @error('sesi')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Jam Masuk & Status Masuk --}}
                        <div x-show="sesi === 'masuk' || sesi === 'keduanya'" class="form-control w-full">
                            <label class="label mb-1 px-1 flex justify-between items-center">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Jam Masuk <span class="text-error">*</span>
                                </span>
                                <span class="flex items-center gap-2 text-xs">
                                    <button type="button" x-show="activeShiftTimes().start"
                                        @click="applyShiftTime('masuk')"
                                        class="link link-hover text-base-content/60">Sesuai Shift</button>
                                    <button type="button" @click="applyNowTime('masuk')"
                                        class="link link-hover text-base-content/60">Sekarang</button>
                                </span>
                            </label>
                            <div class="flex gap-2">
                                <input type="time" x-model="jamMasuk" @change="recomputeStatusMasuk()"
                                    class="input input-bordered flex-1 @error('jamMasuk') input-error @enderror">
                                <select x-model="statusMasuk"
                                    class="select select-bordered w-32 @error('statusMasuk') select-error @enderror">
                                    <option value="HADIR">HADIR</option>
                                    <option value="TELAT">TELAT</option>
                                </select>
                            </div>
                            @error('jamMasuk')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                            @error('statusMasuk')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Jam Pulang & Status Pulang --}}
                        <div x-show="sesi === 'pulang' || sesi === 'keduanya'" class="form-control w-full">
                            <label class="label mb-1 px-1 flex justify-between items-center">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Jam Pulang <span class="text-error">*</span>
                                </span>
                                <span class="flex items-center gap-2 text-xs">
                                    <button type="button" x-show="activeShiftTimes().end"
                                        @click="applyShiftTime('pulang')"
                                        class="link link-hover text-base-content/60">Sesuai Shift</button>
                                    <button type="button" @click="applyNowTime('pulang')"
                                        class="link link-hover text-base-content/60">Sekarang</button>
                                </span>
                            </label>
                            <div class="flex gap-2">
                                <input type="time" x-model="jamPulang" @change="recomputeStatusPulang()"
                                    class="input input-bordered flex-1 @error('jamPulang') input-error @enderror">
                                <select x-model="statusPulang"
                                    class="select select-bordered w-32 @error('statusPulang') select-error @enderror">
                                    <option value="HADIR">HADIR</option>
                                    <option value="PC">PC</option>
                                </select>
                            </div>
                            @error('jamPulang')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                            @error('statusPulang')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Lokasi Kantor --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Lokasi Kantor
                                </span>
                            </label>
                            <select x-model="kantorId" @change="onKantorChanged($event.target.value)"
                                class="select select-bordered w-full @error('kantorId') select-error @enderror">
                                <option value="">-- Tanpa Kantor / Default Personel --</option>
                                <template x-for="k in kantors" :key="k.id">
                                    <option :value="k.id" x-text="k.name"></option>
                                </template>
                            </select>
                            @error('kantorId')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Koordinat GPS --}}
                        <div class="form-control w-full">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Koordinat (Lat, Lng)
                                </span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="lat" placeholder="Latitude"
                                    class="input input-bordered w-full text-sm @error('lat') input-error @enderror">
                                <input type="text" x-model="lng" placeholder="Longitude"
                                    class="input input-bordered w-full text-sm @error('lng') input-error @enderror">
                            </div>
                            @error('lat')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                            @error('lng')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Bagian Foto Bukti Absensi --}}
                        <div class="form-control w-full md:col-span-2 pt-3 border-t border-base-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                <span class="text-sm font-medium text-base-content">
                                    Foto Absensi
                                </span>
                                <label class="label cursor-pointer gap-2 p-0 justify-start sm:justify-end">
                                    <input type="checkbox" x-model="useProfilePhoto"
                                        @change="onToggleProfilePhoto()"
                                        class="checkbox checkbox-sm checkbox-neutral">
                                    <span class="label-text text-xs text-base-content/75">
                                        Gunakan foto profil personel jika tidak mengunggah foto
                                    </span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 gap-4"
                                :class="sesi === 'keduanya' ? 'sm:grid-cols-2' : 'sm:grid-cols-1'">

                                {{-- Foto Masuk --}}
                                <div x-show="sesi === 'masuk' || sesi === 'keduanya'" class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-xs font-medium text-base-content/80">
                                            Foto Masuk (WebP maks. 60KB)
                                        </span>
                                    </label>

                                    @if ($fotoMasuk && (!method_exists($fotoMasuk, 'isPreviewable') || $fotoMasuk->isPreviewable()))
                                        <div wire:key="foto-masuk-preview-{{ $uploadIteration }}">
                                            <div
                                                class="w-full aspect-4/3 bg-base-200/70 rounded-xl overflow-hidden border border-base-300 flex items-center justify-center">
                                                <img src="{{ $fotoMasuk->temporaryUrl() }}" alt="Preview Foto Masuk"
                                                    class="w-full h-full object-contain">
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs">
                                                <span class="text-base-content/70" x-show="compressInfoMasuk"
                                                    x-text="compressInfoMasuk"></span>
                                                <button type="button" wire:click="removeFoto('masuk')"
                                                    @click="resetSlot('masuk')"
                                                    class="text-error hover:underline font-medium flex items-center gap-1 ml-auto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    <span>Hapus Foto</span>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div wire:key="foto-masuk-dropzone-{{ $uploadIteration }}"
                                            class="relative flex flex-col items-center justify-center w-full min-h-44 border-2 border-dashed border-base-300 rounded-xl p-4 text-center transition-colors"
                                            :class="useProfilePhoto ? 'opacity-60 bg-base-200/50 cursor-not-allowed' : 'bg-base-200/30 hover:bg-base-200/50'"
                                            @dragover.prevent
                                            @drop.prevent="if (!useProfilePhoto && !isProcessingMasuk && $event.dataTransfer.files && $event.dataTransfer.files[0]) processFile($event.dataTransfer.files[0], 'masuk')">

                                            {{-- Progress kompresi & upload --}}
                                            <div x-show="isProcessingMasuk" x-cloak
                                                class="flex flex-col items-center justify-center w-full py-2 px-2">
                                                <span class="loading loading-spinner loading-md mb-2"></span>
                                                <span class="text-xs font-medium text-base-content"
                                                    x-text="statusTextMasuk"></span>
                                                <span class="text-xs text-base-content/60 mt-0.5"
                                                    x-show="compressInfoMasuk" x-text="compressInfoMasuk"></span>
                                                <div class="w-full max-w-52 mt-3">
                                                    <div class="w-full bg-base-300 rounded-full h-2 overflow-hidden">
                                                        <div class="bg-base-content h-2 rounded-full transition-all duration-150"
                                                            :style="`width: ${progressMasuk}%`"></div>
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-base-content/60 mt-1">
                                                        <span>Proses</span>
                                                        <span x-text="progressMasuk + '%'"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Default / Preview fallback foto profil --}}
                                            <div x-show="!isProcessingMasuk"
                                                class="flex flex-col items-center justify-center w-full">
                                                <template x-if="useProfilePhoto && selectedPersonnel && selectedPersonnel.has_foto">
                                                    <div class="flex flex-col items-center mb-2">
                                                        <img :src="selectedPersonnel.foto_url" alt="Foto Profil"
                                                            class="w-16 h-16 rounded-lg object-cover border border-base-300 mb-1.5">
                                                        <span class="text-xs text-base-content/70">
                                                            Menggunakan foto profil personel
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="!useProfilePhoto || !selectedPersonnel || !selectedPersonnel.has_foto">
                                                    <div class="flex flex-col items-center mb-2 text-base-content/50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="size-8 mb-1">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                        </svg>
                                                        <span class="text-xs">
                                                            Tarik foto ke sini atau pilih file (otomatis WebP &le; 60KB)
                                                        </span>
                                                    </div>
                                                </template>

                                                <button type="button" @click="$refs.inputFotoMasuk.click()"
                                                    :disabled="useProfilePhoto"
                                                    class="btn btn-sm btn-outline border-base-300 mt-1 gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.8"
                                                        stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                                    </svg>
                                                    <span>Pilih Foto</span>
                                                </button>

                                                <input type="file" x-ref="inputFotoMasuk"
                                                    :disabled="useProfilePhoto"
                                                    @change="handleFileSelect($event, 'masuk')"
                                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                                    class="hidden">
                                            </div>

                                            <div x-show="errorMsgMasuk" x-cloak
                                                class="mt-2 text-error text-xs font-medium"
                                                x-text="errorMsgMasuk"></div>
                                        </div>
                                    @endif

                                    @error('fotoMasuk')
                                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Foto Pulang --}}
                                <div x-show="sesi === 'pulang' || sesi === 'keduanya'" class="form-control w-full">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text text-xs font-medium text-base-content/80">
                                            Foto Pulang (WebP maks. 60KB)
                                        </span>
                                    </label>

                                    @if ($fotoPulang && (!method_exists($fotoPulang, 'isPreviewable') || $fotoPulang->isPreviewable()))
                                        <div wire:key="foto-pulang-preview-{{ $uploadIteration }}">
                                            <div
                                                class="w-full aspect-4/3 bg-base-200/70 rounded-xl overflow-hidden border border-base-300 flex items-center justify-center">
                                                <img src="{{ $fotoPulang->temporaryUrl() }}" alt="Preview Foto Pulang"
                                                    class="w-full h-full object-contain">
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs">
                                                <span class="text-base-content/70" x-show="compressInfoPulang"
                                                    x-text="compressInfoPulang"></span>
                                                <button type="button" wire:click="removeFoto('pulang')"
                                                    @click="resetSlot('pulang')"
                                                    class="text-error hover:underline font-medium flex items-center gap-1 ml-auto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    <span>Hapus Foto</span>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div wire:key="foto-pulang-dropzone-{{ $uploadIteration }}"
                                            class="relative flex flex-col items-center justify-center w-full min-h-44 border-2 border-dashed border-base-300 rounded-xl p-4 text-center transition-colors"
                                            :class="useProfilePhoto ? 'opacity-60 bg-base-200/50 cursor-not-allowed' : 'bg-base-200/30 hover:bg-base-200/50'"
                                            @dragover.prevent
                                            @drop.prevent="if (!useProfilePhoto && !isProcessingPulang && $event.dataTransfer.files && $event.dataTransfer.files[0]) processFile($event.dataTransfer.files[0], 'pulang')">

                                            {{-- Progress kompresi & upload --}}
                                            <div x-show="isProcessingPulang" x-cloak
                                                class="flex flex-col items-center justify-center w-full py-2 px-2">
                                                <span class="loading loading-spinner loading-md mb-2"></span>
                                                <span class="text-xs font-medium text-base-content"
                                                    x-text="statusTextPulang"></span>
                                                <span class="text-xs text-base-content/60 mt-0.5"
                                                    x-show="compressInfoPulang" x-text="compressInfoPulang"></span>
                                                <div class="w-full max-w-52 mt-3">
                                                    <div class="w-full bg-base-300 rounded-full h-2 overflow-hidden">
                                                        <div class="bg-base-content h-2 rounded-full transition-all duration-150"
                                                            :style="`width: ${progressPulang}%`"></div>
                                                    </div>
                                                    <div class="flex justify-between text-[11px] text-base-content/60 mt-1">
                                                        <span>Proses</span>
                                                        <span x-text="progressPulang + '%'"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Default / Preview fallback foto profil --}}
                                            <div x-show="!isProcessingPulang"
                                                class="flex flex-col items-center justify-center w-full">
                                                <template x-if="useProfilePhoto && selectedPersonnel && selectedPersonnel.has_foto">
                                                    <div class="flex flex-col items-center mb-2">
                                                        <img :src="selectedPersonnel.foto_url" alt="Foto Profil"
                                                            class="w-16 h-16 rounded-lg object-cover border border-base-300 mb-1.5">
                                                        <span class="text-xs text-base-content/70">
                                                            Menggunakan foto profil personel
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="!useProfilePhoto || !selectedPersonnel || !selectedPersonnel.has_foto">
                                                    <div class="flex flex-col items-center mb-2 text-base-content/50">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="size-8 mb-1">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                        </svg>
                                                        <span class="text-xs">
                                                            Tarik foto ke sini atau pilih file (otomatis WebP &le; 60KB)
                                                        </span>
                                                    </div>
                                                </template>

                                                <button type="button" @click="$refs.inputFotoPulang.click()"
                                                    :disabled="useProfilePhoto"
                                                    class="btn btn-sm btn-outline border-base-300 mt-1 gap-1.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.8"
                                                        stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                                    </svg>
                                                    <span>Pilih Foto</span>
                                                </button>

                                                <input type="file" x-ref="inputFotoPulang"
                                                    :disabled="useProfilePhoto"
                                                    @change="handleFileSelect($event, 'pulang')"
                                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                                    class="hidden">
                                            </div>

                                            <div x-show="errorMsgPulang" x-cloak
                                                class="mt-2 text-error text-xs font-medium"
                                                x-text="errorMsgPulang"></div>
                                        </div>
                                    @endif

                                    @error('fotoPulang')
                                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan (Opsional) --}}
                        <div class="form-control w-full md:col-span-2">
                            <label class="label mb-1 px-1">
                                <span class="label-text text-sm font-medium text-base-content">
                                    Keterangan
                                </span>
                            </label>
                            <textarea x-model="keterangan" rows="2"
                                class="textarea textarea-bordered w-full @error('keterangan') textarea-error @enderror"
                                placeholder="Catatan tambahan (opsional)..."></textarea>
                            @error('keterangan')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="card-footer border-t border-base-200 p-6 flex justify-end gap-3">
                    <button type="button" wire:click="resetForm"
                        class="btn btn-ghost"
                        wire:loading.attr="disabled"
                        :disabled="isProcessingMasuk || isProcessingPulang">
                        Reset Form
                    </button>
                    <button type="submit"
                        class="btn btn-neutral px-8"
                        wire:loading.attr="disabled"
                        wire:target="save, selectPersonnel, updatedTanggal, fotoMasuk, fotoPulang"
                        :disabled="isProcessingMasuk || isProcessingPulang || isBlockedByShift">
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="save">Simpan Data</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        (function() {
            function registerAbsensiInputForm() {
                if (typeof Alpine !== 'undefined' && Alpine.data) {
                    Alpine.data('absensiInputForm', (config) => ({
                        personnels: config.personnels || [],
                        shifts: config.shifts || [],
                        kantors: config.kantors || [],
                        toleransiMenit: Number(config.toleransiMenit || 15),

                        searchQuery: '',
                        dropdownOpen: false,
                        selectedPersonnel: null,

                        tanggal: @entangle('tanggal'),
                        shiftId: @entangle('shiftId'),
                        sesi: @entangle('sesi'),
                        jamMasuk: @entangle('jamMasuk'),
                        jamPulang: @entangle('jamPulang'),
                        statusMasuk: @entangle('statusMasuk'),
                        statusPulang: @entangle('statusPulang'),
                        kantorId: @entangle('kantorId'),
                        lat: @entangle('lat'),
                        lng: @entangle('lng'),
                        useProfilePhoto: @entangle('useProfilePhoto'),
                        keterangan: @entangle('keterangan'),

                        needsShiftSelection: @entangle('needsShiftSelection'),
                        activeShiftStart: @entangle('activeShiftStart'),
                        activeShiftEnd: @entangle('activeShiftEnd'),

                        isProcessingMasuk: false,
                        hasUploadedMasuk: false,
                        progressMasuk: 0,
                        statusTextMasuk: '',
                        compressInfoMasuk: '',
                        errorMsgMasuk: '',

                        isProcessingPulang: false,
                        hasUploadedPulang: false,
                        progressPulang: 0,
                        statusTextPulang: '',
                        compressInfoPulang: '',
                        errorMsgPulang: '',

                        get filteredPersonnels() {
                            const q = (this.searchQuery || '').toLowerCase().trim();
                            if (!q) {
                                return this.personnels.slice(0, 50);
                            }
                            return this.personnels
                                .filter((p) =>
                                    (p.name && p.name.toLowerCase().includes(q)) ||
                                    (p.nik && String(p.nik).toLowerCase().includes(q)) ||
                                    (p.opd_name && p.opd_name.toLowerCase().includes(q))
                                )
                                .slice(0, 50);
                        },

                        get isBlockedByShift() {
                            return Boolean(this.selectedPersonnel && this.needsShiftSelection && !this.shiftId);
                        },

                        choosePersonnel(p) {
                            this.selectedPersonnel = p;
                            this.searchQuery = p.name;
                            this.dropdownOpen = false;
                            this.kantorId = p.kantor_id ? String(p.kantor_id) : '';
                            this.lat = p.kantor_lat || '';
                            this.lng = p.kantor_lng || '';
                            this.$wire.selectPersonnel(p.id);
                        },

                        clearPersonnel() {
                            this.selectedPersonnel = null;
                            this.searchQuery = '';
                            this.dropdownOpen = false;
                            this.$wire.selectPersonnel(null);
                        },

                        applyServerContext(detail) {
                            const payload = Array.isArray(detail) ? (detail[0] || {}) : (detail || {});
                            if (payload.sesi !== undefined) this.sesi = payload.sesi;
                            if (payload.shiftId !== undefined) this.shiftId = payload.shiftId ? String(payload.shiftId) : '';
                            if (payload.jamMasuk !== undefined) this.jamMasuk = payload.jamMasuk || '';
                            if (payload.jamPulang !== undefined) this.jamPulang = payload.jamPulang || '';
                            if (payload.statusMasuk !== undefined) this.statusMasuk = payload.statusMasuk || 'HADIR';
                            if (payload.statusPulang !== undefined) this.statusPulang = payload.statusPulang || 'HADIR';
                            if (payload.kantorId !== undefined) this.kantorId = payload.kantorId ? String(payload.kantorId) : '';
                            if (payload.lat !== undefined) this.lat = payload.lat || '';
                            if (payload.lng !== undefined) this.lng = payload.lng || '';
                        },

                        activeShiftTimes() {
                            if (this.shiftId) {
                                const found = this.shifts.find((s) => String(s.id) === String(this.shiftId));
                                if (found) {
                                    return {
                                        start: found.start_time || null,
                                        end: found.end_time || null
                                    };
                                }
                            }
                            return {
                                start: this.activeShiftStart || null,
                                end: this.activeShiftEnd || null
                            };
                        },

                        onShiftSelected(val) {
                            const found = this.shifts.find((s) => String(s.id) === String(val));
                            if (found) {
                                if (!this.jamMasuk && found.start_time) {
                                    this.jamMasuk = found.start_time;
                                }
                                if (!this.jamPulang && found.end_time) {
                                    this.jamPulang = found.end_time;
                                }
                            }
                            this.recomputeStatusMasuk();
                            this.recomputeStatusPulang();
                        },

                        onKantorChanged(val) {
                            const found = this.kantors.find((k) => String(k.id) === String(val));
                            if (found) {
                                this.lat = found.latitude;
                                this.lng = found.longitude;
                            } else {
                                this.lat = '';
                                this.lng = '';
                            }
                        },

                        applyShiftTime(type) {
                            const times = this.activeShiftTimes();
                            if (type === 'masuk' && times.start) {
                                this.jamMasuk = times.start;
                                this.recomputeStatusMasuk();
                            } else if (type === 'pulang' && times.end) {
                                this.jamPulang = times.end;
                                this.recomputeStatusPulang();
                            }
                        },

                        applyNowTime(type) {
                            const now = new Date();
                            const hh = String(now.getHours()).padStart(2, '0');
                            const mm = String(now.getMinutes()).padStart(2, '0');
                            const formatted = `${hh}:${mm}`;
                            if (type === 'masuk') {
                                this.jamMasuk = formatted;
                                this.recomputeStatusMasuk();
                            } else {
                                this.jamPulang = formatted;
                                this.recomputeStatusPulang();
                            }
                        },

                        toMinutes(hm) {
                            if (!hm || !hm.includes(':')) return null;
                            const parts = hm.split(':');
                            return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
                        },

                        recomputeStatusMasuk() {
                            const times = this.activeShiftTimes();
                            const inMin = this.toMinutes(this.jamMasuk);
                            const startMin = this.toMinutes(times.start);
                            if (inMin === null || startMin === null) {
                                this.statusMasuk = 'HADIR';
                                return;
                            }
                            this.statusMasuk = (inMin > (startMin + this.toleransiMenit)) ? 'TELAT' : 'HADIR';
                        },

                        recomputeStatusPulang() {
                            const times = this.activeShiftTimes();
                            const outMin = this.toMinutes(this.jamPulang);
                            const startMin = this.toMinutes(times.start);
                            const endMin = this.toMinutes(times.end);
                            if (outMin === null || endMin === null) {
                                this.statusPulang = 'HADIR';
                                return;
                            }
                            const isNightShift = startMin !== null && startMin >= endMin;
                            if (isNightShift) {
                                // Shift malam: jam pulang pada hari yang sama (>= startMin) atau sebelum endMin dianggap PC
                                if (outMin >= startMin || outMin < endMin) {
                                    this.statusPulang = 'PC';
                                } else {
                                    this.statusPulang = 'HADIR';
                                }
                            } else {
                                this.statusPulang = (outMin < endMin) ? 'PC' : 'HADIR';
                            }
                        },

                        resetSlot(slot) {
                            if (slot === 'masuk') {
                                if (this.$refs.inputFotoMasuk) this.$refs.inputFotoMasuk.value = '';
                                this.isProcessingMasuk = false;
                                this.hasUploadedMasuk = false;
                                this.progressMasuk = 0;
                                this.statusTextMasuk = '';
                                this.compressInfoMasuk = '';
                                this.errorMsgMasuk = '';
                            } else {
                                if (this.$refs.inputFotoPulang) this.$refs.inputFotoPulang.value = '';
                                this.isProcessingPulang = false;
                                this.hasUploadedPulang = false;
                                this.progressPulang = 0;
                                this.statusTextPulang = '';
                                this.compressInfoPulang = '';
                                this.errorMsgPulang = '';
                            }
                        },

                        onToggleProfilePhoto() {
                            if (this.useProfilePhoto) {
                                const hadUpload = this.hasUploadedMasuk || this.hasUploadedPulang;
                                this.resetSlot('masuk');
                                this.resetSlot('pulang');
                                if (hadUpload) {
                                    this.$wire.clearUploadedPhotos();
                                }
                            }
                        },

                        resetClientState() {
                            this.selectedPersonnel = null;
                            this.searchQuery = '';
                            this.dropdownOpen = false;
                            this.resetSlot('masuk');
                            this.resetSlot('pulang');
                        },

                        handleFileSelect(event, slot) {
                            if (this.useProfilePhoto) return;
                            const file = event.target.files && event.target.files[0];
                            if (!file) return;
                            event.target.value = '';
                            this.processFile(file, slot);
                        },

                        setSlotState(slot, patch) {
                            const suffix = slot === 'masuk' ? 'Masuk' : 'Pulang';
                            Object.keys(patch).forEach((k) => {
                                this[k + suffix] = patch[k];
                            });
                        },

                        async processFile(file, slot) {
                            if (!file || this.useProfilePhoto) return;

                            if (!file.type || !file.type.startsWith('image/')) {
                                const msg = 'File yang dipilih harus berupa gambar.';
                                this.setSlotState(slot, { errorMsg: msg });
                                this.$dispatch('toast', { type: 'error', message: msg });
                                return;
                            }

                            const maxOriginalBytes = 10 * 1024 * 1024;
                            if (file.size > maxOriginalBytes) {
                                const msg = 'Ukuran file melebihi batas maksimal 10MB.';
                                this.setSlotState(slot, { errorMsg: msg });
                                this.$dispatch('toast', { type: 'error', message: msg });
                                return;
                            }

                            this.setSlotState(slot, {
                                isProcessing: true,
                                progress: 10,
                                statusText: 'Membaca gambar...',
                                compressInfo: '',
                                errorMsg: ''
                            });

                            try {
                                this.setSlotState(slot, {
                                    statusText: 'Mengompresi ke WebP (maks. 60KB)...',
                                    progress: 25
                                });

                                const compressedFile = await this.compressToWebp(
                                    file,
                                    60 * 1024,
                                    (stepPct) => {
                                        this.setSlotState(slot, {
                                            progress: 25 + Math.round(stepPct * 0.30)
                                        });
                                    }
                                );

                                const origSizeStr = file.size >= 1024 * 1024
                                    ? (file.size / (1024 * 1024)).toFixed(1) + 'MB'
                                    : Math.round(file.size / 1024) + 'KB';
                                const finalSizeStr = Math.max(1, Math.round(compressedFile.size / 1024)) + 'KB';
                                const finalExt = (compressedFile.name.split('.').pop() || 'WEBP').toUpperCase();

                                this.setSlotState(slot, {
                                    compressInfo: `${origSizeStr} → ${finalSizeStr} (${finalExt})`,
                                    statusText: 'Mengunggah ke server...',
                                    progress: 60
                                });

                                const targetProp = slot === 'masuk' ? 'fotoMasuk' : 'fotoPulang';

                                await new Promise((resolve, reject) => {
                                    this.$wire.upload(
                                        targetProp,
                                        compressedFile,
                                        () => {
                                            this.setSlotState(slot, {
                                                progress: 100,
                                                statusText: 'Selesai',
                                                isProcessing: false,
                                                hasUploaded: true
                                            });
                                            resolve();
                                        },
                                        (error) => {
                                            reject(new Error(error || 'Gagal mengunggah foto.'));
                                        },
                                        (event) => {
                                            if (event.detail && event.detail.progress) {
                                                const uploadPct = event.detail.progress;
                                                this.setSlotState(slot, {
                                                    progress: 60 + Math.round(uploadPct * 0.4),
                                                    statusText: `Mengunggah... ${uploadPct}%`
                                                });
                                            }
                                        }
                                    );
                                });
                            } catch (err) {
                                const errMsg = err.message || 'Gagal memproses gambar.';
                                this.setSlotState(slot, {
                                    errorMsg: errMsg,
                                    isProcessing: false
                                });
                                this.$dispatch('toast', { type: 'error', message: errMsg });
                            }
                        },

                        compressToWebp(file, maxBytes = 60 * 1024, onProgress = null) {
                            return new Promise((resolve, reject) => {
                                const testCanvas = document.createElement('canvas');
                                testCanvas.width = 1;
                                testCanvas.height = 1;
                                const supportsWebp = testCanvas.toDataURL('image/webp').startsWith('data:image/webp');
                                const targetMime = supportsWebp ? 'image/webp' : 'image/jpeg';
                                const targetExt = supportsWebp ? 'webp' : 'jpg';

                                const reader = new FileReader();
                                reader.onerror = () => reject(new Error('Gagal membaca file gambar.'));
                                reader.onload = (e) => {
                                    const img = new Image();
                                    img.onerror = () => reject(new Error('File bukan gambar yang valid.'));
                                    img.onload = async () => {
                                        try {
                                            let destW = img.naturalWidth || img.width;
                                            let destH = img.naturalHeight || img.height;
                                            const maxDim = 1080;

                                            if (destW > maxDim || destH > maxDim) {
                                                if (destW > destH) {
                                                    destH = Math.round((destH * maxDim) / destW);
                                                    destW = maxDim;
                                                } else {
                                                    destW = Math.round((destW * maxDim) / destH);
                                                    destH = maxDim;
                                                }
                                            }

                                            let currentCanvas = document.createElement('canvas');
                                            currentCanvas.width = destW;
                                            currentCanvas.height = destH;
                                            const ctx = currentCanvas.getContext('2d');
                                            if (targetMime === 'image/jpeg') {
                                                ctx.fillStyle = '#FFFFFF';
                                                ctx.fillRect(0, 0, destW, destH);
                                            }
                                            ctx.drawImage(img, 0, 0, destW, destH);

                                            let quality = 0.82;
                                            let lastBlob = null;
                                            const maxIterations = 18;

                                            for (let i = 1; i <= maxIterations; i++) {
                                                if (onProgress) {
                                                    onProgress(Math.min(100, Math.round((i / maxIterations) * 100)));
                                                }

                                                const blob = await new Promise((res) => {
                                                    currentCanvas.toBlob(res, targetMime, quality);
                                                });

                                                if (!blob) {
                                                    throw new Error('Browser gagal mengonversi gambar.');
                                                }
                                                lastBlob = blob;

                                                if (blob.size <= maxBytes) {
                                                    const baseName = (file.name || 'foto')
                                                        .replace(/\.[^/.]+$/, '')
                                                        .replace(/[^a-zA-Z0-9_-]/g, '_');
                                                    resolve(new File([blob], `${baseName}.${targetExt}`, {
                                                        type: blob.type || targetMime,
                                                        lastModified: Date.now()
                                                    }));
                                                    return;
                                                }

                                                if (quality > 0.30) {
                                                    quality = Math.max(0.20, quality - 0.12);
                                                } else if (currentCanvas.width > 320 && currentCanvas.height > 240) {
                                                    const nextW = Math.round(currentCanvas.width * 0.75);
                                                    const nextH = Math.round(currentCanvas.height * 0.75);
                                                    const scaled = document.createElement('canvas');
                                                    scaled.width = nextW;
                                                    scaled.height = nextH;
                                                    const sCtx = scaled.getContext('2d');
                                                    if (targetMime === 'image/jpeg') {
                                                        sCtx.fillStyle = '#FFFFFF';
                                                        sCtx.fillRect(0, 0, nextW, nextH);
                                                    }
                                                    sCtx.drawImage(currentCanvas, 0, 0, nextW, nextH);
                                                    currentCanvas = scaled;
                                                    quality = 0.72;
                                                } else {
                                                    quality = Math.max(0.05, quality - 0.06);
                                                }
                                            }

                                            const baseName = (file.name || 'foto')
                                                .replace(/\.[^/.]+$/, '')
                                                .replace(/[^a-zA-Z0-9_-]/g, '_');
                                            resolve(new File([lastBlob], `${baseName}.${targetExt}`, {
                                                type: (lastBlob && lastBlob.type) || targetMime,
                                                lastModified: Date.now()
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
                registerAbsensiInputForm();
            } else {
                document.addEventListener('alpine:init', registerAbsensiInputForm);
            }
        })();
    </script>
</div>
