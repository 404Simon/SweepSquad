<?php

declare(strict_types=1);

use App\Actions\CleaningItems\MoveCleaningItemAction;
use App\Models\CleaningItem;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('moves an item to a different parent', function () {
    $group = Group::factory()->create();
    $parent1 = CleaningItem::factory()->create(['group_id' => $group->id]);
    $parent2 = CleaningItem::factory()->create(['group_id' => $group->id]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $parent1->id]);

    $action = new MoveCleaningItemAction();

    $movedItem = $action->handle($item, $parent2->id);

    expect($movedItem->parent_id)->toBe($parent2->id);
});

test('moves an item to root level', function () {
    $group = Group::factory()->create();
    $parent = CleaningItem::factory()->create(['group_id' => $group->id]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $parent->id]);

    $action = new MoveCleaningItemAction();

    $movedItem = $action->handle($item, null);

    expect($movedItem->parent_id)->toBeNull();
});

test('assigns correct order when moving item', function () {
    $group = Group::factory()->create();
    $parent1 = CleaningItem::factory()->create(['group_id' => $group->id]);
    $parent2 = CleaningItem::factory()->create(['group_id' => $group->id]);
    CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $parent2->id, 'order' => 0]);
    CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $parent2->id, 'order' => 1]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $parent1->id]);

    $action = new MoveCleaningItemAction();

    $movedItem = $action->handle($item, $parent2->id);

    expect($movedItem->order)->toBe(2);
});

test('throws exception when moving to parent in different group', function () {
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $item = CleaningItem::factory()->create(['group_id' => $group1->id]);
    $newParent = CleaningItem::factory()->create(['group_id' => $group2->id]);

    $action = new MoveCleaningItemAction();

    $action->handle($item, $newParent->id);
})->throws(InvalidArgumentException::class, 'Cannot move item to a parent in a different group.');

test('throws exception when moving item to itself', function () {
    $item = CleaningItem::factory()->create();

    $action = new MoveCleaningItemAction();

    $action->handle($item, $item->id);
})->throws(InvalidArgumentException::class, 'Cannot move item to itself.');

test('throws exception when moving item to its descendant', function () {
    $group = Group::factory()->create();
    $parent = CleaningItem::factory()->create(['group_id' => $group->id]);
    $child = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $parent->id]);
    $grandchild = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $child->id]);

    $action = new MoveCleaningItemAction();

    $action->handle($parent, $grandchild->id);
})->throws(InvalidArgumentException::class, 'Cannot move item to one of its descendants.');
