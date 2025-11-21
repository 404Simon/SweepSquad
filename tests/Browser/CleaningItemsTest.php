<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can create a cleaning item', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    actingAs($user);

    visit('/cleaning-items/create/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('Create Cleaning Item')
        ->fill('name', 'Living Room')
        ->fill('description', 'Clean the living room')
        ->fill('cleaningFrequencyHours', '168')
        ->fill('baseCoinReward', '50')
        ->click('Create Item')
        ->assertPathIs('/groups/'.$group->id)
        ->assertSee('Living Room');
});

test('user can create a sub-item', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $parent = CleaningItem::factory()->create(['group_id' => $group->id, 'name' => 'Kitchen']);

    actingAs($user);

    visit('/cleaning-items/create/'.$group->id.'/'.$parent->id)
        ->assertNoSmoke()
        ->assertSee('Create Cleaning Item')
        ->assertSee('under Kitchen')
        ->fill('name', 'Kitchen Floor')
        ->fill('baseCoinReward', '20')
        ->click('Create Item')
        ->assertPathIs('/groups/'.$group->id);
});

test('user can view cleaning item details', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Bathroom',
        'description' => 'Clean the bathroom',
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$item->id)
        ->assertNoSmoke()
        ->assertSee('Bathroom')
        ->assertSee('Clean the bathroom');
});

test('user can edit a cleaning item', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Bathroom',
        'description' => 'Clean the bathroom',
        'cleaning_frequency_hours' => 168,
        'base_coin_reward' => 50,
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$item->id.'/edit')
        ->assertNoSmoke()
        ->assertSee('Edit Cleaning Item')
        ->fill('name', 'Master Bathroom')
        ->fill('description', 'Clean the master bathroom thoroughly')
        ->fill('cleaningFrequencyHours', '72')
        ->fill('baseCoinReward', '75')
        ->click('Update Item')
        ->assertPathIs('/groups/'.$group->id)
        ->assertSee('Master Bathroom');
});
