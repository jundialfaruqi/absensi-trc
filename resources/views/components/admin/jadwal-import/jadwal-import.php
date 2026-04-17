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

    public function mount()
    {
        $this->month = request('month', date('m'));
        $this->year = request('year', date('Y'));
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
            'month' => 'required',
            'year' => 'required',
        ]);

        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        try {
            Excel::import(new JadwalImport($this->month, $this->year, $opdId), $this->file);
            
            $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Jadwal berhasil diimpor.');
            return $this->redirectRoute('jadwal', navigate: true);
        } catch (\Exception $e) {
            $this->addError('file', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage());
        }
    }
};
