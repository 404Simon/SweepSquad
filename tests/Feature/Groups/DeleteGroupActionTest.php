<?php

declare(strict_types=1);

use App\Actions\Groups\DeleteGroupAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('deletes a group', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new DeleteGroupAction;
    $action->handle($group);

    expect(Group::query()->find($group->id))->toBeNull();
});

test('deletes group and removes all memberships', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    $action = new DeleteGroupAction;
    $action->handle($group);

    expect(Group::query()->find($group->id))->toBeNull();
    expect($owner->groups()->count())->toBe(0);
    expect($member->groups()->count())->toBe(0);
});
