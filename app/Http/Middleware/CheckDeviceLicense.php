<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Device;

class CheckDeviceLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $device = null;

        // 1. Coba autentikasi via Token (Sanctum) - Metode Baru
        if ($token = $request->bearerToken()) {
            $device = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
            
            // Pastikan yang punya token adalah model Device
            if (!($device instanceof Device)) {
                $device = null;
            }
        }

        // 2. Jika Token tidak ada/tidak valid, coba via Header (Metode Lama/Legacy)
        if (!$device) {
            $licenseKey = $request->header('X-LICENSE-KEY');
            $deviceId = $request->header('X-DEVICE-ID');

            if ($licenseKey && $deviceId) {
                $device = Device::where('license_key', $licenseKey)
                    ->where('unique_device_id', $deviceId)
                    ->first();
            }
        }

        // 3. Jika tetap tidak ketemu
        if (!$device) {
            return response()->json([
                'status' => 'license_required',
                'message' => 'Otentikasi perangkat diperlukan. Silakan aktivasi kembali jika masalah berlanjut.'
            ], 403);
        }

        // 4. Cek Status Perangkat
        if ($device->status !== 'active') {
            $msg = $device->status === 'suspended' 
                ? 'Akses perangkat ditangguhkan oleh Admin. Silakan hubungi operator.' 
                : 'Perangkat belum diaktivasi.';
            
            return response()->json([
                'status' => 'license_' . $device->status,
                'message' => $msg
            ], 403);
        }

        // Update last seen
        $device->update(['last_seen_at' => now()]);

        // Attach device to request
        $request->attributes->add(['device' => $device]);

        return $next($request);
    }
}
