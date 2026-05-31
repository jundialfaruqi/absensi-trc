<?php

namespace App\Http\Middleware;

use App\Models\ApkRelease;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAppVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $minVersion = ApkRelease::minimumVersionCode();
        $clientVersion = $request->header('X-App-Version-Code');

        \Log::info('VerifyAppVersion Debug Log:', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'client_version_header' => $clientVersion,
            'min_version_required' => $minVersion,
            'is_blocked' => (! $clientVersion || (int) $clientVersion < (int) $minVersion),
        ]);

        if (! $clientVersion || (int) $clientVersion < (int) $minVersion) {
            return response()->json([
                'success' => false,
                'message' => 'Aplikasi Anda sudah usang. Silakan perbarui aplikasi ke versi terbaru untuk melanjutkan.',
                'update_required' => true,
                'min_version' => $minVersion,
                'current_version' => $clientVersion ?? 0,
                'download_url' => url('/download-apk'),
            ], 426);
        }

        return $next($request);
    }
}
