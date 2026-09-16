<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelAuthController extends Controller
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Aktivasi Lisensi Perangkat Personel dengan mekanisme Auto-Takeover.
     */
    public function activate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'device_id' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'android_version' => 'nullable|string',
        ]);

        $licenseKey = trim($validated['license_key']);
        $newDeviceId = trim($validated['device_id']);

        $device = Device::with(['personnel.opd', 'personnel.kantor', 'personnel.penugasan', 'personnel.faceEmbeddings'])
            ->where('license_key', $licenseKey)
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode lisensi tidak valid atau tidak ditemukan dalam sistem.',
            ], 404);
        }

        if ($device->status === 'blocked') {
            return response()->json([
                'status' => 'error',
                'message' => 'Lisensi ini telah diblokir oleh Admin. Silakan hubungi operator OPD Anda.',
            ], 403);
        }

        $personnel = $device->personnel;
        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lisensi belum terhubung dengan data personel aktif.',
            ], 404);
        }

        // 1. AUTO-TAKEOVER: Alihkan kepemilikan perangkat ke HP baru
        $deviceName = trim(($validated['brand'] ?? '') . ' ' . ($validated['model'] ?? '')) ?: 'Perangkat Personel';

        $device->update([
            'unique_device_id' => $newDeviceId,
            'brand' => $validated['brand'] ?? $device->brand,
            'model' => $validated['model'] ?? $device->model,
            'android_version' => $validated['android_version'] ?? $device->android_version,
            'status' => 'active',
            'activated_at' => now(),
            'last_seen_at' => now(),
        ]);

        // 2. REVOKE SESSIONS: Cabut seluruh refresh token sesi sebelumnya (Kick-Out HP lama)
        $this->jwtService->revokeAllPersonnelTokens($personnel->id);

        // 3. GENERATE TOKENS: Terbitkan Access Token (JWT) & Refresh Token baru
        $accessToken = $this->jwtService->generatePersonnelAccessToken($personnel);
        $refreshToken = $this->jwtService->generatePersonnelRefreshToken(
            $personnel,
            $device->id,
            $newDeviceId,
            $deviceName,
            $request->ip()
        );

        // 4. CEK KEBUTUHAN PEREKAMAN WAJAH 3D:
        // True jika belum punya 192D atau jumlah pose 3D kurang dari 4
        $has192D = !empty($personnel->face_descriptor_mobile);
        $poseCount = $personnel->faceEmbeddings ? $personnel->faceEmbeddings->count() : 0;
        $needsFaceEnrollment = !$has192D || $poseCount < 4;

        return response()->json([
            'status' => 'success',
            'message' => 'Aktivasi lisensi berhasil. Perangkat siap digunakan.',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'needs_face_enrollment' => $needsFaceEnrollment,
            'face_status' => [
                'has_192d' => $has192D,
                'pose_count' => $poseCount,
            ],
            'personnel' => [
                'id' => $personnel->id,
                'name' => $personnel->name,
                'nik' => $personnel->nik,
                'foto' => $personnel->foto,
                'opd_id' => $personnel->opd_id,
                'opd_name' => $personnel->opd?->name,
                'kantor_id' => $personnel->kantor_id,
                'penugasan_id' => $personnel->penugasan_id,
            ],
            'device' => [
                'id' => $device->id,
                'license_key' => $device->license_key,
                'unique_device_id' => $device->unique_device_id,
                'status' => $device->status,
            ],
        ]);
    }

    /**
     * Silent Refresh Token Rotation.
     */
    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        $result = $this->jwtService->rotatePersonnelRefreshToken(
            $validated['refresh_token'],
            $validated['device_id'] ?? null,
            null,
            $request->ip()
        );

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refresh token tidak valid atau telah dicabut. Lisensi Anda mungkin telah dialihkan ke perangkat lain.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Token berhasil diperbarui.',
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ]);
    }

    /**
     * Logout dan cabut refresh token aktif.
     */
    public function logout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $this->jwtService->revokePersonnelRefreshToken($validated['refresh_token']);

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Dapatkan profil personel yang sedang terautentikasi.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\Personnel $personnel */
        $personnel = $request->attributes->get('personnel');

        $personnel->load(['opd', 'kantor', 'penugasan', 'faceEmbeddings']);

        $has192D = !empty($personnel->face_descriptor_mobile);
        $poseCount = $personnel->faceEmbeddings ? $personnel->faceEmbeddings->count() : 0;
        $needsFaceEnrollment = !$has192D || $poseCount < 4;

        return response()->json([
            'status' => 'success',
            'personnel' => [
                'id' => $personnel->id,
                'name' => $personnel->name,
                'nik' => $personnel->nik,
                'foto' => $personnel->foto,
                'opd_id' => $personnel->opd_id,
                'opd_name' => $personnel->opd?->name,
                'kantor_id' => $personnel->kantor_id,
                'kantor_name' => $personnel->kantor?->nama_kantor,
                'penugasan_id' => $personnel->penugasan_id,
                'regu' => $personnel->regu,
                'nomor_hp' => $personnel->nomor_hp,
                'wajib_absen_di_lokasi' => (bool)$personnel->wajib_absen_di_lokasi,
                'face_recognition' => (bool)$personnel->face_recognition,
                'needs_face_enrollment' => $needsFaceEnrollment,
                'face_status' => [
                    'has_192d' => $has192D,
                    'pose_count' => $poseCount,
                ],
            ],
        ]);
    }
}
