<?php

declare(strict_types=1);

use App\Actions\Groups\TransferOwnershipAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('transfers ownership to another member', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $oldOwner->id]);
    $group->members()->attach($oldOwner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($newOwner->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    $action = new TransferOwnershipAction;
    $updatedGroup = $action->handle($group, $newOwner);

    expect($updatedGroup->owner_id)->toBe($newOwner->id);
});

test('updates old owner role to admin', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $oldOwner->id]);
    $group->members()->attach($oldOwner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($newOwner->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    $action = new TransferOwnershipAction;
    $updatedGroup = $action->handle($group, $newOwner);

    $oldOwnerMembership = $updatedGroup->members()->where('user_id', $oldOwner->id)->first();
    expect($oldOwnerMembership->pivot->role)->toBe(GroupRole::Admin);
});

test('updates new owner role to owner', function () {
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $oldOwner->id]);
    $group->members()->attach($oldOwner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($newOwner->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    $action = new TransferOwnershipAction;
    $updatedGroup = $action->handle($group, $newOwner);

    $newOwnerMembership = $updatedGroup->members()->where('user_id', $newOwner->id)->first();
    expect($newOwnerMembership->pivot->role)->toBe(GroupRole::Owner);
});
