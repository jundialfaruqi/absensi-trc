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

        $existing = Absensi::where('personnel_id', $personnel->id)
            ->where('tanggal', $activeDate)
            ->first();

        if (!$existing) {
            // CHECK IN LOGIC
            $status_masuk = 'HADIR';
            
            // Night Shift Status Logic
            $isNightShift = $jadwal->shift->start_time > $jadwal->shift->end_time;

            if ($isNightShift) {
                // If arrived between 00:00 and EndTime, late relative to Day 1 StartTime
                if ($nowTime <= $jadwal->shift->end_time) {
                    $status_masuk = 'TELAT';
                } else {
                    // Between StartTime and Midnight
                    if ($nowTime > $jadwal->shift->start_time) {
                        $status_masuk = 'TELAT';
                    }
                }
            } else {
                if ($nowTime > $jadwal->shift->start_time) {
                    $status_masuk = 'TELAT';
                }
            }

            // Store photo
            $imageData = base64_decode($request->foto);
            $fileName = 'absensi/' . $personnel->id . '_' . time() . '_in.jpg';
            Storage::disk('public')->put($fileName, $imageData);

            $absensi = Absensi::create([
                'personnel_id' => $personnel->id,
                'jadwal_id' => $jadwal->id,
                'tanggal' => $activeDate,
                'jam_masuk' => $now->format('H:i:s'),
                'status_masuk' => $status_masuk,
                'foto_masuk' => $fileName,
                'lat_masuk' => $request->lat,
                'lng_masuk' => $request->lng,
            ]);

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

            if ($isNightShift) {
                if (!$isNextDay) {
                    $status_pulang = 'PC';
                } else {
                    if ($nowTime < $jadwal->shift->end_time) {
                        $status_pulang = 'PC';
                    }
                }
            } else {
                if ($nowTime < $jadwal->shift->end_time) {
                    $status_pulang = 'PC';
                }
            }

            // Store photo
            $imageData = base64_decode($request->foto);
            $fileName = 'absensi/' . $personnel->id . '_' . time() . '_out.jpg';
            Storage::disk('public')->put($fileName, $imageData);

            $existing->update([
                'jam_pulang' => $now->format('H:i:s'),
                'status_pulang' => $status_pulang,
                'foto_pulang' => $fileName,
                'lat_pulang' => $request->lat,
                'lng_pulang' => $request->lng,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Absen Pulang berhasil. Status: ' . $status_pulang,
                'data' => $existing
            ]);
        }
    }
}
