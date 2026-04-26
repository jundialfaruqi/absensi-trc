<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

Route::middleware('mobile_auth')->group(function () {
    Route::get('/personnels', [AttendanceController::class, 'personnels']);
    Route::get('/personnels/check-status/{id}', [AttendanceController::class, 'checkStatus']);
    Route::post('/login/pin', [AttendanceController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/absensi', [AttendanceController::class, 'store']);
    });
});
