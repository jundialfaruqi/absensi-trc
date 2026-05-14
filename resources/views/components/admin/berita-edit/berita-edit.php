<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Berita;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new #[Title('Edit Berita')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public string $beritaId = '';
    public string $judul = '';
    public string $kategori = '';
    public string $deskripsi = '';
    public string $isi = '';
    public $gambar; // new upload
    public ?string $existingGambar = null;
    public string $selectedKategori = '';
    public string $newKategori = '';

    public function mount(string $id): void
    {
        $berita = Berita::findOrFail($id);

        $this->beritaId = $berita->id;
        $this->judul = $berita->judul;
        $this->kategori = $berita->kategori;
        $this->deskripsi = $berita->deskripsi ?? '';
        $this->isi = $berita->isi;
        $this->existingGambar = $berita->gambar;

        $existing = Berita::select('kategori')->distinct()->pluck('kategori')->toArray();
        if (in_array($berita->kategori, $existing)) {
            $this->selectedKategori = $berita->kategori;
        } else {
            $this->newKategori = $berita->kategori;
        }
    }

    #[Computed]
    public function existingKategoris()
    {
        return Berita::select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }

    public function removeGambar(): void
    {
        $this->existingGambar = null;
        $this->gambar = null;
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

        $berita = Berita::findOrFail($this->beritaId);

        // Handle gambar
        $gambarPath = $this->existingGambar;

        if ($this->gambar) {
            // Hapus gambar lama
            if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $gambarPath = $this->gambar->store('berita', 'public');
        } elseif (!$this->existingGambar && $berita->gambar) {
            // Gambar dihapus oleh user
            if (Storage::disk('public')->exists($berita->gambar)) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $gambarPath = null;
        }

        // Regenerate slug jika judul berubah
        $slug = $berita->slug;
        if ($berita->judul !== $this->judul) {
            $slug = Berita::generateSlug($this->judul, $berita->created_at, $berita->id);
        }

        $berita->update([
            'judul' => $this->judul,
            'slug' => $slug,
            'isi' => $this->isi,
            'gambar' => $gambarPath,
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi ?: null,
        ]);

        session()->flash('toast', ['type' => 'success', 'message' => 'Berita berhasil diperbarui.']);
        $this->redirect(route('berita'), navigate: true);
    }
};
