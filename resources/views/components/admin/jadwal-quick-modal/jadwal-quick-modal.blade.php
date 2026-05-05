<div>
    <dialog id="quick-add-modal" class="modal" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'quick-add-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'quick-add-modal' && $el.close()">
        <div class="modal-box max-w-md">
            {{-- Tabs --}}
            <div class="tabs tabs-boxed mb-6 bg-base-200/50 p-1">
                <button type="button" wire:click="$set('activeTab', 'quick')"
                    class="tab tab-sm flex-1 {{ $activeTab === 'quick' ? 'tab-active bg-base-100! shadow-sm' : '' }}">
                    <span wire:loading wire:target="$set('activeTab', 'quick')"
                        class="loading loading-spinner loading-xs mr-2"></span>
                    Quick Edit
                </button>
                <button type="button" wire:click="$set('activeTab', 'swap')"
                    class="tab tab-sm flex-1 {{ $activeTab === 'swap' ? 'tab-active bg-base-100! shadow-sm' : '' }}">
                    <span wire:loading wire:target="$set('activeTab', 'swap')"
                        class="loading loading-spinner loading-xs mr-2"></span>
                    Tukar Shift
                </button>
            </div>

            <div class="relative min-h-[300px]">
                {{-- Modal Content Loading Overlay --}}
                <div wire:loading wire:target="activeTab"
                    class="absolute inset-0 z-50 flex items-center justify-center rounded-xl bg-base-100/50 backdrop-blur-[1px]">
                    <div class="flex flex-col items-center gap-3">
                        <span class="loading loading-spinner loading-md text-primary"></span>
                        <span class="text-xs font-medium opacity-60">Memuat data...</span>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-lg">
                            {{ $activeTab === 'quick' ? 'Set Jadwal / Status' : 'Tukar Shift (2 Arah)' }}
                        </h3>
                        <p class="text-xs text-base-content/60">
                            {{ $quickDate ? \Carbon\Carbon::parse($quickDate)->translatedFormat('l, d M Y') : '' }}
                        </p>
                    </div>
                </div>

                @if ($activeTab === 'quick')
                    <form wire:submit="saveQuickJadwal">
                        <div class="space-y-6">
                            {{-- Status Selection --}}
                            <div class="form-control">
                                <label class="label mb-1 px-1">
                                    <span class="label-text font-medium text-xs text-base-content">Pilih Status
                                        Kehadiran</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach (['SHIFT', 'OFF'] as $status)
                                        <label
                                            class="label cursor-pointer justify-start gap-2 p-2 border border-base-200 rounded-lg hover:bg-base-200 transition-all {{ $quickStatus == $status ? 'bg-primary/10 border-primary' : '' }}">
                                            <input type="radio" wire:model.live="quickStatus"
                                                value="{{ $status }}" class="radio radio-primary radio-xs">
                                            <span class="text-xs font-bold text-base-content">{{ $status }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Shift Selection --}}
                            <div class="form-control w-full animate-in fade-in slide-in-from-top-1">
                                <label class="label mb-1 px-1">
                                    <span class="label-text font-medium text-xs text-base-content">
                                        {{ $quickStatus === 'SHIFT' ? 'Pilih Shift' : 'Pilih Status OFF' }}
                                    </span>
                                </label>
                                <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto pr-1">
                                    @foreach ($this->shifts as $s)
                                        <label wire:key="shift-{{ $s->id }}"
                                            class="label cursor-pointer justify-start gap-3 p-3 border border-base-200 rounded-xl hover:bg-base-200 transition-all {{ (int) $quickShiftId === (int) $s->id ? 'bg-primary/10 border-primary' : '' }}">
                                            <input type="radio" wire:model.live="quickShiftId"
                                                value="{{ $s->id }}" class="radio radio-primary radio-sm">
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-bold text-xs text-base-content">{{ $s->name }}</span>
                                                <span class="text-[10px] opacity-60 text-base-content">
                                                    @if ($s->type === 'shift')
                                                        <div>{{ $s->keterangan }}</div>
                                                        {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}
                                                    @else
                                                        {{ $s->keterangan }}
                                                    @endif
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('quickShiftId')
                                    <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Keterangan (Only for OFF) --}}
                            @if ($quickStatus === 'OFF')
                                <div class="form-control w-full animate-in fade-in slide-in-from-top-1">
                                    <label class="label mb-1 px-1">
                                        <span class="label-text font-medium text-xs">Keterangan Status</span>
                                    </label>
                                    <textarea wire:model="quickKeterangan" class="textarea textarea-bordered w-full h-24 text-sm focus:textarea-primary"
                                        placeholder="Tulis catatan alasan di sini..."></textarea>
                                    @error('quickKeterangan')
                                        <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div class="modal-action flex justify-between gap-2 mt-8">
                            @if ($this->originJadwal)
                                <button type="button" wire:click="deleteJadwal"
                                    wire:confirm="Apakah Anda yakin ingin menghapus jadwal dan seluruh data absensi personil ini pada tanggal {{ $quickDate }}? Foto absensi juga akan dihapus permanen."
                                    class="btn btn-error btn-sm text-white px-4" wire:loading.attr="disabled">
                                    <span wire:loading wire:target="deleteJadwal"
                                        class="loading loading-spinner loading-xs"></span>
                                    Hapus
                                </button>
                            @else
                                <div></div>
                            @endif
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-ghost btn-sm"
                                    x-on:click="document.getElementById('quick-add-modal').close()">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm px-6" wire:loading.attr="disabled">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="space-y-6">
                        {{-- Origin Personnel Info --}}
                        <div class="bg-base-200/50 rounded-xl p-3 border border-base-200">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div
                                        class="bg-primary text-primary-content rounded-full w-8 flex items-center justify-center">
                                        <span
                                            class="text-xs">{{ substr($this->originPersonnel->name ?? 'P', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider opacity-60 font-bold">Pemohon
                                        Tukar:</span>
                                    <span
                                        class="text-sm font-bold text-primary">{{ $this->originPersonnel->name ?? '-' }}</span>
                                    <span class="text-[10px] font-medium opacity-70">
                                        Jadwal Asli:
                                        <span class="font-bold text-primary italic">
                                            {{ ($this->originJadwal->status ?? '') === 'SHIFT' ? $this->originJadwal->shift->name ?? 'SHIFT' : $this->originJadwal->status ?? '-' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Select Target Personnel --}}
                        <div class="form-control">
                            <label class="label mb-1 px-1">
                                <span class="label-text font-medium text-xs text-base-content/70">Pilih Personel
                                    Pengganti
                                    (Sedang Libur)</span>
                            </label>
                            <select wire:model.live="swapTargetPersonnelId"
                                class="select select-bordered w-full select-sm focus:select-primary">
                                <option value="">-- Pilih Personel --</option>
                                @foreach ($this->availableSubstitutes as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-base-content/50 mt-2 italic">* Hanya menampilkan personel yang
                                libur
                                dan tidak memiliki tabrakan jadwal Malam-Siang.</p>
                        </div>

                        @if ($swapWarning)
                            <div
                                class="p-4 bg-warning/10 border border-warning/20 rounded-2xl flex items-start gap-3 animate-in shake duration-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="text-warning shrink-0 mt-0.5">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 9v4" />
                                    <path
                                        d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                    <path d="M12 16h.01" />
                                </svg>
                                <div>
                                    <h4 class="text-xs font-black uppercase text-warning mb-1">Peringatan Istirahat</h4>
                                    <p class="text-[10px] leading-tight opacity-80">{!! $swapWarning !!}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-action flex justify-end gap-2 mt-8 pt-4 border-t border-base-200">
                        <button type="button" class="btn btn-ghost btn-sm"
                            x-on:click="document.getElementById('quick-add-modal').close()">Batal</button>
                        <button type="button" class="btn btn-primary btn-sm px-6" wire:click="executeSwapGuling"
                            wire:loading.attr="disabled" @if (!$swapTargetPersonnelId) disabled @endif>
                            <span wire:loading wire:target="executeSwapGuling"
                                class="loading loading-spinner loading-xs"></span>
                            Proses Substitusi
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>
