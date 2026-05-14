<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Berita;
use Illuminate\Support\Facades\Auth;

new #[Title('Buat Berita')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public string $judul = '';
    public string $kategori = '';
    public string $deskripsi = '';
    public string $isi = '';
    public $gambar;
    public string $selectedKategori = '';
    public string $newKategori = '';

    #[Computed]
    public function existingKategoris()
    {
        return Berita::select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }

    public function save(): void
    {
        $this->kategori = trim($this->newKategori) ?: $this->selectedKategori;

        $this->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'isi' => 'required|string',
            'deskripsi' => 'nullable|string|max:500',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
        ], [
            'judul.required' => 'Judul berita wajib diisi.',
            'kategori.required' => 'Kategori wajib diisi.',
            'isi.required' => 'Isi berita wajib diisi.',
            'gambar.max' => 'Ukuran gambar maksimal 1 MB.',
            'gambar.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WebP.',
        ]);

        $gambarPath = null;
        if ($this->gambar) {
            $gambarPath = $this->gambar->store('berita', 'public');
        }

        $slug = Berita::generateSlug($this->judul);

        Berita::create([
            'judul' => $this->judul,
            'slug' => $slug,
            'isi' => $this->isi,
            'gambar' => $gambarPath,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'created_by' => Auth::id(),
        ]);

        session()->flash('toast', ['type' => 'success', 'message' => 'Berita berhasil dibuat.']);
        $this->redirect(route('berita'), navigate: true);
    }
};
