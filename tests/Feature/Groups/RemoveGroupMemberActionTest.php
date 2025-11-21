<?php

declare(strict_types=1);

use App\Actions\Groups\RemoveGroupMemberAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('removes member from group', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    $action = new RemoveGroupMemberAction;
    $action->handle($group, $member);

    expect($group->members()->count())->toBe(1);
    expect($group->members->contains($member->id))->toBeFalse();
});

test('owner can remove themselves', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new RemoveGroupMemberAction;
    $action->handle($group, $owner);

    expect($group->members()->count())->toBe(0);
});
