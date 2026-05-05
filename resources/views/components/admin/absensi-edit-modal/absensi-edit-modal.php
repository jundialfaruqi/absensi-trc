<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
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

    #[On('openEditAbsensi')]
    public function open($personnelId, $tanggal)
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
            $this->editingFotoMasuk = $absensi->foto_masuk;
            $this->editingFotoPulang = $absensi->foto_pulang;
            $this->platformMasuk = $absensi->platform_masuk;
            $this->platformPulang = $absensi->platform_pulang;
            $this->deviceNameMasuk = $absensi->device_name_masuk;
            $this->deviceNamePulang = $absensi->device_name_pulang;
            $this->uniqueDeviceIdMasuk = $absensi->unique_device_id_masuk;
            $this->uniqueDeviceIdPulang = $absensi->unique_device_id_pulang;

            if ($this->uniqueDeviceIdMasuk) {
                $device = \App\Models\Device::where('unique_device_id', $this->uniqueDeviceIdMasuk)->first();
                $this->isOfficialDeviceMasuk = !is_null($device);
                $this->officialDeviceNameMasuk = $device?->name;
            }
            if ($this->uniqueDeviceIdPulang) {
                $device = \App\Models\Device::where('unique_device_id', $this->uniqueDeviceIdPulang)->first();
                $this->isOfficialDevicePulang = !is_null($device);
                $this->officialDeviceNamePulang = $device?->name;
            }

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
                'status' => $this->statusMasuk,
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
        $this->dispatch('refreshAbsensi');
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
            // It was originally a placeholder (ALFA/LIBUR), so restore that state
            $jadwal = $absensi->jadwal;
            $placeholderStatus = ($jadwal && $jadwal->status === 'LIBUR') ? 'LIBUR' : 'ALFA';
            
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
        if (!$this->editingAbsensiId) return;

        // Permission check
        if (!Auth::user()->can('reset-absen')) return;

        $absensi = Absensi::findOrFail($this->editingAbsensiId);

        // Authorization check
        if (!Auth::user()->hasRole('super-admin') && $absensi->personnel->opd_id !== Auth::user()->opd()?->id) {
            return;
        }

        // Hapus file foto masuk
        if ($absensi->foto_masuk && Storage::disk('public')->exists($absensi->foto_masuk)) {
            Storage::disk('public')->delete($absensi->foto_masuk);
        }

        // Hapus file foto pulang
        if ($absensi->foto_pulang && Storage::disk('public')->exists($absensi->foto_pulang)) {
            Storage::disk('public')->delete($absensi->foto_pulang);
        }

        // Hapus record absensi
        $absensi->delete();

        $this->dispatch('close-modal', id: 'edit-absensi-modal');
        $this->dispatch('toast', message: 'Data absensi berhasil dihapus (termasuk foto)', type: 'success');
        $this->dispatch('refreshAbsensi');
    }

    #[Computed]
    public function cutis()
    {
        return Cuti::orderBy('name')->get();
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
    }

    public function render()
    {
        return view('components.admin.absensi-edit-modal.absensi-edit-modal');
    }
};
