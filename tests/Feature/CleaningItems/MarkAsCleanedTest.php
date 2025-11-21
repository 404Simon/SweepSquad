<?php

declare(strict_types=1);

use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->group = Group::factory()->create(['owner_id' => $this->user->id]);
    $this->group->members()->attach($this->user, ['role' => 'owner']);

    $this->item = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
    ]);
});

test('authenticated user can mark item as cleaned', function () {
    $this->actingAs($this->user);

    expect(CleaningLog::count())->toBe(0)
        ->and($this->user->total_coins)->toBe(0);

    $log = app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    expect(CleaningLog::count())->toBe(1)
        ->and($log)->toBeInstanceOf(CleaningLog::class);

    $this->user->refresh();
    expect($this->user->total_coins)->toBeGreaterThan(0);
});

test('cleaning item is updated with last cleaned timestamp', function () {
    $this->actingAs($this->user);

    expect($this->item->last_cleaned_at)->toBeNull()
        ->and($this->item->last_cleaned_by)->toBeNull();

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    $this->item->refresh();
    expect($this->item->last_cleaned_at)->not->toBeNull()
        ->and($this->item->last_cleaned_by)->toBe($this->user->id);
});

test('cleaning log contains correct data', function () {
    $this->actingAs($this->user);

    $log = app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user, 'Test note');

    expect($log->cleaning_item_id)->toBe($this->item->id)
        ->and($log->user_id)->toBe($this->user->id)
        ->and($log->group_id)->toBe($this->group->id)
        ->and($log->notes)->toBe('Test note')
        ->and($log->dirtiness_at_clean)->toBeFloat()
        ->and($log->coins_earned)->toBeInt()
        ->and($log->cleaned_at)->not->toBeNull();
});

test('user streak is updated when marking as cleaned', function () {
    $this->actingAs($this->user);

    expect($this->user->current_streak)->toBe(0);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    $this->user->refresh();
    expect($this->user->current_streak)->toBe(1);
});

test('multiple cleanings track correctly', function () {
    $this->actingAs($this->user);

    $item2 = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'base_coin_reward' => 50,
    ]);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($item2, $this->user);

    expect(CleaningLog::count())->toBe(2);

    $this->user->refresh();
    expect($this->user->total_coins)->toBeGreaterThan(100);
});

test('cleaning logs can be filtered by user', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();
    $this->group->members()->attach($otherUser, ['role' => 'member']);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $otherUser);

    expect(CleaningLog::forUser($this->user->id)->count())->toBe(1)
        ->and(CleaningLog::forUser($otherUser->id)->count())->toBe(1);
});

test('cleaning logs can be filtered by item', function () {
    $this->actingAs($this->user);

    $item2 = CleaningItem::factory()->create(['group_id' => $this->group->id]);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($item2, $this->user);

    expect(CleaningLog::forItem($this->item->id)->count())->toBe(1)
        ->and(CleaningLog::forItem($item2->id)->count())->toBe(1);
});

test('cleaning logs can be filtered by group', function () {
    $this->actingAs($this->user);

    $group2 = Group::factory()->create(['owner_id' => $this->user->id]);
    $group2->members()->attach($this->user, ['role' => 'owner']);

    $item2 = CleaningItem::factory()->create(['group_id' => $group2->id]);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($item2, $this->user);

    expect(CleaningLog::forGroup($this->group->id)->count())->toBe(1)
        ->and(CleaningLog::forGroup($group2->id)->count())->toBe(1);
});

test('cleaning logs are returned in recent order', function () {
    $this->actingAs($this->user);

    $item2 = CleaningItem::factory()->create(['group_id' => $this->group->id]);

    $log1 = app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($this->item, $this->user);

    sleep(1);

    $log2 = app(App\Actions\CleaningItems\MarkAsCleanedAction::class)
        ->handle($item2, $this->user);

    $logs = CleaningLog::recent()->get();

    expect($logs->first()->id)->toBe($log2->id)
        ->and($logs->last()->id)->toBe($log1->id);
});
