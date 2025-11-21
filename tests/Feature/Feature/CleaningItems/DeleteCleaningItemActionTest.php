<?php

declare(strict_types=1);

use App\Actions\CleaningItems\DeleteCleaningItemAction;
use App\Models\CleaningItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deletes a cleaning item', function () {
    $item = CleaningItem::factory()->create();
    $action = new DeleteCleaningItemAction();

    $action->handle($item);

    expect(CleaningItem::query()->find($item->id))->toBeNull();
});

test('deletes a cleaning item and its children through cascade', function () {
    $parent = CleaningItem::factory()->create();
    $child = CleaningItem::factory()->create(['parent_id' => $parent->id]);
    $grandchild = CleaningItem::factory()->create(['parent_id' => $child->id]);

    $action = new DeleteCleaningItemAction();

    $action->handle($parent);

    expect(CleaningItem::query()->find($parent->id))->toBeNull()
        ->and(CleaningItem::query()->find($child->id))->toBeNull()
        ->and(CleaningItem::query()->find($grandchild->id))->toBeNull();
});
