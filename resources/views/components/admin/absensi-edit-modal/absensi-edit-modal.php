<?php

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    // Form Edit properties
    public $editingPersonnelId;

    public $editingTanggal;

    public $editingAbsensiId;

    public $editingPersonnelName;

    public $editingPersonnelFoto;

    public $statusMasuk;

    public $statusPulang;

    public $jamMasuk;

    public $jamPulang;

    public $alasanEdit;

    public $nomorSurat;

    public $cutiId;

    public $keterangan;

    public $editingFotoMasuk;

    public $editingFotoPulang;

    public $platformMasuk;

    public $platformPulang;

    public $deviceNameMasuk;

    public $deviceNamePulang;

    public $uniqueDeviceIdMasuk;

    public $uniqueDeviceIdPulang;

    public bool $isOfficialDeviceMasuk = false;

    public bool $isOfficialDevicePulang = false;

    public $officialDeviceNameMasuk;

    public $officialDeviceNamePulang;

    public bool $isEdited = false;

    public $jadwalShiftName;

    public $jadwalJamMasuk;

    public $jadwalJamPulang;

    #[On('openEditAbsensi')]
    public function open($personnelId, $tanggal)
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->resetEditForm();

        $personnel = Personnel::findOrFail($personnelId);

        // Authorization check
        if (! $this->canEditPersonnel($personnel)) {
            $this->dispatch('close-modal', id: 'edit-absensi-modal');
            $this->dispatch('toast', message: 'Anda tidak memiliki izin untuk mengedit absensi ini.', type: 'error');

            return;
        }

        $this->editingPersonnelId = $personnelId;
        $this->editingPersonnelName = $personnel->name;
        $this->editingPersonnelFoto = $personnel->foto;
        $this->editingTanggal = $tanggal;

        $absensi = Absensi::where('personnel_id', $personnelId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Cari jadwal untuk tanggal yang diedit
        $jadwal = Jadwal::where('personnel_id', $personnelId)
            ->whereDate('tanggal', $tanggal)
            ->with('shift')
            ->first();

        if (! $jadwal && $absensi && $absensi->jadwal_id) {
            $jadwal = Jadwal::with('shift')->find($absensi->jadwal_id);
        }

        $shift = $jadwal?->shift;

        if ($shift && $shift->type !== 'off' && $shift->start_time && $shift->end_time) {
            $this->jadwalShiftName = $shift->name . ($shift->keterangan ? ' (' . $shift->keterangan . ')' : '');
            $this->jadwalJamMasuk = Carbon::parse($shift->start_time)->format('H:i');
            $this->jadwalJamPulang = Carbon::parse($shift->end_time)->format('H:i');
        } else {
            // Fallback jika tidak ada jadwal shift berjam pada tanggal tersebut
            $recentJadwal = Jadwal::where('personnel_id', $personnelId)
                ->whereHas('shift', function ($q) {
                    $q->where('type', '!=', 'off')
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time');
                })
                ->with('shift')
                ->orderByDesc('tanggal')
                ->first();

            if ($recentJadwal && $recentJadwal->shift) {
                $this->jadwalShiftName = $recentJadwal->shift->name . ($recentJadwal->shift->keterangan ? ' (' . $recentJadwal->shift->keterangan . ')' : '');
                $this->jadwalJamMasuk = Carbon::parse($recentJadwal->shift->start_time)->format('H:i');
                $this->jadwalJamPulang = Carbon::parse($recentJadwal->shift->end_time)->format('H:i');
            } else {
                $defaultShift = Shift::where('type', '!=', 'off')
                    ->whereNotNull('start_time')
                    ->whereNotNull('end_time')
                    ->first();

                if ($defaultShift) {
                    $this->jadwalShiftName = $defaultShift->name . ($defaultShift->keterangan ? ' (' . $defaultShift->keterangan . ')' : '');
                    $this->jadwalJamMasuk = Carbon::parse($defaultShift->start_time)->format('H:i');
                    $this->jadwalJamPulang = Carbon::parse($defaultShift->end_time)->format('H:i');
                } else {
                    $this->jadwalShiftName = null;
                    $this->jadwalJamMasuk = '08:00';
                    $this->jadwalJamPulang = '16:00';
                }
            }
        }

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
            $this->editingFotoMasuk = $absensi->foto_masuk;
            $this->editingFotoPulang = $absensi->foto_pulang;
            $this->platformMasuk = $absensi->platform_masuk;
            $this->platformPulang = $absensi->platform_pulang;
            $this->deviceNameMasuk = $absensi->device_name_masuk;
            $this->deviceNamePulang = $absensi->device_name_pulang;
            $this->uniqueDeviceIdMasuk = $absensi->unique_device_id_masuk;
            $this->uniqueDeviceIdPulang = $absensi->unique_device_id_pulang;

            if ($this->uniqueDeviceIdMasuk) {
                $device = Device::find($this->uniqueDeviceIdMasuk);
                $this->isOfficialDeviceMasuk = ! is_null($device);
                $this->officialDeviceNameMasuk = $device?->name;
            }
            if ($this->uniqueDeviceIdPulang) {
                $device = Device::find($this->uniqueDeviceIdPulang);
                $this->isOfficialDevicePulang = ! is_null($device);
                $this->officialDeviceNamePulang = $device?->name;
            }

            $this->isEdited = ! is_null($absensi->original_status_masuk);
        }

        $this->dispatch('open-modal', id: 'edit-absensi-modal');
        $this->dispatch('edit-absensi-loaded');
    }

    public function updatedStatusMasuk($value)
    {
        if (in_array($value, ['HADIR', 'TELAT']) && empty($this->jamMasuk) && $this->jadwalJamMasuk) {
            $this->jamMasuk = $this->jadwalJamMasuk;
        }
    }

    public function updatedStatusPulang($value)
    {
        if (in_array($value, ['HADIR', 'PC']) && empty($this->jamPulang) && $this->jadwalJamPulang) {
            $this->jamPulang = $this->jadwalJamPulang;
        }
    }

    public function applyJadwalMasuk()
    {
        if ($this->jadwalJamMasuk) {
            $this->jamMasuk = $this->jadwalJamMasuk;
            if ($this->statusMasuk === 'ALPA' || empty($this->statusMasuk)) {
                $this->statusMasuk = 'HADIR';
            }
        }
    }

    public function applyJadwalPulang()
    {
        if ($this->jadwalJamPulang) {
            $this->jamPulang = $this->jadwalJamPulang;
            if ($this->statusPulang === 'ALPA' || empty($this->statusPulang)) {
                $this->statusPulang = 'HADIR';
            }
        }
    }

    public function closeModal()
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->resetEditForm();
        $this->dispatch('close-modal', id: 'edit-absensi-modal');
    }

    public function saveEdit()
    {
        $this->validate([
            'statusMasuk' => 'required',
            'alasanEdit' => 'required|min:5',
        ], [
            'statusMasuk.required' => 'Status masuk wajib dipilih.',
            'alasanEdit.required' => 'Alasan perubahan data wajib diisi.',
            'alasanEdit.min' => 'Alasan perubahan data minimal :min karakter.',
        ]);

        $personnel = Personnel::findOrFail($this->editingPersonnelId);

        // Authorization check
        if (! $this->canEditPersonnel($personnel)) {
            $this->dispatch('close-modal', id: 'edit-absensi-modal');
            $this->dispatch('toast', message: 'Anda tidak memiliki izin untuk mengedit absensi ini.', type: 'error');

            return;
        }

        $existing = Absensi::where('personnel_id', $this->editingPersonnelId)
            ->whereDate('tanggal', $this->editingTanggal)
            ->first();

        // Capture original status ONLY if it's the first edit
        $originalStatusMasuk = $existing ? ($existing->original_status_masuk ?? $existing->status_masuk) : 'ALPA';
        $originalStatusPulang = $existing ? ($existing->original_status_pulang ?? $existing->status_pulang) : 'ALPA';

        $attributes = [
            'status' => $this->statusMasuk,
            'status_masuk' => $this->statusMasuk,
            'status_pulang' => $this->statusPulang,
            'jam_masuk' => ! empty($this->jamMasuk) ? $this->jamMasuk : null,
            'jam_pulang' => ! empty($this->jamPulang) ? $this->jamPulang : null,
            'alasan_edit' => $this->alasanEdit,
            'nomor_surat' => $this->nomorSurat,
            'cuti_id' => ($this->statusMasuk === 'CUTI' || $this->statusPulang === 'CUTI') ? $this->cutiId : null,
            'keterangan' => $this->keterangan,
            'edited_by_user_id' => Auth::id(),
            'edited_at' => now(),
            'original_status_masuk' => $originalStatusMasuk,
            'original_status_pulang' => $originalStatusPulang,
        ];

        if ($existing) {
            $existing->update($attributes);
        } else {
            Absensi::create(array_merge([
                'personnel_id' => $this->editingPersonnelId,
                'tanggal' => $this->editingTanggal,
            ], $attributes));
        }

        $this->dispatch('close-modal', id: 'edit-absensi-modal');
        $this->dispatch('toast', message: 'Data absensi berhasil diperbarui', type: 'success');
        $this->dispatch('refreshAbsensi');
    }

    public function resetToOriginal()
    {
        if (! $this->editingAbsensiId || ! $this->isEdited) {
            return;
        }

        $absensi = Absensi::findOrFail($this->editingAbsensiId);

        // Authorization check
        if (! $this->canEditPersonnel($absensi->personnel)) {
            $this->dispatch('close-modal', id: 'edit-absensi-modal');
            $this->dispatch('toast', message: 'Anda tidak memiliki izin untuk mengedit absensi ini.', type: 'error');

            return;
        }

        if ($absensi->original_status_masuk === 'ALPA' && $absensi->original_status_pulang === 'ALPA') {
            // It was originally a placeholder (ALPA/LIBUR), so restore that state
            $jadwal = $absensi->jadwal;
            $placeholderStatus = ($jadwal && $jadwal->status === 'LIBUR') ? 'LIBUR' : 'ALPA';

            $absensi->update([
                'status' => $placeholderStatus,
                'status_masuk' => null,
                'status_pulang' => null,
                'jam_masuk' => null,
                'jam_pulang' => null,
                'edited_by_user_id' => null,
                'edited_at' => null,
                'alasan_edit' => null,
                'nomor_surat' => null,
                'cuti_id' => null,
                'keterangan' => null,
                'original_status_masuk' => null,
                'original_status_pulang' => null,
            ]);
        } else {
            // Restore original status and clear audit fields
            $absensi->update([
                'status' => $absensi->original_status_masuk,
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
        $this->dispatch('toast', message: 'Data absensi telah dikembalikan ke kondisi awal', type: 'success');
        $this->dispatch('refreshAbsensi');
    }

    public function resetAbsensi()
    {
        if (! $this->editingAbsensiId) {
            return;
        }

        // Permission check
        if (! Auth::user()->can('reset-absen')) {
            $this->dispatch('toast', message: 'Anda tidak memiliki izin untuk mereset absensi.', type: 'error');

            return;
        }

        $absensi = Absensi::findOrFail($this->editingAbsensiId);

        // Authorization check
        if (! $this->canEditPersonnel($absensi->personnel)) {
            $this->dispatch('close-modal', id: 'edit-absensi-modal');
            $this->dispatch('toast', message: 'Anda tidak memiliki izin untuk mengedit absensi ini.', type: 'error');

            return;
        }

        $personnelId = $absensi->personnel_id;
        $tanggal = $absensi->tanggal;

        // Catat siapa yang menghapus, lalu soft delete
        $absensi->update(['deleted_by_user_id' => Auth::id()]);
        $absensi->delete();

        // Buat ulang record absensi default berdasarkan jadwal
        $jadwal = Jadwal::where('personnel_id', $personnelId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($jadwal) {
            $shift = $jadwal->shift;
            $isOff = $shift && $shift->type === 'off';
            $defaultStatus = $isOff ? ($shift->keterangan ?? 'OFF') : 'ALPA';

            Absensi::create([
                'personnel_id' => $personnelId,
                'tanggal' => $tanggal,
                'jadwal_id' => $jadwal->id,
                'status' => $defaultStatus,
                'status_masuk' => $defaultStatus,
                'status_pulang' => $defaultStatus,
            ]);
        }

        $this->dispatch('close-modal', id: 'edit-absensi-modal');
        $this->dispatch('toast', message: 'Data absensi dipindahkan ke kotak sampah', type: 'success');
        $this->dispatch('refreshAbsensi');
    }

    #[Computed]
    public function cutis()
    {
        return Cuti::orderBy('name')->get();
    }

    /**
     * Cek hak akses untuk mengedit absensi personel:
     * 1. Jika permission = edit-absensi-all-opd (atau role super-admin), bisa mengedit absensi seluruh personel OPD.
     * 2. Jika permission = edit-absensi-opd, hanya bisa mengedit absensi personel OPD-nya sendiri.
     */
    private function canEditPersonnel(?Personnel $personnel): bool
    {
        if (! $personnel) {
            return false;
        }

        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // 1. Permission edit-absensi-all-opd atau role super-admin: bisa edit semua OPD
        if ($user->can('edit-absensi-all-opd') || $user->hasRole('super-admin')) {
            return true;
        }

        // 2. Permission edit-absensi-opd: hanya bisa edit OPD-nya sendiri
        if ($user->can('edit-absensi-opd')) {
            $userOpdId = $user->opd()?->id;

            return ! empty($userOpdId) && ! empty($personnel->opd_id) && (int) $personnel->opd_id === (int) $userOpdId;
        }

        return false;
    }

    private function resetEditForm()
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->editingPersonnelId = null;
        $this->editingTanggal = null;
        $this->editingAbsensiId = null;
        $this->editingPersonnelName = '';
        $this->editingPersonnelFoto = null;
        $this->statusMasuk = '';
        $this->statusPulang = '';
        $this->jamMasuk = '';
        $this->jamPulang = '';
        $this->alasanEdit = '';
        $this->nomorSurat = '';
        $this->cutiId = null;
        $this->keterangan = '';
        $this->editingFotoMasuk = null;
        $this->editingFotoPulang = null;
        $this->platformMasuk = null;
        $this->platformPulang = null;
        $this->deviceNameMasuk = null;
        $this->deviceNamePulang = null;
        $this->uniqueDeviceIdMasuk = null;
        $this->uniqueDeviceIdPulang = null;
        $this->isOfficialDeviceMasuk = false;
        $this->isOfficialDevicePulang = false;
        $this->officialDeviceNameMasuk = null;
        $this->officialDeviceNamePulang = null;
        $this->isEdited = false;
        $this->jadwalShiftName = null;
        $this->jadwalJamMasuk = null;
        $this->jadwalJamPulang = null;
    }

    public function render()
    {
        return view('components.admin.absensi-edit-modal.absensi-edit-modal');
    }
};
