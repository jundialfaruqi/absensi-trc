<?php

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['mobile_auth', 'noindex'])->group(function () {
    // Public Mobile Routes (Only need API Key) — Longgar: 30 request per menit (mencegah terblokir saat testing)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/license/activate', [AttendanceController::class, 'activateLicense']);
        Route::post('/license/check', [AttendanceController::class, 'checkLicense']);
    });

    // Protected Mobile Routes (Need API Key AND Device License)
    Route::middleware('device_auth')->group(function () {
        // Data sync — 30 request per menit
        Route::middleware('throttle:30,1')->group(function () {
            Route::get('/personnels', [AttendanceController::class, 'personnels']);
            Route::post('/personnels/face-mobile', [AttendanceController::class, 'storeFaceDescriptorMobile']);
            Route::get('/personnels/check-status/{id}', [AttendanceController::class, 'checkStatus']);
            Route::post('/device/location', [AttendanceController::class, 'updateLocation']);
            Route::post('/device/fcm-token', [AttendanceController::class, 'updateFcmToken']);
            Route::get('/banners', [AttendanceController::class, 'getBanners']);
            Route::get('/global/dashboard', [AttendanceController::class, 'globalDashboard']);
        });

        // Login PIN (Dinonaktifkan karena sudah tidak digunakan)
        // Route::post('/login/pin', [AttendanceController::class, 'login'])->middleware('throttle:5,5');

        // Absensi — 30 request per menit
        Route::post('/absensi', [AttendanceController::class, 'store'])->middleware('throttle:30,1');
    });
});

/*
|--------------------------------------------------------------------------
| V1 Admin Authentication Routes (Pure JWT + Refresh Token)
|--------------------------------------------------------------------------
| Khusus aplikasi Absensi TRC Admin (role: admin-opd & super-admin).
| Tanpa lisensi perangkat, menggunakan email & password.
*/
Route::prefix('v1/admin/auth')->middleware('noindex')->group(function () {
    // Public Auth Routes (Rate limited: 10 per minute untuk proteksi brute-force)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'login']);
        Route::post('/refresh', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'refresh']);
    });

    // Protected Auth Routes (Wajib JWT Access Token Admin yang valid)
    Route::middleware('jwt.admin')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'me']);
        Route::get('/my-profile', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'myProfile']);
    });
});

Route::prefix('v1/admin')->middleware(['noindex', 'jwt.admin'])->group(function () {
    Route::get('/my-profile', [\App\Http\Controllers\Api\V1\AdminAuthController::class, 'myProfile']);
});
