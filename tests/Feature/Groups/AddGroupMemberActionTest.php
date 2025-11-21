<?php

declare(strict_types=1);

use App\Actions\Groups\AddGroupMemberAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('adds member to group', function () {
    $owner = User::factory()->create();
    $newMember = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new AddGroupMemberAction;
    $membership = $action->handle($group, $newMember);

    expect($group->members()->count())->toBe(2);
    expect($group->members->contains($newMember->id))->toBeTrue();
    expect($membership->role)->toBe(GroupRole::Member);
});

test('adds member with specific role', function () {
    $owner = User::factory()->create();
    $newAdmin = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new AddGroupMemberAction;
    $membership = $action->handle($group, $newAdmin, GroupRole::Admin);

    expect($membership->role)->toBe(GroupRole::Admin);
});

test('adds member with joined_at timestamp', function () {
    $owner = User::factory()->create();
    $newMember = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new AddGroupMemberAction;
    $membership = $action->handle($group, $newMember);

    expect($membership->joined_at)->not->toBeNull();
});
