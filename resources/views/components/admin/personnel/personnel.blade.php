<div wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Personnel</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data dan akun personnel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li>
                    <a href="{{ route('personnel') }}">
                        <span class="text-base-content font-bold">Personnel</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>


    {{-- ─── Toolbar: Search + Buttons ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="join">
                    <span
                        class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                    <select wire:model.live="perPage" class="select join-item w-20 rounded-end-md">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-auto">
                    <input type="text" placeholder="Nama, Email..." wire:model.live.debounce.400ms="search"
                        class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                @if (auth()->user()->hasRole('super-admin') || auth()->user()->can('lihat-personel-all-opd'))
                    <div class="w-full sm:w-auto">
                        <select wire:model.live="selectedOpd" class="select select-bordered w-full sm:w-64 bg-base-100">
                            <option value="">Semua OPD (Filter)</option>
                            @foreach ($this->opds as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" class="btn btn-neutral gap-2"
                x-on:click="window.dispatchEvent(new CustomEvent('open-personnel-download-modal'))">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download
            </button>

            @if (auth()->user()->hasRole('super-admin') || auth()->user()->can('create-personel-all-opd') || auth()->user()->can('create-personel-opd'))
                <button wire:click="goToAdd" class="btn btn-neutral gap-2" wire:loading.attr="disabled">
                    <span wire:loading wire:target="goToAdd" class="loading loading-spinner loading-xs"></span>
                    <svg wire:loading.remove wire:target="goToAdd" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Personnel
                </button>
            @endif
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            {{-- ─── Skeleton Loading ────────────────────────── --}}
            <div class="overflow-x-auto"
                @if ($readyToLoad) wire:loading wire:target="perPage, selectedOpd, search, gotoPage, nextPage, previousPage" @endif>
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-center w-16">#</th>
                            <th>Personnel</th>
                            <th>Kontak</th>
                            <th>Penugasan</th>
                            <th>Kantor / Lokasi</th>
                            <th>OPD Induk</th>
                            <th class="text-center">Face</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < ($perPage > 10 ? 10 : $perPage); $i++)
                            <tr>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-4 mx-auto"></div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                                        <div class="flex flex-col gap-2">
                                            <div class="skeleton h-4 w-32"></div>
                                            <div class="skeleton h-3 w-24"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="skeleton h-4 w-24"></div>
                                </td>
                                <td>
                                    <div class="skeleton h-5 w-20 rounded-full"></div>
                                </td>
                                <td>
                                    <div class="skeleton h-4 w-32 mb-1"></div>
                                    <div class="skeleton h-3 w-16"></div>
                                </td>
                                <td>
                                    <div class="skeleton h-4 w-28"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-4 w-12 mx-auto rounded-full"></div>
                                </td>
                                <td class="text-center">
                                    <div class="skeleton h-6 w-6 mx-auto rounded-full"></div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- ─── Real Table Data ────────────────────────────────────────── --}}
            @if ($readyToLoad)
                <div class="overflow-x-auto" wire:loading.remove
                    wire:target="perPage, selectedOpd, search, gotoPage, nextPage, previousPage">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="text-center w-16">#</th>
                                <th>Nama Personnel</th>
                                <th>Mode Absensi</th>
                                <th>Kontak</th>
                                <th>Penugasan</th>
                                <th>Kantor / Lokasi</th>
                                <th>OPD Induk</th>
                                <th class="text-center">Face</th>
                                <th class="text-center w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $currentOpd = null;
                                $opdCounter = 0;
                                $isSuperAdmin = auth()->user()->hasRole('super-admin');
                            @endphp
                            @forelse ($this->personnels as $r)
                                @if ($loop->first || ($isSuperAdmin && $currentOpd !== $r->opd_id))
                                    @php
                                        $opdCounter = $this->getOpdOffset($r->id, $r->opd_id);
                                    @endphp
                                    @if ($isSuperAdmin && $currentOpd !== $r->opd_id)
                                        <tr class="bg-base-200">
                                            <td colspan="9"
                                                class="sticky left-0 top-12 z-50 p-0 border-b border-base-200 bg-base-200">
                                                <div class="sticky left-0 w-fit px-4 py-2 flex items-center gap-2">
                                                    <div class="w-1.5 h-4 bg-base-content"></div>
                                                    <span
                                                        class="text-[11px] font-black uppercase tracking-[0.2em] text-base-content whitespace-nowrap">
                                                        {{ $r->opd?->singkatan ?? ($r->opd?->name ?? 'TANPA OPD') }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    @php $currentOpd = $r->opd_id; @endphp
                                @endif
                                @php $opdCounter++; @endphp
                                <tr class="hover:bg-base-200/50">
                                    <td class="text-center font-bold">
                                        {{ $opdCounter }}
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                @if ($r->foto)
                                                    <div class="w-10 rounded-full">
                                                        <img src="{{ asset('storage/' . $r->foto) }}"
                                                            alt="{{ $r->name }}" />
                                                    </div>
                                                @else
                                                    <div
                                                        class="flex items-center justify-center bg-neutral text-neutral-content w-10 rounded-full">
                                                        <span
                                                            class="text-lg">{{ strtoupper(substr($r->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold">{{ $r->name }}</div>
                                                <div class="text-xs opacity-70">{{ $r->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div
                                            class="badge badge-sm font-medium text-nowrap text-white {{ $r->attendance_type === 'FLEXIBLE' ? 'badge-error' : 'badge-primary' }}">
                                            {{ ucfirst($r->attendance_type) }}</div>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $r->nomor_hp ?: '-' }}</div>
                                    </td>
                                    <td>
                                        @if ($r->penugasan)
                                            <div class="badge badge-secondary badge-sm font-medium text-nowrap">
                                                {{ $r->penugasan->name }}</div>
                                        @else
                                            <div class="text-xs italic text-base-content/50">-</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->kantor)
                                            <div class="text-sm font-bold">{{ $r->kantor->name }}</div>
                                            @if ($r->wajib_absen_di_lokasi)
                                                <div
                                                    class="badge badge-success badge-xs font-bold text-[8px] text-white uppercase">
                                                    Location On</div>
                                            @else
                                                <div
                                                    class="badge badge-error badge-xs font-bold text-[8px] text-white uppercase">
                                                    Location Off</div>
                                            @endif
                                        @else
                                            <div class="text-xs italic text-base-content/50">Belum diatur</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->opd)
                                            <div class="text-sm font-semibold mb-1">{{ $r->opd->singkatan }}</div>
                                        @else
                                            <div class="text-sm text-base-content/50 italic mb-1">Tanpa OPD</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($r->face_recognition)
                                            <div
                                                class="badge badge-success badge-xs font-bold text-[8px] text-white text-nowrap uppercase">
                                                Face On
                                            </div>
                                        @else
                                            <div
                                                class="badge badge-ghost badge-xs font-bold text-[8px] uppercase text-nowrap opacity-50">
                                                Face Off</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown dropdown-left dropdown-end">
                                            <button tabindex="0"
                                                class="btn btn-ghost btn-xs btn-square rounded-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                </svg>
                                            </button>
                                            <ul tabindex="0"
                                                class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                                @if (auth()->user()->hasRole('super-admin') || auth()->user()->can('edit-personel-all-opd') || (auth()->user()->can('edit-personel-opd') && auth()->user()->opd()?->id === $r->opd_id))
                                                    <li>
                                                        <a wire:navigate
                                                            href="{{ route('personnel.edit', $r->id) }}">Edit</a>
                                                    </li>
                                                @endif
                                                <li>
                                                    @php
                                                        $waPhone = preg_replace('/^0/', '62', $r->nomor_hp);
                                                        $licenseKey =
                                                            $r->devices->first()?->license_key ?? 'Belum dibuat';
                                                        $waText = "Halo *{$r->name}*, Berikut adalah data akun Personnel Anda:\n\n";
                                                        $waText .= "Nama: {$r->name}\n";
                                                        $waText .= "NIK: {$r->nik}\n";
                                                        $waText .= "Email: {$r->email}\n";
                                                        $waText .= "PIN: *{$r->pin}*\n";
                                                        $waText .= "License Key: *{$licenseKey}*\n\n";
                                                        $waText .=
                                                            "Silakan download aplikasi TRC (Android) melalui link berikut:\n";
                                                        $waText .= route('download-app') . "\n\n";
                                                        $waText .= '*(Gunakan PIN Anda untuk mengunduh APK)*';

                                                        $waUrl = "https://wa.me/{$waPhone}?text=" . urlencode($waText);
                                                    @endphp
                                                    <a href="{{ $waUrl }}" target="_blank"
                                                        class="text-success">
                                                        Kirim WA
                                                    </a>
                                                </li>
                                                @if (auth()->user()->hasRole('super-admin') || auth()->user()->can('delete-personel-all-opd') || (auth()->user()->can('delete-personel-opd') && auth()->user()->opd()?->id === $r->opd_id))
                                                    <li>
                                                        <button type="button" class="text-error"
                                                            wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">Delete</button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-sm text-base-content/60 py-8">Tidak ada
                                        data Personnel</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">
                    @if ($readyToLoad)
                        {{ $this->personnels->links('components.admin.pagination') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- ─── Modal: Download Personnel (Client-Side UX) ─────────────────────── --}}
    <dialog id="personnel-download-modal" class="modal"
        x-data="personnelDownloadModal({
            excelUrl: '{{ route('personnel.export-excel') }}',
            pdfUrl: '{{ route('personnel.export-pdf') }}',
            canSelectAllOpd: {{ auth()->user()->hasRole('super-admin') || auth()->user()->can('lihat-personel-all-opd') ? 'true' : 'false' }},
            defaultOpdId: '{{ auth()->user()->hasRole('super-admin') || auth()->user()->can('lihat-personel-all-opd') ? '' : (auth()->user()->opd()?->id ?? '') }}'
        })"
        x-on:open-personnel-download-modal.window="openModal($el)"
        x-on:close="resetValidation()">
        <div class="modal-box max-w-lg bg-base-100 border border-base-300">
            <div class="flex items-center justify-between pb-3 border-b border-base-200">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 text-base-content">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <h3 class="font-bold text-lg text-base-content">Download Data Personnel</h3>
                </div>
                <button type="button" class="btn btn-ghost btn-sm btn-square" x-on:click="closeModal($el)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form x-on:submit.prevent="submitDownload($el)" class="mt-4 space-y-4">
                {{-- Filter OPD --}}
                <div class="form-control">
                    <label class="label pt-0">
                        <span class="label-text font-medium text-base-content">Filter OPD</span>
                    </label>
                    @if (auth()->user()->hasRole('super-admin') || auth()->user()->can('lihat-personel-all-opd'))
                        <select x-model="opdId" class="select select-bordered w-full bg-base-100">
                            <option value="">Semua OPD</option>
                            @foreach ($this->opds as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" readonly
                            value="{{ auth()->user()->opd()?->name ?? 'OPD Anda' }}"
                            class="input input-bordered w-full bg-base-200/60 text-base-content/70 cursor-not-allowed" />
                    @endif
                </div>

                {{-- Pilihan Jenis Download --}}
                <div class="form-control">
                    <label class="label pt-0">
                        <span class="label-text font-medium text-base-content">Pilih Format Download</span>
                    </label>
                    <div class="space-y-2">
                        <label
                            class="flex items-start gap-3 p-3 rounded-lg border border-base-300 cursor-pointer hover:bg-base-200/40 transition-colors"
                            :class="downloadType === 'raw' ? 'border-base-content bg-base-200/40' : ''">
                            <input type="radio" name="download_type" value="raw" x-model="downloadType"
                                class="radio radio-sm mt-0.5" />
                            <div class="text-sm">
                                <div class="font-semibold text-base-content">Download Data Raw Personel (Excel)</div>
                                <div class="text-xs text-base-content/60 mt-0.5">
                                    Berisi nama, email, mode absen, kontak, penugasan, kantor lokasi, dan OPD induk.
                                </div>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-3 rounded-lg border border-base-300 cursor-pointer hover:bg-base-200/40 transition-colors"
                            :class="downloadType === 'blank_columns' ? 'border-base-content bg-base-200/40' : ''">
                            <input type="radio" name="download_type" value="blank_columns" x-model="downloadType"
                                class="radio radio-sm mt-0.5" />
                            <div class="text-sm">
                                <div class="font-semibold text-base-content">Tambahkan Kolom Kosong (PDF)</div>
                                <div class="text-xs text-base-content/60 mt-0.5">
                                    Berisi nomor dan nama personel dikelompokkan per OPD beserta kolom tambahan kosong.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Konfigurasi Kolom Kosong --}}
                <div x-show="downloadType === 'blank_columns'" x-cloak
                    class="space-y-3 pt-2 border-t border-base-200">
                    <div class="form-control">
                        <label class="label pt-0">
                            <span class="label-text font-medium text-base-content">Jumlah Kolom Tambahan</span>
                            <span class="label-text-alt text-base-content/60">Maksimal 5 kolom</span>
                        </label>
                        <input type="number" min="1" max="5" x-model.number="columnCount"
                            x-on:input="syncColumns()"
                            class="input input-bordered w-full bg-base-100"
                            placeholder="Masukkan angka 1 - 5" />
                    </div>

                    <div class="space-y-2">
                        <template x-for="(col, index) in columns" :key="index">
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium text-base-content"
                                        x-text="'Judul Kolom ' + (index + 1) + ' *'"></span>
                                </label>
                                <input type="text" x-model="columns[index]" maxlength="100"
                                    class="input input-bordered input-sm w-full bg-base-100"
                                    :placeholder="index === 0 ? 'Contoh: Setuju' : (index === 1 ? 'Contoh: Tidak Setuju' : 'Masukkan judul kolom')" />
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Pesan Error Client / Server --}}
                <div x-show="errorMessage" x-cloak
                    class="p-3 rounded-lg border border-base-300 bg-base-200/60 text-xs text-error flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span x-text="errorMessage"></span>
                </div>

                <div class="modal-action pt-2">
                    <button type="button" class="btn" x-on:click="closeModal($el)"
                        x-bind:disabled="isDownloading">Batal</button>
                    <button type="submit" class="btn btn-neutral gap-2" x-bind:disabled="isDownloading">
                        <span x-show="isDownloading" class="loading loading-spinner loading-xs"></span>
                        <svg x-show="!isDownloading" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span x-text="isDownloading ? 'Memproses...' : 'Download'"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="personnel-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'personnel-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'personnel-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus data Personnel
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('personnel-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>

    <script>
        window.personnelDownloadModal = function(config) {
            return {
                excelUrl: config.excelUrl,
                pdfUrl: config.pdfUrl,
                canSelectAllOpd: config.canSelectAllOpd,
                opdId: config.defaultOpdId || '',
                downloadType: 'raw',
                columnCount: 2,
                columns: ['', ''],
                errorMessage: '',
                isDownloading: false,

                openModal(dialogEl) {
                    this.errorMessage = '';
                    this.isDownloading = false;
                    this.syncColumns();
                    if (dialogEl && typeof dialogEl.showModal === 'function') {
                        dialogEl.showModal();
                    }
                },

                closeModal(el) {
                    const dialog = el ? el.closest('dialog') : document.getElementById('personnel-download-modal');
                    if (dialog && typeof dialog.close === 'function') {
                        dialog.close();
                    }
                },

                resetValidation() {
                    this.errorMessage = '';
                },

                syncColumns() {
                    let count = parseInt(this.columnCount, 10);
                    if (isNaN(count) || count < 1) {
                        count = 1;
                    } else if (count > 5) {
                        count = 5;
                        this.columnCount = 5;
                    }

                    const updated = [];
                    for (let i = 0; i < count; i++) {
                        updated.push(this.columns[i] ?? '');
                    }
                    this.columns = updated;
                },

                async submitDownload(formEl) {
                    this.errorMessage = '';

                    const params = new URLSearchParams();
                    if (this.canSelectAllOpd && this.opdId) {
                        params.append('opd_id', this.opdId);
                    }

                    let targetUrl = this.excelUrl;
                    let fallbackFilename = 'data_raw_personnel.xlsx';

                    if (this.downloadType === 'blank_columns') {
                        const count = parseInt(this.columnCount, 10);
                        if (isNaN(count) || count < 1 || count > 5) {
                            this.errorMessage = 'Jumlah kolom tambahan harus antara 1 sampai 5 kolom.';
                            return;
                        }

                        this.syncColumns();
                        const trimmedColumns = this.columns.map(col => (col || '').trim());
                        const emptyIndex = trimmedColumns.findIndex(col => col === '');
                        if (emptyIndex !== -1) {
                            this.errorMessage = `Judul Kolom ${emptyIndex + 1} wajib diisi.`;
                            return;
                        }

                        trimmedColumns.forEach(col => params.append('columns[]', col));
                        targetUrl = this.pdfUrl;
                        fallbackFilename = 'daftar_personnel.pdf';
                    }

                    this.isDownloading = true;

                    try {
                        const response = await fetch(`${targetUrl}?${params.toString()}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json, application/pdf, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            let message = 'Gagal mengunduh file.';
                            try {
                                const errJson = await response.json();
                                message = errJson.message || message;
                            } catch (_) {
                                // Ignore non-JSON error responses
                            }
                            throw new Error(message);
                        }

                        const blob = await response.blob();
                        const headerFilename = response.headers.get('X-Filename');
                        const filename = headerFilename || fallbackFilename;

                        const blobUrl = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = blobUrl;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(blobUrl);

                        this.closeModal(formEl);
                    } catch (err) {
                        this.errorMessage = err.message || 'Terjadi kesalahan saat mengunduh file.';
                    } finally {
                        this.isDownloading = false;
                    }
                },
            };
        };
    </script>

</div>
