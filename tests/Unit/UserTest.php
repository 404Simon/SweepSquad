<?php

declare(strict_types=1);

use App\Models\User;

test('addCoins increases total coins', function () {
    $user = User::factory()->create(['total_coins' => 100]);

    $user->addCoins(50);

    expect($user->fresh()->total_coins)->toBe(150);
});

test('addCoins works with zero initial coins', function () {
    $user = User::factory()->create(['total_coins' => 0]);

    $user->addCoins(25);

    expect($user->fresh()->total_coins)->toBe(25);
});

test('updateStreak sets streak to 1 for first cleaning', function () {
    $user = User::factory()->create([
        'current_streak' => 0,
        'last_cleaned_at' => null,
    ]);

    $user->updateStreak();

    $user = $user->fresh();
    expect($user->current_streak)->toBe(1);
    expect($user->last_cleaned_at)->not->toBeNull();
    expect($user->last_cleaned_at->isToday())->toBeTrue();
});

test('updateStreak increments streak when cleaning on consecutive days', function () {
    $user = User::factory()->create([
        'current_streak' => 5,
        'last_cleaned_at' => now()->subDay(),
    ]);

    $user->updateStreak();

    expect($user->fresh()->current_streak)->toBe(6);
});

test('updateStreak does not change streak when already cleaned today', function () {
    $user = User::factory()->create([
        'current_streak' => 3,
        'last_cleaned_at' => now(),
    ]);

    $user->updateStreak();

    expect($user->fresh()->current_streak)->toBe(3);
});

test('updateStreak resets streak to 1 when streak is broken', function () {
    $user = User::factory()->create([
        'current_streak' => 10,
        'last_cleaned_at' => now()->subDays(3),
    ]);

    $user->updateStreak();

    expect($user->fresh()->current_streak)->toBe(1);
});

test('resetStreak sets current streak to 0', function () {
    $user = User::factory()->create([
        'current_streak' => 15,
    ]);

    $user->resetStreak();

    expect($user->fresh()->current_streak)->toBe(0);
});

test('user has groups relationship', function () {
    $user = User::factory()->create();

    expect($user->groups())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

test('user has groupMemberships relationship', function () {
    $user = User::factory()->create();

    expect($user->groupMemberships())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user has cleaningLogs relationship', function () {
    $user = User::factory()->create();

    expect($user->cleaningLogs())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user has achievements relationship', function () {
    $user = User::factory()->create();

    expect($user->achievements())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user casts total_coins as integer', function () {
    $user = User::factory()->create(['total_coins' => '100']);

    expect($user->total_coins)->toBeInt();
    expect($user->total_coins)->toBe(100);
});

test('user casts current_streak as integer', function () {
    $user = User::factory()->create(['current_streak' => '5']);

    expect($user->current_streak)->toBeInt();
    expect($user->current_streak)->toBe(5);
});

test('user casts last_cleaned_at as datetime', function () {
    $user = User::factory()->create([
        'last_cleaned_at' => now(),
    ]);

    expect($user->last_cleaned_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});
