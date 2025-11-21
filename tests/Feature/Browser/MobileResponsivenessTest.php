<?php

declare(strict_types=1);

namespace Tests\Feature\Browser;

use App\Models\CleaningItem;
use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('dashboard is responsive on mobile viewport', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => \App\GroupRole::Owner]);

    actingAs($user);

    visit('/dashboard')
        ->resize(375, 667) // iPhone SE dimensions
        ->assertNoSmoke()
        ->assertNoJavascriptErrors()
        ->assertSee('Welcome back')
        ->assertSee('Create Group');
});

test('groups index is responsive on mobile viewport', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id, 'name' => 'Mobile Test Group']);
    $group->members()->attach($user->id, ['role' => \App\GroupRole::Owner]);

    actingAs($user);

    visit('/groups')
        ->resize(375, 667)
        ->assertNoSmoke()
        ->assertNoJavascriptErrors()
        ->assertSee('My Groups')
        ->assertSee('Mobile Test Group')
        ->assertSee('Create Group');
});

test('group detail page is responsive on tablet viewport', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => \App\GroupRole::Owner]);

    actingAs($user);

    visit("/groups/{$group->id}")
        ->resize(768, 1024) // iPad dimensions
        ->assertNoSmoke()
        ->assertNoJavascriptErrors()
        ->assertSee($group->name);
});

test('cleaning item buttons are touch-friendly on mobile', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => \App\GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Touch Test Item',
        'cleaning_frequency_hours' => 24,
        'base_coin_reward' => 10,
    ]);

    actingAs($user);

    visit("/cleaning-items/{$item->id}")
        ->resize(375, 667)
        ->assertNoSmoke()
        ->assertNoJavascriptErrors()
        ->assertSee('Touch Test Item')
        ->assertSee('Mark as Cleaned');
});

test('mobile navigation works correctly', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/dashboard')
        ->resize(375, 667)
        ->assertNoSmoke()
        ->click('Create Group')
        ->assertPathContains('/groups/create')
        ->assertNoJavascriptErrors();
});

test('forms are usable on mobile viewport', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/groups/create')
        ->resize(375, 667)
        ->assertNoSmoke()
        ->assertNoJavascriptErrors()
        ->assertSee('Create New Group')
        ->fill('name', 'Mobile Created Group')
        ->fill('description', 'Created from mobile test')
        ->click('Create Group')
        ->waitForText('Mobile Created Group', 10)
        ->assertSee('Mobile Created Group');
});

test('cleaning modal works on mobile', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => \App\GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Modal Test Item',
        'cleaning_frequency_hours' => 24,
        'base_coin_reward' => 10,
    ]);

    actingAs($user);

    visit("/cleaning-items/{$item->id}")
        ->resize(375, 667)
        ->assertNoSmoke()
        ->click('Mark as Cleaned')
        ->waitForText('Mark as Cleaned', 10)
        ->assertSee('Current Dirtiness')
        ->assertNoJavascriptErrors();
});

test('empty states display properly on mobile', function () {
    $user = User::factory()->create();

    actingAs($user);

    visit('/dashboard')
        ->resize(375, 667)
        ->assertNoSmoke()
        ->assertNoJavascriptErrors()
        ->assertSee('No Groups Yet')
        ->assertSee('Create Your First Group');
});
