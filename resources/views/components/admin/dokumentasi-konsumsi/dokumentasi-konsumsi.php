<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Personnel;
use App\Models\Opd;
use App\Models\Shift;
use App\Models\DokumentasiKonsumsi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new #[Title('Dokumentasi Konsumsi')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    public bool $readyToLoad = false;

    #[Url]
    public int $perPage = 10;

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    public string $filterStartDate = '';

    public string $filterEndDate = '';

    #[Url]
    public string $selectedOpd = '';

    public string $paperSize = 'a4';

    // State untuk Modal Upload Dokumentasi Foto Konsumsi
    public bool $showAddModal = false;
    public string $modalMode = 'create'; // 'create' atau 'edit'
    public string $uploadTanggal = '';
    public string $sesiKonsumsi = 'siang'; // 'siang', 'malam', atau 'keduanya'
    public ?int $jumlahSiang = 0;
    public ?int $jumlahMalam = 0;
    public $fotoSiang = null;
    public $fotoMalam = null;
    public ?string $existingFotoSiang = null;
    public ?string $existingFotoMalam = null;
    public int $uploadIteration = 0;

    protected $listeners = [
        'refreshKonsumsi' => '$refresh'
    ];

    public function mount(): void
    {
        // Default rentang tanggal: 1 bulan penuh (dari awal bulan ini sampai akhir bulan ini)
        if (!$this->startDate) {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }

        if (!$this->endDate) {
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $this->filterStartDate = $this->startDate;
        $this->filterEndDate = $this->endDate;
        $this->uploadTanggal = Carbon::now()->format('Y-m-d');
    }

    #[Computed]
    public function isDefaultDateFilter(): bool
    {
        $defaultStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $defaultEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

        return $this->startDate === $defaultStart && $this->endDate === $defaultEnd;
    }

    public function load(): void
    {
        $this->readyToLoad = true;
    }

    #[Computed]
    public function dates(): array
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);

            if ($start->diffInDays($end) > 31) {
                $end = $start->copy()->addDays(31);
            }

            $dates = [];
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                $start->addDay();
            }
            return $dates;
        }

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $dates = [];
        while ($start <= $end) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }
        return $dates;
    }

    #[Computed]
    public function monthlySummary(): array
    {
        if (!$this->readyToLoad) {
            return [
                'daily' => [],
                'totalSiang' => 0,
                'totalMalam' => 0,
                'grandTotal' => 0,
            ];
        }

        $dates = $this->dates;
        $daily = [];
        foreach ($dates as $d) {
            $daily[$d] = [
                'siang' => 0,
                'malam' => 0,
                'total' => 0,
            ];
        }

        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        // Ambil semua data personil dan jadwal yang hadir untuk summary konsumsi
        $personnels = Personnel::with([
            'absensis' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates);
            },
            'jadwals' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates)->with('shift.konsumsis');
            }
        ])
            ->when($opdId, function ($q) use ($opdId) {
                $q->where('personnels.opd_id', $opdId);
            })
            ->get();

        $totalSiang = 0;
        $totalMalam = 0;

        foreach ($personnels as $personnel) {
            $absensiMap = $personnel->absensis->keyBy(fn ($a) => $a->tanggal->format('Y-m-d'));
            $jadwalMap = $personnel->jadwals->keyBy(fn ($j) => $j->tanggal->format('Y-m-d'));

            foreach ($dates as $date) {
                $abs = $absensiMap->get($date);
                $jadwal = $jadwalMap->get($date);

                $isHadir = $abs && (
                    $abs->status === 'HADIR' ||
                    $abs->status === 'TELAT' ||
                    !empty($abs->jam_masuk)
                );

                if ($isHadir && $jadwal && $jadwal->shift) {
                    $konsumsis = $jadwal->shift->konsumsis->pluck('nama')->map(fn ($k) => strtolower(trim($k)))->toArray();

                    if (in_array('siang', $konsumsis)) {
                        $daily[$date]['siang']++;
                        $totalSiang++;
                    }
                    if (in_array('malam', $konsumsis)) {
                        $daily[$date]['malam']++;
                        $totalMalam++;
                    }
                }
                $daily[$date]['total'] = $daily[$date]['siang'] + $daily[$date]['malam'];
            }
        }

        return [
            'daily' => $daily,
            'totalSiang' => $totalSiang,
            'totalMalam' => $totalMalam,
            'grandTotal' => $totalSiang + $totalMalam,
        ];
    }

    #[Computed]
    public function dokumentasiMap()
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $dates = $this->dates;
        if (empty($dates)) {
            return collect();
        }

        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        return DokumentasiKonsumsi::whereIn('tanggal', $dates)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->get()
            ->keyBy(fn ($item) => $item->tanggal->format('Y-m-d'));
    }

    #[Computed]
    public function personnels()
    {
        if (!$this->readyToLoad) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        $dates = $this->dates;

        $paginator = Personnel::with([
            'absensis' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates);
            },
            'jadwals' => function ($query) use ($dates) {
                $query->whereIn('tanggal', $dates)->with('shift.konsumsis');
            },
            'penugasan',
            'opd'
        ])
            ->when($opdId, function ($q) use ($opdId) {
                $q->where('personnels.opd_id', $opdId);
            })
            ->join('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select([
                'personnels.id',
                'personnels.name',
                'personnels.foto',
                'personnels.regu',
                'personnels.attendance_type',
                'personnels.opd_id',
                'personnels.penugasan_id',
            ])
            ->orderBy('opds.name')
            ->orderByRaw('LENGTH(personnels.regu) ASC, personnels.regu ASC')
            ->orderBy('personnels.name')
            ->paginate($this->perPage);

        $paginator->getCollection()->transform(function ($personnel) use ($dates) {
            $personnel->absensi_map = $personnel->absensis->keyBy(fn ($a) => $a->tanggal->format('Y-m-d'));
            $personnel->jadwal_map = $personnel->jadwals->keyBy(fn ($j) => $j->tanggal->format('Y-m-d'));

            $totalSiang = 0;
            $totalMalam = 0;

            foreach ($dates as $d) {
                $abs = $personnel->absensi_map->get($d);
                $jadwal = $personnel->jadwal_map->get($d);

                $isHadir = $abs && (
                    $abs->status === 'HADIR' ||
                    $abs->status === 'TELAT' ||
                    !empty($abs->jam_masuk)
                );

                if ($isHadir && $jadwal && $jadwal->shift) {
                    $konsumsis = $jadwal->shift->konsumsis->pluck('nama')->map(fn ($k) => strtolower(trim($k)))->toArray();
                    if (in_array('siang', $konsumsis)) {
                        $totalSiang++;
                    }
                    if (in_array('malam', $konsumsis)) {
                        $totalMalam++;
                    }
                }
            }

            $personnel->total_siang = $totalSiang;
            $personnel->total_malam = $totalMalam;
            $personnel->total_konsumsi = $totalSiang + $totalMalam;

            return $personnel;
        });

        return $paginator;
    }

    public function applyFilter(): void
    {
        if (!$this->filterStartDate && $this->filterEndDate) {
            $this->filterStartDate = $this->filterEndDate;
        } elseif ($this->filterStartDate && !$this->filterEndDate) {
            $this->filterEndDate = $this->filterStartDate;
        }

        if ($this->filterStartDate && $this->filterEndDate) {
            if ($this->filterStartDate > $this->filterEndDate) {
                $temp = $this->filterStartDate;
                $this->filterStartDate = $this->filterEndDate;
                $this->filterEndDate = $temp;
            }

            $start = Carbon::parse($this->filterStartDate);
            $end = Carbon::parse($this->filterEndDate);
            if ($start->diffInDays($end) > 31) {
                $this->filterEndDate = $start->copy()->addDays(31)->format('Y-m-d');
            }
        }

        $this->startDate = $this->filterStartDate;
        $this->endDate = $this->filterEndDate;

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->filterStartDate = $this->startDate;
        $this->filterEndDate = $this->endDate;

        $this->resetPage();
    }

    public function openAddKonsumsiModal(?string $date = null, string $sesi = 'siang'): void
    {
        $this->modalMode = 'create';
        $this->uploadTanggal = $date ?: Carbon::now()->format('Y-m-d');
        $this->sesiKonsumsi = in_array($sesi, ['siang', 'malam', 'keduanya']) ? $sesi : 'siang';
        $this->fotoSiang = null;
        $this->fotoMalam = null;
        $this->uploadIteration++;
        $this->resetValidation();
        $this->loadExistingDokumentasi();
        $this->showAddModal = true;
    }

    public function openEditKonsumsiModal(string $date, string $sesi = 'siang'): void
    {
        $this->modalMode = 'edit';
        $this->uploadTanggal = $date;
        $this->sesiKonsumsi = in_array($sesi, ['siang', 'malam', 'keduanya']) ? $sesi : 'siang';
        $this->fotoSiang = null;
        $this->fotoMalam = null;
        $this->uploadIteration++;
        $this->resetValidation();
        $this->loadExistingDokumentasi();
        // Tetapkan sesi sesuai tombol yang di klik
        $this->sesiKonsumsi = in_array($sesi, ['siang', 'malam', 'keduanya']) ? $sesi : 'siang';
        $this->showAddModal = true;
    }

    public function closeAddKonsumsiModal(): void
    {
        $this->showAddModal = false;
        $this->fotoSiang = null;
        $this->fotoMalam = null;
        $this->modalMode = 'create';
        $this->uploadIteration++;
        $this->resetValidation();
    }

    public function updatedUploadTanggal(): void
    {
        $this->fotoSiang = null;
        $this->fotoMalam = null;
        $this->uploadIteration++;
        $this->resetValidation();
        $this->loadExistingDokumentasi();
    }

    public function updatedSesiKonsumsi(): void
    {
        $this->resetValidation();
    }

    public function updatedJumlahSiang($value): void
    {
        $max = $this->calculatedSiang;
        if ((int)$value > $max) {
            $this->jumlahSiang = $max;
        } elseif ((int)$value < 0) {
            $this->jumlahSiang = 0;
        }
    }

    public function updatedJumlahMalam($value): void
    {
        $max = $this->calculatedMalam;
        if ((int)$value > $max) {
            $this->jumlahMalam = $max;
        } elseif ((int)$value < 0) {
            $this->jumlahMalam = 0;
        }
    }

    public function loadExistingDokumentasi(): void
    {
        if (!$this->uploadTanggal) {
            $this->existingFotoSiang = null;
            $this->existingFotoMalam = null;
            $this->jumlahSiang = 0;
            $this->jumlahMalam = 0;
            return;
        }

        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        $record = DokumentasiKonsumsi::where('tanggal', $this->uploadTanggal)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->first();

        $this->existingFotoSiang = $record?->foto_siang;
        $this->existingFotoMalam = $record?->foto_malam;

        $maxSiang = $this->calculatedSiang;
        $maxMalam = $this->calculatedMalam;

        if ($record && $record->jumlah_siang !== null && $record->jumlah_siang > 0) {
            $this->jumlahSiang = min($record->jumlah_siang, $maxSiang);
        } else {
            $this->jumlahSiang = $maxSiang;
        }

        if ($record && $record->jumlah_malam !== null && $record->jumlah_malam > 0) {
            $this->jumlahMalam = min($record->jumlah_malam, $maxMalam);
        } else {
            $this->jumlahMalam = $maxMalam;
        }

        // Jika dalam mode 'create' dan salah satu sesi sudah memiliki dokumentasi, arahkan ke sesi yang belum
        if ($this->modalMode === 'create') {
            if ($this->existingFotoSiang && !$this->existingFotoMalam) {
                $this->sesiKonsumsi = 'malam';
            } elseif (!$this->existingFotoSiang && $this->existingFotoMalam) {
                $this->sesiKonsumsi = 'siang';
            } elseif ($this->existingFotoSiang && $this->existingFotoMalam) {
                $this->sesiKonsumsi = 'keduanya';
            }
        }
    }

    public function getCalculatedKonsumsi(string $date): array
    {
        if (isset($this->monthlySummary['daily'][$date])) {
            return [
                'siang' => $this->monthlySummary['daily'][$date]['siang'] ?? 0,
                'malam' => $this->monthlySummary['daily'][$date]['malam'] ?? 0,
            ];
        }

        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        $personnels = Personnel::with([
            'absensis' => fn ($q) => $q->where('tanggal', $date),
            'jadwals' => fn ($q) => $q->where('tanggal', $date)->with('shift.konsumsis'),
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
        if (!$this->uploadTanggal) {
            return 0;
        }

        return $this->getCalculatedKonsumsi($this->uploadTanggal)['siang'] ?? 0;
    }

    #[Computed]
    public function calculatedMalam(): int
    {
        if (!$this->uploadTanggal) {
            return 0;
        }

        return $this->getCalculatedKonsumsi($this->uploadTanggal)['malam'] ?? 0;
    }

    public function deleteDokumentasi(?string $sesi = null): void
    {
        if (!$this->uploadTanggal) {
            return;
        }

        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        $record = DokumentasiKonsumsi::where('tanggal', $this->uploadTanggal)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->first();

        if (!$record) {
            $this->showAddModal = false;
            return;
        }

        $sesi = $sesi ?: $this->sesiKonsumsi;

        if ($sesi === 'keduanya') {
            if ($record->foto_siang && Storage::disk('public')->exists($record->foto_siang)) {
                Storage::disk('public')->delete($record->foto_siang);
            }
            if ($record->foto_malam && Storage::disk('public')->exists($record->foto_malam)) {
                Storage::disk('public')->delete($record->foto_malam);
            }
            $record->delete();
            $sesiLabel = 'Makan Siang & Makan Malam';
        } elseif ($sesi === 'siang') {
            if ($record->foto_siang && Storage::disk('public')->exists($record->foto_siang)) {
                Storage::disk('public')->delete($record->foto_siang);
            }
            $record->foto_siang = null;
            $record->jumlah_siang = 0;
            if (empty($record->foto_malam) && (int)$record->jumlah_malam === 0) {
                $record->delete();
            } else {
                $record->save();
            }
            $sesiLabel = 'Makan Siang';
        } else {
            if ($record->foto_malam && Storage::disk('public')->exists($record->foto_malam)) {
                Storage::disk('public')->delete($record->foto_malam);
            }
            $record->foto_malam = null;
            $record->jumlah_malam = 0;
            if (empty($record->foto_siang) && (int)$record->jumlah_siang === 0) {
                $record->delete();
            } else {
                $record->save();
            }
            $sesiLabel = 'Makan Malam';
        }

        $this->showAddModal = false;
        $this->fotoSiang = null;
        $this->fotoMalam = null;
        $this->existingFotoSiang = null;
        $this->existingFotoMalam = null;
        $this->uploadIteration++;
        unset($this->dokumentasiMap);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Dokumentasi {$sesiLabel} tanggal " . Carbon::parse($this->uploadTanggal)->translatedFormat('d F Y') . ' berhasil dihapus.'
        ]);
    }

    public function saveKonsumsi(): void
    {
        $opdId = Auth::user()->hasRole('super-admin')
            ? ($this->selectedOpd ?: null)
            : Auth::user()->opd()?->id;

        $record = DokumentasiKonsumsi::where('tanggal', $this->uploadTanggal)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->first();

        $maxSiang = $this->calculatedSiang;
        $maxMalam = $this->calculatedMalam;

        $hasNewSiang = !empty($this->fotoSiang);
        $hasNewMalam = !empty($this->fotoMalam);

        // Tentukan sesi mana saja yang akan divalidasi dan disimpan:
        // Prioritas: Jika kedua foto diunggah, simpan keduanya secara simultan!
        if ($hasNewSiang && $hasNewMalam) {
            $saveSiang = true;
            $saveMalam = true;
        } elseif ($this->sesiKonsumsi === 'keduanya') {
            $saveSiang = true;
            $saveMalam = true;
        } elseif ($this->modalMode === 'create') {
            if ($hasNewSiang) {
                $saveSiang = true;
                $saveMalam = false;
            } elseif ($hasNewMalam) {
                $saveSiang = false;
                $saveMalam = true;
            } else {
                $saveSiang = ($this->sesiKonsumsi === 'siang');
                $saveMalam = ($this->sesiKonsumsi === 'malam');
            }
        } else {
            // Mode 'edit'
            if ($this->sesiKonsumsi === 'siang') {
                $saveSiang = true;
                $saveMalam = false;
            } elseif ($this->sesiKonsumsi === 'malam') {
                $saveSiang = false;
                $saveMalam = true;
            } else {
                $saveSiang = true;
                $saveMalam = true;
            }
        }

        $rules = [
            'uploadTanggal' => 'required|date',
            'sesiKonsumsi' => 'required|in:siang,malam,keduanya',
        ];

        $messages = [
            'uploadTanggal.required' => 'Tanggal dokumentasi wajib dipilih.',
            'sesiKonsumsi.required' => 'Sesi konsumsi wajib dipilih.',
        ];

        if ($saveSiang) {
            $rules['jumlahSiang'] = "required|integer|min:0|max:{$maxSiang}";
            $fotoSiangRule = ($this->modalMode === 'create' || empty($record?->foto_siang))
                ? 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
                : 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048';
            $rules['fotoSiang'] = $fotoSiangRule;

            $messages['jumlahSiang.required'] = 'Jumlah porsi makan siang wajib diisi.';
            $messages['jumlahSiang.integer'] = 'Jumlah porsi makan siang harus berupa angka.';
            $messages['jumlahSiang.min'] = 'Jumlah porsi makan siang tidak boleh kurang dari 0.';
            $messages['jumlahSiang.max'] = "Jumlah porsi makan siang tidak boleh melebihi {$maxSiang} porsi (maksimal terdata pada tanggal ini).";
            $messages['fotoSiang.required'] = 'Foto bukti makan siang wajib diunggah.';
            $messages['fotoSiang.image'] = 'File foto makan siang harus berupa gambar.';
            $messages['fotoSiang.mimes'] = 'Format foto makan siang harus berupa JPG, JPEG, PNG, atau WEBP.';
            $messages['fotoSiang.max'] = 'Ukuran foto makan siang maksimal 2MB (2048 KB).';
        }

        if ($saveMalam) {
            $rules['jumlahMalam'] = "required|integer|min:0|max:{$maxMalam}";
            $fotoMalamRule = ($this->modalMode === 'create' || empty($record?->foto_malam))
                ? 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
                : 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048';
            $rules['fotoMalam'] = $fotoMalamRule;

            $messages['jumlahMalam.required'] = 'Jumlah porsi makan malam wajib diisi.';
            $messages['jumlahMalam.integer'] = 'Jumlah porsi makan malam harus berupa angka.';
            $messages['jumlahMalam.min'] = 'Jumlah porsi makan malam tidak boleh kurang dari 0.';
            $messages['jumlahMalam.max'] = "Jumlah porsi makan malam tidak boleh melebihi {$maxMalam} porsi (maksimal terdata pada tanggal ini).";
            $messages['fotoMalam.required'] = 'Foto bukti makan malam wajib diunggah.';
            $messages['fotoMalam.image'] = 'File foto makan malam harus berupa gambar.';
            $messages['fotoMalam.mimes'] = 'Format foto makan malam harus berupa JPG, JPEG, PNG, atau WEBP.';
            $messages['fotoMalam.max'] = 'Ukuran foto makan malam maksimal 2MB (2048 KB).';
        }

        $this->validate($rules, $messages);

        // Jika modal dalam mode 'create' dan sesi sudah ada data tersimpan, larang overwrite
        if ($this->modalMode === 'create') {
            if ($saveSiang && $record?->foto_siang) {
                $this->addError('sesiKonsumsi', 'Dokumentasi makan siang untuk tanggal ini sudah tersimpan. Silakan buka form edit untuk memperbaruinya.');
                return;
            }

            if ($saveMalam && $record?->foto_malam) {
                $this->addError('sesiKonsumsi', 'Dokumentasi makan malam untuk tanggal ini sudah tersimpan. Silakan buka form edit untuk memperbaruinya.');
                return;
            }
        }

        $dataToUpdate = [
            'tanggal' => $this->uploadTanggal,
            'opd_id' => $opdId,
            'created_by' => Auth::id(),
        ];

        $folderTanggal = Carbon::parse($this->uploadTanggal)->format('d-m-Y');
        $targetDirectory = 'dokumentasi-konsumsi/' . $folderTanggal;

        if ($saveSiang) {
            $pathSiang = $record?->foto_siang;
            if ($this->fotoSiang) {
                if ($pathSiang && Storage::disk('public')->exists($pathSiang)) {
                    Storage::disk('public')->delete($pathSiang);
                }
                $pathSiang = $this->fotoSiang->store($targetDirectory, 'public');
            }
            $dataToUpdate['jumlah_siang'] = $this->jumlahSiang;
            $dataToUpdate['foto_siang'] = $pathSiang;
        } else {
            $dataToUpdate['jumlah_siang'] = $record?->jumlah_siang ?? 0;
            $dataToUpdate['foto_siang'] = $record?->foto_siang ?? null;
        }

        if ($saveMalam) {
            $pathMalam = $record?->foto_malam;
            if ($this->fotoMalam) {
                if ($pathMalam && Storage::disk('public')->exists($pathMalam)) {
                    Storage::disk('public')->delete($pathMalam);
                }
                $pathMalam = $this->fotoMalam->store($targetDirectory, 'public');
            }
            $dataToUpdate['jumlah_malam'] = $this->jumlahMalam;
            $dataToUpdate['foto_malam'] = $pathMalam;
        } else {
            $dataToUpdate['jumlah_malam'] = $record?->jumlah_malam ?? 0;
            $dataToUpdate['foto_malam'] = $record?->foto_malam ?? null;
        }

        DokumentasiKonsumsi::updateOrCreate(
            [
                'tanggal' => $this->uploadTanggal,
                'opd_id' => $opdId,
            ],
            $dataToUpdate
        );

        if ($saveSiang && $saveMalam) {
            $sesiLabel = 'Makan Siang & Makan Malam';
        } elseif ($saveSiang) {
            $sesiLabel = 'Makan Siang';
        } else {
            $sesiLabel = 'Makan Malam';
        }

        $verb = $this->modalMode === 'edit' ? 'diperbarui' : 'disimpan';
        $this->showAddModal = false;
        $this->fotoSiang = null;
        $this->fotoMalam = null;
        $this->uploadIteration++;
        unset($this->dokumentasiMap);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Dokumentasi {$sesiLabel} tanggal " . Carbon::parse($this->uploadTanggal)->translatedFormat('d F Y') . " berhasil {$verb}."
        ]);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedOpd(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function opds()
    {
        return Opd::select('id', 'name')->orderBy('name')->get();
    }

    #[Computed]
    public function shifts()
    {
        return Shift::where('type', '!=', 'off')
            ->with('konsumsis')
            ->orderBy('name')
            ->get();
    }
};
