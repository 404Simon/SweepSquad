<?php

declare(strict_types=1);

use App\GroupRole;
use App\Models\Group;
use App\Models\User;
use Livewire\Volt\Volt;

it('can render with no activity', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    Volt::test('groups.recent-activity', ['group' => $group])
        ->assertSee('Recent Activity')
        ->assertSee('No activity yet');
});

it('displays recent cleaning logs', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    $item = App\Models\CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Kitchen',
    ]);

    App\Models\CleaningLog::factory()->create([
        'user_id' => $user->id,
        'group_id' => $group->id,
        'cleaning_item_id' => $item->id,
        'coins_earned' => 50,
        'cleaned_at' => now(),
    ]);

    Volt::test('groups.recent-activity', ['group' => $group])
        ->assertSee('Recent Activity')
        ->assertSee('Test User')
        ->assertSee('Kitchen')
        ->assertSee('50');
});
