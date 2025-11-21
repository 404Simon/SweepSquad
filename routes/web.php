<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Group routes
    Volt::route('groups', 'groups.index')->name('groups.index');
    Volt::route('groups/create', 'groups.create')->name('groups.create');
    Volt::route('groups/{id}', 'groups.show')->name('groups.show');
    Volt::route('groups/{id}/edit', 'groups.edit')->name('groups.edit');
});

// Invite routes (accessible to all users, authenticated or not)
Volt::route('invite/{uuid}', 'invites.accept')->name('invites.accept');
