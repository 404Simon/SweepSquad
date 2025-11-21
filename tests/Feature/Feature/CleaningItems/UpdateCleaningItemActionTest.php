<?php

declare(strict_types=1);

use App\Actions\CleaningItems\UpdateCleaningItemAction;
use App\Models\CleaningItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('updates a cleaning item with all fields', function () {
    $item = CleaningItem::factory()->create([
        'name' => 'Old Name',
        'description' => 'Old Description',
        'cleaning_frequency_hours' => 48,
        'base_coin_reward' => 50,
    ]);

    $action = new UpdateCleaningItemAction();

    $updatedItem = $action->handle(
        item: $item,
        name: 'New Name',
        description: 'New Description',
        cleaningFrequencyHours: 24,
        baseCoinReward: 100,
    );

    expect($updatedItem->name)->toBe('New Name')
        ->and($updatedItem->description)->toBe('New Description')
        ->and($updatedItem->cleaning_frequency_hours)->toBe(24)
        ->and($updatedItem->base_coin_reward)->toBe(100);
});

test('updates a cleaning item with null description', function () {
    $item = CleaningItem::factory()->create([
        'name' => 'Test',
        'description' => 'Old Description',
    ]);

    $action = new UpdateCleaningItemAction();

    $updatedItem = $action->handle(
        item: $item,
        name: 'Test Updated',
        description: null,
    );

    expect($updatedItem->description)->toBeNull();
});

test('persists changes to database', function () {
    $item = CleaningItem::factory()->create(['name' => 'Old Name']);
    $action = new UpdateCleaningItemAction();

    $action->handle(
        item: $item,
        name: 'New Name',
    );

    expect($item->fresh()->name)->toBe('New Name');
});
