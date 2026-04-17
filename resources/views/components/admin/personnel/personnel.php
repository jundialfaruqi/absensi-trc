<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Personnel;
use App\Models\Opd;
use App\Models\Penugasan;

new #[Title('Manajemen Personnel')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    public int $perPage = 10;
    public string $search = '';

    // Form
    public ?int $personnelId = null;
    public string $name = '';
    public string $opd_id = '';
    public string $penugasan_id = '';
    public string $nomor_hp = '';
    public $foto;
    public ?string $oldFoto = null;
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $pin = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    #[Computed]
    public function stats(): array
    {
        $total = Personnel::count();
        return [
            'total' => $total,
        ];
    }

    #[Computed]
    public function personnels()
    {
        $query = Personnel::with(['opd', 'penugasan'])
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            }));

        if (!auth()->user()->hasRole('super-admin')) {
            $userOpdId = auth()->user()->opd()?->id;
            $query->where('opd_id', $userOpdId);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    #[Computed]
    public function opds()
    {
        if (auth()->user()->hasRole('super-admin')) {
            return Opd::orderBy('name')->get();
        } else {
            $userOpdId = auth()->user()->opd()?->id;
            return Opd::where('id', $userOpdId)->get();
        }
    }

    #[Computed]
    public function penugasans()
    {
        return Penugasan::orderBy('name')->get();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        if (!auth()->user()->hasRole('super-admin')) {
            $this->opd_id = (string) auth()->user()->opd()?->id;
        }
        $this->dispatch('open-modal', id: 'personnel-modal');
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $item = Personnel::findOrFail($id);
        
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->personnelId = $item->id;
        $this->name = $item->name;
        $this->opd_id = (string) $item->opd_id;
        $this->penugasan_id = (string) $item->penugasan_id;
        $this->nomor_hp = $item->nomor_hp ?? '';
        $this->email = $item->email;
        $this->oldFoto = $item->foto;
        
        $this->dispatch('open-modal', id: 'personnel-modal');
    }

    public function save(): void
    {
        // Intercept validation if not super admin to ensure opd_id wasn't tampered
        if (!auth()->user()->hasRole('super-admin')) {
            if ($this->opd_id != auth()->user()->opd()?->id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $rules = [
            'name' => 'required|string|max:255',
            'opd_id' => 'required|exists:opds,id',
            'penugasan_id' => 'required|exists:penugasans,id',
            'nomor_hp' => 'nullable|string|max:30',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('personnels')->ignore($this->personnelId),
            ],
            'foto' => $this->personnelId ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ];

        if (!$this->personnelId || $this->password) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        if (!$this->personnelId || $this->pin) {
            $rules['pin'] = 'required|numeric|digits:4';
        } else {
            $rules['pin'] = 'nullable|numeric|digits:4';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'opd_id' => $this->opd_id,
            'penugasan_id' => $this->penugasan_id,
            'nomor_hp' => $this->nomor_hp,
            'email' => $this->email,
        ];

        if ($this->foto) {
            $data['foto'] = $this->foto->store('personnel-fotos', 'public');

            if ($this->personnelId && $this->oldFoto) {
                Storage::disk('public')->delete($this->oldFoto);
            }
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->pin) {
            $data['pin'] = Hash::make($this->pin);
        }

        if ($this->personnelId) {
            $personnel = Personnel::findOrFail($this->personnelId);
            $personnel->update($data);
        } else {
            Personnel::create($data);
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'personnel-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Personnel berhasil disimpan.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $item = Personnel::findOrFail($id);
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'personnel-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Personnel::findOrFail($this->deleteId);
        
        if (!auth()->user()->hasRole('super-admin') && $item->opd_id !== auth()->user()->opd()?->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($item->foto) {
            Storage::disk('public')->delete($item->foto);
        }
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'personnel-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Data Personnel berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->personnelId = null;
        $this->name = '';
        $this->opd_id = '';
        $this->penugasan_id = '';
        $this->nomor_hp = '';
        $this->foto = null;
        $this->oldFoto = null;
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->pin = '';
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