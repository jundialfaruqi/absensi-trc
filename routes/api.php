<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;

Route::get('/personnels', [AttendanceController::class, 'personnels']);
Route::post('/login/pin', [AttendanceController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/absensi', [AttendanceController::class, 'store']);
});
