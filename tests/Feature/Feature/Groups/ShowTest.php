<?php

declare(strict_types=1);

use App\Models\CleaningItem;
use App\Models\Group;
use App\Models\User;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('group show page displays correctly for members', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertSuccessful();
    $response->assertSee($group->name);
    $response->assertSee($group->description);
});

test('non-members cannot access group show page', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertForbidden();
});

test('group stats are displayed correctly', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    // Create items with different states
    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(30), // Overdue
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(22), // Needs attention
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(2), // Clean
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->assertSee('Total Items')
        ->assertSee('3')
        ->assertSee('Overdue')
        ->assertSee('1')
        ->assertSee('Needs Attention')
        ->assertSee('Clean');
});

test('hierarchical items are displayed correctly', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    $parentItem = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Kitchen',
        'parent_id' => null,
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Countertops',
        'parent_id' => $parentItem->id,
    ]);

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertSee('Kitchen');
    $response->assertSee('Countertops');
});

test('filter by overdue works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Overdue Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(30),
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Clean Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(2),
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setFilter', 'overdue')
        ->assertSee('Overdue Item');
});

test('filter by needs attention works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Needs Attention Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(22),
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Clean Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(2),
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setFilter', 'needs_attention')
        ->assertSee('Needs Attention Item');
});

test('filter by clean works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Clean Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(2),
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Dirty Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(22),
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setFilter', 'clean')
        ->assertSee('Clean Item');
});

test('sorting by dirtiness works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Very Dirty',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(30),
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Slightly Dirty',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(10),
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setSortBy', 'dirtiness')
        ->assertSuccessful();
});

test('sorting by coins works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(30), // Overdue = 1.5x bonus
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'base_coin_reward' => 50,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(10),
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setSortBy', 'coins')
        ->assertSuccessful();
});

test('sorting by last cleaned works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Recently Cleaned',
        'last_cleaned_at' => now()->subHour(),
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Long Ago Cleaned',
        'last_cleaned_at' => now()->subDays(5),
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setSortBy', 'last_cleaned')
        ->assertSuccessful();
});

test('sorting by name works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Zebra Room',
    ]);

    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Apple Kitchen',
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('setSortBy', 'name')
        ->assertSuccessful();
});

test('mark as cleaned action works', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    $item = CleaningItem::factory()->create([
        'group_id' => $group->id,
        'last_cleaned_at' => now()->subDays(2),
        'last_cleaned_by' => null,
    ]);

    actingAs($user);

    Volt::test('groups.show', ['id' => $group->id])
        ->call('markAsCleaned', $item->id)
        ->assertSuccessful();

    $item->refresh();

    expect($item->last_cleaned_by)->toBe($user->id);
    expect($item->last_cleaned_at)->not->toBeNull();
});

test('progress bars show correct colors', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    // Overdue item (red)
    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Overdue Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(30),
    ]);

    // Clean item (green)
    CleaningItem::factory()->create([
        'group_id' => $group->id,
        'name' => 'Clean Item',
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(2),
    ]);

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertSee('Overdue');
    $response->assertSee('Clean');
});

test('owner can see edit and delete buttons', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $user->id]);
    $group->members()->attach($user, ['role' => 'owner']);

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertSee('Edit Group');
    $response->assertSee('Delete');
});

test('members see leave button instead of delete', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner, ['role' => 'owner']);
    $group->members()->attach($member, ['role' => 'member']);

    actingAs($member);

    $response = get(route('groups.show', $group));

    $response->assertSee('Leave Group');
    $response->assertDontSee('Edit Group');
});

test('admins can add items', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'admin']);

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertSee('Add Item');
});

test('empty state shows when no items exist', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->members()->attach($user, ['role' => 'member']);

    actingAs($user);

    $response = get(route('groups.show', $group));

    $response->assertSee('No cleaning items yet');
});
