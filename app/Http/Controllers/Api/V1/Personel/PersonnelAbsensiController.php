<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Setting;
use App\Services\AbsensiLokasiService;
use App\Services\AdaptiveFaceLearningService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PersonnelAbsensiController extends Controller
{
    /**
     * Dapatkan profil biometrik (Master 192D & 4 Pose 3D) dan geofence kantor milik personel yang sedang login.
     */
    public function myBiometrics(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');
        $personnel->load(['opd', 'kantor', 'penugasan', 'faceEmbeddings']);

        $master192 = null;
        if (!empty($personnel->face_descriptor_mobile)) {
            $master192 = is_array($personnel->face_descriptor_mobile)
                ? $personnel->face_descriptor_mobile
                : json_decode($personnel->face_descriptor_mobile, true);
        }

        $poses = [];
        if ($personnel->faceEmbeddings) {
            foreach ($personnel->faceEmbeddings as $fe) {
                $emb192 = null;
                if (!empty($fe->face_descriptor_mobile)) {
                    $emb192 = is_array($fe->face_descriptor_mobile)
                        ? $fe->face_descriptor_mobile
                        : json_decode($fe->face_descriptor_mobile, true);
                }

                $adaptive192 = null;
                if (!empty($fe->adaptive_descriptor_mobile)) {
                    $adaptive192 = is_array($fe->adaptive_descriptor_mobile)
                        ? $fe->adaptive_descriptor_mobile
                        : json_decode($fe->adaptive_descriptor_mobile, true);
                }

                $poses[] = [
                    'pose' => $fe->pose_type,
                    'pose_type' => $fe->pose_type,
                    'face_descriptor_mobile' => $emb192 ?: [],
                    'adaptive_descriptor_mobile' => $adaptive192,
                    'adaptation_count' => (int) $fe->adaptation_count,
                    'last_adapted_at' => $fe->last_adapted_at?->toIso8601String(),
                    'foto' => $fe->foto ? asset('storage/' . $fe->foto) : null,
                ];
            }
        }

        $kantor = $personnel->kantor;
        $kantorData = null;
        if ($kantor) {
            $kantorData = [
                'id' => (string) $kantor->id,
                'name' => $kantor->name ?? $kantor->nama_kantor ?? 'Kantor Penugasan',
                'latitude' => (float) ($kantor->latitude ?? 0.0),
                'longitude' => (float) ($kantor->longitude ?? 0.0),
                'radius_meter' => (int) ($kantor->radius_meter ?? $kantor->radius ?? 100),
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data biometrik personel berhasil dimuat.',
            'data' => [
                'id' => $personnel->id,
                'name' => $personnel->name,
                'nik' => $personnel->nik,
                'foto' => $personnel->foto ? asset('storage/' . $personnel->foto) : null,
                'face_descriptor_mobile' => $master192,
                'multi_embeddings_192' => $poses,
                'face_recognition' => (bool) $personnel->face_recognition,
                'wajib_absen_di_lokasi' => (bool) $personnel->wajib_absen_di_lokasi,
                'opd_id' => (string) $personnel->opd_id,
                'opd_name' => $personnel->opd?->name ?? '-',
                'kantor' => $kantorData,
            ],
        ]);
    }

    /**
     * Memeriksa status presensi, jadwal, dan jendela toleransi personel yang sedang login.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');
        $personnel->load(['opd', 'kantor']);

        $id = $personnel->id;
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');

        // Night shift buffer: apakah masih dalam window pulang shift malam kemarin
        $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);
        $jadwal = null;
        $activeDate = $today;

        $yesterdayJadwal = Jadwal::where('personnel_id', $id)
            ->whereDate('tanggal', $yesterday)
            ->with('shift')
            ->first();

        if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->type !== 'off' && $yesterdayJadwal->shift->start_time && $yesterdayJadwal->shift->end_time) {
            $sTime = Carbon::parse($yesterdayJadwal->shift->start_time);
            $eTime = Carbon::parse($yesterdayJadwal->shift->end_time);

            if ($sTime->format('H:i:s') >= $eTime->format('H:i:s')) {
                $endDatetime = Carbon::parse($today)->setTimeFrom($eTime);
                $windowOutEnd = $endDatetime->copy()->addMinutes($selesaiOut);

                // Jika sekarang sebelum windowOutEnd dan user belum absen pulang kemarin
                if ($now->lessThanOrEqualTo($windowOutEnd)) {
                    $existingYest = Absensi::where('personnel_id', $id)
                        ->whereDate('tanggal', $yesterday)
                        ->first();
                    if (!$existingYest || !$existingYest->jam_pulang) {
                        $jadwal = $yesterdayJadwal;
                        $activeDate = $yesterday;
                    }
                }
            }
        }

        if (!$jadwal) {
            $jadwal = Jadwal::where('personnel_id', $id)
                ->whereDate('tanggal', $today)
                ->with('shift')
                ->first();
        }

        // Cek apakah personel sedang libur atau tidak ada jadwal
        if (!$jadwal || !$jadwal->shift || $jadwal->shift->type === 'off') {
            return response()->json([
                'status' => 'info',
                'can_attend' => false,
                'action_type' => 'libur',
                'message' => 'Hari ini adalah hari libur atau Anda tidak memiliki jadwal dinas aktif.',
                'data' => [
                    'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                    'shift' => null,
                    'jadwal' => null,
                ],
            ]);
        }

        $shift = $jadwal->shift;
        $shiftData = [
            'id' => (string) $shift->id,
            'name' => $shift->name,
            'start_time' => $shift->start_time,
            'end_time' => $shift->end_time,
        ];

        // Ambil transaksi absensi yang ada pada tanggal jadwal aktif
        $absensi = Absensi::where('personnel_id', $id)
            ->whereDate('tanggal', $activeDate)
            ->first();

        // Cek apakah sudah absen lengkap (masuk & pulang)
        if ($absensi && $absensi->jam_masuk && $absensi->jam_pulang) {
            return response()->json([
                'status' => 'info',
                'can_attend' => false,
                'action_type' => 'selesai',
                'message' => 'Anda telah menyelesaikan presensi masuk dan pulang untuk jadwal hari ini.',
                'data' => [
                    'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                    'shift' => $shiftData,
                    'jadwal' => [
                        'id' => (string) $jadwal->id,
                        'tanggal' => $activeDate,
                        'shift' => $shiftData,
                    ],
                    'action_type' => 'selesai',
                    'absensi' => $absensi,
                ],
            ]);
        }

        // Hitung Window Presensi (Toleransi Waktu)
        $isDirectCheckOut = false;
        if ($shift->start_time && $shift->end_time) {
            $startTime = Carbon::parse($activeDate . ' ' . $shift->start_time);
            $isNightShift = Carbon::parse($shift->start_time)->format('H:i:s') >= Carbon::parse($shift->end_time)->format('H:i:s');
            $endDate = $isNightShift ? Carbon::parse($activeDate)->addDay()->format('Y-m-d') : $activeDate;
            $endTime = Carbon::parse($endDate . ' ' . $shift->end_time);

            $mulaiIn = (int) Setting::get('absensi_masuk_mulai', 60);
            $selesaiIn = (int) Setting::get('absensi_masuk_selesai', 120);
            $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 60);
            $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

            $windowInStart = $startTime->copy()->subMinutes($mulaiIn);
            $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);
            $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);
            $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);

            // DIRECT CHECK-OUT: Jika belum absen masuk dan waktu sekarang sudah masuk/melewati window pulang
            if ((!$absensi || !$absensi->jam_masuk) && $now->greaterThanOrEqualTo($windowOutStart)) {
                $isDirectCheckOut = true;
            }

            if (!$isDirectCheckOut && (!$absensi || !$absensi->jam_masuk)) {
                // Skenario: Belum Absen Masuk
                if ($now->lessThan($windowInStart)) {
                    $diff = $windowInStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'belum_masuk',
                        'message' => "Belum waktunya Absen Masuk. Jadwal shift {$shift->name} dimulai pukul {$startTime->format('H:i')} WIB (dibuka mulai {$windowInStart->format('H:i')} WIB). Silakan kembali $diff lagi.",
                        'data' => [
                            'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                            'shift' => $shiftData,
                            'jadwal' => [
                                'id' => (string) $jadwal->id,
                                'tanggal' => $activeDate,
                                'shift' => $shiftData,
                            ],
                            'action_type' => 'masuk',
                        ],
                    ]);
                } elseif ($now->greaterThan($windowInEnd) && $now->lessThan($windowOutStart)) {
                    $diff = $windowOutStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'belum_pulang',
                        'message' => "Batas waktu toleransi Absen Masuk untuk jadwal ini telah berakhir ({$windowInEnd->format('H:i')} WIB). Silakan kembali $diff lagi untuk Absen Pulang (dibuka mulai {$windowOutStart->format('H:i')} WIB).",
                        'data' => [
                            'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                            'shift' => $shiftData,
                            'jadwal' => [
                                'id' => (string) $jadwal->id,
                                'tanggal' => $activeDate,
                                'shift' => $shiftData,
                            ],
                            'action_type' => 'pulang',
                        ],
                    ]);
                } elseif ($now->greaterThan($windowOutEnd)) {
                    return response()->json([
                        'status' => 'error',
                        'can_attend' => false,
                        'action_type' => 'terlewat',
                        'message' => "Batas waktu toleransi presensi untuk jadwal ini telah berakhir ({$windowOutEnd->format('H:i')} WIB).",
                        'data' => [
                            'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                            'shift' => $shiftData,
                            'jadwal' => [
                                'id' => (string) $jadwal->id,
                                'tanggal' => $activeDate,
                                'shift' => $shiftData,
                            ],
                            'action_type' => 'terlewat',
                        ],
                    ]);
                }
            } else {
                // Skenario: Sudah Masuk, Mau Absen Pulang
                if ($now->lessThan($windowOutStart)) {
                    $diff = $windowOutStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'belum_pulang',
                        'message' => "Belum waktunya Absen Pulang. Jadwal shift {$shift->name} pulang pukul {$endTime->format('H:i')} WIB (dibuka mulai {$windowOutStart->format('H:i')} WIB). Silakan kembali $diff lagi.",
                        'data' => [
                            'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                            'shift' => $shiftData,
                            'jadwal' => [
                                'id' => (string) $jadwal->id,
                                'tanggal' => $activeDate,
                                'shift' => $shiftData,
                            ],
                            'action_type' => 'pulang',
                        ],
                    ]);
                }

                if ($now->greaterThan($windowOutEnd)) {
                    return response()->json([
                        'status' => 'error',
                        'can_attend' => false,
                        'action_type' => 'terlewat',
                        'message' => "Batas waktu toleransi Absen Pulang untuk jadwal ini telah berakhir ({$windowOutEnd->format('H:i')} WIB).",
                        'data' => [
                            'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                            'shift' => $shiftData,
                            'jadwal' => [
                                'id' => (string) $jadwal->id,
                                'tanggal' => $activeDate,
                                'shift' => $shiftData,
                            ],
                            'action_type' => 'pulang',
                        ],
                    ]);
                }
            }
        }

        $nextAction = ($isDirectCheckOut || ($absensi && $absensi->jam_masuk)) ? 'pulang' : 'masuk';
        $pesan = $isDirectCheckOut
            ? "Siap melakukan presensi pulang (Shift {$shift->name})."
            : "Siap melakukan presensi $nextAction.";

        return response()->json([
            'status' => 'success',
            'can_attend' => true,
            'action_type' => $nextAction,
            'message' => $pesan,
            'data' => [
                'personnel' => [
                    'id' => (string) $personnel->id,
                    'name' => $personnel->name,
                    'opd_name' => $personnel->opd?->name ?? '-',
                ],
                'shift' => $shiftData,
                'jadwal' => [
                    'id' => (string) $jadwal->id,
                    'tanggal' => $activeDate,
                    'shift' => $shiftData,
                ],
                'action_type' => $nextAction,
                'absensi' => $absensi,
            ],
        ]);
    }

    /**
     * Menyimpan transaksi absensi mandiri (Masuk atau Pulang) oleh Personel.
     */
    public function store(
        Request $request,
        AbsensiLokasiService $lokasiService,
        AdaptiveFaceLearningService $adaptiveService
    ): JsonResponse {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');
        $personnel->load(['kantor', 'opd']);

        $validator = Validator::make($request->all(), [
            'foto' => 'required|string', // Base64 JPEG
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'platform' => 'nullable|string',
            'device_name' => 'nullable|string',
            'unique_device_id' => 'nullable|string',
            'face_descriptor_mobile' => 'nullable',
            'confidence_score' => 'nullable|numeric',
            'euler_angles' => 'nullable|array',
            'pose_type' => 'nullable|string',
            'is_mocked' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // 1. Validasi Fake GPS / Mock Location
        if ($request->boolean('is_mocked')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terdeteksi penggunaan aplikasi lokasi palsu (Fake GPS). Presensi dibatalkan.',
            ], 403);
        }

        // 2. Validasi Geofencing
        $hasilLokasi = $lokasiService->validasiLokasi($personnel, (float) $request->lat, (float) $request->lng);
        if (!$hasilLokasi['boleh']) {
            return response()->json([
                'status' => 'error',
                'message' => $hasilLokasi['pesan'] ?: 'Lokasi Anda berada di luar radius kantor yang diizinkan.',
                'data' => [
                    'jarak_meter' => $hasilLokasi['jarak_meter'],
                    'kantor_name' => $hasilLokasi['kantor_name'],
                ],
            ], 422);
        }

        // 3. Pemrosesan & Sanitasi Gambar Base64 via GD Library (Anti-Polyglot / Anti-XSS)
        $rawImage = base64_decode($request->foto);
        if (!$rawImage || strlen($rawImage) > 2 * 1024 * 1024) {
            return response()->json([
                'status' => 'error',
                'message' => 'Foto bukti absensi tidak valid atau melebihi 2 MB.',
            ], 422);
        }

        $gdImg = @imagecreatefromstring($rawImage);
        if (!$gdImg) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format file yang diunggah bukan gambar yang valid.',
            ], 422);
        }

        $fileName = 'absensi/' . $personnel->id . '_' . time() . '_' . Str::random(8) . '.jpg';
        ob_start();
        imagejpeg($gdImg, null, 80);
        $cleanJpeg = ob_get_clean();
        imagedestroy($gdImg);
        Storage::disk('public')->put($fileName, $cleanJpeg);

        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');

        // 4. Tentukan Jadwal Aktif & Action (Masuk / Pulang)
        $jadwal = null;
        $activeDate = $today;

        $yesterdayJadwal = Jadwal::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $yesterday)
            ->with('shift')
            ->first();

        if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->type !== 'off' && $yesterdayJadwal->shift->start_time && $yesterdayJadwal->shift->end_time) {
            $sTime = Carbon::parse($yesterdayJadwal->shift->start_time);
            $eTime = Carbon::parse($yesterdayJadwal->shift->end_time);

            if ($sTime->format('H:i:s') >= $eTime->format('H:i:s')) {
                $endDatetime = Carbon::parse($today)->setTimeFrom($eTime);
                $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);
                $windowOutEnd = $endDatetime->copy()->addMinutes($selesaiOut);

                if ($now->lessThanOrEqualTo($windowOutEnd)) {
                    $existingYest = Absensi::where('personnel_id', $personnel->id)
                        ->whereDate('tanggal', $yesterday)
                        ->first();
                    if (!$existingYest || !$existingYest->jam_pulang) {
                        $jadwal = $yesterdayJadwal;
                        $activeDate = $yesterday;
                    }
                }
            }
        }

        if (!$jadwal) {
            $jadwal = Jadwal::where('personnel_id', $personnel->id)
                ->whereDate('tanggal', $today)
                ->with('shift')
                ->first();
        }

        if (!$jadwal || !$jadwal->shift || $jadwal->shift->type === 'off') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat melakukan presensi karena tidak ada jadwal dinas aktif hari ini.',
            ], 422);
        }

        $shift = $jadwal->shift;
        $absensi = Absensi::firstOrNew([
            'personnel_id' => $personnel->id,
            'tanggal' => $activeDate,
        ]);

        $isDirectCheckOut = false;
        if ($shift->start_time && $shift->end_time) {
            $isNightShift = Carbon::parse($shift->start_time)->format('H:i:s') >= Carbon::parse($shift->end_time)->format('H:i:s');
            $endDate = $isNightShift ? Carbon::parse($activeDate)->addDay()->format('Y-m-d') : $activeDate;
            $endTime = Carbon::parse($endDate . ' ' . $shift->end_time);
            $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 60);
            $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);

            if (!$absensi->jam_masuk && $now->greaterThanOrEqualTo($windowOutStart)) {
                $isDirectCheckOut = true;
            }
        }

        $action = ($isDirectCheckOut || $absensi->jam_masuk) ? 'pulang' : 'masuk';
        $currentTime = $now->format('H:i:s');

        if ($action === 'masuk') {
            $absensi->jadwal_id = $jadwal->id;
            $absensi->shift_id = $shift->id;
            $absensi->jam_masuk = $currentTime;
            $absensi->lat_masuk = $request->lat;
            $absensi->long_masuk = $request->lng;
            $absensi->foto_masuk = $fileName;

            // Status keterlambatan
            if ($shift->start_time) {
                $toleransiTerlambat = (int) Setting::get('absensi_toleransi_terlambat', 15);
                $jadwalMasuk = Carbon::parse($activeDate . ' ' . $shift->start_time);
                $batasToleransi = $jadwalMasuk->copy()->addMinutes($toleransiTerlambat);

                if ($now->greaterThan($batasToleransi)) {
                    $absensi->status = 'terlambat';
                } else {
                    $absensi->status = 'hadir';
                }
            } else {
                $absensi->status = 'hadir';
            }
        } else {
            // Skenario Absen Pulang
            $absensi->jadwal_id = $jadwal->id;
            $absensi->shift_id = $shift->id;
            $absensi->jam_pulang = $currentTime;
            $absensi->lat_pulang = $request->lat;
            $absensi->long_pulang = $request->lng;
            $absensi->foto_pulang = $fileName;

            if ($isDirectCheckOut && !$absensi->jam_masuk) {
                $absensi->status = 'hadir';
            }
        }

        $absensi->save();

        // 5. Pilar 4: Adaptive Face Learning jika confidence tinggi
        $adapted = false;
        if ($request->filled('face_descriptor_mobile') && $request->filled('confidence_score')) {
            $descriptorRaw = $request->input('face_descriptor_mobile');
            $descriptor = is_array($descriptorRaw) ? $descriptorRaw : json_decode($descriptorRaw, true);

            if (is_array($descriptor) && count($descriptor) === 192) {
                $adaptiveResult = $adaptiveService->adaptEmbedding(
                    personnelId: $personnel->id,
                    newEmbedding: $descriptor,
                    similarityScore: (float) $request->input('confidence_score'),
                    poseType: $request->input('pose_type', 'FRONT'),
                    eulerAngles: $request->input('euler_angles')
                );
                $adapted = $adaptiveResult['adapted'] ?? false;
            }
        }

        return response()->json([
            'status' => 'success',
            'action_type' => $action,
            'message' => "Presensi $action berhasil dicatat pada " . $now->format('H:i') . ' WIB.',
            'data' => [
                'id' => (string) $absensi->id,
                'action_type' => $action,
                'tanggal' => $activeDate,
                'jam' => $currentTime,
                'status' => $absensi->status,
                'personnel' => [
                    'id' => (string) $personnel->id,
                    'name' => $personnel->name,
                ],
                'shift' => [
                    'id' => (string) $shift->id,
                    'name' => $shift->name,
                ],
                'foto_url' => asset('storage/' . $fileName),
                'adaptive_learning' => [
                    'adapted' => $adapted,
                ],
            ],
        ]);
    }
}
