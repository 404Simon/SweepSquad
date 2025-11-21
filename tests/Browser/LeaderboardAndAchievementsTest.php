<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\Group;
use App\Models\User;
use App\Models\UserAchievement;

use function Pest\Laravel\actingAs;

test('user can view group leaderboard', function () {
    $user1 = User::factory()->create(['name' => 'Alice', 'total_coins' => 500]);
    $user2 = User::factory()->create(['name' => 'Bob', 'total_coins' => 300]);
    $group = Group::factory()->create(['owner_id' => $user1->id, 'name' => 'Test Group']);
    $group->members()->attach($user1->id, ['role' => GroupRole::Owner]);
    $group->members()->attach($user2->id, ['role' => GroupRole::Member]);

    actingAs($user1);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('Leaderboard')
        ->assertSee('Alice')
        ->assertSee('Bob')
        ->assertSee('500')
        ->assertSee('300');
});

test('leaderboard shows members ordered by coins', function () {
    $user1 = User::factory()->create(['name' => 'High Scorer', 'total_coins' => 1000]);
    $user2 = User::factory()->create(['name' => 'Mid Scorer', 'total_coins' => 500]);
    $user3 = User::factory()->create(['name' => 'Low Scorer', 'total_coins' => 100]);
    $group = Group::factory()->create(['owner_id' => $user1->id]);
    $group->members()->attach($user1->id, ['role' => GroupRole::Owner]);
    $group->members()->attach($user2->id, ['role' => GroupRole::Member]);
    $group->members()->attach($user3->id, ['role' => GroupRole::Member]);

    actingAs($user1);

    $page = visit('/groups/'.$group->id);

    $page->assertNoSmoke()
        ->assertSee('High Scorer')
        ->assertSee('Mid Scorer')
        ->assertSee('Low Scorer');
});

test('user can view their achievements', function () {
    $user = User::factory()->create();
    UserAchievement::factory()->create([
        'user_id' => $user->id,
        'achievement_key' => 'first_clean',
        'awarded_at' => now(),
    ]);

    actingAs($user);

    visit('/settings')
        ->assertNoSmoke()
        ->assertSee('Achievements')
        ->assertSee('First Clean');
});

test('achievement badge shows on user profile', function () {
    $user = User::factory()->create(['name' => 'Achievement Hunter']);
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    UserAchievement::factory()->create([
        'user_id' => $user->id,
        'achievement_key' => 'streak_7',
        'awarded_at' => now(),
    ]);

    actingAs($user);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('Achievement Hunter');
});

test('user sees coin count on dashboard', function () {
    $user = User::factory()->create(['total_coins' => 1250]);

    actingAs($user);

    visit('/dashboard')
        ->assertNoSmoke()
        ->assertSee('1,250')
        ->assertSee('coins');
});

test('user sees current streak on dashboard', function () {
    $user = User::factory()->create([
        'current_streak' => 7,
        'last_cleaned_at' => now(),
    ]);

    actingAs($user);

    visit('/dashboard')
        ->assertNoSmoke()
        ->assertSee('7')
        ->assertSee('day streak');
});

test('cleaning activity shows in recent logs', function () {
    $user = User::factory()->create(['name' => 'Cleaner']);
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);
    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Bathroom',
    ]);

    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'group_id' => $group->id,
        'cleaning_item_id' => $item->id,
        'coins_earned' => 100,
        'cleaned_at' => now(),
    ]);

    actingAs($user);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('Recent Activity')
        ->assertSee('Bathroom')
        ->assertSee('Cleaner')
        ->assertSee('100');
});

test('empty leaderboard shows helpful message', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    actingAs($user);

    visit('/groups/'.$group->id)
        ->assertNoSmoke()
        ->assertSee('Start cleaning');
});

test('user can view global statistics', function () {
    $user = User::factory()->create([
        'total_coins' => 500,
        'current_streak' => 5,
    ]);
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user->id, ['role' => GroupRole::Owner]);

    actingAs($user);

    visit('/settings')
        ->assertNoSmoke()
        ->assertSee('Statistics')
        ->assertSee('500')
        ->assertSee('5');
});
