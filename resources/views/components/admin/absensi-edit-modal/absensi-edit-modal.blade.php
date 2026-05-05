<div>
    <dialog id="edit-absensi-modal" class="modal backdrop-blur-xs modal-bottom sm:modal-middle" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'edit-absensi-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'edit-absensi-modal' && $el.close()">
        <div class="modal-box p-0 shadow max-h-[80vh] max-w-2xl overflow-y-auto relative">
            {{-- Modal Header - Sticky --}}
            <div class="p-6 border-b border-base-200 bg-base-200 flex justify-between items-center sticky top-0 z-50">
                <h3 class="font-bold text-lg">
                    {{ $editingPersonnelName }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('edit-absensi-modal').close()">✕</button>
            </div>

            {{-- Modal Body - Scrollable --}}
            <div class="overflow-y-auto flex-1">
                <form wire:submit="saveEdit" class="p-6 space-y-5">
                    <div class="bg-primary/5 p-4 flex items-center justify-between rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary/10 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-5 text-primary">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase font-black opacity-40 tracking-widest">Tanggal Absen
                                </div>
                                <div class="font-bold uppercase">
                                    {{ $editingTanggal ? \Carbon\Carbon::parse($editingTanggal)->translatedFormat('l, d F Y') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bukti Foto --}}
                    @if ($editingFotoMasuk || $editingFotoPulang)
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] uppercase font-black opacity-40 tracking-widest text-base-content">Foto
                                    Masuk</label>
                                @if ($editingFotoMasuk)
                                    <div
                                        class="relative group aspect-square rounded-2xl overflow-hidden border-2 border-base-200 bg-base-200/50">
                                        <img src="{{ asset('storage/' . $editingFotoMasuk) }}"
                                            class="w-full h-full object-cover">
                                        <a href="{{ asset('storage/' . $editingFotoMasuk) }}" target="_blank"
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                                            <span class="btn btn-circle btn-sm btn-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                @else
                                    <div
                                        class="aspect-square rounded-2xl border-2 border-dashed border-base-200 flex items-center justify-center opacity-30 text-[10px] font-bold uppercase text-base-content">
                                        Tidak ada foto
                                    </div>
                                @endif

                                {{-- Device Info Masuk --}}
                                @if ($platformMasuk)
                                    <div class="mt-2 flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5">
                                            @if ($platformMasuk === 'android')
                                                <div class="badge badge-neutral badge-xs py-2 px-2 gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                    </svg>
                                                    <span class="text-[9px] font-bold uppercase">{{ $officialDeviceNameMasuk ?? ($deviceNameMasuk ?? 'Android') }}</span>
                                                </div>
                                            @else
                                                <div class="badge badge-ghost badge-xs py-2 px-2 gap-1 border-base-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                                    </svg>
                                                    <span class="text-[9px] font-bold uppercase">WEB</span>
                                                </div>
                                            @endif

                                            @if ($isOfficialDeviceMasuk)
                                                <div class="badge badge-success badge-xs py-2 px-2 gap-1 text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-2.5">
                                                        <path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-[8px] font-black tracking-tighter">TERDAFTAR</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] uppercase font-black opacity-40 tracking-widest text-base-content">Foto
                                    Pulang</label>
                                @if ($editingFotoPulang)
                                    <div
                                        class="relative group aspect-square rounded-2xl overflow-hidden border-2 border-base-200 bg-base-200/50">
                                        <img src="{{ asset('storage/' . $editingFotoPulang) }}"
                                            class="w-full h-full object-cover">
                                        <a href="{{ asset('storage/' . $editingFotoPulang) }}" target="_blank"
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                                            <span class="btn btn-circle btn-sm btn-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                @else
                                    <div
                                        class="aspect-square rounded-2xl border-2 border-dashed border-base-200 flex items-center justify-center opacity-30 text-[10px] font-bold uppercase text-base-content">
                                        Tidak ada foto
                                    </div>
                                @endif

                                {{-- Device Info Pulang --}}
                                @if ($platformPulang)
                                    <div class="mt-2 flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5">
                                            @if ($platformPulang === 'android')
                                                <div class="badge badge-neutral badge-xs py-2 px-2 gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                    </svg>
                                                    <span class="text-[9px] font-bold uppercase">{{ $officialDeviceNamePulang ?? ($deviceNamePulang ?? 'Android') }}</span>
                                                </div>
                                            @else
                                                <div class="badge badge-ghost badge-xs py-2 px-2 gap-1 border-base-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                                    </svg>
                                                    <span class="text-[9px] font-bold uppercase">WEB</span>
                                                </div>
                                            @endif

                                            @if ($isOfficialDevicePulang)
                                                <div class="badge badge-success badge-xs py-2 px-2 gap-1 text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-2.5">
                                                        <path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-[8px] font-black tracking-tighter">TERDAFTAR</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Status Masuk --}}
                        <div class="form-control">
                            <label class="label py-1"><span
                                    class="label-text text-sm font-medium text-base-content">Status
                                    Masuk</span></label>
                            <select wire:model.live="statusMasuk"
                                class="select select-bordered w-full bg-base-50 focus:border-primary">
                                <option value="">Pilih Status</option>
                                <option value="HADIR">HADIR</option>
                                <option value="TELAT">TELAT</option>
                                <option value="SAKIT">SAKIT</option>
                                <option value="IZIN">IZIN</option>
                                <option value="CUTI">CUTI</option>
                                <option value="DINAS">DINAS</option>
                                <option value="ALFA">ALFA</option>
                            </select>
                            @error('statusMasuk')
                                <span class="text-error text-[10px] mt-1 font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status Pulang --}}
                        <div class="form-control">
                            <label class="label py-1"><span
                                    class="label-text text-sm font-medium text-base-content">Status
                                    Pulang</span></label>
                            <select wire:model.live="statusPulang"
                                class="select select-bordered w-full bg-base-50 focus:border-primary">
                                <option value="">Pilih Status</option>
                                <option value="HADIR">HADIR</option>
                                <option value="PC">PC (Pulang Cepat)</option>
                                <option value="SAKIT">SAKIT</option>
                                <option value="IZIN">IZIN</option>
                                <option value="CUTI">CUTI</option>
                                <option value="DINAS">DINAS</option>
                                <option value="ALFA">ALFA</option>
                            </select>
                        </div>

                        {{-- Jam Masuk --}}
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm font-medium">Jam
                                    Masuk</span></label>
                            <div class="relative">
                                <input type="time" wire:model="jamMasuk" step="60"
                                    class="input input-bordered w-full text-base-content/70 pl-10 bg-base-50 focus:border-primary" />
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Jam Pulang --}}
                        <div class="form-control">
                            <label class="label py-1"><span class="label-text text-sm font-medium">Jam
                                    Pulang</span></label>
                            <div class="relative">
                                <input type="time" wire:model="jamPulang" step="60"
                                    class="input input-bordered w-full text-base-content/70 pl-10 bg-base-50 focus:border-primary" />
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-40">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Conditional Fields --}}
                    <div class="grid grid-cols-1 gap-4">
                        @if (in_array($statusMasuk, ['SAKIT', 'IZIN']) || in_array($statusPulang, ['SAKIT', 'IZIN']))
                            <div class="form-control">
                                <label class="label py-1"><span
                                        class="label-text text-sm font-medium text-base-content">
                                        Nomor Surat (Sakit/Izin)
                                    </span></label>
                                <input type="text" wire:model="nomorSurat"
                                    placeholder="Contoh: 123/SKP/IV/2026..."
                                    class="input w-full placeholder:text-base-content/70" />
                            </div>
                        @endif

                        @if ($statusMasuk === 'CUTI' || $statusPulang === 'CUTI')
                            <div class="form-control">
                                <label class="label py-1"><span
                                        class="label-text text-sm font-medium text-base-content">Jenis
                                        Cuti</span></label>
                                <select wire:model="cutiId"
                                    class="select select-bordered w-full bg-base-50 focus:border-primary">
                                    <option value="">Pilih Jenis Cuti</option>
                                    @foreach ($this->cutis as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    {{-- Alasan Edit --}}
                    <div class="form-control w-full">
                        <label class="label py-1.5"><span class="label-text text-sm font-medium text-base-content">
                                Alasan Perubahan / Keterangan
                            </span></label>
                        <textarea wire:model="alasanEdit"
                            class="textarea textarea-bordered w-full h-32 bg-base-50 focus:border-primary border-base-300 transition-all placeholder:text-base-content/70"
                            placeholder="Jelaskan alasan pengeditan data secara detail untuk justifikasi perubahan data"></textarea>
                        @error('alasanEdit')
                            <span class="text-error text-[10px] mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modal-action flex justify-between gap-3 pt-4 border-t border-base-200">
                        <div>
                            @if ($isEdited)
                                <button type="button" wire:click="resetToOriginal"
                                    wire:confirm="Apakah Anda yakin ingin mengembalikan data ini ke kondisi asli? Seluruh riwayat pengeditan admin akan dihapus."
                                    class="btn btn-error btn-outline btn-sm">
                                    Reset Ke Asli
                                </button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-ghost btn-sm"
                                onclick="document.getElementById('edit-absensi-modal').close()">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm px-8" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveEdit"
                                    class="loading loading-spinner loading-xs"></span>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>
