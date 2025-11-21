<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('authenticated user can view dashboard', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/dashboard')
        ->assertNoSmoke()
        ->assertSee('Welcome back')
        ->assertSee('Create Group');
});

test('dashboard shows user groups', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Test Household']);
    $group->members()->attach($user->id, ['role' => \App\GroupRole::Owner]);

    actingAs($user);

    visit('/dashboard')
        ->assertNoSmoke()
        ->assertSee('My Groups')
        ->assertSee('Test Household');
});

test('dashboard shows create group button', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/dashboard')
        ->assertNoSmoke()
        ->assertSee('Create Group');
});
