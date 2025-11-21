<?php

declare(strict_types=1);

use App\Actions\Groups\LeaveGroupAction;
use App\GroupRole;
use App\Models\Group;
use App\Models\User;

test('member can leave group', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    $action = new LeaveGroupAction;
    $action->handle($group, $member);

    expect($group->members()->count())->toBe(1);
    expect($group->members->contains($member->id))->toBeFalse();
});

test('owner cannot leave group', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    $action = new LeaveGroupAction;

    expect(fn () => $action->handle($group, $owner))
        ->toThrow(RuntimeException::class, 'The group owner cannot leave the group. Transfer ownership first.');
});

test('admin can leave group', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($admin->id, ['role' => GroupRole::Admin, 'joined_at' => now()]);

    $action = new LeaveGroupAction;
    $action->handle($group, $admin);

    expect($group->members()->count())->toBe(1);
    expect($group->members->contains($admin->id))->toBeFalse();
});
