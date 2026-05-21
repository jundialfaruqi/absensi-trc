<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApkController;
use App\Http\Controllers\BeritaController;

// Public Route
Route::get('/', function () {
    return view('welcome');
})->name('welcome')->middleware('noindex');

Route::livewire('/login', 'login')
    ->name('login')
    ->middleware('noindex');

Route::get('/berita/{berita}', [BeritaController::class, 'show'])->name('public.berita.show')->middleware('noindex');

Route::livewire('/download-app', 'public::download-app')
    ->name('download-app')
    ->middleware('noindex');

Route::get('/direct-download-apk/{pin}', [ApkController::class, 'directDownload'])->name('apk.download.direct')
    ->middleware('noindex');

// Admin Route
Route::group([
    'middleware' => ['auth','noindex'],
    'prefix' => '/page',
], function () {
    Route::livewire('/dashboard', 'admin::dashboard')
        ->middleware('permission:lihat-dashboard')
        ->name('dashboard');

    Route::livewire('/absensi-web', 'absensi')
        ->middleware('permission:halaman-absensi')
        ->name('absensi-web');

    Route::livewire('/permohonan-cuti', 'admin::permohonan-cuti')
        ->middleware('permission:manajemen-permohonan-cuti')
        ->name('permohonan-cuti');

    Route::livewire('/role-permission', 'admin::role-permission')
        ->middleware('permission:manajemen-role-permission')
        ->name('role-permission');

    Route::livewire('/opd', 'admin::opd')
        ->middleware('permission:manajemen-opd')
        ->name('opd');

    Route::livewire('/penugasan', 'admin::penugasan')
        ->middleware('permission:manajemen-penugasan')
        ->name('penugasan');

    Route::livewire('/user', 'admin::user')
        ->middleware('permission:manajemen-user')
        ->name('user');

    Route::livewire('/personnel', 'admin::personnel')
        ->middleware('permission:manajemen-personel')
        ->name('personnel');

    Route::livewire('/personnel/tambah', 'admin::personnel-create')
        ->middleware('permission:manajemen-personel')
        ->name('personnel.tambah');

    Route::livewire('/personnel/{id}/edit', 'admin::personnel-edit')
        ->middleware('permission:manajemen-personel')
        ->name('personnel.edit');

    Route::livewire('/kantor', 'admin::kantors')
        ->middleware('permission:manajemen-kantor')
        ->name('kantor');

    Route::livewire('/shift', 'admin::shift')
        ->middleware('permission:manajemen-shift')
        ->name('shift');

    Route::livewire('/konsumsi', 'admin::konsumsi')
        ->middleware('permission:manajemen-konsumsi')
        ->name('konsumsi');

    Route::livewire('/jadwal', 'admin::jadwal')
        ->middleware('permission:manajemen-jadwal')
        ->name('jadwal');

    Route::livewire('/cuti', 'admin::cutis')
        ->middleware('permission:manajemen-master-cuti')
        ->name('cuti');

    Route::livewire('/jadwal/import', 'admin::jadwal-import')
        ->middleware('permission:manajemen-jadwal-import')
        ->name('jadwal.import');

    Route::livewire('/jadwal/generate', 'admin::jadwal-generate')
        ->middleware('permission:manajemen-jadwal')
        ->name('jadwal.generate');

    Route::livewire('/absensi', 'admin::absensi')
        ->middleware('permission:manajemen-absensi')
        ->name('absensi');

    Route::livewire('/absensi/log', 'admin::absensi-log')
        ->middleware('permission:manajemen-absensi')
        ->name('absensi.log');

    Route::livewire('/absensi/log-pin', 'admin::pin-attempt-log')
        ->middleware('permission:manajemen-absensi')
        ->name('absensi.log.pin');

    Route::livewire('/absensi/anomaly', 'admin::absensi-anomaly')
        ->middleware('permission:manajemen-absensi-anomaly')
        ->name('absensi.anomaly');

    Route::livewire('/absensi/kotak-sampah', 'admin::absensi-trash')
        ->middleware('permission:manajemen-kotak-sampah-absensi')
        ->name('absensi.trash');

    Route::livewire('/berita', 'admin::berita')
        ->middleware('permission:manajemen-berita')
        ->name('berita');

    Route::livewire('/berita/create', 'admin::berita-create')
        ->middleware('permission:manajemen-berita')
        ->name('berita.create');

    Route::livewire('/berita/{id}/edit', 'admin::berita-edit')
        ->middleware('permission:manajemen-berita')
        ->name('berita.edit');

    Route::livewire('/pengaturan', 'admin::pengaturan')
        ->middleware('permission:manajemen-pengaturan')
        ->name('pengaturan');

    Route::get('/pengaturan/download-apk', [ApkController::class, 'download'])->middleware('permission:download-apk')->name('pengaturan.download-apk');

    Route::livewire('/perangkat', 'admin::device')
        ->middleware('permission:manajemen-perangkat')
        ->name('perangkat');

    Route::livewire('/maps', 'admin::maps')
        ->middleware('permission:manajemen-maps')
        ->name('maps');

    Route::livewire('/profil-saya', 'admin::profile-saya')
        ->name('profile');

    Route::get('/absensi/export-pdf', [App\Http\Controllers\Admin\ReportController::class, 'exportAbsensiPdf'])
        ->middleware('permission:manajemen-absensi')
        ->name('absensi.export-pdf');

    Route::get('/jadwal/download-template', function () {
        $month = request('month', date('m'));
        $year = request('year', date('Y'));
        $opdId = Auth::user()->hasRole('super-admin') ? null : Auth::user()->opd()?->id;

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\JadwalTemplateExport($month, $year, $opdId), "template_jadwal_{$year}_{$month}.xlsx");
    })->name('jadwal.download-template');
});

// --- Personnel Portal Routes ---
Route::livewire('/personnel/login', 'personnel::login')
    ->name('personnel.login')
    ->middleware('noindex');

Route::livewire('/personnel/register', 'personnel::register')
    ->name('personnel.register')
    ->middleware('noindex');

Route::livewire('/personnel/panduan', 'personnel::panduan')
    ->name('personnel.panduan')
    ->middleware('noindex');

Route::group([
    'middleware' => ['auth:personnel','noindex'],
    'prefix' => '/personnel',
], function () {
    Route::livewire('/dashboard', 'personnel::dashboard')
        ->name('personnel.dashboard');

    Route::livewire('/profile', 'personnel::profile')
        ->name('personnel.profile');

    Route::livewire('/riwayat', 'personnel::riwayat-absensi')
        ->name('personnel.riwayat');

    Route::livewire('/jadwal', 'personnel::jadwal')
        ->name('personnel.jadwal');

    Route::livewire('/ajukan-cuti', 'personnel::ajukan-cuti')
        ->name('personnel.ajukan-cuti');
});
