<?php

declare(strict_types=1);

use App\GroupRole;
use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can create a new group', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/groups/create')
        ->assertNoSmoke()
        ->assertSee('Create New Group')
        ->fill('name', 'My Test Group')
        ->fill('description', 'This is a test group description')
        ->click('Create Group')
        ->assertNoSmoke()
        ->assertSee('My Test Group')
        ->assertSee('This is a test group description')
        ->assertPathContains('/groups/');

    expect(Group::query()->where('name', 'My Test Group')->exists())->toBeTrue();
});

test('user can view groups list', function () {
    $user = User::factory()->create();
    $group1 = Group::factory()->create(['owner_id' => $user->id, 'name' => 'First Group']);
    $group2 = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Second Group']);

    $group1->members()->attach($user->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group2->members()->attach($user->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    actingAs($user);

    visit('/groups')
        ->assertNoSmoke()
        ->assertSee('My Groups')
        ->assertSee('First Group')
        ->assertSee('Second Group')
        ->assertSee('Groups I Own');
});

test('user can edit group details', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Original Name']);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    actingAs($user);

    visit("/groups/{$group->id}/edit")
        ->assertNoSmoke()
        ->assertSee('Edit Group')
        ->assertValue('name', 'Original Name')
        ->fill('name', 'Updated Name')
        ->fill('description', 'Updated description')
        ->click('Update Group')
        ->assertNoSmoke()
        ->assertSee('Updated Name')
        ->assertSee('Updated description')
        ->assertPathIs("/groups/{$group->id}");

    expect($group->fresh()->name)->toBe('Updated Name');
});

test('user can view group details', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['name' => 'John Doe']);
    $group = Group::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Test Group',
        'description' => 'Test Description',
    ]);

    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    actingAs($owner);

    visit("/groups/{$group->id}")
        ->assertNoSmoke()
        ->assertSee('Test Group')
        ->assertSee('Test Description')
        ->assertSee('Members')
        ->assertSee('John Doe')
        ->assertSee('2 members');
});

test('owner can delete group', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Group to Delete']);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    actingAs($user);

    visit("/groups/{$group->id}")
        ->assertNoSmoke()
        ->assertSee('Group to Delete')
        ->click('Delete Group')
        ->assertSee('Delete Group to Delete?')
        ->click('Delete')
        ->assertPathIs('/groups')
        ->assertNoSmoke();

    expect(Group::query()->find($group->id))->toBeNull();
});

test('member can leave group', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);

    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    actingAs($member);

    visit("/groups/{$group->id}")
        ->assertNoSmoke()
        ->assertSee('Leave Group')
        ->click('Leave Group')
        ->assertSee('Leave '.$group->name.'?')
        ->click('Leave')
        ->assertPathIs('/groups')
        ->assertNoSmoke();

    expect($group->members()->where('user_id', $member->id)->exists())->toBeFalse();
});

test('empty state shows when user has no groups', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/groups')
        ->assertNoSmoke()
        ->assertSee("You're not part of any groups yet")
        ->assertSee('Create Your First Group');
});

test('non-owner cannot see edit and delete buttons', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id, 'name' => 'Test Group']);

    $group->members()->attach($owner->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => GroupRole::Member, 'joined_at' => now()]);

    actingAs($member);

    visit("/groups/{$group->id}")
        ->assertNoSmoke()
        ->assertDontSee('Edit Group')
        ->assertDontSee('Delete Group')
        ->assertSee('Leave Group');
});

test('owner can see owner badge in group list', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner, 'joined_at' => now()]);

    actingAs($user);

    visit('/groups')
        ->assertNoSmoke()
        ->assertSee('Owner');
});
