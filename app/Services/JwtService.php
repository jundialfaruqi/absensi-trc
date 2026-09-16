<?php

namespace App\Services;

use App\Models\AdminRefreshToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Throwable;

class JwtService
{
    /**
     * Dapatkan secret key untuk tanda tangan HMAC-SHA256.
     */
    protected function getSecretKey(): string
    {
        $secret = env('JWT_SECRET');
        if (!empty($secret)) {
            return $secret;
        }

        $appKey = config('app.key');
        if (str_starts_with($appKey, 'base64:')) {
            return base64_decode(substr($appKey, 7));
        }

        return $appKey ?: 'absensi-trc-jwt-default-secret-key-32b!';
    }

    /**
     * Generate Access Token (JWT) berdurasi pendek (default 60 menit).
     */
    public function generateAccessToken(User $user, int $ttlMinutes = 60): string
    {
        $now = time();
        $opd = $user->opd();

        $payload = [
            'iss' => config('app.url') ?? 'https://absensitrc.pekanbaru.go.id',
            'aud' => 'absensitrc-admin-app',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + ($ttlMinutes * 60),
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'roles' => $user->getRoleNames()->values()->toArray(),
            'opd_id' => $opd?->id,
            'opd_name' => $opd?->name,
        ];

        return JWT::encode($payload, $this->getSecretKey(), 'HS256');
    }

    /**
     * Generate Refresh Token (Opaque Token) berdurasi 30 hari dan simpan hash-nya di database.
     */
    public function generateRefreshToken(User $user, ?string $deviceName = null, ?string $ip = null, int $ttlDays = 30): string
    {
        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        AdminRefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => $tokenHash,
            'device_name' => $deviceName,
            'ip_address' => $ip,
            'expires_at' => now()->addDays($ttlDays),
        ]);

        return $rawToken;
    }

    /**
     * Validasi Access Token (JWT).
     */
    public function verifyAccessToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->getSecretKey(), 'HS256'));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Rotasi Refresh Token dan terbitkan Access Token baru.
     */
    public function rotateRefreshToken(string $rawToken, ?string $deviceName = null, ?string $ip = null): ?array
    {
        $tokenHash = hash('sha256', $rawToken);

        $record = AdminRefreshToken::with('user')
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$record || !$record->isValid() || !$record->user) {
            return null;
        }

        // Cek apakah user masih memiliki role admin yang diizinkan
        $user = $record->user;
        if (!$user->hasAnyRole(['admin-opd', 'super-admin'])) {
            return null;
        }

        // Cabut token lama (rotasi)
        $record->update(['revoked_at' => now()]);

        // Buat pasangan token baru
        $newAccessToken = $this->generateAccessToken($user);
        $newRefreshToken = $this->generateRefreshToken(
            $user,
            $deviceName ?: $record->device_name,
            $ip ?: $record->ip_address
        );

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 60 menit dalam detik
            'user' => $user,
        ];
    }

    /**
     * Cabut refresh token admin (Logout).
     */
    public function revokeRefreshToken(string $rawToken): bool
    {
        $tokenHash = hash('sha256', $rawToken);

        $record = AdminRefreshToken::where('token_hash', $tokenHash)->first();
        if ($record && $record->revoked_at === null) {
            $record->update(['revoked_at' => now()]);
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Personnel Token Management (Pure JWT + Refresh Token)
    |--------------------------------------------------------------------------
    */

    /**
     * Generate Access Token (JWT) untuk Personel.
     */
    public function generatePersonnelAccessToken(\App\Models\Personnel $personnel, int $ttlMinutes = 60): string
    {
        $now = time();
        $opd = $personnel->opd;

        $payload = [
            'iss' => config('app.url') ?? 'https://absensitrc.pekanbaru.go.id',
            'aud' => 'absensitrc-personel-app',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + ($ttlMinutes * 60),
            'sub' => $personnel->id,
            'nik' => $personnel->nik,
            'name' => $personnel->name,
            'type' => 'personnel',
            'opd_id' => $personnel->opd_id,
            'opd_name' => $opd?->name,
            'kantor_id' => $personnel->kantor_id,
            'penugasan_id' => $personnel->penugasan_id,
        ];

        return JWT::encode($payload, $this->getSecretKey(), 'HS256');
    }

    /**
     * Generate Refresh Token untuk Personel.
     */
    public function generatePersonnelRefreshToken(
        \App\Models\Personnel $personnel,
        ?int $deviceId = null,
        ?string $uniqueDeviceId = null,
        ?string $deviceName = null,
        ?string $ip = null,
        int $ttlDays = 30
    ): string {
        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        \App\Models\PersonnelRefreshToken::create([
            'personnel_id' => $personnel->id,
            'device_id' => $deviceId,
            'unique_device_id' => $uniqueDeviceId,
            'token_hash' => $tokenHash,
            'device_name' => $deviceName,
            'ip_address' => $ip,
            'expires_at' => now()->addDays($ttlDays),
        ]);

        return $rawToken;
    }

    /**
     * Rotasi Refresh Token Personel (Silent Refresh).
     */
    public function rotatePersonnelRefreshToken(
        string $rawToken,
        ?string $uniqueDeviceId = null,
        ?string $deviceName = null,
        ?string $ip = null
    ): ?array {
        $tokenHash = hash('sha256', $rawToken);

        $record = \App\Models\PersonnelRefreshToken::with(['personnel.opd', 'personnel.kantor'])
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$record || !$record->isValid() || !$record->personnel) {
            return null;
        }

        $personnel = $record->personnel;

        // Cabut token lama (rotasi)
        $record->update(['revoked_at' => now()]);

        // Terbitkan token baru
        $newAccessToken = $this->generatePersonnelAccessToken($personnel);
        $newRefreshToken = $this->generatePersonnelRefreshToken(
            $personnel,
            $record->device_id,
            $uniqueDeviceId ?: $record->unique_device_id,
            $deviceName ?: $record->device_name,
            $ip ?: $record->ip_address
        );

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'personnel' => $personnel,
        ];
    }

    /**
     * Cabut refresh token personel (Logout).
     */
    public function revokePersonnelRefreshToken(string $rawToken): bool
    {
        $tokenHash = hash('sha256', $rawToken);

        $record = \App\Models\PersonnelRefreshToken::where('token_hash', $tokenHash)->first();
        if ($record && $record->revoked_at === null) {
            $record->update(['revoked_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Cabut seluruh token milik personel (Digunakan saat Takeover).
     */
    public function revokeAllPersonnelTokens(int $personnelId): void
    {
        \App\Models\PersonnelRefreshToken::where('personnel_id', $personnelId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}
