<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JadwalImport;
use Illuminate\Support\Facades\Auth;

new #[Title('Import Jadwal')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public $file;
    public $month;
    public $year;
    public $showConfirmModal = false;

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

                if (!in_array($mimeType, $allowedMimes)) {
                    $this->reset('file');
                    $this->addError('file', 'File yang diunggah bukan merupakan dokumen Excel atau CSV yang valid.');
                    return;
                }
            } catch (\Exception $e) {
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
        $exists = \App\Models\Jadwal::whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->whereHas('personnel', function($q) use ($opdId) {
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
            Excel::import(new JadwalImport($this->month, $this->year, $opdId, $shouldReset), $this->file);
            
            $this->dispatch('toast', type: 'success', title: 'Berhasil', message: $shouldReset ? 'Data lama dibersihkan dan Jadwal baru berhasil diimpor.' : 'Data Jadwal berhasil diimpor.');
            return $this->redirectRoute('jadwal', navigate: true);
        } catch (\Exception $e) {
            $this->showConfirmModal = false;
            $this->addError('file', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage());
        }
    }
};
