<div x-data="{
    importId: @entangle('importId'),
    progress: { total: 0, processed: 0, status: 'processing', percentage: 0, error: '' },
    listen() {
        if (this.importId) {
            this.progress = { total: 0, processed: 0, status: 'processing', percentage: 0, error: '' };
            
            // Inisialisasi Echo secara lazy jika belum ada
            const EchoConstructor = window._EchoHandler || window.Echo;
            if (!window.Echo && typeof EchoConstructor === 'function') {
                const reverbHost = '{{ env('REVERB_HOST') }}';
                const wsHost = (reverbHost === '127.0.0.1' || reverbHost === 'localhost' || !reverbHost) ?
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
            }

            if (window.Echo) {
                window.Echo.channel('import-channel.' + this.importId)
                    .listen('ImportProgressUpdated', (e) => {
                        this.progress = e.progress;
                    });
            } else {
                console.error('WebSocket (Echo) tidak dapat diinisialisasi.');
            }
        }
    }
}" x-init="listen(); $watch('importId', value => listen())">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Import Jadwal</h1>
            <p class="text-sm text-base-content/60 mt-1">Unggah jadwal shift secara massal</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data</li>
                <li><a wire:navigate href="{{ route('jadwal') }}">Jadwal</a></li>
                <li><span class="text-base-content font-bold">Import</span></li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg mb-4">Langkah-langkah Import</h3>
                <ul class="steps steps-vertical text-sm">
                    <li class="step step-primary">
                        <div class="text-left ml-2">
                            <span class="font-medium text-base">Unduh Template</span>
                            <p class="text-base-content/60 text-xs mt-1">Pilih periode (Bulan & Tahun) lalu klik tombol
                                unduh template matrix di bawah.</p>
                        </div>
                    </li>
                    <li class="step step-primary">
                        <div class="text-left ml-2">
                            <span class="font-medium text-base">Isi Data Jadwal</span>
                            <p class="text-base-content/60 text-xs mt-1">Buka file Excel dan isi kolom tanggal dengan
                                <b>Nama Shift</b> (PAGI, SIANG, MALAM) atau ketik <b>LIBUR</b>. Lihat daftar referensi
                                shift di bagian bawah file.
                            </p>
                        </div>
                    </li>
                    <li class="step step-primary">
                        <div class="text-left ml-2">
                            <span class="font-medium text-base">Unggah File</span>
                            <p class="text-base-content/60 text-xs mt-1">Simpan perubahan file Anda, lalu unggah kembali
                                pada kolom di samping untuk memproses jadwal.</p>
                        </div>
                    </li>
                </ul>
                <div class="mt-8 space-y-4">
                    <div class="flex gap-4">
                        <div class="form-control flex-1">
                            <label class="label p-1">
                                <span class="label-text text-xs text-base-content font-medium">Bulan</span>
                            </label>
                            <select wire:model.live="month" class="select select-bordered select-sm w-full">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-control flex-1">
                            <label class="label p-1">
                                <span class="label-text text-xs text-base-content font-medium">Tahun</span>
                            </label>
                            <select wire:model.live="year" class="select select-bordered select-sm w-full">
                                @for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <a href="{{ route('jadwal.download-template', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-outline btn-primary w-full gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Template Matrix
                    </a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-6">
                <h3 class="font-bold text-lg mb-4">Upload File Excel</h3>
                <form wire:submit="import">
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text font-medium text-base-content mb-2">Pilih File (.xlsx, .xls) <span
                                    class="text-error">*</span></span>
                        </label>
                        <input type="file" wire:model="file"
                            class="file-input file-input-bordered focus:file-input-primary w-full @error('file') file-input-error @enderror"
                            accept=".xlsx,.xls,.csv" onchange="validateImportFile(this)" />
                        @error('file')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror

                        @if ($file && !$errors->has('file'))
                            <div
                                class="mt-4 p-4 bg-primary/10 rounded-xl border border-primary/20 flex gap-3 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-primary shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <div class="truncate flex-1">
                                    <div class="font-medium text-sm text-primary">{{ $file->getClientOriginalName() }}
                                    </div>
                                    <div class="text-xs text-primary/70">Siap diproses</div>
                                </div>
                            </div>
                        @else
                            <div
                                class="mt-4 p-8 border-2 border-dashed border-base-300 rounded-xl flex flex-col items-center justify-center text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-12 h-12 text-base-content/20 mb-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                </svg>
                                <span class="text-sm text-base-content/50">Atau seret dan lepas file Anda ke sini</span>
                            </div>
                        @endif

                        <div wire:loading wire:target="file" class="mt-2 text-xs text-info font-medium italic">
                            Mengunggah file...</div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-neutral flex-1" wire:loading.attr="disabled"
                            {{ !$file ? 'disabled' : '' }}>
                            <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                            <span wire:loading.remove wire:target="import">Proses Import</span>
                        </button>
                        <a wire:navigate href="{{ route('jadwal') }}" class="btn btn-ghost">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Konfirmasi Reset --}}
    @if ($showConfirmModal)
        <div class="modal modal-open backdrop-blur-sm">
            <div class="modal-box shadow-2xl border border-error/20 max-w-md">
                <div class="flex items-center gap-4 text-error mb-4">
                    <div class="p-3 bg-error/10 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-xl leading-tight">Data Sudah Ada!</h3>
                        <p class="text-[10px] uppercase font-black opacity-40 tracking-widest">Konfirmasi Penghapusan
                        </p>
                    </div>
                </div>

                <div class="py-4 space-y-4">
                    <p class="text-sm leading-relaxed">
                        Sistem mendeteksi bahwa sudah ada data <span class="font-bold">Jadwal</span> dan <span
                            class="font-bold">Absensi</span> untuk periode <span
                            class="badge badge-neutral font-bold">{{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }}
                            {{ $year }}</span>.
                    </p>
                    <div class="alert alert-error bg-error/5 text-[11px] py-3 rounded-xl border-error/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="stroke-error shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-medium">Melanjutkan proses ini akan <span
                                class="underline font-bold">MENGHAPUS
                                PERMANEN</span> seluruh data Jadwal, Absensi, dan <span
                                class="font-bold uppercase">FILE
                                FOTO ABSENSI</span> pada periode tersebut.</span>
                    </div>
                    <div class="text-center">
                        <p class="text-[11px] text-base-content/50 italic">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div class="modal-action grid grid-cols-2 gap-3 mt-2">
                    <button type="button" wire:click="$set('showConfirmModal', false)"
                        class="btn btn-ghost border-base-300">Batal</button>
                    <button type="button" wire:click="confirmImport" class="btn btn-error text-white">
                        <span wire:loading wire:target="confirmImport"
                            class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="confirmImport">Ya, Bersihkan & Import</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Progress Modal Overlay --}}
    <template x-if="importId">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-base-300/60 backdrop-blur-sm transition-all duration-300">
            <div class="card w-full max-w-md bg-base-100 shadow-2xl border border-base-200 p-6 mx-4">
                <div class="flex flex-col items-center text-center">
                    
                    <!-- Success State -->
                    <template x-if="progress.status === 'completed'">
                        <div class="w-full flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full bg-success/15 flex items-center justify-center text-success mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>

                            <h3 class="font-black text-xl text-base-content mb-1">Proses Selesai</h3>
                            <p class="text-sm text-base-content/60 mb-6">Data jadwal berhasil diimpor sepenuhnya ke sistem.</p>

                            <!-- Progress Bar Container (Static Green) -->
                            <div class="w-full bg-base-200 rounded-full h-3 mb-4 overflow-hidden">
                                <div class="bg-success h-full rounded-full w-full"></div>
                            </div>

                            <div class="text-xs text-base-content/75 font-semibold bg-success/10 py-2 px-4 rounded-lg border border-success/15 mb-6">
                                Berhasil memproses <span x-text="Number(progress.processed).toLocaleString('id-ID')"></span> baris data jadwal.
                            </div>

                            <button type="button" wire:click="finishImport" class="btn btn-success text-white w-full">
                                Selesai & Lihat Jadwal
                            </button>
                        </div>
                    </template>

                    <!-- Failed State -->
                    <template x-if="progress.status === 'failed'">
                        <div class="w-full flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full bg-error/15 flex items-center justify-center text-error mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </div>

                            <h3 class="font-black text-xl text-error mb-1">Impor Gagal</h3>
                            <p class="text-sm text-base-content/60 mb-4">Terjadi kendala saat memproses file Excel.</p>

                            <!-- Error Message Container -->
                            <div class="w-full text-xs text-left text-error bg-error/5 p-4 rounded-xl border border-error/20 mb-6 max-h-32 overflow-y-auto font-mono"
                                 x-text="progress.error || 'Terjadi kesalahan tidak dikenal saat mengimpor data.'">
                            </div>

                            <button type="button" wire:click="resetImport" class="btn btn-neutral w-full">
                                Tutup
                            </button>
                        </div>
                    </template>

                    <!-- Processing State -->
                    <template x-if="progress.status !== 'completed' && progress.status !== 'failed'">
                        <div class="w-full flex flex-col items-center">
                            <div class="relative mb-6">
                                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary animate-pulse">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                                    </svg>
                                </div>
                                <span class="absolute inset-0 rounded-full border-4 border-primary border-t-transparent animate-spin"></span>
                            </div>

                            <h3 class="font-black text-xl text-base-content mb-1">Sedang Memproses Impor</h3>
                            <p class="text-sm text-base-content/60 mb-6">Harap tunggu, server sedang mengimpor data jadwal Anda.</p>

                            <!-- Percentage Indicator -->
                            <div class="flex items-baseline justify-center gap-1 mb-2">
                                <span class="text-3xl font-black text-primary" x-text="progress.percentage">0</span>
                                <span class="text-sm font-bold text-primary">%</span>
                            </div>

                            <!-- Progress Bar Container -->
                            <div class="w-full bg-base-200 rounded-full h-3 mb-4 overflow-hidden">
                                <div class="bg-primary h-full rounded-full transition-all duration-500 ease-out animate-pulse"
                                     :style="'width: ' + progress.percentage + '%'"></div>
                            </div>

                            <!-- Processed Data Info -->
                            <div class="text-xs text-base-content/50 font-medium mb-4">
                                <span x-show="progress.total > 0">
                                    Memproses <span x-text="Number(progress.processed).toLocaleString('id-ID')"></span> dari <span x-text="Number(progress.total).toLocaleString('id-ID')"></span> baris data...
                                </span>
                                <span x-show="!progress.total || progress.total <= 0">
                                    Menghubungkan ke antrean & menghitung total data...
                                </span>
                            </div>

                            <div class="py-2 px-4 bg-info/10 rounded-lg text-info border border-info/15 text-[11px] font-semibold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Jangan menutup atau menyegarkan halaman ini.</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <script>
        function validateImportFile(input) {
            const file = input.files[0];
            if (!file) return;

            const allowedExtensions = ['xlsx', 'xls', 'csv'];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(extension)) {
                alert('Format file tidak didukung! Harap gunakan file .xlsx, .xls, atau .csv');
                input.value = '';
            }
        }
    </script>
</div>

