<?php

namespace App\Http\Middleware;

use App\Models\Personnel;
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

        // Bind data personel ke request
        $request->setUserResolver(fn () => $personnel);
        $request->attributes->set('personnel', $personnel);
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
