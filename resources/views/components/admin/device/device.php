<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Device;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new #[Title('Manajemen Perangkat')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;

    #[Url]
    public string $search = '';

    public int $perPage = 10;

    // Form Properties
    public $deviceId;
    public $opd_id;
    public $holder_type = 'personnel'; // personnel, user, manual
    public $personnel_id;
    public $user_id;
    public $holder_name;
    public $name;
    public $license_key;
    public $status = 'inactive';
    public $notes;

    // Delete Properties
    public $deleteId;
    public $deleteName;

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function opds()
    {
        return Auth::user()->hasRole('super-admin') 
            ? Opd::orderBy('name')->get() 
            : [Auth::user()->opd()];
    }

    #[Computed]
    public function personnelList()
    {
        return Personnel::orderBy('name')
            ->when(!Auth::user()->hasRole('super-admin'), function($q) {
                $q->where('opd_id', Auth::user()->opd()?->id);
            })
            ->get();
    }

    #[Computed]
    public function usersList()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function devices()
    {
        if (!$this->readyToLoad) return [];

        return Device::with(['opd', 'user', 'personnel'])
            ->when(!Auth::user()->hasRole('super-admin'), function ($q) {
                $q->where('opd_id', Auth::user()->opd()?->id);
            })
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('license_key', 'like', '%' . $this->search . '%')
                  ->orWhere('holder_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($uq) {
                      $uq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('personnel', function($pq) {
                      $pq->where('name', 'like', '%' . $this->search . '%');
                  });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function generateLicense()
    {
        $this->license_key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
    }

    public function resetForm()
    {
        $this->deviceId = null;
        $this->opd_id = Auth::user()->hasRole('super-admin') ? '' : Auth::user()->opd()?->id;
        $this->holder_type = 'personnel';
        $this->personnel_id = '';
        $this->user_id = '';
        $this->holder_name = '';
        $this->name = '';
        $this->license_key = '';
        $this->status = 'inactive';
        $this->notes = '';
        $this->resetErrorBag();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->generateLicense();
        $this->dispatch('open-modal', id: 'device-modal');
    }

    public function edit(Device $device)
    {
        $this->deviceId = $device->id;
        $this->opd_id = $device->opd_id;
        $this->personnel_id = $device->personnel_id;
        $this->user_id = $device->user_id;
        $this->holder_name = $device->holder_name;
        
        if ($device->personnel_id) {
            $this->holder_type = 'personnel';
        } elseif ($device->user_id) {
            $this->holder_type = 'user';
        } else {
            $this->holder_type = 'manual';
        }
        $this->name = $device->name;
        $this->license_key = $device->license_key;
        $this->status = $device->status;
        $this->notes = $device->notes;
        
        $this->dispatch('open-modal', id: 'device-modal');
    }

    public function save()
    {
        $this->validate([
            'opd_id' => 'required',
            'name' => 'required|min:2',
            'license_key' => 'required|unique:devices,license_key,' . $this->deviceId,
            'status' => 'required|in:active,inactive,suspended',
            'personnel_id' => 'required_if:holder_type,personnel',
            'user_id' => 'required_if:holder_type,user',
            'holder_name' => 'required_if:holder_type,manual',
        ], [
            'personnel_id.required_if' => 'Pilih personel dari daftar.',
            'user_id.required_if' => 'Pilih user dari daftar.',
            'holder_name.required_if' => 'Masukkan nama pemegang perangkat.',
        ]);

        Device::updateOrCreate(
            ['id' => $this->deviceId],
            [
                'opd_id' => $this->opd_id,
                'personnel_id' => $this->holder_type === 'personnel' ? $this->personnel_id : null,
                'user_id' => $this->holder_type === 'user' ? $this->user_id : null,
                'holder_name' => $this->holder_type === 'manual' ? $this->holder_name : null,
                'name' => $this->name,
                'license_key' => $this->license_key,
                'status' => $this->status,
                'notes' => $this->notes,
            ]
        );

        $this->dispatch('close-modal', id: 'device-modal');
        $this->dispatch('toast', message: 'Perangkat berhasil disimpan', type: 'success');
        $this->resetForm();
    }

    public function confirmDelete($id, $name)
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'device-delete-modal');
    }

    public function executeDelete()
    {
        if ($this->deleteId) {
            Device::find($this->deleteId)->delete();
            $this->dispatch('close-modal', id: 'device-delete-modal');
            $this->dispatch('toast', message: 'Perangkat berhasil dihapus', type: 'success');
            $this->deleteId = null;
            $this->deleteName = null;
        }
    }

    public function toggleStatus($id)
    {
        $device = Device::find($id);
        $newStatus = $device->status === 'active' ? 'suspended' : 'active';
        $device->update(['status' => $newStatus]);
        
        $msg = $newStatus === 'active' ? 'Perangkat diaktifkan' : 'Perangkat ditangguhkan';
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
};
