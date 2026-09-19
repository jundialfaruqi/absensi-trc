<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Device;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Services\AbsensiLokasiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Events\PersonnelVectorUpdated;
use App\Services\AdaptiveFaceLearningService;
use Illuminate\Support\Facades\Log;

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

        // Base query OPD (untuk kalkulasi stats global seluruh personil)
        $baseQuery = Personnel::query();
        if (!$isSuperAdmin) {
            $baseQuery->where('opd_id', $opdId);
        }

        // Global stats (tidak terpengaruh oleh search/filter chip)
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'with_photo' => (clone $baseQuery)->whereNotNull('foto')->where('foto', '!=', '')->count(),
            'ready_192' => (clone $baseQuery)->whereNotNull('face_descriptor_mobile')->where('face_descriptor_mobile', '!=', '')->count(),
            'missing_192' => (clone $baseQuery)->where(function ($q) {
                $q->whereNull('face_descriptor_mobile')->orWhere('face_descriptor_mobile', '');
            })->count(),
        ];

        $query = (clone $baseQuery)->with([
            'opd:id,name',
            'kantor:id,name,latitude,longitude,radius_meter',
            'faceEmbeddings:id,personnel_id,pose_type,face_descriptor_mobile,adaptive_descriptor_mobile,adaptation_count,last_adapted_at,foto',
        ])
        ->select([
            'id', 'name', 'nik', 'foto', 'face_descriptor_mobile',
            'face_recognition', 'opd_id', 'kantor_id',
            'wajib_absen_di_lokasi', 'attendance_type',
        ]);

        // Filter status biometrik jika diminta
        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            if ($filter === 'missing_192' || $filter === 'missing') {
                $query->where(function ($q) {
                    $q->whereNull('face_descriptor_mobile')->orWhere('face_descriptor_mobile', '');
                });
            } elseif ($filter === 'ready_192' || $filter === 'ready') {
                $query->whereNotNull('face_descriptor_mobile')->where('face_descriptor_mobile', '!=', '');
            }
        }

        // Pencarian nama atau NIK
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }
        }

        $query->orderBy('name');

        $formatPersonnel = function (Personnel $p) {
            $multiFaceDescriptors = $p->faceEmbeddings?->map(function ($fe) {
                return [
                    'pose' => $fe->pose_type,
                    'face_descriptor_mobile' => $fe->face_descriptor_mobile,
                    'adaptive_descriptor_mobile' => $fe->adaptive_descriptor_mobile,
                    'adaptation_count' => (int) ($fe->adaptation_count ?? 0),
                    'last_adapted_at' => $fe->last_adapted_at ? $fe->last_adapted_at->toIso8601String() : null,
                    'foto' => $fe->foto ? url('storage/' . $fe->foto) : null,
                ];
            })->values()->all() ?? [];

            return [
                'id' => (string) $p->id,
                'name' => $p->name,
                'nik' => $p->nik ?? '',
                'foto' => $p->foto ? url('storage/' . $p->foto) : null,
                'face_descriptor_mobile' => $p->face_descriptor_mobile,
                'multi_face_descriptors' => $multiFaceDescriptors,
                'has_adaptive' => $p->faceEmbeddings?->contains(fn($fe) => !empty($fe->adaptive_descriptor_mobile)) ?? false,
                'total_adaptations' => (int) ($p->faceEmbeddings?->sum('adaptation_count') ?? 0),
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
        };

        // Jika request meminta paginasi
        if ($request->boolean('paginate', false) || $request->filled('page') || $request->filled('per_page')) {
            $perPage = max(1, min((int) $request->input('per_page', 15), 100));
            $paginated = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Data personil dan biometrik berhasil diambil.',
                'data' => [
                    'total' => $paginated->total(),
                    'personnels' => collect($paginated->items())->map($formatPersonnel),
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'last_page' => $paginated->lastPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'has_more' => $paginated->hasMorePages(),
                    ],
                    'stats' => $stats,
                ],
            ]);
        }

        $personnels = $query->get()->map($formatPersonnel);

        return response()->json([
            'status' => 'success',
            'message' => 'Data personil dan biometrik berhasil diambil.',
            'data' => [
                'total' => $personnels->count(),
                'personnels' => $personnels,
                'stats' => $stats,
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
                if ($absensi && $absensi->jam_pulang) {
                    return response()->json([
                        'status' => 'info',
                        'can_attend' => false,
                        'action_type' => 'selesai',
                        'message' => 'Personel sudah menyelesaikan absen' . ($absensi->jam_masuk ? ' masuk dan pulang' : ' pulang') . ' hari ini.',
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
    public function store(Request $request, AbsensiLokasiService $lokasiService, AdaptiveFaceLearningService $adaptiveService): JsonResponse
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
            'face_descriptor_mobile' => 'nullable',
            'confidence_score' => 'nullable|numeric',
            'euler_angles' => 'nullable|array',
            'pose_type' => 'nullable|string',
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

        ob_start();
        imagejpeg($gdImg, null, 80);
        $cleanJpeg = ob_get_clean();
        imagedestroy($gdImg);

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
                'status' => 'error',
                'message' => 'Personel ini sudah menyelesaikan seluruh sesi absensi untuk jadwal ini.',
                'data' => $absensi,
            ], 422);
        }

        $registeredDevice = Device::where('personnel_id', $personnel->id)->where('status', 'active')->latest()->first()
            ?? Device::where('personnel_id', $personnel->id)->latest()->first();

        $platform = $request->platform ?: 'android';
        $deviceName = $request->device_name ?: "Admin Supervisor ({$user->name})";
        $uniqueId = $registeredDevice?->id ?? ($request->unique_device_id ?: 'admin-' . $user->id);

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

        // Pilar 4: Self-Learning Biometric Adaptation (EMA)
        $adaptationResult = null;
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
            } catch (\Throwable $e) {
                Log::error("Error executing biometric adaptation: " . $e->getMessage());
            }
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
                'adaptation' => $adaptationResult,
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

    /**
     * Memperbarui atau menyimpan multi face descriptor mobile (192-D) untuk berbagai sudut (pose_type) personil.
     */
    public function updateMultiFaceDescriptorMobile(Request $request, Personnel $personnel): JsonResponse
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
            'poses' => 'required|array|min:1',
            'poses.*.pose_type' => 'required|string|in:FRONT,RIGHT,LEFT,UP',
            'poses.*.face_descriptor_mobile' => [
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

        $updatedPoses = [];
        foreach ($request->poses as $poseItem) {
            $poseType = $poseItem['pose_type'];
            $descriptor = $poseItem['face_descriptor_mobile'];

            $embedding = $personnel->faceEmbeddings()->firstOrNew(['pose_type' => $poseType]);
            $embedding->face_descriptor_mobile = $descriptor;
            $embedding->save();

            // Jika pose FRONT, sinkronkan ke tabel personnel utama demi kompatibilitas penuh
            if ($poseType === 'FRONT') {
                $personnel->update([
                    'face_descriptor_mobile' => $descriptor,
                ]);
            }

            $updatedPoses[] = $poseType;
        }

        PersonnelVectorUpdated::dispatch(
            $personnel->id,
            $personnel->opd_id,
            'ready'
        );

        return response()->json([
            'status' => 'success',
            'message' => "Multi-angle biometrik MobileFaceNet 192-D berhasil disimpan untuk personil {$personnel->name}.",
            'data' => [
                'personnel_id' => (string) $personnel->id,
                'name' => $personnel->name,
                'updated_poses' => $updatedPoses,
            ],
        ]);
    }

    /**
     * Mengembalikan template adaptif personil ke Master Anchor asli (Reset to Master).
     */
    public function resetFaceLearning(Request $request, Personnel $personnel, AdaptiveFaceLearningService $adaptiveService): JsonResponse
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
                'message' => 'Anda tidak berwenang mereset data biometrik personil di luar OPD Anda.',
            ], 403);
        }

        $poseType = $request->input('pose_type'); // null = reset semua pose
        $result = $adaptiveService->resetToMaster($personnel, $poseType);

        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
            'data' => $result,
        ]);
    }

    /**
     * Helper: Memeriksa apakah admin memiliki izin untuk mengedit absensi personel.
     */
    public function canEditPersonnel(?Personnel $personnel, ?User $user): bool
    {
        if (!$personnel || !$user) {
            return false;
        }

        // 1. Permission edit-absensi-all-opd atau role super-admin: bisa edit semua OPD
        if ($user->can('edit-absensi-all-opd') || $user->hasRole('super-admin')) {
            return true;
        }

        // 2. Permission edit-absensi-opd atau role admin-opd: hanya bisa edit OPD-nya sendiri
        if ($user->can('edit-absensi-opd') || $user->hasRole('admin-opd')) {
            $userOpd = $user->opds()->first();
            $userOpdId = $userOpd?->id;

            return !empty($userOpdId) && !empty($personnel->opd_id) && (int) $personnel->opd_id === (int) $userOpdId;
        }

        return false;
    }

    /**
     * Mengambil detail absensi & jadwal untuk form Quick Edit di Admin Mobile.
     */
    public function getEditData(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels,id',
            'tanggal' => 'required|date_format:Y-m-d',
        ], [
            'personnel_id.required' => 'ID personel wajib disertakan.',
            'personnel_id.exists' => 'Data personel tidak ditemukan.',
            'tanggal.required' => 'Tanggal absensi wajib disertakan.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $personnelId = (int) $request->input('personnel_id');
        $tanggal = $request->input('tanggal');

        $personnel = Personnel::with(['opd', 'kantor'])->find($personnelId);
        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan.',
            ], 404);
        }

        if (!$this->canEditPersonnel($personnel, $user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit absensi ini.',
            ], 403);
        }

        $absensi = Absensi::where('personnel_id', $personnelId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Cari jadwal untuk tanggal yang diedit
        $jadwal = Jadwal::where('personnel_id', $personnelId)
            ->whereDate('tanggal', $tanggal)
            ->with('shift')
            ->first();

        if (!$jadwal && $absensi && $absensi->jadwal_id) {
            $jadwal = Jadwal::with('shift')->find($absensi->jadwal_id);
        }

        $shift = $jadwal?->shift;
        $jadwalShiftName = null;
        $jadwalJamMasuk = null;
        $jadwalJamPulang = null;

        if ($shift && $shift->type !== 'off' && $shift->start_time && $shift->end_time) {
            $jadwalShiftName = $shift->name . ($shift->keterangan ? ' (' . $shift->keterangan . ')' : '');
            $jadwalJamMasuk = Carbon::parse($shift->start_time)->format('H:i');
            $jadwalJamPulang = Carbon::parse($shift->end_time)->format('H:i');
        } else {
            // Fallback jadwal shift berjam terbaru
            $recentJadwal = Jadwal::where('personnel_id', $personnelId)
                ->whereHas('shift', function ($q) {
                    $q->where('type', '!=', 'off')
                        ->whereNotNull('start_time')
                        ->whereNotNull('end_time');
                })
                ->with('shift')
                ->orderByDesc('tanggal')
                ->first();

            if ($recentJadwal && $recentJadwal->shift) {
                $jadwalShiftName = $recentJadwal->shift->name . ($recentJadwal->shift->keterangan ? ' (' . $recentJadwal->shift->keterangan . ')' : '');
                $jadwalJamMasuk = Carbon::parse($recentJadwal->shift->start_time)->format('H:i');
                $jadwalJamPulang = Carbon::parse($recentJadwal->shift->end_time)->format('H:i');
            } else {
                $defaultShift = Shift::where('type', '!=', 'off')
                    ->whereNotNull('start_time')
                    ->whereNotNull('end_time')
                    ->first();

                if ($defaultShift) {
                    $jadwalShiftName = $defaultShift->name . ($defaultShift->keterangan ? ' (' . $defaultShift->keterangan . ')' : '');
                    $jadwalJamMasuk = Carbon::parse($defaultShift->start_time)->format('H:i');
                    $jadwalJamPulang = Carbon::parse($defaultShift->end_time)->format('H:i');
                } else {
                    $jadwalShiftName = null;
                    $jadwalJamMasuk = '08:00';
                    $jadwalJamPulang = '16:00';
                }
            }
        }

        // Resolusi Device
        $resolveDevice = function ($uniqueDeviceId, $pId) {
            if (!empty($uniqueDeviceId)) {
                $device = is_numeric($uniqueDeviceId)
                    ? Device::find($uniqueDeviceId)
                    : Device::where('unique_device_id', $uniqueDeviceId)->first();

                if ($device) {
                    return $device;
                }
            }

            if ($pId) {
                return Device::where('personnel_id', $pId)->where('status', 'active')->latest()->first()
                    ?? Device::where('personnel_id', $pId)->latest()->first();
            }

            return null;
        };

        $deviceMasuk = $absensi ? $resolveDevice($absensi->unique_device_id_masuk, $personnelId) : null;
        $isOfficialDeviceMasuk = !is_null($deviceMasuk);
        $officialDeviceNameMasuk = $deviceMasuk?->name;

        $devicePulang = $absensi ? $resolveDevice($absensi->unique_device_id_pulang, $personnelId) : null;
        $isOfficialDevicePulang = !is_null($devicePulang);
        $officialDeviceNamePulang = $devicePulang?->name;

        $isEdited = $absensi ? !is_null($absensi->original_status_masuk) : false;
        $canResetAbsen = $user->can('reset-absen') || $user->hasRole('super-admin');

        $cutis = Cuti::orderBy('name')->get(['id', 'name'])->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
        ]);

        $jamMasukFormatted = $absensi?->jam_masuk ? Carbon::parse($absensi->jam_masuk)->format('H:i') : null;
        $jamPulangFormatted = $absensi?->jam_pulang ? Carbon::parse($absensi->jam_pulang)->format('H:i') : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Data edit absensi berhasil diambil.',
            'data' => [
                'personnel' => [
                    'id' => (string) $personnel->id,
                    'name' => $personnel->name,
                    'nik' => $personnel->nik ?? '',
                    'foto' => $personnel->foto ? url('storage/' . $personnel->foto) : null,
                    'opd_id' => (string) $personnel->opd_id,
                    'opd_name' => $personnel->opd?->name ?? '-',
                    'attendance_type' => $personnel->attendance_type ?? 'SHIFT',
                ],
                'tanggal' => $tanggal,
                'absensi_id' => $absensi?->id,
                'jadwal' => [
                    'shift_name' => $jadwalShiftName,
                    'jam_masuk' => $jadwalJamMasuk,
                    'jam_pulang' => $jadwalJamPulang,
                ],
                'current_values' => [
                    'status_masuk' => $absensi?->status_masuk ?? '',
                    'status_pulang' => $absensi?->status_pulang ?? '',
                    'jam_masuk' => $jamMasukFormatted,
                    'jam_pulang' => $jamPulangFormatted,
                    'nomor_surat' => $absensi?->nomor_surat ?? '',
                    'cuti_id' => $absensi?->cuti_id,
                    'keterangan' => $absensi?->keterangan ?? '',
                    'alasan_edit' => $absensi?->alasan_edit ?? '',
                ],
                'proof' => [
                    'foto_masuk' => $absensi?->foto_masuk ? url('storage/' . $absensi->foto_masuk) : null,
                    'foto_pulang' => $absensi?->foto_pulang ? url('storage/' . $absensi->foto_pulang) : null,
                    'platform_masuk' => $absensi?->platform_masuk,
                    'platform_pulang' => $absensi?->platform_pulang,
                    'device_name_masuk' => $absensi?->device_name_masuk,
                    'device_name_pulang' => $absensi?->device_name_pulang,
                    'is_official_device_masuk' => $isOfficialDeviceMasuk,
                    'is_official_device_pulang' => $isOfficialDevicePulang,
                    'official_device_name_masuk' => $officialDeviceNameMasuk,
                    'official_device_name_pulang' => $officialDeviceNamePulang,
                ],
                'is_edited' => $isEdited,
                'can_reset_absen' => $canResetAbsen,
                'cuti_options' => $cutis,
            ],
        ]);
    }

    /**
     * Menyimpan perubahan data absensi (Quick Edit) dari Admin Mobile.
     */
    public function saveEditData(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels,id',
            'tanggal' => 'required|date_format:Y-m-d',
            'status_masuk' => 'required|string',
            'status_pulang' => 'nullable|string',
            'jam_masuk' => 'nullable|string',
            'jam_pulang' => 'nullable|string',
            'nomor_surat' => 'nullable|string',
            'cuti_id' => 'nullable|exists:cutis,id',
            'keterangan' => 'nullable|string',
            'alasan_edit' => 'required|string|min:5',
        ], [
            'personnel_id.required' => 'ID personel wajib disertakan.',
            'status_masuk.required' => 'Status masuk wajib dipilih.',
            'alasan_edit.required' => 'Alasan perubahan data wajib diisi.',
            'alasan_edit.min' => 'Alasan perubahan data minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $personnelId = (int) $request->input('personnel_id');
        $tanggal = $request->input('tanggal');

        $personnel = Personnel::findOrFail($personnelId);

        if (!$this->canEditPersonnel($personnel, $user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit absensi ini.',
            ], 403);
        }

        $statusMasuk = $request->input('status_masuk');
        $statusPulang = $request->input('status_pulang');
        $jamMasuk = $request->input('jam_masuk');
        $jamPulang = $request->input('jam_pulang');
        $alasanEdit = $request->input('alasan_edit');
        $nomorSurat = $request->input('nomor_surat');
        $cutiId = ($statusMasuk === 'CUTI' || $statusPulang === 'CUTI') ? $request->input('cuti_id') : null;
        $keterangan = $request->input('keterangan');

        $existing = Absensi::where('personnel_id', $personnelId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // Capture original status ONLY if it's the first edit
        $originalStatusMasuk = $existing ? ($existing->original_status_masuk ?? $existing->status_masuk) : 'ALPA';
        $originalStatusPulang = $existing ? ($existing->original_status_pulang ?? $existing->status_pulang) : 'ALPA';

        // Tentukan status kehadiran utama (status):
        $overallStatus = $statusMasuk;
        if (in_array($statusMasuk, ['HADIR', 'TELAT']) || in_array($statusPulang, ['HADIR', 'PC'])) {
            $overallStatus = 'HADIR';
        } elseif (!empty($statusMasuk)) {
            $overallStatus = $statusMasuk;
        } elseif (!empty($statusPulang)) {
            $overallStatus = $statusPulang;
        } else {
            $overallStatus = 'ALPA';
        }

        $attributes = [
            'status' => $overallStatus,
            'status_masuk' => $statusMasuk,
            'status_pulang' => $statusPulang,
            'jam_masuk' => !empty($jamMasuk) ? $jamMasuk : null,
            'jam_pulang' => !empty($jamPulang) ? $jamPulang : null,
            'alasan_edit' => $alasanEdit,
            'nomor_surat' => $nomorSurat,
            'cuti_id' => $cutiId,
            'keterangan' => $keterangan,
            'edited_by_user_id' => $user->id,
            'edited_at' => now(),
            'original_status_masuk' => $originalStatusMasuk,
            'original_status_pulang' => $originalStatusPulang,
        ];

        if ($existing) {
            $existing->update($attributes);
            $saved = $existing;
        } else {
            $saved = Absensi::create(array_merge([
                'personnel_id' => $personnelId,
                'tanggal' => $tanggal,
            ], $attributes));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil diperbarui.',
            'data' => $saved,
        ]);
    }

    /**
     * Mengembalikan data absensi ke kondisi awal sebelum diedit admin (Reset to Original).
     */
    public function resetToOriginal(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'absensi_id' => 'required|exists:absensis,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $absensi = Absensi::with('personnel')->findOrFail($request->input('absensi_id'));

        if (!$this->canEditPersonnel($absensi->personnel, $user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit absensi ini.',
            ], 403);
        }

        if (is_null($absensi->original_status_masuk)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi ini belum pernah diedit atau sudah berada dalam status asli.',
            ], 422);
        }

        if ($absensi->original_status_masuk === 'ALPA' && $absensi->original_status_pulang === 'ALPA') {
            $jadwal = $absensi->jadwal;
            $placeholderStatus = ($jadwal && $jadwal->status === 'LIBUR') ? 'LIBUR' : 'ALPA';

            $absensi->update([
                'status' => $placeholderStatus,
                'status_masuk' => null,
                'status_pulang' => null,
                'jam_masuk' => null,
                'jam_pulang' => null,
                'edited_by_user_id' => null,
                'edited_at' => null,
                'alasan_edit' => null,
                'nomor_surat' => null,
                'cuti_id' => null,
                'keterangan' => null,
                'original_status_masuk' => null,
                'original_status_pulang' => null,
            ]);
        } else {
            $origStatus = $absensi->original_status_masuk;
            if (in_array($absensi->original_status_masuk, ['HADIR', 'TELAT']) || in_array($absensi->original_status_pulang, ['HADIR', 'PC'])) {
                $origStatus = 'HADIR';
            } elseif (!empty($absensi->original_status_masuk)) {
                $origStatus = $absensi->original_status_masuk;
            } elseif (!empty($absensi->original_status_pulang)) {
                $origStatus = $absensi->original_status_pulang;
            }

            $absensi->update([
                'status' => $origStatus,
                'status_masuk' => $absensi->original_status_masuk,
                'status_pulang' => $absensi->original_status_pulang,
                'edited_by_user_id' => null,
                'edited_at' => null,
                'alasan_edit' => null,
                'nomor_surat' => null,
                'cuti_id' => null,
                'keterangan' => null,
                'original_status_masuk' => null,
                'original_status_pulang' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi telah dikembalikan ke kondisi awal.',
            'data' => $absensi,
        ]);
    }

    /**
     * Memindahkan data absensi ke kotak sampah (soft delete) & membuat ulang placeholder default.
     */
    public function resetAbsensi(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeAdmin($request)) {
            return $authError;
        }

        /** @var User $user */
        $user = $request->user();

        if (!$user->can('reset-absen') && !$user->hasRole('super-admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mereset absensi.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'absensi_id' => 'required|exists:absensis,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $absensi = Absensi::with('personnel')->findOrFail($request->input('absensi_id'));

        if (!$this->canEditPersonnel($absensi->personnel, $user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengedit absensi ini.',
            ], 403);
        }

        $personnelId = $absensi->personnel_id;
        $tanggal = $absensi->tanggal;

        $absensi->update(['deleted_by_user_id' => $user->id]);
        $absensi->delete();

        // Buat ulang record absensi default berdasarkan jadwal jika ada
        $jadwal = Jadwal::where('personnel_id', $personnelId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($jadwal) {
            $shift = $jadwal->shift;
            $isOff = $shift && $shift->type === 'off';
            $defaultStatus = $isOff ? ($shift->keterangan ?? 'OFF') : 'ALPA';

            Absensi::create([
                'personnel_id' => $personnelId,
                'tanggal' => $tanggal,
                'jadwal_id' => $jadwal->id,
                'status' => $defaultStatus,
                'status_masuk' => $defaultStatus,
                'status_pulang' => $defaultStatus,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi dipindahkan ke kotak sampah.',
        ]);
    }
}


