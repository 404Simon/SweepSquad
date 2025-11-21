<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\GroupRole;
use App\InviteType;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can accept permanent invite link', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id, 'name' => 'Test Family']);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);

    $invite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $owner->id,
        'type' => InviteType::Permanent,
        'code' => 'TESTCODE123',
    ]);

    $newUser = User::factory()->create();
    actingAs($newUser);

    visit('/invites/TESTCODE123')
        ->assertNoSmoke()
        ->assertSee('Test Family')
        ->assertSee('Join Group')
        ->click('Join Group')
        ->assertPathIs('/groups/'.$group->id)
        ->assertSee('Test Family');
});

test('user sees error for invalid invite code', function () {
    $user = User::factory()->create();
    actingAs($user);

    visit('/invites/INVALIDCODE')
        ->assertNoSmoke()
        ->assertSee('Invalid invite');
});

test('user sees error for expired invite', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);

    $invite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $owner->id,
        'type' => InviteType::TimeLimited,
        'code' => 'EXPIRED123',
        'expires_at' => now()->subDay(),
    ]);

    $newUser = User::factory()->create();
    actingAs($newUser);

    visit('/invites/EXPIRED123')
        ->assertNoSmoke()
        ->assertSee('expired');
});

test('user can see group members before accepting invite', function () {
    $owner = User::factory()->create(['name' => 'Group Owner']);
    $member = User::factory()->create(['name' => 'Existing Member']);
    $group = Group::factory()->create(['owner_id' => $owner->id, 'name' => 'Active Household']);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member]);

    $invite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $owner->id,
        'type' => InviteType::Permanent,
        'code' => 'PREVIEW123',
    ]);

    $newUser = User::factory()->create();
    actingAs($newUser);

    visit('/invites/PREVIEW123')
        ->assertNoSmoke()
        ->assertSee('Active Household')
        ->assertSee('Group Owner')
        ->assertSee('Existing Member');
});

test('owner can create and share invite link from group page', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id, 'name' => 'My Group']);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);

    actingAs($owner);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('Invite Members')
        ->click('Invite Members')
        ->assertSee('Invite Link');
});

test('member cannot see invite management', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member]);

    actingAs($member);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertDontSee('Invite Members');
});

test('already joined user sees message when visiting invite', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Joined Group']);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    $invite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $user->id,
        'type' => InviteType::Permanent,
        'code' => 'ALREADY123',
    ]);

    actingAs($user);

    visit('/invites/ALREADY123')
        ->assertNoSmoke()
        ->assertSee('already a member');
});

test('single use invite becomes invalid after first use', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id, 'name' => 'Single Use Group']);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);

    $invite = GroupInvite::factory()->create([
        'group_id' => $group->id,
        'created_by' => $owner->id,
        'type' => InviteType::SingleUse,
        'code' => 'ONCE123',
    ]);

    // First user accepts
    $user1 = User::factory()->create();
    actingAs($user1);

    visit('/invites/ONCE123')
        ->assertNoSmoke()
        ->click('Join Group')
        ->assertPathIs('/groups/'.$group->id);

    // Second user tries to use same invite
    $user2 = User::factory()->create();
    actingAs($user2);

    visit('/invites/ONCE123')
        ->assertNoSmoke()
        ->assertSee('no longer valid');
});
