<?php

use App\Models\User;
use App\Models\Opd;
use App\Models\DokumentasiKonsumsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'lihat-dokumentasi-konsumsi', 'group' => 'Dokumentasi Konsumsi']);
    $role = Role::firstOrCreate(['name' => 'super-admin', 'color' => '#ef4444']);
    $role->givePermissionTo($permission);
});

test('dokumentasi konsumsi component renders skeleton loading and instant modal hooks', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->assertOk()
        ->assertSeeHtml('openKonsumsiModalInstantly')
        ->assertSeeHtml('isKonsumsiModalLoading')
        ->assertSeeHtml('isKonsumsiModalOpen')
        ->assertSeeHtml('closeKonsumsiModal')
        ->assertSeeHtml('submitWithCrop')
        ->assertSeeHtml("cropRatioSiang: 'original'");
});

test('openAddKonsumsiModal opens modal and sets default create properties', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = Carbon::today()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->assertSet('showAddModal', false)
        ->call('openAddKonsumsiModal', $targetDate, 'siang')
        ->assertSet('showAddModal', true)
        ->assertSet('modalMode', 'create')
        ->assertSet('uploadTanggal', $targetDate)
        ->assertSet('sesiKonsumsi', 'siang')
        ->call('closeAddKonsumsiModal')
        ->assertSet('showAddModal', false);
});

test('openEditKonsumsiModal opens modal with edit mode and loads existing data', function () {
    $opd = Opd::create(['name' => 'BPBD']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $targetDate = Carbon::today()->format('Y-m-d');

    DokumentasiKonsumsi::create([
        'opd_id' => $opd->id,
        'tanggal' => $targetDate,
        'foto_siang' => 'dokumentasi-konsumsi/test_siang.webp',
        'jumlah_siang' => 5,
    ]);

    Livewire::actingAs($user)
        ->test('admin::dokumentasi-konsumsi')
        ->set('selectedOpd', $opd->id)
        ->call('openEditKonsumsiModal', $targetDate, 'siang')
        ->assertSet('showAddModal', true)
        ->assertSet('modalMode', 'edit')
        ->assertSet('uploadTanggal', $targetDate)
        ->assertSet('sesiKonsumsi', 'siang')
        ->assertSet('existingFotoSiang', 'dokumentasi-konsumsi/test_siang.webp')
        ->assertSeeHtml("cropRatioSiang === 'original' ? 'object-contain' : 'object-cover'")
        ->assertSeeHtml("x-text=\"getBadgeRatioText('fotoSiang')\"")
        ->assertSeeHtml("rotatePhoto('fotoSiang')")
        ->assertSeeHtml("toggleFlipPhoto('fotoSiang', 'H')")
        ->assertSeeHtml("toggleFlipPhoto('fotoSiang', 'V')")
        ->assertSeeHtml("resetOrientation('fotoSiang')")
        ->call('closeAddKonsumsiModal')
        ->assertSet('showAddModal', false);
});

