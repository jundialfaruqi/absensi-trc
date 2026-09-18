<?php

use App\Models\User;
use App\Models\Setting;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'manajemen-pengaturan', 'group' => 'Pengaturan']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($permission);
});

test('pengaturan component loads default konsumsi settings and saves new values', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Load component and assert initial default values
    $test = Livewire::actingAs($user)
        ->test('admin::pengaturan')
        ->assertSet('konsumsiSiangMulai', '06:00')
        ->assertSet('konsumsiSiangSelesai', '15:59')
        ->assertSet('konsumsiMalamMulai', '16:00')
        ->assertSet('konsumsiMalamSelesai', '05:59');

    // Update and save new values
    $test->set('konsumsiSiangMulai', '07:00')
        ->set('konsumsiSiangSelesai', '14:30')
        ->set('konsumsiMalamMulai', '17:00')
        ->set('konsumsiMalamSelesai', '04:00')
        ->call('saveKonsumsiSettings')
        ->assertDispatched('toast', type: 'success');

    // Assert values in database
    expect(Setting::get('konsumsi_siang_mulai'))->toBe('07:00');
    expect(Setting::get('konsumsi_siang_selesai'))->toBe('14:30');
    expect(Setting::get('konsumsi_malam_mulai'))->toBe('17:00');
    expect(Setting::get('konsumsi_malam_selesai'))->toBe('04:00');
});
