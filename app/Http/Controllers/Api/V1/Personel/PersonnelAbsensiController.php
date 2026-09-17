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
use Illuminate\Support\Facades\Log;
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

        // Ambil transaksi absensi yang ada pada tanggal jadwal aktif
        $absensi = Absensi::where('personnel_id', $id)
            ->whereDate('tanggal', $activeDate)
            ->first();

        // 1. Cek apakah tidak ada jadwal
        if (!$jadwal) {
            if ($personnel->attendance_type === 'FLEXIBLE') {
                if ($absensi && $absensi->jam_masuk && $absensi->jam_pulang) {
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'selesai',
                        'message' => 'Anda telah menyelesaikan presensi masuk dan pulang hari ini (Mode Fleksibel).',
                        'data' => [
                            'personnel' => [
                                'id' => (string) $personnel->id,
                                'name' => $personnel->name,
                                'opd_name' => $personnel->opd?->name ?? '-',
                            ],
                            'action_type' => 'selesai',
                            'absensi' => $absensi,
                        ],
                    ]);
                }

                $nextAction = ($absensi && $absensi->jam_masuk) ? 'pulang' : 'masuk';
                return response()->json([
                    'status' => 'success',
                    'can_attend' => true,
                    'action_type' => $nextAction,
                    'message' => "Siap melakukan presensi $nextAction (Mode Fleksibel).",
                    'data' => [
                        'personnel' => [
                            'id' => (string) $personnel->id,
                            'name' => $personnel->name,
                            'opd_name' => $personnel->opd?->name ?? '-',
                        ],
                        'action_type' => $nextAction,
                        'absensi' => $absensi,
                    ],
                ]);
            }

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

        // 2. Cek apakah jadwal adalah LIBUR / OFF
        if ($jadwal->shift && $jadwal->shift->type === 'off') {
            if ($personnel->attendance_type === 'FLEXIBLE') {
                if ($absensi && $absensi->jam_masuk && $absensi->jam_pulang) {
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'selesai',
                        'message' => 'Anda telah menyelesaikan presensi masuk dan pulang hari ini (Mode Fleksibel).',
                        'data' => [
                            'personnel' => [
                                'id' => (string) $personnel->id,
                                'name' => $personnel->name,
                                'opd_name' => $personnel->opd?->name ?? '-',
                            ],
                            'action_type' => 'selesai',
                            'absensi' => $absensi,
                        ],
                    ]);
                }

                $nextAction = ($absensi && $absensi->jam_masuk) ? 'pulang' : 'masuk';
                return response()->json([
                    'status' => 'success',
                    'can_attend' => true,
                    'action_type' => $nextAction,
                    'message' => "Siap melakukan presensi $nextAction (Mode Fleksibel).",
                    'data' => [
                        'personnel' => [
                            'id' => (string) $personnel->id,
                            'name' => $personnel->name,
                            'opd_name' => $personnel->opd?->name ?? '-',
                        ],
                        'action_type' => $nextAction,
                        'absensi' => $absensi,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'info',
                'can_attend' => false,
                'action_type' => 'libur',
                'message' => 'Hari ini adalah hari libur (OFF) untuk jadwal dinas Anda.',
                'data' => [
                    'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                    'shift' => ['name' => $jadwal->shift->name],
                    'jadwal' => [
                        'id' => (string) $jadwal->id,
                        'tanggal' => $activeDate,
                        'shift' => ['name' => $jadwal->shift->name],
                    ],
                ],
            ]);
        }

        $shift = $jadwal->shift;
        $shiftData = [
            'id' => (string) $shift->id,
            'name' => $shift->name,
            'start_time' => Carbon::parse($shift->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($shift->end_time)->format('H:i:s'),
        ];

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
            $sStart = Carbon::parse($shift->start_time);
            $sEnd = Carbon::parse($shift->end_time);

            $startTime = Carbon::parse($activeDate)->setTime($sStart->hour, $sStart->minute, $sStart->second);
            $isNightShift = $sStart->format('H:i:s') >= $sEnd->format('H:i:s');
            $endDate = $isNightShift ? Carbon::parse($activeDate)->addDay()->format('Y-m-d') : $activeDate;
            $endTime = Carbon::parse($endDate)->setTime($sEnd->hour, $sEnd->minute, $sEnd->second);

            $mulaiIn = (int) Setting::get('absensi_masuk_mulai', 30);
            $selesaiIn = (int) Setting::get('absensi_masuk_selesai', 120);
            $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 30);
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

        ob_start();
        imagejpeg($gdImg, null, 80);
        $cleanJpeg = ob_get_clean();
        imagedestroy($gdImg);

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

        if ((!$jadwal || !$jadwal->shift || $jadwal->shift->type === 'off') && $personnel->attendance_type !== 'FLEXIBLE') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat melakukan presensi karena tidak ada jadwal dinas aktif hari ini.',
            ], 422);
        }

        $shift = $jadwal?->shift;
        $absensi = Absensi::firstOrNew([
            'personnel_id' => $personnel->id,
            'tanggal' => $activeDate,
        ]);

        if ($absensi->exists && $absensi->jam_pulang) {
            return response()->json([
                'status' => 'info',
                'message' => 'Anda sudah menyelesaikan seluruh sesi absensi untuk jadwal ini.',
                'data' => $absensi,
            ]);
        }

        $platform = $request->platform ?: 'android';
        $deviceName = $request->device_name ?: 'Mobile Personel';
        $uniqueId = $request->unique_device_id ?: 'personnel-' . $personnel->id;

        // 3b. Validasi Jendela Waktu Jadwal Shift (Time Window Validation)
        $isDirectCheckOut = false;
        if ($jadwal && $jadwal->shift && $jadwal->shift->start_time && $jadwal->shift->end_time && $jadwal->shift->type !== 'off') {
            $mulaiIn = (int) Setting::get('absensi_masuk_mulai', 30);
            $selesaiIn = (int) Setting::get('absensi_masuk_selesai', 120);
            $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 30);
            $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

            $startTime = Carbon::parse($activeDate)->setTimeFrom($shift->start_time);
            $windowInStart = $startTime->copy()->subMinutes($mulaiIn);
            $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

            $pulangDate = $activeDate;
            if (Carbon::parse($shift->start_time)->format('H:i:s') >= Carbon::parse($shift->end_time)->format('H:i:s')) {
                $pulangDate = Carbon::parse($activeDate)->addDay()->format('Y-m-d');
            }
            $endTime = Carbon::parse($pulangDate)->setTimeFrom($shift->end_time);
            $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);
            $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);

            if (!$absensi->exists || !$absensi->jam_masuk) {
                // Skenario: Belum Absen Masuk
                if ($now->between($windowOutStart, $windowOutEnd)) {
                    // DIRECT CHECK-OUT: Diizinkan langsung absen pulang jika berada di jendela pulang
                    $isDirectCheckOut = true;
                } elseif ($now->lessThan($windowInStart)) {
                    $diff = $windowInStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'error',
                        'message' => "Belum waktunya Absen Masuk. Jadwal shift {$shift->name} masuk pukul {$startTime->format('H:i')} WIB (dibuka mulai {$windowInStart->format('H:i')} WIB). Silakan kembali $diff lagi.",
                    ], 422);
                } elseif ($now->greaterThan($windowInEnd) && $now->lessThan($windowOutStart)) {
                    $diff = $windowOutStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'error',
                        'message' => "Batas waktu toleransi Absen Masuk untuk jadwal ini telah berakhir ({$windowInEnd->format('H:i')} WIB). Silakan kembali $diff lagi untuk Absen Pulang.",
                    ], 422);
                } elseif ($now->greaterThan($windowOutEnd)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Batas waktu toleransi presensi untuk jadwal ini telah berakhir ({$windowOutEnd->format('H:i')} WIB).",
                    ], 422);
                }
            } else {
                // Skenario: Mencoba Absen Pulang (Sudah Masuk)
                if ($now->lessThan($windowOutStart)) {
                    $diff = $windowOutStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'error',
                        'message' => "Belum waktunya Absen Pulang. Jadwal shift {$shift->name} pulang pukul {$endTime->format('H:i')} WIB (dibuka mulai {$windowOutStart->format('H:i')} WIB). Silakan kembali $diff lagi.",
                    ], 422);
                }

                if ($now->greaterThan($windowOutEnd)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Batas waktu toleransi Absen Pulang untuk jadwal ini telah berakhir ({$windowOutEnd->format('H:i')} WIB).",
                    ], 422);
                }
            }
        }

        // 4. LOGIKA ABSEN MASUK VS PULANG (Simpan ke Sub-folder per tanggal: absensi/YYYY-MM-DD/)
        $folderPath = 'absensi/' . $activeDate;

        if ($isDirectCheckOut || ($absensi->exists && $absensi->jam_masuk && !$absensi->jam_pulang)) {
            // --- ABSEN PULANG (Normal atau Direct Check-Out) ---
            $statusPulang = 'HADIR';
            if ($jadwal && $jadwal->shift && $jadwal->shift->end_time) {
                $eDate = $activeDate;
                if ($jadwal->shift->start_time && Carbon::parse($jadwal->shift->start_time)->format('H:i:s') >= Carbon::parse($jadwal->shift->end_time)->format('H:i:s')) {
                    $eDate = Carbon::parse($activeDate)->addDay()->format('Y-m-d');
                }
                $shiftEnd = Carbon::parse($eDate)->setTimeFrom($jadwal->shift->end_time);
                if ($now->lessThan($shiftEnd)) {
                    $statusPulang = 'PULANG CEPAT';
                }
            }

            $fileName = $folderPath . '/out_' . $personnel->id . '_' . time() . '_' . Str::random(8) . '.jpg';
            Storage::disk('public')->put($fileName, $cleanJpeg);

            $absensi->jadwal_id = $jadwal?->id;
            $absensi->status = 'HADIR';

            if ($isDirectCheckOut) {
                // Sesi masuk tidak dilakukan -> ditandai ALPA
                $absensi->status_masuk = 'ALPA';
            }

            $absensi->kantor_id_pulang = $hasilLokasi['kantor_id'];
            $absensi->jam_pulang = $now;
            $absensi->status_pulang = $statusPulang;
            $absensi->foto_pulang = $fileName;
            $absensi->lat_pulang = $request->lat;
            $absensi->lng_pulang = $request->lng;
            $absensi->jarak_meter_pulang = $hasilLokasi['jarak_meter'];
            $absensi->is_within_radius_pulang = $hasilLokasi['is_within_radius'];
            $absensi->platform_pulang = $platform;
            $absensi->device_name_pulang = $deviceName;
            $absensi->unique_device_id_pulang = $uniqueId;
            $absensi->save();

            $pesan = "Absen PULANG ($statusPulang) berhasil dicatat." . ($isDirectCheckOut ? " (Masuk: ALPA)" : "");
            $actionType = 'pulang';
        } elseif (!$absensi->exists || !$absensi->jam_masuk) {
            // --- ABSEN MASUK ---
            $statusMasuk = 'HADIR';
            if ($jadwal && $jadwal->shift && $jadwal->shift->start_time) {
                $shiftStart = Carbon::parse($activeDate)->setTimeFrom($jadwal->shift->start_time);
                $toleransi = (int) ($jadwal->shift->toleransi_menit ?? Setting::get('absensi_masuk_toleransi', 15));
                $batasToleransi = $shiftStart->copy()->addMinutes($toleransi);
                if ($now->greaterThan($batasToleransi)) {
                    $statusMasuk = 'TELAT';
                }
            }

            $fileName = $folderPath . '/in_' . $personnel->id . '_' . time() . '_' . Str::random(8) . '.jpg';
            Storage::disk('public')->put($fileName, $cleanJpeg);

            $absensi->jadwal_id = $jadwal?->id;
            $absensi->kantor_id = $hasilLokasi['kantor_id'];
            $absensi->jam_masuk = $now;
            $absensi->status_masuk = $statusMasuk;
            $absensi->status = 'HADIR';
            $absensi->foto_masuk = $fileName;
            $absensi->lat_masuk = $request->lat;
            $absensi->lng_masuk = $request->lng;
            $absensi->jarak_meter = $hasilLokasi['jarak_meter'];
            $absensi->is_within_radius = $hasilLokasi['is_within_radius'];
            $absensi->platform_masuk = $platform;
            $absensi->device_name_masuk = $deviceName;
            $absensi->unique_device_id_masuk = $uniqueId;
            $absensi->save();

            $pesan = "Absen MASUK ($statusMasuk) berhasil dicatat.";
            $actionType = 'masuk';
        } else {
            return response()->json([
                'status' => 'info',
                'message' => 'Anda sudah menyelesaikan seluruh sesi absen masuk dan pulang.',
                'data' => $absensi,
            ]);
        }

        // 5. Pilar 4: Self-Learning Biometric Adaptation (EMA)
        $adaptationResult = null;
        $adapted = false;
        if ($request->filled('face_descriptor_mobile') && $request->filled('confidence_score')) {
            try {
                $adaptationResult = $adaptiveService->attemptAdaptation(
                    personnel: $personnel,
                    capturedDescriptor: $request->input('face_descriptor_mobile'),
                    confidenceScore: (float) $request->input('confidence_score'),
                    poseType: $request->input('pose_type', 'FRONT'),
                    absensiId: $absensi->id,
                    deviceInfo: $deviceName . ' (' . $platform . ')',
                    eulerAngles: $request->input('euler_angles')
                );
                $adapted = $adaptationResult['adapted'] ?? false;
            } catch (\Throwable $e) {
                Log::error("Error executing biometric adaptation in PersonnelAbsensiController: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'action_type' => $actionType,
            'message' => $pesan,
            'data' => [
                'id' => (string) $absensi->id,
                'action_type' => $actionType,
                'tanggal' => $activeDate,
                'jam' => $now->format('H:i:s'),
                'status' => $absensi->status,
                'status_masuk' => $absensi->status_masuk,
                'status_pulang' => $absensi->status_pulang,
                'jarak_meter' => $hasilLokasi['jarak_meter'],
                'kantor_name' => $hasilLokasi['kantor_name'],
                'personnel' => [
                    'id' => (string) $personnel->id,
                    'name' => $personnel->name,
                ],
                'shift' => $shift ? [
                    'id' => (string) $shift->id,
                    'name' => $shift->name,
                ] : null,
                'foto_url' => asset('storage/' . $fileName),
                'adaptive_learning' => [
                    'adapted' => $adapted,
                    'result' => $adaptationResult,
                ],
            ],
        ]);
    }

    /**
     * Dapatkan ringkasan statistik kehadiran bulan berjalan & log aktifitas terbaru milik personel yang sedang login.
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');
        $personnel->load(['opd', 'kantor', 'penugasan']);

        $now = Carbon::now();
        $year = (int) $request->query('year', $now->year);
        $month = (int) $request->query('month', $now->month);

        $targetMonth = Carbon::create($year, $month, 1);
        $startOfMonth = $targetMonth->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $targetMonth->copy()->endOfMonth()->format('Y-m-d');
        $todayStr = $now->format('Y-m-d');

        // 1. Ambil seluruh data absensi personel di bulan tersebut
        $absensis = Absensi::where('personnel_id', $personnel->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $hadirCount = 0;
        $alpaCount = 0;
        $izinCount = 0;

        foreach ($absensis as $a) {
            $statusUpper = strtoupper((string) $a->status);
            if (in_array($statusUpper, ['HADIR', 'TELAT']) || $a->jam_masuk || $a->jam_pulang) {
                $hadirCount++;
            } elseif ($statusUpper === 'ALPA') {
                $alpaCount++;
            } elseif (in_array($statusUpper, ['IZIN', 'SAKIT', 'CUTI', 'DINAS'])) {
                $izinCount++;
            }
        }

        // 2. Hitung total hari dinas/kerja bulan ini
        if ($personnel->attendance_type === 'FLEXIBLE') {
            $totalHari = max($hadirCount + $alpaCount + $izinCount, $now->isSameMonth($targetMonth) ? $now->day : $targetMonth->daysInMonth);
        } else {
            $totalJadwal = Jadwal::where('personnel_id', $personnel->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereHas('shift', fn ($q) => $q->where('type', 'shift'))
                ->count();

            $totalHari = $totalJadwal > 0 ? $totalJadwal : ($now->isSameMonth($targetMonth) ? $now->day : $targetMonth->daysInMonth);
        }

        $hadirPercentage = $totalHari > 0 ? (int) round(($hadirCount / $totalHari) * 100) : 0;
        if ($hadirPercentage > 100) {
            $hadirPercentage = 100;
        }

        // 3. Tentukan status presensi hari ini
        $statusHariIni = 'Belum Melakukan Presensi';
        $todayAbsensi = Absensi::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $todayStr)
            ->first();

        if ($todayAbsensi) {
            if ($todayAbsensi->jam_masuk && $todayAbsensi->jam_pulang) {
                $statusHariIni = 'Sudah Selesai (Pulang: ' . Carbon::parse($todayAbsensi->jam_pulang)->format('H:i') . ' WIB)';
            } elseif ($todayAbsensi->jam_masuk) {
                $statusHariIni = 'Sudah Absen Masuk (' . Carbon::parse($todayAbsensi->jam_masuk)->format('H:i') . ' WIB)';
            } elseif ($todayAbsensi->jam_pulang) {
                $statusHariIni = 'Sudah Absen Pulang (' . Carbon::parse($todayAbsensi->jam_pulang)->format('H:i') . ' WIB)';
            } elseif (in_array(strtoupper((string) $todayAbsensi->status), ['IZIN', 'SAKIT', 'CUTI', 'DINAS'])) {
                $statusHariIni = ucfirst(strtolower($todayAbsensi->status));
            } elseif (strtoupper((string) $todayAbsensi->status) === 'LIBUR') {
                $statusHariIni = 'Hari Ini Libur';
            }
        } else {
            if ($personnel->attendance_type !== 'FLEXIBLE') {
                $todayJadwal = Jadwal::where('personnel_id', $personnel->id)
                    ->whereDate('tanggal', $todayStr)
                    ->with('shift')
                    ->first();

                if ($todayJadwal && $todayJadwal->shift && $todayJadwal->shift->type === 'off') {
                    $statusHariIni = 'Hari Ini Libur (OFF)';
                } elseif ($todayJadwal && $todayJadwal->shift) {
                    $statusHariIni = 'Belum Absen (Shift ' . $todayJadwal->shift->name . ')';
                }
            }
        }

        // 4. Bangun Aktifitas Hari Ini (Section Aktifitas Hari Ini)
        $tglStr = $now->translatedFormat('d M Y');
        $tglFullStr = $now->translatedFormat('l, d M Y');

        $todayAbsensi = Absensi::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $todayStr)
            ->with(['kantor', 'kantorPulang', 'jadwal.shift'])
            ->first();

        $todayJadwal = Jadwal::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $todayStr)
            ->with('shift')
            ->first();

        $shift = $todayAbsensi?->jadwal?->shift ?? $todayJadwal?->shift;
        $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : null);
        $shiftHours = null;
        if ($shift && $shift->start_time && $shift->end_time) {
            $sStart = Carbon::parse($shift->start_time)->format('H:i');
            $sEnd = Carbon::parse($shift->end_time)->format('H:i');
            $shiftHours = "{$sStart} - {$sEnd}";
        }

        $kantorName = $todayAbsensi?->kantor?->name 
            ?? $todayAbsensi?->kantor?->nama_kantor 
            ?? $personnel->kantor?->name 
            ?? 'Lapangan';

        $kantorPulangName = $todayAbsensi?->kantorPulang?->name 
            ?? $todayAbsensi?->kantorPulang?->nama_kantor 
            ?? $todayAbsensi?->kantor?->name 
            ?? $personnel->kantor?->name 
            ?? 'Lapangan';

        // Tentukan status utama secara dinamis dari tabel absensi
        $statusUtama = $todayAbsensi?->status;
        if (empty($statusUtama)) {
            $shiftObj = $todayJadwal?->shift;
            $isShiftOff = $shiftObj && ($shiftObj->type === 'off' || stripos($shiftObj->name ?? '', 'libur') !== false);

            if ($isShiftOff) {
                $statusUtama = $shiftObj->keterangan 
                    ?: (stripos($shiftObj->name ?? '', 'dinas') !== false ? 'DINAS' : 'LIBUR');
            } elseif ($todayJadwal?->status && !in_array(strtoupper($todayJadwal->status), ['SHIFT', 'KERJA'])) {
                $statusUtama = $todayJadwal->status;
            } else {
                $statusUtama = 'ALPA';
            }
        }
        $statusUpper = strtoupper(trim((string) $statusUtama));

        // Jika status nya ALPA atau jam kerja normal (HADIR/sudah absen), tampilkan 2 Card (Presensi Masuk dan Pulang).
        // Selain itu (LIBUR, DINAS, IZIN, SAKIT, CUTI, dll), cukup tampilkan 1 Card dengan judul sesuai status utama di tabel absensi.
        $isWorkingShift = ($statusUpper === 'ALPA' || $statusUpper === 'HADIR' || ($todayAbsensi && ($todayAbsensi->jam_masuk || $todayAbsensi->jam_pulang)));

        $recentActivities = [];

        if (!$isWorkingShift) {
            // Status utama non-kerja (LIBUR, DINAS, IZIN, SAKIT, CUTI, dll): Cukup 1 Card
            $statusName = $statusUpper;
            $statusType = strtolower($statusUpper);
            $subtitle = $todayAbsensi?->keterangan 
                ?: ($shift?->keterangan 
                    ?: ($todayAbsensi?->nomor_surat ? "No: {$todayAbsensi->nomor_surat}" : $statusName));

            $recentActivities[] = [
                'id' => ($todayAbsensi ? (string) $todayAbsensi->id : 'today') . '_' . $statusType,
                'type' => $statusType,
                'title' => $statusName,
                'subtitle' => $subtitle,
                'time' => '-',
                'date' => $tglStr,
                'full_date' => $tglFullStr,
                'status' => $statusName,
                'status_type' => $statusType,
                'foto_url' => null,
                'shift_name' => $shiftName,
                'shift_hours' => null,
                'jarak_meter' => null,
                'created_at' => $now->startOfDay()->toISOString(),
            ];
        } else {
            // Jadwal Kerja Normal / Status ALPA: TAMPILKAN 2 CARD (Presensi Masuk dan Presensi Pulang)
            $mulaiIn = (int) Setting::get('absensi_masuk_mulai', 30);
            $selesaiIn = (int) Setting::get('absensi_masuk_selesai', 120);
            $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 30);
            $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

            $windowInEnd = null;
            $windowOutEnd = null;

            if ($shift && $shift->start_time && $shift->end_time) {
                $startTime = Carbon::parse($todayStr)->setTimeFrom($shift->start_time);
                $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

                $isNightShift = Carbon::parse($shift->start_time)->format('H:i:s') >= Carbon::parse($shift->end_time)->format('H:i:s');
                $endDate = $isNightShift ? Carbon::parse($todayStr)->addDay()->format('Y-m-d') : $todayStr;
                $endTime = Carbon::parse($endDate)->setTimeFrom($shift->end_time);
                $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);
            }

            // --- 1. Card Presensi Masuk ---
            if ($todayAbsensi && $todayAbsensi->jam_masuk) {
                $jamMasukCarbon = $todayAbsensi->jam_masuk instanceof Carbon
                    ? $todayAbsensi->jam_masuk
                    : Carbon::parse($todayStr . ' ' . $todayAbsensi->jam_masuk);
                $isTelat = strtoupper((string) $todayAbsensi->status_masuk) === 'TELAT';
                $cardMasuk = [
                    'id' => (string) $todayAbsensi->id . '_masuk',
                    'type' => 'masuk',
                    'title' => 'Presensi Masuk',
                    'subtitle' => $kantorName,
                    'time' => $jamMasukCarbon->format('H:i') . ' WIB',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isTelat ? 'Terlambat' : 'Tepat Waktu',
                    'status_type' => $isTelat ? 'telat' : 'masuk',
                    'foto_url' => $todayAbsensi->foto_masuk ? asset('storage/' . $todayAbsensi->foto_masuk) : null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => $todayAbsensi->jarak_meter,
                    'created_at' => $jamMasukCarbon->toISOString(),
                ];
            } else {
                $isMasukAlpa = false;
                if ($windowInEnd && $now->greaterThan($windowInEnd)) {
                    $isMasukAlpa = true;
                } elseif ($todayAbsensi && (in_array(strtoupper((string) $todayAbsensi->status_masuk), ['ALPA', 'TIDAK MASUK']) || strtoupper((string) $todayAbsensi->status) === 'ALPA')) {
                    $isMasukAlpa = true;
                }

                $cardMasuk = [
                    'id' => ($todayAbsensi ? (string) $todayAbsensi->id : 'today') . '_masuk',
                    'type' => 'masuk',
                    'title' => 'Presensi Masuk',
                    'subtitle' => $kantorName,
                    'time' => '-',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isMasukAlpa ? 'Alpa' : '-',
                    'status_type' => $isMasukAlpa ? 'alpa' : 'pending',
                    'foto_url' => null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => null,
                    'created_at' => $now->startOfDay()->toISOString(),
                ];
            }

            // --- 2. Card Presensi Pulang ---
            if ($todayAbsensi && $todayAbsensi->jam_pulang) {
                $jamPulangCarbon = $todayAbsensi->jam_pulang instanceof Carbon
                    ? $todayAbsensi->jam_pulang
                    : Carbon::parse($todayStr . ' ' . $todayAbsensi->jam_pulang);
                $isPulangCepat = strtoupper((string) $todayAbsensi->status_pulang) === 'PULANG CEPAT';
                $cardPulang = [
                    'id' => (string) $todayAbsensi->id . '_pulang',
                    'type' => 'pulang',
                    'title' => 'Presensi Pulang',
                    'subtitle' => $kantorPulangName,
                    'time' => $jamPulangCarbon->format('H:i') . ' WIB',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isPulangCepat ? 'Pulang Cepat' : 'Selesai',
                    'status_type' => $isPulangCepat ? 'pulang_cepat' : 'pulang',
                    'foto_url' => $todayAbsensi->foto_pulang ? asset('storage/' . $todayAbsensi->foto_pulang) : null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => $todayAbsensi->jarak_meter_pulang,
                    'created_at' => $jamPulangCarbon->toISOString(),
                ];
            } else {
                $isPulangAlpa = false;
                if ($windowOutEnd && $now->greaterThan($windowOutEnd)) {
                    $isPulangAlpa = true;
                }

                $cardPulang = [
                    'id' => ($todayAbsensi ? (string) $todayAbsensi->id : 'today') . '_pulang',
                    'type' => 'pulang',
                    'title' => 'Presensi Pulang',
                    'subtitle' => $kantorPulangName,
                    'time' => '-',
                    'date' => $tglStr,
                    'full_date' => $tglFullStr,
                    'status' => $isPulangAlpa ? 'Alpa' : '-',
                    'status_type' => $isPulangAlpa ? 'alpa' : 'pending',
                    'foto_url' => null,
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jarak_meter' => null,
                    'created_at' => $now->startOfDay()->addMinute()->toISOString(),
                ];
            }

            $recentActivities = [$cardMasuk, $cardPulang];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'month_name' => $targetMonth->translatedFormat('F Y'),
                'month' => $month,
                'year' => $year,
                'hadir_count' => $hadirCount,
                'alpa_count' => $alpaCount,
                'izin_count' => $izinCount,
                'total_hari' => $totalHari,
                'hadir_percentage' => $hadirPercentage,
                'status_hari_ini' => $statusHariIni,
                'recent_activities' => $recentActivities,
            ],
        ]);
    }

    /**
     * Dapatkan daftar riwayat presensi bulanan lengkap untuk personel login.
     */
    public function riwayat(Request $request): JsonResponse
    {
        /** @var Personnel $personnel */
        $personnel = $request->attributes->get('personnel');

        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('m');
        }
        if ($year < 2020 || $year > 2050) {
            $year = (int) date('Y');
        }

        $targetDate = Carbon::createFromDate($year, $month, 1);
        $monthName = $targetDate->translatedFormat('F Y');
        $daysInMonth = $targetDate->daysInMonth;

        // Ambil seluruh data absensi pada bulan & tahun yang diminta
        $records = Absensi::where('personnel_id', $personnel->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with(['jadwal.shift', 'kantor', 'kantorPulang'])
            ->get()
            ->keyBy(function ($item) {
                $t = $item->tanggal instanceof Carbon ? $item->tanggal : Carbon::parse($item->tanggal);
                return $t->format('Y-m-d');
            });

        // Ambil jadwal jika ada
        $jadwals = Jadwal::where('personnel_id', $personnel->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with('shift')
            ->get()
            ->keyBy(function ($item) {
                $t = $item->tanggal instanceof Carbon ? $item->tanggal : Carbon::parse($item->tanggal);
                return $t->format('Y-m-d');
            });

        $items = [];
        $hadirCount = 0;
        $telatCount = 0;
        $alpaCount = 0;
        $izinCount = 0;
        $liburCount = 0;
        $today = Carbon::today();

        // Generate seluruh tanggal dari 1 sampai akhir bulan (1 kalender penuh)
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = Carbon::createFromDate($year, $month, $d)->startOfDay();
            $tglStr = $currentDate->format('Y-m-d');
            $isFuture = $currentDate->greaterThan($today);

            $rec = $records->get($tglStr);
            $jadwal = $jadwals->get($tglStr);

            if ($rec) {
                $shift = $rec->jadwal?->shift ?? $jadwal?->shift;
                $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : null);
                
                $shiftHours = null;
                if ($shift && $shift->start_time && $shift->end_time) {
                    $sStart = Carbon::parse($shift->start_time)->format('H:i');
                    $sEnd = Carbon::parse($shift->end_time)->format('H:i');
                    $shiftHours = "{$sStart} - {$sEnd}";
                }

                // Stat counters
                $statusUpper = strtoupper((string) $rec->status);
                $statusMasukUpper = strtoupper((string) $rec->status_masuk);
                $itemStatus = $rec->status;

                if ($statusUpper === 'HADIR') {
                    $hadirCount++;
                } elseif ($statusUpper === 'ALPA') {
                    if (!$isFuture) {
                        $alpaCount++;
                    } else {
                        // Tanggal di masa depan (> hari ini) tidak dihitung Alpa, tampilkan '-'
                        $itemStatus = '-';
                    }
                } elseif (in_array($statusUpper, ['IZIN', 'SAKIT', 'CUTI', 'DINAS'])) {
                    $izinCount++;
                } elseif ($statusUpper === 'LIBUR') {
                    $liburCount++;
                }

                if ($statusMasukUpper === 'TELAT') {
                    $telatCount++;
                }

                // Format Jam Masuk
                $jamMasukStr = null;
                if ($rec->jam_masuk && (!$isFuture || $statusUpper !== 'ALPA')) {
                    $jm = $rec->jam_masuk instanceof Carbon ? $rec->jam_masuk : Carbon::parse($tglStr . ' ' . $rec->jam_masuk);
                    $jamMasukStr = $jm->format('H:i') . ' WIB';
                }

                // Format Jam Pulang
                $jamPulangStr = null;
                if ($rec->jam_pulang && (!$isFuture || $statusUpper !== 'ALPA')) {
                    $jp = $rec->jam_pulang instanceof Carbon ? $rec->jam_pulang : Carbon::parse($tglStr . ' ' . $rec->jam_pulang);
                    $jamPulangStr = $jp->format('H:i') . ' WIB';
                }

                $kantorName = $rec->kantor?->name ?? $rec->kantor?->nama_kantor ?? $personnel->kantor?->name ?? 'Kantor Penugasan';

                $items[] = [
                    'id' => (string) $rec->id,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $itemStatus,
                    'keterangan' => $rec->keterangan,
                    'is_edited' => !empty($rec->edited_at),
                    'shift_name' => $shiftName,
                    'shift_hours' => $shiftHours,
                    'jam_masuk' => $jamMasukStr,
                    'status_masuk' => $isFuture && $statusUpper === 'ALPA' ? null : $rec->status_masuk,
                    'foto_masuk' => $rec->foto_masuk ? asset('storage/' . $rec->foto_masuk) : null,
                    'jarak_masuk' => $rec->jarak_meter,
                    'jam_pulang' => $jamPulangStr,
                    'status_pulang' => $isFuture && $statusUpper === 'ALPA' ? null : $rec->status_pulang,
                    'foto_pulang' => $rec->foto_pulang ? asset('storage/' . $rec->foto_pulang) : null,
                    'jarak_pulang' => $rec->jarak_meter_pulang,
                    'kantor_name' => $kantorName,
                ];
            } else {
                // Tidak ada data absensi di tanggal ini
                // Dapatkan status dari jadwal / shift off secara dinamis
                $shift = $jadwal?->shift;
                $isOff = false;
                if ($shift) {
                    $isOff = ($shift->type === 'off') || (stripos($shift->name ?? '', 'libur') !== false);
                }

                $status = '-';
                $keterangan = null;
                if ($isOff) {
                    $status = strtoupper(trim((string) ($shift->keterangan ?: ($jadwal?->status && $jadwal->status !== 'SHIFT' ? $jadwal->status : ($shift->name === 'L' ? 'LIBUR' : 'DINAS')))));
                    if (empty($status) || $status === 'OFF') {
                        $status = 'LIBUR';
                    }
                    $keterangan = $shift->keterangan ?: 'Hari Libur';
                    if ($status === 'LIBUR') {
                        $liburCount++;
                    } else {
                        $izinCount++;
                    }
                }

                $shiftName = $shift?->name ?? ($personnel->attendance_type === 'FLEXIBLE' ? 'Fleksibel' : null);

                $items[] = [
                    'id' => 'empty_' . $tglStr,
                    'tanggal' => $tglStr,
                    'tanggal_formatted' => $currentDate->translatedFormat('d M Y'),
                    'hari' => $currentDate->translatedFormat('l'),
                    'full_date' => $currentDate->translatedFormat('l, d F Y'),
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'is_edited' => false,
                    'shift_name' => $shiftName,
                    'shift_hours' => null,
                    'jam_masuk' => null,
                    'status_masuk' => null,
                    'foto_masuk' => null,
                    'jarak_masuk' => null,
                    'jam_pulang' => null,
                    'status_pulang' => null,
                    'foto_pulang' => null,
                    'jarak_pulang' => null,
                    'kantor_name' => $personnel->kantor?->name ?? null,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data riwayat presensi berhasil dimuat.',
            'data' => [
                'month' => $month,
                'year' => $year,
                'month_name' => $monthName,
                'summary' => [
                    'total_hari' => $daysInMonth,
                    'total_hadir' => $hadirCount,
                    'total_terlambat' => $telatCount,
                    'total_alpa' => $alpaCount,
                    'total_izin' => $izinCount,
                    'total_libur' => $liburCount,
                ],
                'riwayat' => $items,
            ],
        ]);
    }
}
