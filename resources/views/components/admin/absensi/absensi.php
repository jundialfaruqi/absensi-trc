<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new #[Title('Monitoring Absensi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $search = '';
    public string $month = '';
    public string $year = '';

    // Form Edit properties
    public $editingPersonnelId;
    public $editingTanggal;
    public $editingAbsensiId;
    public $editingPersonnelName;

    public $statusMasuk;
    public $statusPulang;
    public $jamMasuk;
    public $jamPulang;
    public $alasanEdit;
    public $nomorSurat;
    public $cutiId;
    public $keterangan;
    public bool $isEdited = false;

    public function mount(): void
    {
        $this->month = Carbon::now()->format('m');
        $this->year = Carbon::now()->format('Y');
    }

    #[Computed]
    public function dates(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = Carbon::create($this->year, $this->month, $i)->format('Y-m-d');
        }
        return $dates;
    }

    #[Computed]
    public function personnels()
    {
        $opdId = Auth::user()->opd()?->id;

        $paginator = Personnel::with(['absensis' => function ($query) {
                $query->whereYear('tanggal', $this->year)
                      ->whereMonth('tanggal', $this->month)
                      ->with('kantor');
            }, 'jadwals' => function ($query) {
                $query->whereYear('tanggal', $this->year)
                      ->whereMonth('tanggal', $this->month)
                      ->with('shift');
            }, 'penugasan'])
            ->when(!Auth::user()->hasRole('super-admin'), function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            })
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        // Key their data by date for easy lookup in the view
        $paginator->getCollection()->transform(function ($personnel) {
            $personnel->absensi_map = $personnel->absensis->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
            $personnel->jadwal_map = $personnel->jadwals->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));
            return $personnel;
        });

        return $paginator;
    }

    #[Computed]
    public function cutis()
    {
        return Cuti::orderBy('name')->get();
    }

    public function editAbsensi($personnelId, $tanggal)
    {
        $this->resetEditForm();
        
        $personnel = Personnel::findOrFail($personnelId);
        
        // Authorization check
        if (!Auth::user()->hasRole('super-admin') && $personnel->opd_id !== Auth::user()->opd()?->id) {
            return;
        }

        $this->editingPersonnelId = $personnelId;
        $this->editingPersonnelName = $personnel->name;
        $this->editingTanggal = $tanggal;

        $absensi = Absensi::where('personnel_id', $personnelId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($absensi) {
            $this->editingAbsensiId = $absensi->id;
            $this->statusMasuk = $absensi->status_masuk;
            $this->statusPulang = $absensi->status_pulang;
            $this->jamMasuk = $absensi->jam_masuk ? Carbon::parse($absensi->jam_masuk)->format('H:i') : null;
            $this->jamPulang = $absensi->jam_pulang ? Carbon::parse($absensi->jam_pulang)->format('H:i') : null;
            $this->alasanEdit = $absensi->alasan_edit;
            $this->nomorSurat = $absensi->nomor_surat;
            $this->cutiId = $absensi->cuti_id;
            $this->keterangan = $absensi->keterangan;
            $this->isEdited = !is_null($absensi->original_status_masuk);
        }

        $this->dispatch('open-modal', id: 'edit-absensi-modal');
    }

    public function saveEdit()
    {
        $this->validate([
            'statusMasuk' => 'required',
            'alasanEdit' => 'required|min:5',
        ]);

        $personnel = Personnel::findOrFail($this->editingPersonnelId);
        
        // Authorization check
        if (!Auth::user()->hasRole('super-admin') && $personnel->opd_id !== Auth::user()->opd()?->id) {
            return;
        }

        $existing = Absensi::where('personnel_id', $this->editingPersonnelId)
            ->where('tanggal', $this->editingTanggal)
            ->first();

        // Capture original status ONLY if it's the first edit
        $originalStatusMasuk = $existing ? ($existing->original_status_masuk ?? $existing->status_masuk) : 'ALFA';
        $originalStatusPulang = $existing ? ($existing->original_status_pulang ?? $existing->status_pulang) : 'ALFA';

        $absensi = Absensi::updateOrCreate(
            [
                'personnel_id' => $this->editingPersonnelId,
                'tanggal' => $this->editingTanggal,
            ],
            [
                'status_masuk' => $this->statusMasuk,
                'status_pulang' => $this->statusPulang,
                'jam_masuk' => $this->jamMasuk,
                'jam_pulang' => $this->jamPulang,
                'alasan_edit' => $this->alasanEdit,
                'nomor_surat' => $this->nomorSurat,
                'cuti_id' => ($this->statusMasuk === 'CUTI' || $this->statusPulang === 'CUTI') ? $this->cutiId : null,
                'keterangan' => $this->keterangan,
                'edited_by_user_id' => Auth::id(),
                'edited_at' => now(),
                'original_status_masuk' => $originalStatusMasuk,
                'original_status_pulang' => $originalStatusPulang,
            ]
        );

        $this->dispatch('close-modal', id: 'edit-absensi-modal');
        $this->dispatch('toast', message: 'Data absensi berhasil diperbarui', type: 'success');
        
        // Re-calculate matrix
        unset($this->personnels);
    }

    public function resetToOriginal()
    {
        if (!$this->editingAbsensiId || !$this->isEdited) return;

        $absensi = Absensi::findOrFail($this->editingAbsensiId);

        // Authorization check
        if (!Auth::user()->hasRole('super-admin') && $absensi->personnel->opd_id !== Auth::user()->opd()?->id) {
            return;
        }

        if ($absensi->original_status_masuk === 'ALFA' && $absensi->original_status_pulang === 'ALFA') {
            // It was originally empty, so delete the record
            $absensi->delete();
        } else {
            // Restore original status and clear audit fields
            $absensi->update([
                'status_masuk' => $absensi->original_status_masuk,
                'status_pulang' => $absensi->original_status_pulang,
                'edited_by_user_id' => null,
                'edited_at' => null,
                'alasan_edit' => null,
                'nomor_surat' => null,
                'cuti_id' => null,
                'keterangan' => null,
                'original_status_masuk' => null, // Reset the "edited" flag
                'original_status_pulang' => null,
            ]);
        }

        $this->dispatch('close-modal', id: 'edit-absensi-modal');
        $this->dispatch('toast', message: 'Data absensi telah dikembalikan ke kondisi awal', type: 'info');
        
        unset($this->personnels);
    }

    private function resetEditForm()
    {
        $this->editingPersonnelId = null;
        $this->editingTanggal = null;
        $this->editingAbsensiId = null;
        $this->editingPersonnelName = '';
        $this->statusMasuk = '';
        $this->statusPulang = '';
        $this->jamMasuk = '';
        $this->jamPulang = '';
        $this->alasanEdit = '';
        $this->nomorSurat = '';
        $this->cutiId = null;
        $this->keterangan = '';
        $this->isEdited = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
