<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAdminAuthMiddleware
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Handle an incoming request.
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

        $user = User::with('opds')->find($payload->sub);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun pengguna tidak ditemukan.',
            ], 401);
        }

        // Pastikan pengguna memiliki salah satu role admin
        if (!$user->hasAnyRole(['admin-opd', 'super-admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Aplikasi ini khusus untuk Admin OPD dan Super Admin.',
            ], 403);
        }

        // Bind user ke request
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
