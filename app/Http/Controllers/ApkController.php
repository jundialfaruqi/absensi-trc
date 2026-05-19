<?php

namespace App\Http\Controllers;

use App\Models\ApkRelease;
use App\Models\Setting;
use App\Models\Personnel;

class ApkController extends Controller
{
    /**
     * Get the dynamic filename based on the latest APK version.
     *
     * @return string
     */
    private function getDynamicFilename(): string
    {
        $latest = ApkRelease::latestRelease();
        $version = $latest ? $latest->version : Setting::get('apk_version', 'v2.1.0');
        
        $version = trim($version);
        if (!preg_match('/^[vV]/', $version) && preg_match('/^[0-9]/', $version)) {
            $version = 'v' . $version;
        }

        return 'TRC-Pekanbaru-Aman-' . $version . '.apk';
    }

    /**
     * Download APK from admin settings page.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        $filePath = storage_path('app/protected-downloads/app-arm64-v8a-release.apk');
        if (!file_exists($filePath)) {
            abort(404, 'File aplikasi tidak ditemukan di server.');
        }

        return response()->download($filePath, $this->getDynamicFilename(), [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    /**
     * Direct download APK for personnel using PIN.
     *
     * @param string $pin
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function directDownload(string $pin)
    {
        $personnel = Personnel::where('pin', $pin)->first();
        if (!$personnel) {
            abort(403, 'Akses tidak sah.');
        }

        $filePath = storage_path('app/protected-downloads/app-arm64-v8a-release.apk');
        if (!file_exists($filePath)) {
            abort(404, 'File aplikasi tidak ditemukan di server.');
        }

        return response()->download($filePath, $this->getDynamicFilename(), [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
