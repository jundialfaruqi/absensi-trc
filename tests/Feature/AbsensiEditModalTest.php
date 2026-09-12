<?php

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Opd;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'manajemen-absensi', 'group' => 'Absensi']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($permission);
});

test('absensi edit modal sets jadwalJamMasuk and jadwalJamPulang on open based on personnel shift', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $opd = Opd::create(['name' => 'Badan Penanggulangan Bencana Daerah', 'code' => 'BPBD']);
    $personnel = Personnel::create([
        'name' => 'Budi Santoso',
        'nik' => '1234567890123456',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'budi.jpg',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $shift = Shift::create([
        'name' => 'P1',
        'type' => 'shift',
        'keterangan' => 'PAGI',
        'start_time' => '08:00',
        'end_time' => '20:00',
        'color' => '#22c55e',
    ]);

    $date = '2026-08-15';
    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => $date,
        'status' => 'KERJA',
    ]);

    Absensi::create([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'ALPA',
        'status_masuk' => 'ALPA',
        'status_pulang' => 'ALPA',
        'jam_masuk' => null,
        'jam_pulang' => null,
    ]);

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('editingPersonnelId', $personnel->id)
        ->assertSet('jadwalJamMasuk', '08:00')
        ->assertSet('jadwalJamPulang', '20:00')
        ->assertSet('statusMasuk', 'ALPA')
        ->assertSet('statusPulang', 'ALPA')
        ->assertSet('jamMasuk', null)
        ->assertSet('jamPulang', null)
        ->assertSee('P1 (PAGI)')
        ->assertSee('Jadwal: 08:00')
        ->assertSee('Jadwal: 20:00');
});

test('changing statusMasuk to HADIR auto-populates jamMasuk with jadwalJamMasuk when empty', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
    $personnel = Personnel::create([
        'name' => 'Siti Aminah',
        'nik' => '1234567890123457',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'siti.jpg',
        'email' => 'siti@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $shift = Shift::create([
        'name' => 'M1',
        'type' => 'shift',
        'keterangan' => 'MALAM',
        'start_time' => '20:00',
        'end_time' => '08:00',
        'color' => '#2563eb',
    ]);

    $date = '2026-08-16';
    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => $date,
        'status' => 'KERJA',
    ]);

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('jamMasuk', null)
        ->assertSet('jamPulang', null)
        ->set('statusMasuk', 'HADIR')
        ->assertSet('jamMasuk', '20:00')
        ->set('statusPulang', 'HADIR')
        ->assertSet('jamPulang', '08:00');
});

test('applyJadwalMasuk and applyJadwalPulang populate times and set status to HADIR', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
    $personnel = Personnel::create([
        'name' => 'Ahmad Dani',
        'nik' => '1234567890123458',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'ahmad.jpg',
        'email' => 'ahmad@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $shift = Shift::create([
        'name' => 'P1',
        'type' => 'shift',
        'keterangan' => 'PAGI',
        'start_time' => '08:00',
        'end_time' => '20:00',
        'color' => '#22c55e',
    ]);

    $date = '2026-08-17';
    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => $date,
        'status' => 'KERJA',
    ]);

    Absensi::create([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'ALPA',
        'status_masuk' => 'ALPA',
        'status_pulang' => 'ALPA',
        'jam_masuk' => null,
        'jam_pulang' => null,
    ]);

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->call('applyJadwalMasuk')
        ->assertSet('jamMasuk', '08:00')
        ->assertSet('statusMasuk', 'HADIR')
        ->call('applyJadwalPulang')
        ->assertSet('jamPulang', '20:00')
        ->assertSet('statusPulang', 'HADIR')
        ->set('alasanEdit', 'Dihadirkan oleh admin sesuai jadwal kerja shift')
        ->call('saveEdit')
        ->assertDispatched('close-modal', id: 'edit-absensi-modal');

    $saved = Absensi::where('personnel_id', $personnel->id)->whereDate('tanggal', $date)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->status_masuk)->toBe('HADIR')
        ->and($saved->status_pulang)->toBe('HADIR')
        ->and(Carbon::parse($saved->jam_masuk)->format('H:i'))->toBe('08:00')
        ->and(Carbon::parse($saved->jam_pulang)->format('H:i'))->toBe('20:00');
});

test('validation messages are in Indonesian and validation errors are reset on closeModal and open', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
    $personnel = Personnel::create([
        'name' => 'Siti Aminah',
        'nik' => '1234567890123457',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'siti.jpg',
        'email' => 'siti@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-16';

    $component = Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->set('statusMasuk', '')
        ->set('alasanEdit', '')
        ->call('saveEdit')
        ->assertHasErrors([
            'statusMasuk' => 'required',
            'alasanEdit' => 'required',
        ])
        ->assertSee('Status masuk wajib dipilih.')
        ->assertSee('Alasan perubahan data wajib diisi.');

    // When closeModal is called, errors should be cleared
    $component->call('closeModal')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', id: 'edit-absensi-modal');

    // Trigger validation error again
    $component->call('open', $personnel->id, $date)
        ->set('alasanEdit', 'abc') // min 5 characters
        ->call('saveEdit')
        ->assertHasErrors(['alasanEdit' => 'min'])
        ->assertSee('Alasan perubahan data minimal 5 karakter.');

    // Opening modal again resets errors
    $component->call('open', $personnel->id, $date)
        ->assertHasNoErrors();
});

test('user with edit-absensi-all-opd permission can open and edit attendance of any OPD personnel', function () {
    $permAll = Permission::firstOrCreate(['name' => 'edit-absensi-all-opd', 'group' => 'Absensi']);
    $user = User::factory()->create();
    $user->givePermissionTo($permAll);

    $opd1 = Opd::create(['name' => 'OPD Satu', 'code' => 'OPD1']);
    $opd2 = Opd::create(['name' => 'OPD Dua', 'code' => 'OPD2']);
    $user->opds()->attach($opd1->id);

    $personnel = Personnel::create([
        'name' => 'Pegawai OPD 2',
        'nik' => '1234567890123499',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'foto' => 'opd2.jpg',
        'email' => 'opd2@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-20';

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('editingPersonnelId', $personnel->id)
        ->set('statusMasuk', 'HADIR')
        ->set('alasanEdit', 'Perubahan absensi oleh admin all opd')
        ->call('saveEdit')
        ->assertDispatched('toast', message: 'Data absensi berhasil diperbarui', type: 'success');

    $saved = Absensi::where('personnel_id', $personnel->id)->whereDate('tanggal', $date)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->status_masuk)->toBe('HADIR');
});

test('user with edit-absensi-opd permission can open and edit attendance of own OPD personnel', function () {
    $permOpd = Permission::firstOrCreate(['name' => 'edit-absensi-opd', 'group' => 'Absensi']);
    $user = User::factory()->create();
    $user->givePermissionTo($permOpd);

    $opd1 = Opd::create(['name' => 'OPD Satu', 'code' => 'OPD1']);
    $user->opds()->attach($opd1->id);

    $personnel = Personnel::create([
        'name' => 'Pegawai OPD 1',
        'nik' => '1234567890123498',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'foto' => 'opd1.jpg',
        'email' => 'opd1@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-20';

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('editingPersonnelId', $personnel->id)
        ->set('statusMasuk', 'HADIR')
        ->set('alasanEdit', 'Perubahan absensi oleh admin opd sendiri')
        ->call('saveEdit')
        ->assertDispatched('toast', message: 'Data absensi berhasil diperbarui', type: 'success');

    $saved = Absensi::where('personnel_id', $personnel->id)->whereDate('tanggal', $date)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->status_masuk)->toBe('HADIR');
});

test('user with edit-absensi-opd permission cannot open or edit attendance of other OPD personnel', function () {
    $permOpd = Permission::firstOrCreate(['name' => 'edit-absensi-opd', 'group' => 'Absensi']);
    $user = User::factory()->create();
    $user->givePermissionTo($permOpd);

    $opd1 = Opd::create(['name' => 'OPD Satu', 'code' => 'OPD1']);
    $opd2 = Opd::create(['name' => 'OPD Dua', 'code' => 'OPD2']);
    $user->opds()->attach($opd1->id);

    $personnel = Personnel::create([
        'name' => 'Pegawai OPD 2',
        'nik' => '1234567890123497',
        'opd_id' => $opd2->id,
        'penugasan_id' => 1,
        'foto' => 'opd2b.jpg',
        'email' => 'opd2b@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-20';

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('editingPersonnelId', null)
        ->assertDispatched('close-modal', id: 'edit-absensi-modal')
        ->assertDispatched('toast', message: 'Anda tidak memiliki izin untuk mengedit absensi ini.', type: 'error');
});

test('user without edit-absensi-all-opd or edit-absensi-opd cannot open or edit attendance', function () {
    $user = User::factory()->create();

    $opd1 = Opd::create(['name' => 'OPD Satu', 'code' => 'OPD1']);
    $user->opds()->attach($opd1->id);

    $personnel = Personnel::create([
        'name' => 'Pegawai OPD 1',
        'nik' => '1234567890123496',
        'opd_id' => $opd1->id,
        'penugasan_id' => 1,
        'foto' => 'opd1c.jpg',
        'email' => 'opd1c@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-20';

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('editingPersonnelId', null)
        ->assertDispatched('close-modal', id: 'edit-absensi-modal')
        ->assertDispatched('toast', message: 'Anda tidak memiliki izin untuk mengedit absensi ini.', type: 'error');
});
