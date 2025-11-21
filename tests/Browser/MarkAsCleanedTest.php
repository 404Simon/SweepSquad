<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\Group;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can view item detail page with clean button', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Bathroom',
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$item->id)
        ->assertNoSmoke()
        ->assertSee('Bathroom')
        ->assertSee('100 coins');
});

test('item shows dirtiness percentage', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Kitchen',
        'base_coin_reward' => 50,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12), // 50% dirty
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$item->id)
        ->assertNoSmoke()
        ->assertSee('Kitchen');
});

test('overdue item shows bonus coins available', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Overdue Item',
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subDays(3), // Very overdue
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$item->id)
        ->assertNoSmoke()
        ->assertSee('Overdue Item')
        ->assertSee('Bonus!');
});

test('item shows cleaning history', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Bedroom',
        'last_cleaned_at' => now()->subDay(),
        'last_cleaned_by' => $user->id,
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$item->id)
        ->assertNoSmoke()
        ->assertSee('Bedroom');
});

test('item shows sub-items', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $parent = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Kitchen',
    ]);
    $child = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'parent_id' => $parent->id,
        'name' => 'Kitchen Floor',
    ]);

    actingAs($user);

    visit('/cleaning-items/'.$parent->id)
        ->assertNoSmoke()
        ->assertSee('Kitchen')
        ->assertSee('Sub-Items')
        ->assertSee('Kitchen Floor');
});

test('non-member cannot mark item as cleaned', function () {
    $owner = User::factory()->create();
    $nonMember = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create(['group_id' => $group->id]);

    actingAs($nonMember);

    visit('/cleaning-items/'.$item->id)
        ->assertSee('403');
});
