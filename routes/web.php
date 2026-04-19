<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Public Route
Route::livewire('/login', 'login')
    ->name('login');

Route::livewire('/absensi-web', 'absensi')
    ->name('absensi-web');

// Admin Route
Route::group([
    'middleware' => ['auth'],
    'prefix' => '/page',
], function () {
    Route::livewire('/dashboard', 'admin::dashboard')
        ->name('dashboard');

    Route::livewire('/role-permission', 'admin::role-permission')
        ->name('role-permission');

    Route::livewire('/opd', 'admin::opd')
        ->name('opd');

    Route::livewire('/penugasan', 'admin::penugasan')
        ->name('penugasan');

    Route::livewire('/user', 'admin::user')
        ->name('user');

    Route::livewire('/personnel', 'admin::personnel')
        ->name('personnel');

    Route::livewire('/kantor', 'admin::kantors')
        ->name('kantor');

    Route::livewire('/shift', 'admin::shift')
        ->name('shift');

    Route::livewire('/jadwal', 'admin::jadwal')
        ->name('jadwal');
    Route::livewire('/cuti', 'admin::cutis')
        ->name('cuti');
    Route::livewire('/jadwal/import', 'admin::jadwal-import')
        ->name('jadwal.import');
    
    Route::livewire('/absensi', 'admin::absensi')
        ->name('absensi');
    Route::get('/jadwal/download-template', function () {
        $month = request('month', date('m'));
        $year = request('year', date('Y'));
        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\JadwalTemplateExport($month, $year, $opdId), "template_jadwal_{$year}_{$month}.xlsx");
    })->name('jadwal.download-template');
});

// --- Personnel Portal Routes ---
Route::livewire('/personnel/login', 'personnel::login')
    ->name('personnel.login');

Route::group([
    'middleware' => ['auth:personnel'],
    'prefix' => '/personnel',
], function () {
    Route::livewire('/dashboard', 'personnel::dashboard')
        ->name('personnel.dashboard');

    Route::livewire('/profile', 'personnel::profile')
        ->name('personnel.profile');
});
