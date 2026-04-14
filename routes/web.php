<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public Route
Route::livewire('/login', 'login')
    ->name('login');

// Admin Route
Route::group([
    'middleware' => ['auth'],
    'prefix' => '/page',
], function () {
    Route::livewire('/dashboard', 'admin::dashboard')
        ->name('dashboard');
});
