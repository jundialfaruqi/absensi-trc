<?php

use App\Imports\JadwalImport;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new #[Title('Import Jadwal')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public $file;

    public $month;

    public $year;

    public $showConfirmModal = false;

    public $importId;

    public $progress = null;

    public function mount()
    {
        $this->month = request('month', date('m'));
        $this->year = request('year', date('Y'));
    }

    public function rules()
    {
        return [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'month' => 'required',
            'year' => 'required',
        ];
    }

    public function updatedFile()
    {
        if ($this->file) {
            try {
                $mimeType = $this->file->getMimeType();
                $allowedMimes = [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv',
                    'text/plain',
                    'application/csv',
                    'application/excel',
                    'application/vnd.ms-excel',
                    'application/vnd.msexcel',
                ];

                if (! in_array($mimeType, $allowedMimes)) {
                    $this->reset('file');
                    $this->addError('file', 'File yang diunggah bukan merupakan dokumen Excel atau CSV yang valid.');

                    return;
                }
            } catch (Exception $e) {
                $this->reset('file');
                $this->addError('file', 'File tidak dapat dibaca atau rusak.');

                return;
            }
        }

        $this->validateOnly('file');
    }

    public function import()
    {
        $this->validate();

        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        // Check if data already exists
        $exists = Jadwal::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function ($q) use ($opdId) {
                if ($opdId) {
                    $q->where('opd_id', $opdId);
                }
            })
            ->exists();

        if ($exists) {
            $this->showConfirmModal = true;

            return;
        }

        $this->executeImport(false);
    }

    public function confirmImport()
    {
        $this->executeImport(true);
    }

    protected function executeImport($shouldReset)
    {
        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        try {
            $path = $this->file->store('imports');
            $importId = (string) Str::uuid();

            // Set progress awal di Cache
            Cache::put("import_progress_{$importId}", [
                'total' => 0,
                'processed' => 0,
                'status' => 'processing',
                'percentage' => 0,
            ], 3600);

            Excel::queueImport(new JadwalImport($this->month, $this->year, $opdId, $shouldReset, $importId), $path);

            $this->importId = $importId;
            $this->progress = [
                'total' => 0,
                'processed' => 0,
                'status' => 'processing',
                'percentage' => 0,
            ];

            $this->showConfirmModal = false;
            $this->dispatch('toast', type: 'info', title: 'Impor Dimulai', message: 'Proses impor sedang berjalan di latar belakang.');
        } catch (Exception $e) {
            $this->showConfirmModal = false;
            $this->addError('file', 'Terjadi kesalahan saat mengimpor file: '.$e->getMessage());
        }
    }

    public function checkProgress()
    {
        if ($this->importId) {
            $progressData = Cache::get("import_progress_{$this->importId}");
            if ($progressData) {
                $this->progress = $progressData;

                if ($progressData['status'] === 'failed') {
                    $errorMsg = $progressData['error'] ?? 'Terjadi kesalahan saat mengimpor file di antrean.';
                    $this->addError('file', 'Gagal memproses file di antrean: '.$errorMsg);
                }
            }
        }
    }

    public function finishImport()
    {
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Jadwal berhasil diimpor.');
        $this->reset(['file', 'importId', 'progress']);
        $this->showConfirmModal = false;

        return $this->redirectRoute('jadwal', navigate: true);
    }

    public function resetImport()
    {
        $this->reset(['file', 'importId', 'progress']);
        $this->showConfirmModal = false;
    }
};
