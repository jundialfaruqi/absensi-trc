<?php

namespace App\Livewire\Admin\UploadDokumentasi;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\DokumentasiKonsumsi;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $calculatedSiang
 * @property-read int $calculatedMalam
 * @property-read int $maxPorsi
 * @property-read ?DokumentasiKonsumsi $existingRecord
 */
new #[Title('Upload Dokumentasi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;

    public bool $readyToLoad = false;
    public string $tanggal = '';
    public string $shift = ''; // '' | 'siang' | 'malam'
    public int|string|null $jumlah_porsi = 0;
    public ?string $keterangan = '';
    public $foto1 = null;
    public $foto2 = null;
    public int $uploadIteration = 0;

    public function mount(): void
    {
        if (!Auth::user()?->can('upload-dokumentasi')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $this->tanggal = Carbon::now()->format('Y-m-d');
        $this->shift = '';
        $this->jumlah_porsi = 0;
    }

    public function load(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedTanggal(): void
    {
        $this->syncJumlahPorsi();
        $this->checkExistingDocumentation();
    }

    public function updatedShift(): void
    {
        $this->reset(['foto1', 'foto2']);
        $this->uploadIteration++;
        $this->syncJumlahPorsi();
        $this->checkExistingDocumentation();
    }

    private function syncJumlahPorsi(): void
    {
        if ($this->shift === 'siang') {
            $this->jumlah_porsi = $this->calculatedSiang;
        } elseif ($this->shift === 'malam') {
            $this->jumlah_porsi = $this->calculatedMalam;
        } else {
            $this->jumlah_porsi = 0;
        }
    }

    #[Computed]
    public function maxPorsi(): int
    {
        if ($this->shift === 'siang') {
            return $this->calculatedSiang;
        } elseif ($this->shift === 'malam') {
            return $this->calculatedMalam;
        }

        return 0;
    }

    public function getCalculatedKonsumsi(string $date): array
    {
        if (!$date) {
            return ['siang' => 0, 'malam' => 0];
        }

        $opdId = Auth::user()?->opd()?->id;

        $personnels = Personnel::with([
            'absensis' => fn ($q) => $q->whereDate('tanggal', $date),
            'jadwals' => fn ($q) => $q->whereDate('tanggal', $date)->with('shift.konsumsis'),
        ])
        ->when($opdId, fn ($q) => $q->where('personnels.opd_id', $opdId))
        ->get();

        $siang = 0;
        $malam = 0;

        foreach ($personnels as $personnel) {
            $abs = $personnel->absensis->first();
            $jadwal = $personnel->jadwals->first();

            $isHadir = $abs && (
                $abs->status === 'HADIR' ||
                $abs->status === 'TELAT' ||
                !empty($abs->jam_masuk)
            );

            if ($isHadir && $jadwal && $jadwal->shift) {
                $konsumsis = $jadwal->shift->konsumsis->pluck('nama')->map(fn ($k) => strtolower(trim($k)))->toArray();
                if (in_array('siang', $konsumsis)) {
                    $siang++;
                }
                if (in_array('malam', $konsumsis)) {
                    $malam++;
                }
            }
        }

        return ['siang' => $siang, 'malam' => $malam];
    }

    #[Computed]
    public function calculatedSiang(): int
    {
        if (!$this->tanggal) {
            return 0;
        }
        return $this->getCalculatedKonsumsi($this->tanggal)['siang'] ?? 0;
    }

    #[Computed]
    public function calculatedMalam(): int
    {
        if (!$this->tanggal) {
            return 0;
        }
        return $this->getCalculatedKonsumsi($this->tanggal)['malam'] ?? 0;
    }

    #[Computed]
    public function existingRecord(): ?DokumentasiKonsumsi
    {
        if (!$this->tanggal) {
            return null;
        }

        $opdId = Auth::user()?->opd()?->id;

        return DokumentasiKonsumsi::whereDate('tanggal', $this->tanggal)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId), fn ($q) => $q->whereNull('opd_id'))
            ->first();
    }

    #[Computed]
    public function isShiftAlreadyDocumented(): bool
    {
        if (!$this->tanggal || !$this->shift) {
            return false;
        }

        $record = $this->existingRecord;
        if (!$record) {
            return false;
        }

        if ($this->shift === 'siang') {
            return !empty($record->foto_siang);
        }

        if ($this->shift === 'malam') {
            return !empty($record->foto_malam);
        }

        return false;
    }

    public function checkExistingDocumentation(): void
    {
        $this->resetErrorBag('shift');

        if ($this->isShiftAlreadyDocumented) {
            $shiftLabel = $this->shift === 'siang' ? 'Siang' : 'Malam';
            $dateLabel = Carbon::parse($this->tanggal)->translatedFormat('d F Y');
            $this->addError('shift', "Data dokumentasi untuk Shift {$shiftLabel} pada tanggal {$dateLabel} sudah ada di sistem. Silahkan pilih shift atau tanggal lain.");
        }
    }

    public function removeFoto(int $index): void
    {
        if ($index === 1) {
            $this->foto1 = null;
        } elseif ($index === 2) {
            $this->foto2 = null;
        }
        $this->uploadIteration++;
    }

    public function resetForm(): void
    {
        $this->tanggal = Carbon::now()->format('Y-m-d');
        $this->shift = '';
        $this->jumlah_porsi = 0;
        $this->keterangan = '';
        $this->foto1 = null;
        $this->foto2 = null;
        $this->uploadIteration++;
        $this->resetValidation();
    }

    public function updatedFoto1(): void
    {
        if ($this->foto1) {
            $this->validateOnly('foto1', [
                'foto1' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            ], [
                'foto1.image' => 'File harus berupa gambar.',
                'foto1.mimes' => 'Format foto utama harus JPEG, JPG, PNG, atau WebP.',
                'foto1.max' => 'Ukuran foto utama maksimal 2MB (2048KB).',
            ]);
        }
    }

    public function updatedFoto2(): void
    {
        if ($this->foto2) {
            $this->validateOnly('foto2', [
                'foto2' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            ], [
                'foto2.image' => 'File harus berupa gambar.',
                'foto2.mimes' => 'Format foto kedua harus JPEG, JPG, PNG, atau WebP.',
                'foto2.max' => 'Ukuran foto kedua maksimal 2MB (2048KB).',
            ]);
        }
    }

    public function save()
    {
        if (!Auth::user()?->can('upload-dokumentasi')) {
            abort(403);
        }

        $max = $this->maxPorsi;

        $this->validate([
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'in:siang,malam'],
            'jumlah_porsi' => ['required', 'integer', 'min:0', 'max:' . $max],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'foto1' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'foto2' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], [
            'tanggal.required' => 'Tanggal dokumentasi wajib diisi.',
            'shift.required' => 'Silakan pilih shift terlebih dahulu.',
            'shift.in' => 'Shift yang dipilih tidak valid.',
            'jumlah_porsi.required' => 'Jumlah porsi wajib diisi.',
            'jumlah_porsi.integer' => 'Jumlah porsi harus berupa angka bulat.',
            'jumlah_porsi.min' => 'Jumlah porsi tidak boleh kurang dari 0.',
            'jumlah_porsi.max' => "Jumlah porsi tidak boleh melebihi perhitungan otomatis ({$max} Porsi).",
            'foto1.required' => 'Foto dokumentasi utama wajib diunggah.',
            'foto1.image' => 'File harus berupa gambar.',
            'foto1.mimes' => 'Format foto utama harus JPEG, JPG, PNG, atau WebP.',
            'foto1.max' => 'Ukuran foto utama maksimal 2MB (2048KB).',
            'foto2.image' => 'File harus berupa gambar.',
            'foto2.mimes' => 'Format foto kedua harus JPEG, JPG, PNG, atau WebP.',
            'foto2.max' => 'Ukuran foto kedua maksimal 2MB (2048KB).',
        ]);

        $this->checkExistingDocumentation();
        if ($this->isShiftAlreadyDocumented) {
            return null;
        }

        $opdId = Auth::user()?->opd()?->id;

        $record = DokumentasiKonsumsi::whereDate('tanggal', $this->tanggal)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId), fn ($q) => $q->whereNull('opd_id'))
            ->first();

        $targetDir = 'dokumentasi-konsumsi/' . Carbon::parse($this->tanggal)->format('d-m-Y');

        $dataToSave = [];
        if ($this->keterangan) {
            $dataToSave['keterangan'] = $this->keterangan;
        }

        if ($this->shift === 'siang') {
            // Foto 1 Siang
            $path1 = $record?->foto_siang;
            if ($this->foto1) {
                if ($path1 && Storage::disk('public')->exists($path1)) {
                    Storage::disk('public')->delete($path1);
                }
                $path1 = $this->foto1->store($targetDir, 'public');
            }

            // Foto 2 Siang (opsional)
            $path2 = $record?->foto_siang_2;
            if ($this->foto2) {
                if ($path2 && Storage::disk('public')->exists($path2)) {
                    Storage::disk('public')->delete($path2);
                }
                $path2 = $this->foto2->store($targetDir, 'public');
            }

            $dataToSave['jumlah_siang'] = $this->jumlah_porsi;
            $dataToSave['foto_siang'] = $path1;
            $dataToSave['foto_siang_2'] = $path2;
        } elseif ($this->shift === 'malam') {
            // Foto 1 Malam
            $path1 = $record?->foto_malam;
            if ($this->foto1) {
                if ($path1 && Storage::disk('public')->exists($path1)) {
                    Storage::disk('public')->delete($path1);
                }
                $path1 = $this->foto1->store($targetDir, 'public');
            }

            // Foto 2 Malam (opsional)
            $path2 = $record?->foto_malam_2;
            if ($this->foto2) {
                if ($path2 && Storage::disk('public')->exists($path2)) {
                    Storage::disk('public')->delete($path2);
                }
                $path2 = $this->foto2->store($targetDir, 'public');
            }

            $dataToSave['jumlah_malam'] = $this->jumlah_porsi;
            $dataToSave['foto_malam'] = $path1;
            $dataToSave['foto_malam_2'] = $path2;
        }

        if ($record) {
            $record->update($dataToSave);
        } else {
            $dataToSave['tanggal'] = $this->tanggal;
            $dataToSave['opd_id'] = $opdId;
            $dataToSave['created_by'] = Auth::id();
            DokumentasiKonsumsi::create($dataToSave);
        }

        $shiftLabel = $this->shift === 'siang' ? 'Makan Siang' : 'Makan Malam';
        $tanggalFormatted = Carbon::parse($this->tanggal)->translatedFormat('d F Y');

        $this->dispatch('set-pending-toast', [
            'type' => 'success',
            'message' => "Dokumentasi {$shiftLabel} tanggal {$tanggalFormatted} berhasil disimpan."
        ]);

        return $this->redirect(route('upload-dokumentasi'), navigate: true);
    }
};
