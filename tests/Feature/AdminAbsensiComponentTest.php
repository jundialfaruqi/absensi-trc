<?php

use App\Models\User;
use App\Models\Opd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'manajemen-absensi', 'group' => 'Absensi']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($permission);
});

test('admin absensi component renders without month and year selects and has Terapkan and Reset buttons', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $yesterday = Carbon::yesterday()->format('Y-m-d');
    $defaultEnd = Carbon::yesterday()->addDays(10)->format('Y-m-d');

    Livewire::actingAs($user)
        ->test('admin::absensi')
        ->assertOk()
        ->assertSet('startDate', $yesterday)
        ->assertSet('endDate', $defaultEnd)
        ->assertSet('filterStartDate', $yesterday)
        ->assertSet('filterEndDate', $defaultEnd)
        ->assertSee('Terapkan')
        ->assertSee('Reset')
        ->assertDontSee('wire:model.live="month"', false)
        ->assertDontSee('wire:model.live="year"', false);
});

test('admin absensi date filter changes are deferred until applyFilter is called', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $yesterday = Carbon::yesterday()->format('Y-m-d');
    $defaultEnd = Carbon::yesterday()->addDays(10)->format('Y-m-d');

    $component = Livewire::actingAs($user)
        ->test('admin::absensi')
        ->assertSet('startDate', $yesterday)
        ->assertSet('endDate', $defaultEnd);

    // Change input values (deferred)
    $component->set('filterStartDate', '2026-08-01')
        ->set('filterEndDate', '2026-08-10')
        // startDate and endDate should remain unchanged before calling applyFilter
        ->assertSet('startDate', $yesterday)
        ->assertSet('endDate', $defaultEnd)
        // Call applyFilter
        ->call('applyFilter')
        ->assertSet('startDate', '2026-08-01')
        ->assertSet('endDate', '2026-08-10');
});

test('admin absensi resetFilters restores default dates', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $yesterday = Carbon::yesterday()->format('Y-m-d');
    $defaultEnd = Carbon::yesterday()->addDays(10)->format('Y-m-d');

    Livewire::actingAs($user)
        ->test('admin::absensi')
        ->set('filterStartDate', '2026-08-01')
        ->set('filterEndDate', '2026-08-10')
        ->call('applyFilter')
        ->assertSet('startDate', '2026-08-01')
        ->assertSet('endDate', '2026-08-10')
        ->call('resetFilters')
        ->assertSet('startDate', $yesterday)
        ->assertSet('endDate', $defaultEnd)
        ->assertSet('filterStartDate', $yesterday)
        ->assertSet('filterEndDate', $defaultEnd);
});
