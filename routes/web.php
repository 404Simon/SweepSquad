<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'dashboard.index')
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

    // Cleaning Item routes
    Volt::route('cleaning-items/create/{groupId}/{parentId?}', 'cleaning-items.create')->name('cleaning-items.create');
    Volt::route('cleaning-items/{id}', 'cleaning-items.show')->name('cleaning-items.show');
    Volt::route('cleaning-items/{id}/edit', 'cleaning-items.edit')->name('cleaning-items.edit');
    Volt::route('cleaning-items/tree/{groupId}', 'cleaning-items.tree')->name('cleaning-items.tree');

    // Profile / Stats routes
    Volt::route('profile/stats', 'profile.stats')->name('profile.stats');
    Volt::route('profile/achievements', 'profile.achievements')->name('profile.achievements');
});

// Invite routes (accessible to all users, authenticated or not)
Volt::route('invites/{code}', 'invites.accept')->name('invites.accept');
