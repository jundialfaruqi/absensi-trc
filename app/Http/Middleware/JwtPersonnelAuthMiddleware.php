<?php

namespace App\Http\Middleware;

use App\Models\Device;
use App\Models\Personnel;
use App\Models\PersonnelRefreshToken;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtPersonnelAuthMiddleware
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Handle an incoming request for Personnel API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token autentikasi tidak ditemukan.',
            ], 401);
        }

        $payload = $this->jwtService->verifyAccessToken($token);

        if (!$payload || !isset($payload->sub)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau telah kedaluwarsa.',
            ], 401);
        }

        // Pastikan token diperuntukkan bagi aplikasi personel
        if (!isset($payload->aud) || $payload->aud !== 'absensitrc-personel-app') {
            return response()->json([
                'status' => 'error',
                'message' => 'Audiens token tidak sesuai untuk aplikasi personel.',
            ], 403);
        }

        $personnel = Personnel::with(['opd', 'kantor', 'penugasan'])->find($payload->sub);

        if (!$personnel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data personel tidak ditemukan atau dinonaktifkan.',
            ], 401);
        }

        // 1. Validasi Keberadaan dan Status Perangkat
        $device = Device::where('personnel_id', $personnel->id)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'License perangkat anda belum dibuat, silahkan hubungi Admin.',
            ], 403);
        }

        if (in_array($device->status, ['suspended', 'blocked'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'License belum aktif, silahkan hubungi Admin untuk aktifasi license',
            ], 403);
        }

        // 2. Validasi Sesi Aktif (Refresh token tidak boleh kosong atau dicabut)
        $hasActiveSession = PersonnelRefreshToken::where('personnel_id', $personnel->id)
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at')
            ->exists();

        if (!$hasActiveSession) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi perangkat telah dicabut atau diakhiri. Silakan aktivasi kembali.',
            ], 401);
        }

        // Bind data personel ke request
        $request->setUserResolver(fn () => $personnel);
        $request->attributes->set('personnel', $personnel);
        $request->attributes->set('device', $device);
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
