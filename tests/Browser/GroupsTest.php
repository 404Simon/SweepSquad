<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\GroupRole;
use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can view groups index', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/groups')
        ->assertNoSmoke()
        ->assertSee('My Groups');
});

test('user can access group creation page', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/groups/create')
        ->assertNoSmoke()
        ->assertSee('Create Group')
        ->assertSee('Name');
});

test('user can view group details', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'My Household']);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    actingAs($user);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('My Household')
        ->assertSee('Owner')
        ->assertSee('Add Item');
});

test('user can navigate to edit group page', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Edit Test Group']);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    actingAs($user);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->click('Edit Group')
        ->assertPathIs('/groups/'.$group->id.'/edit')
        ->assertSee('Edit Group');
});

test('non-member cannot access group', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $otherUser->id]);
    $group->members()->attach($otherUser->id, ['role' => GroupRole::Owner]);

    actingAs($user);

    visit('/groups/'.$group->id)
        ->assertSee('403');
});
