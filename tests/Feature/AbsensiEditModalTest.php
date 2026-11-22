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

test('saving edit with statusMasuk TELAT sets overall status to HADIR', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $opd = Opd::create(['name' => 'BPBD', 'code' => 'BPBD']);
    $personnel = Personnel::create([
        'name' => 'Rudi Hartono',
        'nik' => '1234567890123477',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'foto' => 'rudi.jpg',
        'email' => 'rudi@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-25';

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->set('statusMasuk', 'TELAT')
        ->set('jamMasuk', '08:45')
        ->set('statusPulang', 'HADIR')
        ->set('jamPulang', '16:00')
        ->set('alasanEdit', 'Perubahan absensi menjadi telat')
        ->call('saveEdit')
        ->assertDispatched('toast', message: 'Data absensi berhasil diperbarui', type: 'success');

    $saved = Absensi::where('personnel_id', $personnel->id)->whereDate('tanggal', $date)->first();
    expect($saved)->not->toBeNull()
        ->and($saved->status_masuk)->toBe('TELAT')
        ->and($saved->status_pulang)->toBe('HADIR')
        ->and($saved->status)->toBe('HADIR');
});

test('absensi edit modal sets Fleksibel and null times without shift fallback for flexible personnel', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Create default shift that would normally be picked by fallback
    Shift::create([
        'name' => 'P1',
        'type' => 'shift',
        'keterangan' => 'PAGI',
        'start_time' => '08:00',
        'end_time' => '20:00',
        'color' => '#22c55e',
    ]);

    $opd = Opd::create(['name' => 'Dinkes', 'code' => 'DINKES']);
    $personnel = Personnel::create([
        'name' => 'Dokter Maya',
        'nik' => '9999888877771234',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'email' => 'maya@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'FLEXIBLE',
    ]);

    $date = '2026-08-25';

    Livewire::actingAs($user)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSet('editingPersonnelId', $personnel->id)
        ->assertSet('jadwalShiftName', 'Fleksibel')
        ->assertSet('jadwalJamMasuk', null)
        ->assertSet('jadwalJamPulang', null)
        ->assertSee('Fleksibel')
        ->assertDontSee('P1 (PAGI)')
        ->assertDontSee('Jadwal: 08:00')
        ->assertDontSee('Jadwal: 20:00');
});

test('dev role can edit any OPD absensi without alasanEdit, clears all edit traces, and preserves updated_at', function () {
    $devRole = Role::firstOrCreate(['name' => 'dev', 'color' => '#8b5cf6']);
    $otherAdmin = User::factory()->create();
    $devUser = User::factory()->create();
    $devUser->assignRole($devRole);

    $opd = Opd::create(['name' => 'Dishub', 'code' => 'DISHUB']);
    $personnel = Personnel::create([
        'name' => 'Eko Prasetyo',
        'nik' => '1471000011112222',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'email' => 'eko@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $date = '2026-08-26';
    $originalUpdatedAt = Carbon::parse('2026-08-26 08:05:00');

    $existing = new Absensi([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'HADIR',
        'status_masuk' => 'TELAT',
        'jam_masuk' => '08:40:00',
        'edited_by_user_id' => $otherAdmin->id,
        'edited_at' => Carbon::parse('2026-08-26 09:00:00'),
        'alasan_edit' => 'Edit admin sebelumnya',
        'original_status_masuk' => 'ALPA',
        'original_status_pulang' => 'ALPA',
    ]);
    $existing->timestamps = false;
    $existing->created_at = Carbon::parse('2026-08-26 08:05:00');
    $existing->updated_at = $originalUpdatedAt;
    $existing->save();

    Carbon::setTestNow(Carbon::parse('2026-11-22 14:55:00'));

    Livewire::actingAs($devUser)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertDontSee('Alasan Perubahan / Keterangan')
        ->set('statusMasuk', 'HADIR')
        ->set('jamMasuk', '07:55')
        ->set('statusPulang', 'HADIR')
        ->set('jamPulang', '16:00')
        ->set('alasanEdit', '')
        ->call('saveEdit')
        ->assertHasNoErrors()
        ->assertDispatched('toast', message: 'Data absensi berhasil diperbarui', type: 'success');

    $saved = Absensi::find($existing->id);
    expect($saved->status)->toBe('HADIR')
        ->and($saved->status_masuk)->toBe('HADIR')
        ->and($saved->status_pulang)->toBe('HADIR')
        ->and($saved->edited_by_user_id)->toBeNull()
        ->and($saved->edited_at)->toBeNull()
        ->and($saved->alasan_edit)->toBeNull()
        ->and($saved->original_status_masuk)->toBeNull()
        ->and($saved->original_status_pulang)->toBeNull()
        ->and($saved->updated_at->format('Y-m-d H:i:s'))->toBe($originalUpdatedAt->format('Y-m-d H:i:s'));

    Carbon::setTestNow();
});

test('dev role can soft-delete absensi to trash with null deleted_by_user_id and untouched updated_at', function () {
    $devRole = Role::firstOrCreate(['name' => 'dev', 'color' => '#8b5cf6']);
    $devUser = User::factory()->create();
    $devUser->assignRole($devRole);

    $opd = Opd::create(['name' => 'Disdik', 'code' => 'DISDIK']);
    $personnel = Personnel::create([
        'name' => 'Fajar Nugroho',
        'nik' => '1471000011113333',
        'opd_id' => $opd->id,
        'penugasan_id' => 1,
        'email' => 'fajar@example.com',
        'password' => bcrypt('password'),
        'pin' => '123456',
        'attendance_type' => 'SCHEDULED',
    ]);

    $shift = Shift::create([
        'name' => 'P1',
        'type' => 'shift',
        'start_time' => '08:00',
        'end_time' => '16:00',
    ]);

    $date = '2026-08-27';
    Jadwal::create([
        'personnel_id' => $personnel->id,
        'shift_id' => $shift->id,
        'tanggal' => $date,
        'status' => 'SHIFT',
    ]);

    $originalUpdatedAt = Carbon::parse('2026-08-27 08:00:00');
    $absensi = new Absensi([
        'personnel_id' => $personnel->id,
        'tanggal' => $date,
        'status' => 'HADIR',
        'status_masuk' => 'HADIR',
        'jam_masuk' => '07:55:00',
    ]);
    $absensi->timestamps = false;
    $absensi->created_at = Carbon::parse('2026-08-27 08:00:00');
    $absensi->updated_at = $originalUpdatedAt;
    $absensi->save();

    Carbon::setTestNow(Carbon::parse('2026-11-22 14:58:00'));

    Livewire::actingAs($devUser)
        ->test('admin::absensi-edit-modal')
        ->call('open', $personnel->id, $date)
        ->assertSee('Hapus')
        ->call('resetAbsensi')
        ->assertDispatched('toast', message: 'Data absensi dipindahkan ke kotak sampah', type: 'success');

    $trashed = Absensi::onlyTrashed()->find($absensi->id);
    expect($trashed)->not->toBeNull()
        ->and($trashed->deleted_by_user_id)->toBeNull()
        ->and($trashed->deleted_at)->not->toBeNull()
        ->and($trashed->updated_at->format('Y-m-d H:i:s'))->toBe($originalUpdatedAt->format('Y-m-d H:i:s'));

    Carbon::setTestNow();
});
