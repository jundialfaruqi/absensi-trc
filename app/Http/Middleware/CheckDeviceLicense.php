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
        $licenseKey = $request->header('X-LICENSE-KEY');
        $deviceId = $request->header('X-DEVICE-ID');

        if (!$licenseKey || !$deviceId) {
            return response()->json([
                'status' => 'license_required',
                'message' => 'Lisensi perangkat diperlukan untuk mengakses layanan ini.'
            ], 403);
        }

        $device = Device::where('license_key', $licenseKey)
            ->where('unique_device_id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'status' => 'license_invalid',
                'message' => 'Lisensi perangkat tidak valid atau tidak terdaftar.'
            ], 403);
        }

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
