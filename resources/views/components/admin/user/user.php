<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

new #[Title('Manajemen Pengguna')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    public int $perPage = 10;
    public string $search = '';

    // Form
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $nomor_hp = '';
    public $foto;
    public ?string $oldFoto = null;
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function mount(): void
    {
        //
    }

    #[Computed]
    public function stats(): array
    {
        $total = User::count();
        $withRole = User::has('roles')->count();

        return [
            'total' => $total,
            'with_role' => $withRole,
        ];
    }

    #[Computed]
    public function users()
    {
        return User::with(['roles', 'opds'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                                              ->orWhere('email', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('name')->get();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'user-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = User::findOrFail($id);
        
        $this->userId = $item->id;
        $this->name = $item->name;
        $this->email = $item->email;
        $this->nomor_hp = $item->nomor_hp ?? '';
        $this->oldFoto = $item->foto;
        $this->role = $item->roles->first()?->name ?? '';
        
        $this->dispatch('open-modal', id: 'user-modal');
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'nomor_hp' => 'nullable|string|max:30',
            'foto' => 'nullable|image|max:2048',
            'role' => 'nullable|string|exists:roles,name',
        ];

        if (!$this->userId || $this->password) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'nomor_hp' => $this->nomor_hp,
        ];

        if ($this->foto) {
            $data['foto'] = $this->foto->store('user-fotos', 'public');

            if ($this->userId && $this->oldFoto) {
                Storage::disk('public')->delete($this->oldFoto);
            }
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        if ($this->role) {
            $user->syncRoles([$this->role]);
        } else {
            $user->syncRoles([]);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'user-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Pengguna berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'user-delete-modal');
    }

    public function executeDelete(): void
    {
        if ($this->deleteId === auth()->id()) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Tidak dapat menghapus akun Anda sendiri.');
            $this->dispatch('close-modal', id: 'user-delete-modal');
            return;
        }

        $item = User::findOrFail($this->deleteId);
        if ($item->foto) {
            Storage::disk('public')->delete($item->foto);
        }
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'user-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Pengguna berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->nomor_hp = '';
        $this->foto = null;
        $this->oldFoto = null;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = '';
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