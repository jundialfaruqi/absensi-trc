<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Berita;
use Illuminate\Support\Facades\Storage;

new #[Title('Manajemen Berita')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;

    #[Url]
    public int $perPage = 10;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterKategori = '';

    // Delete
    public ?string $deleteId = null;
    public string $deleteName = '';

    public function load(): void
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function beritas()
    {
        if (!$this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        return Berita::with('creator')
            ->when(strlen($this->search) >= 3, fn($q) => $q->where('judul', 'like', '%' . $this->search . '%'))
            ->when($this->filterKategori, fn($q) => $q->where('kategori', $this->filterKategori))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function kategoris()
    {
        return Berita::select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }

    public function confirmDelete(string $id, string $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'berita-delete-modal');
    }

    public function executeDelete(): void
    {
        $berita = Berita::findOrFail($this->deleteId);

        // Hapus gambar dari storage
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $berita->delete();

        $this->deleteId = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'berita-delete-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Berita berhasil dihapus.');
    }

    public function toggleBannerActive(string $id): void
    {
        $berita = Berita::findOrFail($id);
        $berita->is_banner_active = !$berita->is_banner_active;
        $berita->save();

        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Status banner berhasil diubah.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterKategori(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
};
