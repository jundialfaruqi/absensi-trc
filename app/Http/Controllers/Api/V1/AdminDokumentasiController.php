<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DokumentasiKonsumsi;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminDokumentasiController extends Controller
{
    /**
     * Helper untuk memeriksa apakah user saat ini adalah Super Admin.
     */
    private function isSuperAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return in_array($user->role, ['SUPER_ADMIN', 'SUPERADMIN', 'super-admin'])
            || (method_exists($user, 'hasRole') && $user->hasRole('super-admin'));
    }

    /**
     * Helper untuk mendapatkan OPD ID user jika bukan Super Admin.
     */
    private function getUserOpdId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        if (method_exists($user, 'opd') && $user->opd()) {
            return (int) $user->opd()->id;
        }

        return $user->opd_id ? (int) $user->opd_id : null;
    }

    /**
     * Menghitung jumlah konsumsi otomatis berdasarkan absensi hadir dan jadwal shift personel.
     */
    private function calculateKonsumsi(string $date, ?int $opdId = null): array
    {
        if (!$date) {
            return ['siang' => 0, 'malam' => 0];
        }

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

            $isHadir = $abs &&
                !in_array($abs->status, ['IZIN', 'SAKIT', 'CUTI', 'ALPA', 'LIBUR']) &&
                (
                    $abs->status === 'HADIR' ||
                    $abs->status === 'TELAT' ||
                    $abs->status_masuk === 'HADIR' ||
                    $abs->status_masuk === 'TELAT' ||
                    !empty($abs->jam_masuk) ||
                    !empty($abs->jam_pulang)
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

    /**
     * Memeriksa kuota konsumsi dan riwayat dokumentasi pada tanggal tertentu.
     * GET /api/v1/admin/dokumentasi/check
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $tanggal = $request->query('tanggal', Carbon::now()->format('Y-m-d'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $tanggal = Carbon::now()->format('Y-m-d');
            }

            $opdId = $this->getUserOpdId($request);
            $calculated = $this->calculateKonsumsi($tanggal, $opdId);

            $record = DokumentasiKonsumsi::whereDate('tanggal', $tanggal)
                ->when($opdId, fn ($q) => $q->where('opd_id', $opdId), fn ($q) => $q->whereNull('opd_id'))
                ->first();

            $existingRecordData = null;
            if ($record) {
                $existingRecordData = [
                    'id' => $record->id,
                    'is_siang_documented' => !empty($record->foto_siang),
                    'jumlah_siang' => $record->jumlah_siang,
                    'foto_siang_url' => $record->foto_siang ? url(Storage::url($record->foto_siang)) : null,
                    'foto_siang_2_url' => $record->foto_siang_2 ? url(Storage::url($record->foto_siang_2)) : null,
                    'is_malam_documented' => !empty($record->foto_malam),
                    'jumlah_malam' => $record->jumlah_malam,
                    'foto_malam_url' => $record->foto_malam ? url(Storage::url($record->foto_malam)) : null,
                    'foto_malam_2_url' => $record->foto_malam_2 ? url(Storage::url($record->foto_malam_2)) : null,
                    'keterangan' => $record->keterangan,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Data kuota dan dokumentasi berhasil dimuat.',
                'data' => [
                    'tanggal' => $tanggal,
                    'calculated_siang' => $calculated['siang'],
                    'calculated_malam' => $calculated['malam'],
                    'existing_record' => $existingRecordData,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa data dokumentasi: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengunggah dokumentasi konsumsi baru (Siang atau Malam).
     * POST /api/v1/admin/dokumentasi
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $opdId = $this->getUserOpdId($request);

            $validator = Validator::make($request->all(), [
                'tanggal' => ['required', 'date_format:Y-m-d'],
                'shift' => ['required', 'in:siang,malam'],
                'jumlah_porsi' => ['required', 'integer', 'min:0'],
                'keterangan' => ['nullable', 'string', 'max:1000'],
                'foto1' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
                'foto2' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            ], [
                'tanggal.required' => 'Tanggal dokumentasi wajib diisi.',
                'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
                'shift.required' => 'Pilih shift terlebih dahulu.',
                'shift.in' => 'Shift harus berupa siang atau malam.',
                'jumlah_porsi.required' => 'Jumlah porsi wajib diisi.',
                'jumlah_porsi.integer' => 'Jumlah porsi harus berupa angka bulat.',
                'jumlah_porsi.min' => 'Jumlah porsi tidak boleh kurang dari 0.',
                'foto1.required' => 'Foto dokumentasi utama wajib diunggah.',
                'foto1.image' => 'File foto utama harus berupa gambar.',
                'foto1.mimes' => 'Format foto utama harus JPEG, JPG, PNG, atau WebP.',
                'foto1.max' => 'Ukuran foto utama maksimal 2MB (2048KB).',
                'foto2.image' => 'File foto kedua harus berupa gambar.',
                'foto2.mimes' => 'Format foto kedua harus JPEG, JPG, PNG, atau WebP.',
                'foto2.max' => 'Ukuran foto kedua maksimal 2MB (2048KB).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tanggal = $request->input('tanggal');
            $shift = strtolower($request->input('shift'));
            $jumlahPorsi = (int) $request->input('jumlah_porsi');
            $keterangan = $request->input('keterangan');

            // 1. Validasi batas kuota porsi otomatis
            $calculated = $this->calculateKonsumsi($tanggal, $opdId);
            $maxPorsi = $shift === 'siang' ? $calculated['siang'] : $calculated['malam'];

            if ($jumlahPorsi > $maxPorsi) {
                return response()->json([
                    'success' => false,
                    'message' => "Jumlah porsi ({$jumlahPorsi}) tidak boleh melebihi kuota kalkulasi otomatis ({$maxPorsi} Porsi).",
                ], 422);
            }

            // 2. Periksa apakah shift sudah terdokumentasi sebelumnya
            $record = DokumentasiKonsumsi::whereDate('tanggal', $tanggal)
                ->when($opdId, fn ($q) => $q->where('opd_id', $opdId), fn ($q) => $q->whereNull('opd_id'))
                ->first();

            if ($record) {
                if ($shift === 'siang' && !empty($record->foto_siang)) {
                    $dateLabel = Carbon::parse($tanggal)->translatedFormat('d F Y');
                    return response()->json([
                        'success' => false,
                        'message' => "Dokumentasi Shift Siang pada tanggal {$dateLabel} sudah pernah diunggah.",
                    ], 422);
                }
                if ($shift === 'malam' && !empty($record->foto_malam)) {
                    $dateLabel = Carbon::parse($tanggal)->translatedFormat('d F Y');
                    return response()->json([
                        'success' => false,
                        'message' => "Dokumentasi Shift Malam pada tanggal {$dateLabel} sudah pernah diunggah.",
                    ], 422);
                }
            }

            // 3. Simpan File Foto
            $targetDir = 'dokumentasi-konsumsi/' . Carbon::parse($tanggal)->format('d-m-Y');

            $path1 = $request->file('foto1')->store($targetDir, 'public');
            $path2 = $request->hasFile('foto2') ? $request->file('foto2')->store($targetDir, 'public') : null;

            $dataToSave = [];
            if ($keterangan !== null && $keterangan !== '') {
                $dataToSave['keterangan'] = $keterangan;
            }

            if ($shift === 'siang') {
                $dataToSave['jumlah_siang'] = $jumlahPorsi;
                $dataToSave['foto_siang'] = $path1;
                $dataToSave['foto_siang_2'] = $path2;
            } else {
                $dataToSave['jumlah_malam'] = $jumlahPorsi;
                $dataToSave['foto_malam'] = $path1;
                $dataToSave['foto_malam_2'] = $path2;
            }

            if ($record) {
                $record->update($dataToSave);
            } else {
                $dataToSave['tanggal'] = $tanggal;
                $dataToSave['opd_id'] = $opdId;
                $dataToSave['created_by'] = $user ? $user->id : null;
                $record = DokumentasiKonsumsi::create($dataToSave);
            }

            $shiftLabel = $shift === 'siang' ? 'Makan Siang' : 'Makan Malam';
            $tanggalFormatted = Carbon::parse($tanggal)->translatedFormat('d F Y');

            return response()->json([
                'success' => true,
                'message' => "Dokumentasi {$shiftLabel} tanggal {$tanggalFormatted} berhasil disimpan.",
                'data' => [
                    'id' => $record->id,
                    'tanggal' => $record->tanggal->format('Y-m-d'),
                    'shift' => $shift,
                    'jumlah_porsi' => $jumlahPorsi,
                    'foto1_url' => url(Storage::url($path1)),
                    'foto2_url' => $path2 ? url(Storage::url($path2)) : null,
                ],
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan dokumentasi: ' . $th->getMessage(),
            ], 500);
        }
    }
}
