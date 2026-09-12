<?php

namespace App\Livewire\Admin\Personnel;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use App\Models\Personnel;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new #[Title('Manajemen Personnel')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;
    #[Url]
    public int $perPage = 20;

    #[Url]
    public string $search = '';

    #[Url]
    public string $selectedOpd = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    public function load()
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function personnels()
    {
        return $this->getPersonnelsQuery()->paginate($this->perPage);
    }

    /**
     * @return User|null
     */
    private function user(): ?User
    {
        /** @var User|null */
        return Auth::user();
    }

    private function getPersonnelsQuery()
    {
        if (! $this->readyToLoad) {
            // Return a query that returns nothing but has the right structure
            return Personnel::query()->whereRaw('1 = 0');
        }

        /** @var User|null $user */
        $user = $this->user();
        $canSeeAll = $user && ($user->hasRole('super-admin') || $user->can('lihat-personel-all-opd'));
        $canSeeOpd = $user && $user->can('lihat-personel-opd');

        if (! $canSeeAll && ! $canSeeOpd) {
            return Personnel::query()->whereRaw('1 = 0');
        }

        return Personnel::query()
            ->with(['opd', 'penugasan', 'kantor', 'devices'])
            ->join('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select(['personnels.*'])
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->where('personnels.name', 'like', '%' . $this->search . '%')
                    ->orWhere('personnels.nik', 'like', '%' . $this->search . '%')
                    ->orWhere('personnels.email', 'like', '%' . $this->search . '%')
                    ->orWhere('personnels.pin', 'like', '%' . $this->search . '%');
            }))
            ->when($canSeeAll, function ($q) {
                $q->when($this->selectedOpd, fn($sub) => $sub->where('personnels.opd_id', '=', $this->selectedOpd));
            }, function ($q) use ($user) {
                $q->where('personnels.opd_id', '=', $user->opd()?->id);
            })
            ->orderBy('opds.name', 'asc')
            ->orderBy('personnels.name', 'asc')
            ->orderBy('personnels.id', 'asc');
    }

    /**
     * Hitung offset urutan personel per OPD untuk penomoran tabel.
     */
    public function getOpdOffset(int $personnelId, ?int $opdId = null): int
    {
        $target = Personnel::findOrFail($personnelId);

        return $this->getPersonnelsQuery()
            ->where('personnels.opd_id', $opdId)
            ->where(function ($q) use ($target) {
                $q->where('personnels.name', '<', $target->name)
                    ->orWhere(function ($q2) use ($target) {
                        $q2->where('personnels.name', '=', $target->name)
                            ->where('personnels.id', '<', $target->id);
                    });
            })
            ->count();
    }

    #[Computed]
    public function opds()
    {
        /** @var User|null $user */
        $user = $this->user();
        if ($user && ($user->hasRole('super-admin') || $user->can('lihat-personel-all-opd'))) {
            return Opd::query()->orderBy('name', 'asc')->get(['*']);
        } else {
            $userOpdId = $user?->opd()?->id;

            return Opd::query()->where('id', '=', $userOpdId)->get(['*']);
        }
    }

    private function canEditPersonnel(?Personnel $personnel): bool
    {
        if (! $personnel) {
            return false;
        }

        /** @var User|null $user */
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // 1. edit-personel-all-opd atau role super-admin
        if ($user->hasRole('super-admin') || $user->can('edit-personel-all-opd')) {
            return true;
        }

        // 2. edit-personel-opd: hanya bisa jika se-OPD
        if ($user->can('edit-personel-opd')) {
            $userOpdId = $user->opd()?->id;

            return ! empty($userOpdId) && ! empty($personnel->opd_id) && (int) $personnel->opd_id === (int) $userOpdId;
        }

        return false;
    }

    private function canDeletePersonnel(?Personnel $personnel): bool
    {
        if (! $personnel) {
            return false;
        }

        /** @var User|null $user */
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // 1. delete-personel-all-opd atau role super-admin
        if ($user->hasRole('super-admin') || $user->can('delete-personel-all-opd')) {
            return true;
        }

        // 2. delete-personel-opd: hanya bisa jika se-OPD
        if ($user->can('delete-personel-opd')) {
            $userOpdId = $user->opd()?->id;

            return ! empty($userOpdId) && ! empty($personnel->opd_id) && (int) $personnel->opd_id === (int) $userOpdId;
        }

        return false;
    }

    public function resetPin(int $id): void
    {
        $item = Personnel::findOrFail($id);
        if (! $this->canEditPersonnel($item)) {
            abort(403, 'Anda tidak memiliki izin untuk mereset PIN personel ini.');
        }

        $newPin = $this->generateUniquePin();
        $item->update(['pin' => $newPin]);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'PIN Direset',
            'message' => "PIN baru untuk {$item->name} adalah: {$newPin}",
        ]);
    }

    private function generateUniquePin(): string
    {
        do {
            $pin = sprintf("%06d", mt_rand(1, 999999));
        } while (Personnel::query()->where('pin', '=', $pin)->exists());

        return $pin;
    }

    public function confirmDelete(int $id, string $name): void
    {
        $item = Personnel::findOrFail($id);
        if (! $this->canDeletePersonnel($item)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus personel ini.');
        }

        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'personnel-delete-modal');
    }

    public function executeDelete(): void
    {
        $item = Personnel::findOrFail($this->deleteId);

        if (! $this->canDeletePersonnel($item)) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus personel ini.');
        }

        if ($item->foto) {
            Storage::disk('public')->delete($item->foto);
        }
        $item->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'personnel-delete-modal');
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Personnel berhasil dihapus.',
        ]);
    }

    public function goToAdd()
    {
        /** @var User|null $user */
        $user = $this->user();
        if (! $user || (! $user->hasRole('super-admin') && ! $user->can('create-personel-all-opd') && ! $user->can('create-personel-opd'))) {
            abort(403, 'Anda tidak memiliki izin untuk menambah personel.');
        }

        return $this->redirectRoute('personnel.tambah', [], true, true);
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
