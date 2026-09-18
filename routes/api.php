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
    Route::get('/jadwal', [\App\Http\Controllers\Api\V1\AdminJadwalController::class, 'index']);

    // Fitur Absensi & Cek Absensi Khusus Admin Supervisor Lapangan
    Route::prefix('absensi')->group(function () {
        Route::get('/personnels', [\App\Http\Controllers\Api\V1\AdminAbsensiController::class, 'personnels']);
        Route::get('/check-status/{id}', [\App\Http\Controllers\Api\V1\AdminAbsensiController::class, 'checkStatus']);
        Route::post('/store', [\App\Http\Controllers\Api\V1\AdminAbsensiController::class, 'store']);
    });

    // Manajemen Personel Admin
    Route::prefix('personnels')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'index']);
        Route::get('/form-options', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'formOptions']);
        Route::get('/{id}', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'store']);
        Route::post('/{id}', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'destroy']);
        Route::delete('/{id}/face-data', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'deleteFaceData']);
        Route::post('/{id}/face-enroll', [\App\Http\Controllers\Api\V1\AdminPersonnelController::class, 'enrollFace'])
            ->middleware('throttle:30,1');
    });

    // Perekaman Biometrik (192-D MobileFaceNet)
    Route::post('/personnels/{personnel}/face-mobile', [\App\Http\Controllers\Api\V1\AdminAbsensiController::class, 'updateFaceDescriptorMobile'])
        ->middleware('throttle:30,1');
    Route::post('/personnels/{personnel}/multi-face-mobile', [\App\Http\Controllers\Api\V1\AdminAbsensiController::class, 'updateMultiFaceDescriptorMobile'])
        ->middleware('throttle:30,1');
    Route::post('/personnels/{personnel}/reset-face-learning', [\App\Http\Controllers\Api\V1\AdminAbsensiController::class, 'resetFaceLearning'])
        ->middleware('throttle:30,1');
});

/*
|--------------------------------------------------------------------------
| V1 Personnel Authentication Routes (Auto-Takeover & Pure JWT)
|--------------------------------------------------------------------------
| Khusus aplikasi Absensi TRC Personel (Mandiri).
*/
Route::prefix('v1/personel/auth')->middleware('noindex')->group(function () {
    // Public: Aktivasi lisensi (Auto-Takeover) & Refresh token
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/activate', [\App\Http\Controllers\Api\V1\Personel\PersonnelAuthController::class, 'activate']);
        Route::post('/refresh', [\App\Http\Controllers\Api\V1\Personel\PersonnelAuthController::class, 'refresh']);
    });

    // Protected: Logout & Me
    Route::middleware('jwt.personel')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\Personel\PersonnelAuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\V1\Personel\PersonnelAuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| V1 Personnel Features (Face 3D Enrollment, Biometric Template, etc.)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/personel')->middleware(['noindex', 'jwt.personel'])->group(function () {
    // Perekaman Wajah 3D Multi-Angle & Master Template 192D
    Route::post('/face-enroll', [\App\Http\Controllers\Api\V1\Personel\PersonnelFaceEnrollmentController::class, 'enroll'])
        ->middleware('throttle:30,1');
    Route::get('/face-template', [\App\Http\Controllers\Api\V1\Personel\PersonnelFaceEnrollmentController::class, 'template']);

    // Dashboard Summary, Jadwal Kerja, & Riwayat Presensi
    Route::get('/dashboard/summary', [\App\Http\Controllers\Api\V1\Personel\PersonnelDashboardController::class, 'summary']);
    Route::get('/jadwal', [\App\Http\Controllers\Api\V1\Personel\PersonnelJadwalController::class, 'index']);
    Route::get('/riwayat', [\App\Http\Controllers\Api\V1\Personel\PersonnelRiwayatController::class, 'index']);
    Route::get('/banners', [\App\Http\Controllers\Api\V1\Personel\PersonnelBannerController::class, 'index']);

    // Real-Time Location Tracking & Push Notification FCM Token
    Route::post('/device/location', [\App\Http\Controllers\Api\V1\Personel\PersonnelLocationController::class, 'updateLocation']);
    Route::post('/device/fcm-token', [\App\Http\Controllers\Api\V1\Personel\PersonnelAuthController::class, 'updateFcmToken']);

    // Fitur Presensi Mandiri 1:1 Biometrik
    Route::prefix('absensi')->group(function () {
        Route::get('/biometrics', [\App\Http\Controllers\Api\V1\Personel\PersonnelAbsensiController::class, 'myBiometrics']);
        Route::get('/check-status', [\App\Http\Controllers\Api\V1\Personel\PersonnelAbsensiController::class, 'checkStatus']);
        Route::get('/riwayat', [\App\Http\Controllers\Api\V1\Personel\PersonnelRiwayatController::class, 'index']);
        Route::post('/store', [\App\Http\Controllers\Api\V1\Personel\PersonnelAbsensiController::class, 'store'])
            ->middleware('throttle:30,1');
    });
});

