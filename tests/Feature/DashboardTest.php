<?php

declare(strict_types=1);

use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Livewire\Volt\Volt;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('dashboard'))->assertSuccessful();
});

test('dashboard displays welcome message with user name', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('Welcome back, John Doe');
});

test('dashboard displays current streak when user has one', function () {
    $user = User::factory()->create(['current_streak' => 5]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('Current streak: 5 days');
});

test('dashboard does not display streak when user has no streak', function () {
    $user = User::factory()->create(['current_streak' => 0]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertDontSee('Current streak');
});

test('dashboard displays today\'s coins earned', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
    ]);

    // Create logs from today
    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'coins_earned' => 10,
        'cleaned_at' => now(),
    ]);

    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'coins_earned' => 15,
        'cleaned_at' => now(),
    ]);

    // Create a log from yesterday (should not be counted)
    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'coins_earned' => 20,
        'cleaned_at' => now()->subDay(),
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('Coins Earned Today')
        ->assertSee('25'); // 10 + 15
});

test('dashboard displays today\'s items cleaned count', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
    ]);

    // Create 3 logs from today
    CleaningLog::factory()->count(3)->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'cleaned_at' => now(),
    ]);

    // Create a log from yesterday (should not be counted)
    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'cleaned_at' => now()->subDay(),
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('Items Cleaned Today')
        ->assertSee('3');
});

test('dashboard displays total coins', function () {
    $user = User::factory()->create(['total_coins' => 1250]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('Total Coins')
        ->assertSee('1,250');
});

test('dashboard displays user\'s groups', function () {
    $user = User::factory()->create();
    $group1 = Group::factory()->create([
        'owner_id' => $user->id,
        'name' => 'Test Group 1',
    ]);
    $group2 = Group::factory()->create([
        'owner_id' => $user->id,
        'name' => 'Test Group 2',
    ]);

    GroupMember::factory()->create([
        'group_id' => $group1->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    GroupMember::factory()->create([
        'group_id' => $group2->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('My Groups')
        ->assertSee('Test Group 1')
        ->assertSee('Test Group 2');
});

test('dashboard shows empty state when user has no groups', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('not part of any groups yet')
        ->assertSee('Create Your First Group');
});

test('dashboard displays recent activity feed', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create([
        'owner_id' => $user->id,
        'name' => 'My Group',
    ]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Kitchen Counter',
    ]);

    CleaningLog::factory()->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'coins_earned' => 15,
        'cleaned_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('Recent Activity')
        ->assertSee('Kitchen Counter')
        ->assertSee('My Group')
        ->assertSee('+15');
});

test('dashboard limits recent activity to 10 items', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
    ]);

    // Create 15 logs
    CleaningLog::factory()->count(15)->create([
        'user_id' => $user->id,
        'cleaning_item_id' => $item->id,
        'group_id' => $group->id,
        'cleaned_at' => now(),
    ]);

    $this->actingAs($user);

    // The dashboard should limit to 10 recent activities
    $logs = $user->cleaningLogs()->orderBy('cleaned_at', 'desc')->limit(10)->get();
    expect($logs)->toHaveCount(10);
});

test('dashboard shows items needing attention count for groups', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create([
        'owner_id' => $user->id,
        'name' => 'Test Group',
    ]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    // Create an overdue item (last cleaned 25 hours ago, frequency is 24 hours)
    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'last_cleaned_at' => now()->subHours(25),
        'cleaning_frequency_hours' => 24,
    ]);

    // Create an item due soon (last cleaned 21 hours ago, frequency is 24 hours = 87.5% dirty)
    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'last_cleaned_at' => now()->subHours(21),
        'cleaning_frequency_hours' => 24,
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('2 need attention');
});

test('dashboard shows overdue items count for groups', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create([
        'owner_id' => $user->id,
        'name' => 'Test Group',
    ]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    // Create 2 overdue items (last cleaned 25 hours ago, frequency is 24 hours)
    CleaningItem::factory()->count(2)->create([
        'group_id' => $group->id,
        'last_cleaned_at' => now()->subHours(25),
        'cleaning_frequency_hours' => 24,
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('2 overdue');
});

test('dashboard shows all caught up when no items need attention', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create([
        'owner_id' => $user->id,
        'name' => 'Test Group',
    ]);
    GroupMember::factory()->create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'role' => GroupRole::Owner,
    ]);

    // Create an item that doesn't need attention (last cleaned 1 hour ago, frequency is 168 hours)
    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'last_cleaned_at' => now()->subHour(),
        'cleaning_frequency_hours' => 168,
    ]);

    $this->actingAs($user);

    Volt::test('dashboard.index')
        ->assertSee('All caught up!');
});
