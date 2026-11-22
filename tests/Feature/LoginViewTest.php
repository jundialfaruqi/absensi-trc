<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin login page renders white background, desktop-only card wrapper, and icons moved to labels', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('login-page-root')
        ->assertSee('bg-white')
        ->assertDontSee('bg-[#0a192f] p-4', false);

    $html = Livewire::test('login')
        ->call('load')
        ->assertOk()
        ->html();

    expect($html)
        ->toContain('login-card')
        ->toContain('background: transparent;')
        ->toContain('@media (min-width: 768px)')
        ->toContain('<span>Email</span>')
        ->toContain('<span>Password</span>')
        ->toContain('assets/logo/aman.webp')
        ->toContain('border-radius: 9999px;')
        ->toContain('rounded-full transition-all')
        ->toContain('box-shadow: none;')
        ->not->toContain('Kembali')
        ->not->toContain('login-back-link')
        ->not->toContain('rgba(220, 38, 38, 0.35)')
        ->not->toContain('Bantuan')
        ->not->toContain('Syarat &amp; Ketentuan')
        ->not->toContain('Dengan melanjutkan')
        ->not->toContain('pl-12 pr-4')
        ->not->toContain('pl-12 pr-12')
        ->not->toContain('glass-panel');
});
