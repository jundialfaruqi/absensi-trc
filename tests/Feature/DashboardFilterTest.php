<?php

use App\Models\User;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Shift;
use App\Models\Konsumsi;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Penugasan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'lihat-dashboard', 'group' => 'Dashboard']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($permission);
});

test('dashboard filter siang and malam includes flexible personnel based on actual attendance hours', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $opd = Opd::create(['name' => 'Dinkes', 'singkatan' => 'DINKES', 'warna' => '#22c55e']);
    $penugasan = Penugasan::create(['name' => 'Medis', 'opd_id' => $opd->id]);

    $today = Carbon::today()->format('Y-m-d');

    // 1. Personel Fleksibel Siang
    $personnelSiang = Personnel::create([
        'name' => 'Budi Siang Flex',
        'nik' => '1234567890123451',
        'nomor_hp' => '081234567891',
        'email' => 'budi.flex@test.com',
        'password' => bcrypt('password'),
        'pin' => bcrypt('123456'),
        'opd_id' => $opd->id,
        'penugasan_id' => $penugasan->id,
        'attendance_type' => 'FLEXIBLE',
    ]);
    Absensi::create([
        'personnel_id' => $personnelSiang->id,
        'tanggal' => $today,
        'jam_masuk' => $today . ' 08:00:00',
        'jam_pulang' => $today . ' 14:00:00',
        'status' => 'HADIR',
        'status_masuk' => 'HADIR',
        'status_pulang' => 'HADIR',
    ]);

    // 2. Personel Fleksibel Malam
    $personnelMalam = Personnel::create([
        'name' => 'Siti Malam Flex',
        'nik' => '1234567890123452',
        'nomor_hp' => '081234567892',
        'email' => 'siti.flex@test.com',
        'password' => bcrypt('password'),
        'pin' => bcrypt('123456'),
        'opd_id' => $opd->id,
        'penugasan_id' => $penugasan->id,
        'attendance_type' => 'FLEXIBLE',
    ]);
    Absensi::create([
        'personnel_id' => $personnelMalam->id,
        'tanggal' => $today,
        'jam_masuk' => $today . ' 19:30:00',
        'jam_pulang' => $today . ' 23:30:00',
        'status' => 'HADIR',
        'status_masuk' => 'HADIR',
        'status_pulang' => 'HADIR',
    ]);

    // Test Filter Siang
    Livewire::actingAs($user)
        ->test('admin::dashboard')
        ->call('load')
        ->set('filterShift', 'siang')
        ->assertSee('Budi Siang Flex')
        ->assertDontSee('Siti Malam Flex');

    // Test Filter Malam
    Livewire::actingAs($user)
        ->test('admin::dashboard')
        ->call('load')
        ->set('filterShift', 'malam')
        ->assertSee('Siti Malam Flex')
        ->assertDontSee('Budi Siang Flex');

    // Test Filter Flexible
    Livewire::actingAs($user)
        ->test('admin::dashboard')
        ->call('load')
        ->set('filterShift', 'flexible')
        ->assertSee('Budi Siang Flex')
        ->assertSee('Siti Malam Flex');
});
