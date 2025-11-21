<?php

declare(strict_types=1);

use App\Actions\CleaningItems\MarkAsCleanedAction;
use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'current_streak' => 0,
        'total_coins' => 0,
    ]);

    $group = Group::factory()->create(['owner_id' => $this->user->id]);
    $group->members()->attach($this->user, ['role' => 'owner']);

    $this->item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => null,
    ]);

    $this->action = new MarkAsCleanedAction();
});

test('marks item as cleaned and creates log entry', function () {
    $log = $this->action->handle($this->item, $this->user);

    expect($log)->toBeInstanceOf(CleaningLog::class)
        ->and($log->cleaning_item_id)->toBe($this->item->id)
        ->and($log->user_id)->toBe($this->user->id)
        ->and($log->group_id)->toBe($this->item->group_id);

    $this->item->refresh();
    expect($this->item->last_cleaned_at)->not->toBeNull()
        ->and($this->item->last_cleaned_by)->toBe($this->user->id);
});

test('awards base coins for new item at 100% dirtiness', function () {
    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Perfect Clean bonus 25% = 125 coins
    expect($log->coins_earned)->toBe(125);

    $this->user->refresh();
    expect($this->user->total_coins)->toBe(125);
});

test('applies streak bonus of 10% for 7+ day streak', function () {
    $this->user->update(['current_streak' => 7]);

    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Perfect Clean 25% + Streak 10% = 135 coins
    expect($log->coins_earned)->toBe(135);
});

test('applies streak bonus of 20% for 14+ day streak', function () {
    $this->user->update(['current_streak' => 14]);

    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Perfect Clean 25% + Streak 20% = 145 coins
    expect($log->coins_earned)->toBe(145);
});

test('applies speed bonus of 5% when cleaning before 80% dirtiness', function () {
    // Set last cleaned to 10 hours ago (out of 24 hours = 41.67% dirtiness)
    $this->item->update(['last_cleaned_at' => now()->subHours(10)]);

    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Speed bonus 5% = 105 coins
    expect($log->coins_earned)->toBe(105);
});

test('does not apply perfect clean bonus when under 100% dirtiness', function () {
    $this->item->update(['last_cleaned_at' => now()->subHours(10)]);

    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Speed bonus 5% (no perfect clean) = 105 coins
    expect($log->coins_earned)->toBe(105);
});

test('applies all bonuses when applicable', function () {
    // Set streak to 14+ days and item never cleaned (100% dirtiness)
    $this->user->update(['current_streak' => 14]);

    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Perfect Clean 25% + Streak 20% = 145 coins
    expect($log->coins_earned)->toBe(145);
});

test('updates user streak on first cleaning', function () {
    expect($this->user->current_streak)->toBe(0);

    $this->action->handle($this->item, $this->user);

    $this->user->refresh();
    expect($this->user->current_streak)->toBe(1)
        ->and($this->user->last_cleaned_at)->not->toBeNull();
});

test('increments streak when cleaning on consecutive days', function () {
    $this->user->update([
        'current_streak' => 5,
        'last_cleaned_at' => now()->subDay(),
    ]);

    $this->action->handle($this->item, $this->user);

    $this->user->refresh();
    expect($this->user->current_streak)->toBe(6);
});

test('resets streak when days are skipped', function () {
    $this->user->update([
        'current_streak' => 10,
        'last_cleaned_at' => now()->subDays(3),
    ]);

    $this->action->handle($this->item, $this->user);

    $this->user->refresh();
    expect($this->user->current_streak)->toBe(1);
});

test('stores dirtiness percentage in log', function () {
    $this->item->update(['last_cleaned_at' => now()->subHours(18)]);

    $log = $this->action->handle($this->item, $this->user);

    // 18 hours out of 24 = 75% dirtiness (approximately)
    expect($log->dirtiness_at_clean)->toBeGreaterThanOrEqual(74.9)
        ->and($log->dirtiness_at_clean)->toBeLessThanOrEqual(75.1);
});

test('stores optional notes in log', function () {
    $log = $this->action->handle($this->item, $this->user, 'Deep cleaned with vinegar');

    expect($log->notes)->toBe('Deep cleaned with vinegar');
});

test('handles multiple bonuses with speed and streak', function () {
    $this->user->update(['current_streak' => 7]);
    $this->item->update(['last_cleaned_at' => now()->subHours(10)]);

    $log = $this->action->handle($this->item, $this->user);

    // Base 100 + Speed 5% + Streak 10% = 115 coins
    expect($log->coins_earned)->toBe(115);
});
