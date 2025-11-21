<?php

declare(strict_types=1);

use App\Actions\Groups\UpdateGroupAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('updates group name', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new UpdateGroupAction;
    $updatedGroup = $action->handle($group, ['name' => 'Updated Name']);

    expect($updatedGroup->name)->toBe('Updated Name');
});

test('updates group description', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new UpdateGroupAction;
    $updatedGroup = $action->handle($group, ['description' => 'New Description']);

    expect($updatedGroup->description)->toBe('New Description');
});

test('updates multiple fields', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new UpdateGroupAction;
    $updatedGroup = $action->handle($group, [
        'name' => 'New Name',
        'description' => 'New Description',
    ]);

    expect($updatedGroup->name)->toBe('New Name');
    expect($updatedGroup->description)->toBe('New Description');
});
