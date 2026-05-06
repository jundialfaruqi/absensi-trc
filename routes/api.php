<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

Route::middleware('mobile_auth')->group(function () {
    // Public Mobile Routes (Only need API Key) — Ketat: 5 request per menit
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/license/activate', [AttendanceController::class, 'activateLicense']);
        Route::post('/license/check', [AttendanceController::class, 'checkLicense']);
    });

    // Protected Mobile Routes (Need API Key AND Device License)
    Route::middleware('device_auth')->group(function () {
        // Data sync — 30 request per menit
        Route::middleware('throttle:30,1')->group(function () {
            Route::get('/personnels', [AttendanceController::class, 'personnels']);
            Route::get('/personnels/check-status/{id}', [AttendanceController::class, 'checkStatus']);
        });

        // Login PIN (Dinonaktifkan karena sudah tidak digunakan)
        // Route::post('/login/pin', [AttendanceController::class, 'login'])->middleware('throttle:5,5');

        // Absensi — 10 request per menit
        Route::post('/absensi', [AttendanceController::class, 'store'])->middleware('throttle:10,1');
    });
});
