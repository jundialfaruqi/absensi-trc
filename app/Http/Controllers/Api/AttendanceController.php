<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function personnels()
    {
        $personnels = Personnel::select('id', 'name', 'foto')->orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $personnels
        ]);
    }

    public function checkStatus($id)
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        $jadwal = null;
        
        // Night Shift Buffer
        if ($nowTime < '09:00:00') {
            $yesterdayJadwal = Jadwal::where('personnel_id', $id)
                ->whereDate('tanggal', $yesterday)
                ->with('shift')
                ->first();

            if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->start_time > $yesterdayJadwal->shift->end_time) {
                $endTimePlusBuffer = Carbon::parse($yesterdayJadwal->shift->end_time)->addHours(2)->format('H:i:s');
                if ($nowTime < $endTimePlusBuffer) {
                    $jadwal = $yesterdayJadwal;
                }
            }
        }

        if (!$jadwal) {
            $jadwal = Jadwal::where('personnel_id', $id)
                ->whereDate('tanggal', $today)
                ->with('shift')
                ->first();
        }

        if (!$jadwal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, Anda tidak memiliki jadwal shift hari ini.'
            ], 404);
        }

        if ($jadwal->status === 'LIBUR') {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, Anda sedang LIBUR hari ini.'
            ], 403);
        }

        $shift = $jadwal->shift;
        $activeDate = ($jadwal->tanggal instanceof \DateTime) ? $jadwal->tanggal->format('Y-m-d') : $jadwal->tanggal;

        $existing = Absensi::where('personnel_id', $id)
            ->where('tanggal', $activeDate)
            ->first();

        if ($existing) {
            // Rule: If status is not ALFA and not HADIR (e.g. CUTI, IZIN, SAKIT), REJECT
            if ($existing->status !== 'ALFA' && $existing->status !== 'HADIR') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Maaf, status absensi Anda hari ini adalah {$existing->status}. Anda tidak dapat melakukan absensi."
                ], 403);
            }

            // Rule: If status is HADIR and already has jam_pulang, REJECT
            if ($existing->status === 'HADIR' && $existing->jam_pulang) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Anda sudah melakukan absen masuk dan pulang hari ini."
                ], 403);
            }
        }

        // ─── TIME WINDOW VALIDATION (BEFORE PIN) ───
        $mulaiIn = (int) \App\Models\Setting::get('absensi_masuk_mulai', 30);
        $selesaiIn = (int) \App\Models\Setting::get('absensi_masuk_selesai', 120);
        $mulaiOut = (int) \App\Models\Setting::get('absensi_pulang_mulai', 30);
        $selesaiOut = (int) \App\Models\Setting::get('absensi_pulang_selesai', 120);

        $startTime = Carbon::parse($activeDate)->setTimeFrom($shift->start_time);
        $windowInStart = $startTime->copy()->subMinutes($mulaiIn);
        $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

        $pulangDate = $activeDate;
        if ($shift->start_time > $shift->end_time) {
            $pulangDate = Carbon::parse($activeDate)->addDay()->format('Y-m-d');
        }
        $endTime = Carbon::parse($pulangDate)->setTimeFrom($shift->end_time);
        $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);
        $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);

        // Logic check: Are we in ANY valid window?
        $isInWindow = $now->between($windowInStart, $windowInEnd);
        $isOutWindow = $now->between($windowOutStart, $windowOutEnd);

        if (!$isInWindow && !$isOutWindow) {
            // Check if too early for everything
            if ($now->lessThan($windowInStart)) {
                $diff = $windowInStart->diffForHumans($now, true);
                return response()->json([
                    'status' => 'error',
                    'message' => "Belum waktunya Absen Masuk. Silakan kembali $diff lagi."
                ], 403);
            }
            
            // Check if in gap between IN and OUT
            if ($now->greaterThan($windowInEnd) && $now->lessThan($windowOutStart)) {
                $diff = $windowOutStart->diffForHumans($now, true);
                return response()->json([
                    'status' => 'error',
                    'message' => "Batas waktu Absen Masuk sudah berakhir. Silakan kembali $diff lagi untuk Absen Pulang."
                ], 403);
            }

            // Default: past everything
            return response()->json([
                'status' => 'error',
                'message' => "Batas waktu Absen hari ini sudah berakhir."
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal ditemukan',
            'data' => $jadwal
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'personnel_id' => 'required|exists:personnels,id',
            'pin' => 'required|string',
        ]);

        $personnel = Personnel::findOrFail($request->personnel_id);

        // In this app, PIN is stored as a hash in the 'pin' column.
        if (!Hash::check($request->pin, $personnel->pin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN yang Anda masukkan salah.'
            ], 401);
        }

        $token = $personnel->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'personnel' => $personnel,
                'token' => $token
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required|string', // Base64 image
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $personnel = $request->user();
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // SMART LOOKUP: Check if we should use yesterday's night shift
        $jadwal = null;
        $activeDate = $today;

        // Buffer: 00:00 to 09:00 AM
        if ($nowTime < '09:00:00') {
            $yesterdayJadwal = Jadwal::where('personnel_id', $personnel->id)
                ->whereDate('tanggal', $yesterday)
                ->with('shift')
                ->first();

            if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->start_time > $yesterdayJadwal->shift->end_time) {
                // If now is before EndTime + 2 hours buffer, use yesterday
                $endTimePlusBuffer = Carbon::parse($yesterdayJadwal->shift->end_time)->addHours(2)->format('H:i:s');
                if ($nowTime < $endTimePlusBuffer) {
                    $jadwal = $yesterdayJadwal;
                    $activeDate = $yesterday;
                }
            }
        }

        if (!$jadwal) {
            $jadwal = Jadwal::where('personnel_id', $personnel->id)
                ->whereDate('tanggal', $today)
                ->with('shift')
                ->first();
        }

        if (!$jadwal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki jadwal shift hari ini.'
            ], 404);
        }

        if ($jadwal->status === 'LIBUR') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sedang LIBUR hari ini.'
            ], 403);
        }

        // ─── TIME WINDOW VALIDATION (SYCHRONIZED WITH WEB) ───
        $shift = $jadwal->shift;
        $mulaiIn = (int) \App\Models\Setting::get('absensi_masuk_mulai', 30);
        $selesaiIn = (int) \App\Models\Setting::get('absensi_masuk_selesai', 120);
        $mulaiOut = (int) \App\Models\Setting::get('absensi_pulang_mulai', 30);
        $selesaiOut = (int) \App\Models\Setting::get('absensi_pulang_selesai', 120);

        // Windows Calculation
        $startTime = Carbon::parse($activeDate)->setTimeFrom($shift->start_time);
        $windowInStart = $startTime->copy()->subMinutes($mulaiIn);
        $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

        $pulangDate = $activeDate;
        if ($shift->start_time > $shift->end_time) {
            $pulangDate = Carbon::parse($activeDate)->addDay()->format('Y-m-d');
        }
        $endTime = Carbon::parse($pulangDate)->setTimeFrom($shift->end_time);
        $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);
        $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);

        $existing = Absensi::where('personnel_id', $personnel->id)
            ->where('tanggal', $activeDate)
            ->first();

        // VALIDATION LOGIC
        if (!$existing || !$existing->jam_masuk) {
            // MODE: ATTEMPT CHECK IN
            if ($now->lessThan($windowInStart)) {
                $diff = $windowInStart->diffForHumans($now, true);
                return response()->json([
                    'status' => 'error',
                    'message' => "Belum waktunya Absen Masuk. Silakan kembali $diff lagi."
                ], 403);
            }
            if ($now->greaterThan($windowInEnd)) {
                // If it's too late to check in, check if it's already time for check out (missing IN)
                if ($now->between($windowOutStart, $windowOutEnd)) {
                    // Allowed to proceed but will be treated as check-in later? 
                    // Actually web allows OUT if missing IN during OUT window.
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Batas waktu Absen Masuk sudah berakhir."
                    ], 403);
                }
            }
        } else {
            // MODE: ATTEMPT CHECK OUT
            if ($now->lessThan($windowOutStart)) {
                $diff = $windowOutStart->diffForHumans($now, true);
                return response()->json([
                    'status' => 'error',
                    'message' => "Belum waktunya Absen Pulang. Silakan kembali $diff lagi."
                ], 403);
            }
            if ($now->greaterThan($windowOutEnd)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Batas waktu Absen Pulang sudah berakhir."
                ], 403);
            }
        }

        // 1. Validasi Lokasi (Geofencing)
        $lokasiService = app(\App\Services\AbsensiLokasiService::class);
        $lokasiResult = $lokasiService->validasiLokasi(
            $personnel,
            (float) $request->lat,
            (float) $request->lng
        );

        if (!$lokasiResult['boleh']) {
            return response()->json([
                'status' => 'error',
                'message' => $lokasiResult['pesan']
            ], 403);
        }

        $existing = Absensi::where('personnel_id', $personnel->id)
            ->where('tanggal', $activeDate)
            ->first();

        if (!$existing || !$existing->jam_masuk) {
            // CHECK IN LOGIC
            $status_masuk = 'HADIR';
            
            // Night Shift Status Logic
            $isNightShift = $jadwal->shift->start_time > $jadwal->shift->end_time;
            
            // Tolerance: 1 minute
            $startTimeWithBuffer = Carbon::parse($jadwal->shift->start_time)->addMinute()->format('H:i:s');

            if ($isNightShift) {
                // If arrived between 00:00 and EndTime, late relative to Day 1 StartTime
                if ($nowTime <= $jadwal->shift->end_time) {
                    $status_masuk = 'TELAT';
                } else {
                    // Between StartTime and Midnight
                    if ($nowTime > $startTimeWithBuffer) {
                        $status_masuk = 'TELAT';
                    }
                }
            } else {
                if ($nowTime > $startTimeWithBuffer) {
                    $status_masuk = 'TELAT';
                }
            }

            // Store photo
            $imageData = base64_decode($request->foto);
            $fileName = 'absensi/in_' . $personnel->id . '_' . time() . '.jpg';
            Storage::disk('public')->put($fileName, $imageData);

            if (!$existing) {
                $absensi = Absensi::create([
                    'personnel_id' => $personnel->id,
                    'jadwal_id' => $jadwal->id,
                    'kantor_id' => $lokasiResult['kantor_id'],
                    'tanggal' => $activeDate,
                    'status' => 'HADIR',
                    'jam_masuk' => $now->format('H:i:s'),
                    'status_masuk' => $status_masuk,
                    'foto_masuk' => $fileName,
                    'lat_masuk' => $request->lat,
                    'lng_masuk' => $request->lng,
                    'is_within_radius' => $lokasiResult['is_within_radius'],
                    'jarak_meter' => $lokasiResult['jarak_meter'],
                ]);
            } else {
                $existing->update([
                    'jadwal_id' => $jadwal->id,
                    'kantor_id' => $lokasiResult['kantor_id'],
                    'status' => 'HADIR',
                    'jam_masuk' => $now->format('H:i:s'),
                    'status_masuk' => $status_masuk,
                    'foto_masuk' => $fileName,
                    'lat_masuk' => $request->lat,
                    'lng_masuk' => $request->lng,
                    'is_within_radius' => $lokasiResult['is_within_radius'],
                    'jarak_meter' => $lokasiResult['jarak_meter'],
                ]);
                $absensi = $existing;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Absen Masuk berhasil. Status: ' . $status_masuk,
                'data' => $absensi
            ]);
        } else {
            // CHECK OUT LOGIC
            if ($existing->jam_pulang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah melakukan absen pulang hari ini.'
                ], 400);
            }

            $status_pulang = 'HADIR';
            
            $isNightShift = $jadwal->shift->start_time > $jadwal->shift->end_time;
            $isNextDay = ($activeDate !== $today);
            $endTime = Carbon::parse($jadwal->shift->end_time)->format('H:i:s');

            if ($isNightShift) {
                if (!$isNextDay) {
                    $status_pulang = 'PC';
                } else {
                    if ($nowTime < $endTime) {
                        $status_pulang = 'PC';
                    }
                }
            } else {
                if ($nowTime < $endTime) {
                    $status_pulang = 'PC';
                }
            }

            // Store photo
            $imageData = base64_decode($request->foto);
            $fileName = 'absensi/out_' . $personnel->id . '_' . time() . '.jpg';
            Storage::disk('public')->put($fileName, $imageData);

            $existing->update([
                'status' => 'HADIR',
                'jam_pulang' => $now->format('H:i:s'),
                'status_pulang' => $status_pulang,
                'foto_pulang' => $fileName,
                'lat_pulang' => $request->lat,
                'lng_pulang' => $request->lng,
                'kantor_id_pulang' => $lokasiResult['kantor_id'],
                'is_within_radius_pulang' => $lokasiResult['is_within_radius'],
                'jarak_meter_pulang' => $lokasiResult['jarak_meter'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Absen Pulang berhasil. Status: ' . $status_pulang,
                'data' => $existing
            ]);
        }
    }
}
