<?php

declare(strict_types=1);

use App\Jobs\UpdateUserStreaksJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('resets streak for users who have not cleaned in more than 24 hours', function () {
    // Create a user who cleaned 3 days ago with a streak of 5
    $user = User::factory()->create([
        'last_cleaned_at' => now()->subDays(3),
        'current_streak' => 5,
    ]);

    // Run the job
    (new UpdateUserStreaksJob)->handle();

    // Streak should be reset to 0
    expect($user->fresh()->current_streak)->toBe(0);
});

test('maintains streak for users who cleaned yesterday', function () {
    // Create a user who cleaned yesterday with a streak of 3
    $user = User::factory()->create([
        'last_cleaned_at' => now()->subDay()->setTime(12, 0),
        'current_streak' => 3,
    ]);

    // Run the job
    (new UpdateUserStreaksJob)->handle();

    // Streak should remain unchanged
    expect($user->fresh()->current_streak)->toBe(3);
});

test('maintains streak for users who cleaned today', function () {
    // Create a user who cleaned today with a streak of 7
    $user = User::factory()->create([
        'last_cleaned_at' => now()->setTime(8, 0),
        'current_streak' => 7,
    ]);

    // Run the job
    (new UpdateUserStreaksJob)->handle();

    // Streak should remain unchanged
    expect($user->fresh()->current_streak)->toBe(7);
});

test('does not affect users who have never cleaned', function () {
    // Create a user who has never cleaned
    $user = User::factory()->create([
        'last_cleaned_at' => null,
        'current_streak' => 0,
    ]);

    // Run the job
    (new UpdateUserStreaksJob)->handle();

    // Should remain unchanged
    expect($user->fresh()->current_streak)->toBe(0);
    expect($user->fresh()->last_cleaned_at)->toBeNull();
});

test('does not affect users with zero streak', function () {
    // Create a user with last_cleaned_at but already zero streak
    $user = User::factory()->create([
        'last_cleaned_at' => now()->subDays(5),
        'current_streak' => 0,
    ]);

    // Run the job
    (new UpdateUserStreaksJob)->handle();

    // Should remain unchanged
    expect($user->fresh()->current_streak)->toBe(0);
});

test('processes multiple users correctly', function () {
    // User with expired streak
    $userExpired = User::factory()->create([
        'last_cleaned_at' => now()->subDays(2),
        'current_streak' => 5,
    ]);

    // User with valid streak from yesterday
    $userValid = User::factory()->create([
        'last_cleaned_at' => now()->subDay()->setTime(10, 0),
        'current_streak' => 3,
    ]);

    // User with valid streak from today
    $userToday = User::factory()->create([
        'last_cleaned_at' => now()->setTime(14, 0),
        'current_streak' => 10,
    ]);

    // Run the job
    (new UpdateUserStreaksJob)->handle();

    // Check results
    expect($userExpired->fresh()->current_streak)->toBe(0);
    expect($userValid->fresh()->current_streak)->toBe(3);
    expect($userToday->fresh()->current_streak)->toBe(10);
});
