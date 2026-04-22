<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Import Jadwal</h1>
            <p class="text-sm text-base-content/60 mt-1">Unggah jadwal shift secara massal</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
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
                            <p class="text-base-content/60 text-xs mt-1">Unduh format template Excel yang telah
                                disediakan.</p>
                        </div>
                    </li>
                    <li class="step step-primary">
                        <div class="text-left ml-2">
                            <span class="font-medium text-base">Isi Data Jadwal</span>
                            <p class="text-base-content/60 text-xs mt-1">Buka file di Excel dan isi berdasar ID
                                Personnel dan ID Shift. Format tanggal harus YYYY-MM-DD.</p>
                        </div>
                    </li>
                    <li class="step step-primary">
                        <div class="text-left ml-2">
                            <span class="font-medium text-base">Unggah File</span>
                            <p class="text-base-content/60 text-xs mt-1">Simpan dan unggah kembali file pada kolom di
                                samping untuk memproses jadwal.</p>
                        </div>
                    </li>
                </ul>
                <div class="mt-8 space-y-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs">Bulan</span></label>
                            <select wire:model.live="month" class="select select-bordered select-sm">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs">Tahun</span></label>
                            <select wire:model.live="year" class="select select-bordered select-sm">
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
                            <span class="label-text font-medium">Pilih File (.xlsx, .xls) <span
                                    class="text-error">*</span></span>
                        </label>
                        <input type="file" wire:model="file"
                            class="file-input file-input-bordered focus:file-input-primary w-full @error('file') file-input-error @enderror"
                            accept=".xlsx,.xls,.csv"
                            onchange="validateImportFile(this)" />
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
