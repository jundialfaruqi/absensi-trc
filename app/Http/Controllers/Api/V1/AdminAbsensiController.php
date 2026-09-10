<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Setting;
use App\Models\User;
use App\Services\AbsensiLokasiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Events\PersonnelVectorUpdated;

class AdminAbsensiController extends Controller
{
    /**
     * Helper: Validasi bahwa user yang login memiliki role admin-opd atau super-admin.
     */
    protected function authorizeAdmin(Request $request): ?JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin-opd', 'super-admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus untuk Admin OPD dan Super Admin.',
            ], 403);
        }
        return null;
    }

    /**
     * Mengambil daftar personil di bawah OPD Admin yang login (atau seluruhnya untuk Super Admin).
     * Disertai data biometrik (face_descriptor_mobile) dan koordinat/radius kantor geofence.
     */
    public function personnels(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $opd = $user->opds()->first();
        $opdId = $opd?->id;

        $query = Personnel::with([
            'opd:id,name',
            'kantor:id,name,latitude,longitude,radius_meter',
        ])
        ->select([
            'id', 'name', 'nik', 'foto', 'face_descriptor_mobile',
            'face_recognition', 'opd_id', 'kantor_id',
            'wajib_absen_di_lokasi', 'attendance_type',
        ]);

        if (!$isSuperAdmin) {
            $query->where('opd_id', $opdId);
        }

        $personnels = $query->orderBy('name')
            ->get()
            ->map(function (Personnel $p) {
                return [
                    'id' => (string) $p->id,
                    'name' => $p->name,
                    'nik' => $p->nik ?? '',
                    'foto' => $p->foto ? url('storage/' . $p->foto) : null,
                    'face_descriptor_mobile' => $p->face_descriptor_mobile,
                    'face_recognition' => (bool) $p->face_recognition,
                    'wajib_absen_di_lokasi' => (bool) $p->wajib_absen_di_lokasi,
                    'attendance_type' => $p->attendance_type ?? 'SHIFT',
                    'opd_id' => (string) $p->opd_id,
                    'opd_name' => $p->opd?->name ?? '-',
                    'kantor' => $p->kantor ? [
                        'id' => (string) $p->kantor->id,
                        'name' => $p->kantor->name,
                        'latitude' => (float) $p->kantor->latitude,
                        'longitude' => (float) $p->kantor->longitude,
                        'radius_meter' => (int) $p->kantor->radius_meter,
                    ] : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Data personil dan biometrik berhasil diambil.',
            'data' => [
                'total' => $personnels->count(),
                'personnels' => $personnels,
            ],
        ]);
    }

    /**
     * Memeriksa status presensi, jadwal, dan jendela toleransi personil hari ini.
     */
    public function checkStatus(Request $request, $id): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $opd = $user->opds()->first();
        $opdId = $opd?->id;

        $personnel = Personnel::with(['opd', 'kantor'])->find($id);
        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personil tidak ditemukan.',
            ], 404);
        }

        if (!$isSuperAdmin && $personnel->opd_id != $opdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Personel ini tidak berada di bawah wewenang OPD Anda.',
            ], 403);
        }

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
                    // DIRECT CHECK-OUT: Selama belum absen pulang kemarin, gunakan jadwal shift kemarin
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
            $activeDate = $today;
        }

        // Cek absensi pada tanggal aktif
        $absensi = Absensi::where('personnel_id', $id)
            ->whereDate('tanggal', $activeDate)
            ->first();

        // 1. Jika mode flexible atau tidak ada jadwal
        if (!$jadwal) {
            if ($personnel->attendance_type === 'FLEXIBLE') {
                if ($absensi && $absensi->jam_masuk && $absensi->jam_pulang) {
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'selesai',
                        'message' => 'Personel sudah menyelesaikan absen masuk dan pulang hari ini.',
                        'data' => [
                            'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
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
                        'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                        'action_type' => $nextAction,
                        'absensi' => $absensi,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'error',
                'can_attend' => false,
                'action_type' => 'tidak_ada_jadwal',
                'message' => 'Personel tidak memiliki jadwal kerja aktif untuk hari ini.',
                'data' => [
                    'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                ],
            ], 404);
        }

        // 2. Jika jadwal adalah LIBUR / OFF
        if ($jadwal->shift && $jadwal->shift->type === 'off') {
            return response()->json([
                'status' => 'info',
                'can_attend' => false,
                'action_type' => 'libur',
                'message' => 'Hari ini adalah hari libur (OFF) untuk personel ini.',
                'data' => [
                    'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                    'shift' => ['name' => $jadwal->shift->name],
                ],
            ]);
        }

        // 3. Jika sudah absen pulang untuk jadwal ini (baik normal maupun direct check-out)
        if ($absensi && $absensi->jam_pulang) {
            return response()->json([
                'status' => 'info',
                'can_attend' => false,
                'action_type' => 'selesai',
                'message' => 'Personel sudah menyelesaikan absen' . ($absensi->jam_masuk ? ' masuk dan pulang' : ' pulang') . ' untuk jadwal ini.',
                'data' => [
                    'personnel' => ['id' => (string) $personnel->id, 'name' => $personnel->name],
                    'absensi' => $absensi,
                ],
            ]);
        }

        // 4. Validasi Jendela Waktu Jadwal Shift (Time Window Validation)
        $shift = $jadwal->shift;
        $isDirectCheckOut = false;

        $shiftStart = ($shift && $shift->start_time) ? Carbon::parse($shift->start_time)->format('H:i') : '';
        $shiftEnd = ($shift && $shift->end_time) ? Carbon::parse($shift->end_time)->format('H:i') : '';
        $shiftJam = ($shiftStart && $shiftEnd) ? "$shiftStart - $shiftEnd WIB" : '';

        $shiftData = $shift ? [
            'id' => (string) $shift->id,
            'name' => $shift->name,
            'start_time' => $shiftStart,
            'end_time' => $shiftEnd,
            'jam' => $shiftJam,
        ] : null;

        if ($shift && $shift->start_time && $shift->end_time) {
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

            if (!$absensi || !$absensi->jam_masuk) {
                // Skenario: Belum Absen Masuk
                if ($now->between($windowOutStart, $windowOutEnd)) {
                    // DIRECT CHECK-OUT: Berada di rentang waktu pulang, langsung diizinkan absen pulang
                    $isDirectCheckOut = true;
                } elseif ($now->lessThan($windowInStart)) {
                    $diff = $windowInStart->diffForHumans($now, syntax: true, parts: 2);
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'belum_mulai',
                        'message' => "Belum waktunya Absen Masuk. Jadwal shift {$shift->name} masuk pukul {$startTime->format('H:i')} WIB (dibuka mulai {$windowInStart->format('H:i')} WIB). Silakan kembali $diff lagi.",
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
     * Menyimpan transaksi absensi (Masuk atau Pulang) oleh Admin OPD supervisor.
     */
    public function store(Request $request, AbsensiLokasiService $lokasiService): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $opd = $user->opds()->first();
        $opdId = $opd?->id;

        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels,id',
            'foto' => 'required|string', // Base64 JPEG
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'platform' => 'nullable|string',
            'device_name' => 'nullable|string',
            'unique_device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $personnel = Personnel::with(['kantor', 'opd'])->find($request->personnel_id);
        if (!$isSuperAdmin && $personnel->opd_id != $opdId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Personel ini tidak berada di bawah wewenang OPD Anda.',
            ], 403);
        }

        // 1. Validasi Geofencing
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

        // 2. Pemrosesan & Sanitasi Gambar Base64 via GD Library (Anti-Polyglot / Anti-XSS)
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

        // 3. Tentukan Jadwal Aktif & Action (Masuk / Pulang)
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
                    // DIRECT CHECK-OUT: Selama belum absen pulang kemarin, gunakan jadwal shift kemarin
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
            $activeDate = $today;
        }

        // Cari atau inisialisasi record Absensi
        $absensi = Absensi::where('personnel_id', $personnel->id)
            ->whereDate('tanggal', $activeDate)
            ->first();

        if (!$absensi) {
            $absensi = new Absensi([
                'personnel_id' => $personnel->id,
                'tanggal' => $activeDate,
            ]);
        }

        if ($absensi->exists && $absensi->jam_pulang) {
            return response()->json([
                'status' => 'info',
                'message' => 'Personel ini sudah menyelesaikan seluruh sesi absensi untuk jadwal ini.',
                'data' => $absensi,
            ]);
        }

        $platform = $request->platform ?: 'android';
        $deviceName = $request->device_name ?: 'Admin Mobile';
        $uniqueId = $request->unique_device_id ?: 'admin-' . $user->id;

        // 3b. Validasi Jendela Waktu Jadwal Shift (Time Window Validation)
        $isDirectCheckOut = false;
        if ($jadwal && $jadwal->shift && $jadwal->shift->start_time && $jadwal->shift->end_time) {
            $shift = $jadwal->shift;
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

        // 4. LOGIKA ABSEN MASUK VS PULANG
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

            $absensi->jadwal_id = $jadwal?->id;
            $absensi->status = 'HADIR'; // Status keseluruhan tetap HADIR

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
            $absensi->edited_by_user_id = $user->id; // Tercatat diawasi oleh admin
            $absensi->save();

            $pesan = "Absen PULANG ($statusPulang) berhasil dicatat untuk {$personnel->name}." . ($isDirectCheckOut ? " (Masuk: ALPA)" : "");
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
            $absensi->edited_by_user_id = $user->id; // Tercatat diawasi oleh admin
            $absensi->save();

            $pesan = "Absen MASUK ($statusMasuk) berhasil dicatat untuk {$personnel->name}.";
            $actionType = 'masuk';
        } else {
            return response()->json([
                'status' => 'info',
                'message' => 'Personel ini sudah menyelesaikan seluruh sesi absen masuk dan pulang.',
                'data' => $absensi,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $pesan,
            'data' => [
                'id' => (string) $absensi->id,
                'personnel_id' => (string) $personnel->id,
                'personnel_name' => $personnel->name,
                'action_type' => $actionType,
                'tanggal' => $absensi->tanggal->format('Y-m-d'),
                'jam' => $now->format('H:i:s'),
                'status' => $absensi->status,
                'status_masuk' => $absensi->status_masuk,
                'status_pulang' => $absensi->status_pulang,
                'jarak_meter' => $hasilLokasi['jarak_meter'],
                'kantor_name' => $hasilLokasi['kantor_name'],
                'foto_url' => url('storage/' . $fileName),
            ],
        ]);
    }


    /**
     * Memperbarui atau menyimpan face_descriptor_mobile (192-D) untuk personil tertentu.
     */
    public function updateFaceDescriptorMobile(Request $request, Personnel $personnel): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        if (!$isSuperAdmin && $user->opds()->where('opds.id', $personnel->opd_id)->doesntExist()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak berwenang memperbarui data biometrik personil di luar OPD Anda.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'face_descriptor_mobile' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);
                    if (!is_array($decoded) || count($decoded) !== 192) {
                        return $fail('face_descriptor_mobile harus berupa string JSON array berisi tepat 192 elemen numerik.');
                    }
                    foreach ($decoded as $val) {
                        if (!is_numeric($val) || is_nan((float)$val) || is_infinite((float)$val)) {
                            return $fail('Semua elemen dalam face_descriptor_mobile harus berupa angka float yang valid.');
                        }
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $personnel->update([
            'face_descriptor_mobile' => $request->face_descriptor_mobile,
        ]);

        PersonnelVectorUpdated::dispatch(
            $personnel->id,
            $personnel->opd_id,
            'ready'
        );

        return response()->json([
            'status' => 'success',
            'message' => "Biometrik MobileFaceNet 192-D berhasil disimpan untuk personil {$personnel->name}.",
            'data' => [
                'personnel_id' => (string) $personnel->id,
                'name' => $personnel->name,
                'face_descriptor_mobile_count' => 192,
            ],
        ]);
    }
}


