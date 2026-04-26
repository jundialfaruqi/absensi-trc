<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

Route::middleware('mobile_auth')->group(function () {
    // Public Mobile Routes (Only need API Key)
    Route::post('/license/activate', [AttendanceController::class, 'activateLicense']);
    Route::post('/license/check', [AttendanceController::class, 'checkLicense']);

    // Protected Mobile Routes (Need API Key AND Device License)
    Route::middleware('device_auth')->group(function () {
        Route::get('/personnels', [AttendanceController::class, 'personnels']);
        Route::get('/personnels/check-status/{id}', [AttendanceController::class, 'checkStatus']);
        Route::post('/login/pin', [AttendanceController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/absensi', [AttendanceController::class, 'store']);
        });
    });
});
