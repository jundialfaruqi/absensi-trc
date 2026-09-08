<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Login Admin (khusus role: admin-opd & super-admin)
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::with(['roles', 'opds'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau kata sandi yang Anda masukkan salah.',
                'errors' => [
                    'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
                ],
            ], 401);
        }

        // Cek Role: Hanya admin-opd dan super-admin yang diizinkan
        if (!$user->hasAnyRole(['admin-opd', 'super-admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Aplikasi ini khusus untuk Admin OPD dan Super Admin.',
            ], 403);
        }

        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken(
            $user,
            $request->device_name ?: $request->userAgent(),
            $request->ip()
        );

        $opd = $user->opd();

        return response()->json([
            'status' => 'success',
            'message' => "Login berhasil. Selamat datang, {$user->name}!",
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 3600,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'foto' => $user->foto ? url('storage/' . $user->foto) : null,
                    'nomor_hp' => $user->nomor_hp,
                    'roles' => $user->getRoleNames()->values(),
                    'is_super_admin' => $user->hasRole('super-admin'),
                    'opd' => $opd ? [
                        'id' => $opd->id,
                        'name' => $opd->name,
                        'singkatan' => $opd->singkatan ?? null,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Memperbarui Access Token via Refresh Token
     */
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
            'device_name' => 'nullable|string',
        ], [
            'refresh_token.required' => 'Refresh token wajib disertakan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->jwtService->rotateRefreshToken(
            $request->refresh_token,
            $request->device_name ?: $request->userAgent(),
            $request->ip()
        );

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refresh token tidak valid atau telah kedaluwarsa. Silakan login kembali.',
            ], 401);
        }

        $user = $result['user'];
        $opd = $user->opd();

        return response()->json([
            'status' => 'success',
            'message' => 'Token berhasil diperbarui.',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'expires_in' => $result['expires_in'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->values(),
                    'opd' => $opd ? [
                        'id' => $opd->id,
                        'name' => $opd->name,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Logout Admin (Mencabut Refresh Token)
     */
    public function logout(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if ($refreshToken) {
            $this->jwtService->revokeRefreshToken($refreshToken);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Sesi telah diakhiri.',
        ]);
    }

    /**
     * Profil Admin yang sedang login
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $opd = $user->opd();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'foto' => $user->foto ? url('storage/' . $user->foto) : null,
                'nomor_hp' => $user->nomor_hp,
                'roles' => $user->getRoleNames()->values(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'is_super_admin' => $user->hasRole('super-admin'),
                'opd' => $opd ? [
                    'id' => $opd->id,
                    'name' => $opd->name,
                    'singkatan' => $opd->singkatan ?? null,
                ] : null,
            ],
        ]);
    }
}
