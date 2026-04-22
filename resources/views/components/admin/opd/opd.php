<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

new #[Title('Manajemen OPD')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    public int $perPage = 10;
    public string $search = '';
    public string $userSearch = '';

    // Form
    public ?int $opdId = null;
    public string $name = '';
    public string $singkatan = '';
    public string $alamat = '';
    public $logo;
    public ?string $oldLogo = null;
    public array $selectedUsers = [];

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function mount(): void
    {
        //
    }

    #[Computed]
    public function opds()
    {
        return Opd::withCount('users')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                                              ->orWhere('singkatan', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function allUsers()
    {
        return User::when($this->userSearch, fn($q) => $q->where('name', 'like', '%' . $this->userSearch . '%'))
            ->orderBy('name')
            ->get();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'opd-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $opd = Opd::with('users')->findOrFail($id);

        $this->opdId = $opd->id;
        $this->name = $opd->name;
        $this->singkatan = $opd->singkatan ?? '';
        $this->alamat = $opd->alamat ?? '';
        $this->oldLogo = $opd->logo;
        $this->selectedUsers = $opd->users->pluck('id')->map(fn($id) => (string)$id)->toArray();

        $this->dispatch('open-modal', id: 'opd-modal');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'singkatan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // max 2MB
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'name' => 'Nama OPD',
            'singkatan' => 'Singkatan OPD',
            'alamat' => 'Alamat',
            'logo' => 'Logo',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'singkatan' => $this->singkatan ?: null,
            'alamat' => $this->alamat ?: null,
        ];

        if ($this->logo) {
            if ($this->oldLogo) {
                Storage::disk('public')->delete($this->oldLogo);
            }
            $data['logo'] = $this->logo->store('opd-logos', 'public');
        }

        if ($this->opdId) {
            $opd = Opd::findOrFail($this->opdId);
            $opd->update($data);
        } else {
            $opd = Opd::create($data);
        }

        // Handle user_id (1 user only 1 opd)
        $userIds = array_map('intval', $this->selectedUsers);
        if (!empty($userIds)) {
            // Hapus relasi di OPD lain untuk user yang dipilih agar tidak melanggar constraint unique user_id
            DB::table('opd_user')
                ->whereIn('user_id', $userIds)
                ->where('opd_id', '!=', $opd->id)
                ->delete();
        }

        $opd->users()->sync($userIds);

        $this->resetForm();
        $this->dispatch('close-modal', id: 'opd-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data OPD berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'opd-delete-modal');
    }

    public function executeDelete(): void
    {
        $opd = Opd::findOrFail($this->deleteId);

        if ($opd->logo) {
            Storage::disk('public')->delete($opd->logo);
        }

        $opd->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'opd-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data OPD berhasil dihapus.');
    }

    public function updatedLogo()
    {
        if ($this->logo) {
            try {
                $mimeType = $this->logo->getMimeType();
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
                    $this->reset('logo');
                    $this->addError('logo', 'File yang diunggah bukan merupakan gambar yang valid.');
                    return;
                }
            } catch (\Exception $e) {
                $this->reset('logo');
                $this->addError('logo', 'File yang diunggah tidak dapat dibaca atau rusak.');
                return;
            }
        }

        $this->validateOnly('logo');
    }

    private function resetForm(): void
    {
        $this->opdId = null;
        $this->name = '';
        $this->singkatan = '';
        $this->alamat = '';
        $this->logo = null;
        $this->oldLogo = null;
        $this->selectedUsers = [];
        $this->userSearch = '';
        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
