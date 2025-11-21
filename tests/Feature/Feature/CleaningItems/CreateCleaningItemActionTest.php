<?php

declare(strict_types=1);

use App\Actions\CleaningItems\CreateCleaningItemAction;
use App\Models\CleaningItem;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates a cleaning item with all fields', function () {
    $group = Group::factory()->create();
    $action = new CreateCleaningItemAction();

    $item = $action->handle(
        group: $group,
        name: 'Living Room',
        description: 'Main living area',
        cleaningFrequencyHours: 24,
        baseCoinReward: 100,
    );

    expect($item)->toBeInstanceOf(CleaningItem::class)
        ->and($item->name)->toBe('Living Room')
        ->and($item->description)->toBe('Main living area')
        ->and($item->cleaning_frequency_hours)->toBe(24)
        ->and($item->base_coin_reward)->toBe(100)
        ->and($item->group_id)->toBe($group->id)
        ->and($item->parent_id)->toBeNull();
});

test('creates a cleaning item with minimal fields', function () {
    $group = Group::factory()->create();
    $action = new CreateCleaningItemAction();

    $item = $action->handle(
        group: $group,
        name: 'Kitchen',
    );

    expect($item)->toBeInstanceOf(CleaningItem::class)
        ->and($item->name)->toBe('Kitchen')
        ->and($item->description)->toBeNull()
        ->and($item->cleaning_frequency_hours)->toBeNull()
        ->and($item->base_coin_reward)->toBe(0);
});

test('creates a child cleaning item', function () {
    $group = Group::factory()->create();
    $parent = CleaningItem::factory()->create(['group_id' => $group->id]);
    $action = new CreateCleaningItemAction();

    $child = $action->handle(
        group: $group,
        name: 'Kitchen Counter',
        parentId: $parent->id,
    );

    expect($child->parent_id)->toBe($parent->id)
        ->and($child->group_id)->toBe($group->id);
});

test('assigns correct order when creating items', function () {
    $group = Group::factory()->create();
    $action = new CreateCleaningItemAction();

    $item1 = $action->handle($group, 'Item 1');
    $item2 = $action->handle($group, 'Item 2');
    $item3 = $action->handle($group, 'Item 3');

    expect($item1->order)->toBe(0)
        ->and($item2->order)->toBe(1)
        ->and($item3->order)->toBe(2);
});

test('throws exception when parent belongs to different group', function () {
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $parent = CleaningItem::factory()->create(['group_id' => $group1->id]);
    $action = new CreateCleaningItemAction();

    $action->handle(
        group: $group2,
        name: 'Child Item',
        parentId: $parent->id,
    );
})->throws(InvalidArgumentException::class, 'Parent item must belong to the same group.');
