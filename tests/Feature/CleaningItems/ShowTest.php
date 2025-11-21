<?php

declare(strict_types=1);

use App\GroupRole;
use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->group = Group::factory()->create(['owner_id' => $this->user->id]);
    $this->group->members()->attach($this->user, ['role' => GroupRole::Owner->value]);

    $this->item = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'name' => 'Living Room',
        'description' => 'Main living area',
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12),
        'last_cleaned_by' => $this->user->id,
    ]);
});

test('authenticated user can view item detail page', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertOk()
        ->assertSee($this->item->name)
        ->assertSee($this->item->description);
});

test('guest cannot view item detail page', function () {
    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertRedirect(route('login'));
});

test('non-member cannot view item detail page', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertForbidden();
});

test('item detail page displays breadcrumb navigation', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee($this->group->name)
        ->assertSee($this->item->name);
});

test('item detail page displays breadcrumb with parent', function () {
    $this->actingAs($this->user);

    $parent = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'name' => 'Kitchen',
    ]);

    $child = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'parent_id' => $parent->id,
        'name' => 'Countertops',
    ]);

    $response = $this->get(route('cleaning-items.show', $child->id));

    $response->assertSee($this->group->name)
        ->assertSee($parent->name)
        ->assertSee($child->name);
});

test('item detail page displays dirtiness percentage', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $dirtiness = round($this->item->dirtiness_percentage);
    $response->assertSee($dirtiness.'%');
});

test('item detail page displays coins available', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee($this->item->coins_available.' coins');
});

test('item detail page displays edit button for admins', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Edit');
});

test('item detail page hides edit button for regular members', function () {
    $member = User::factory()->create();
    $this->group->members()->attach($member, ['role' => GroupRole::Member->value]);

    $this->actingAs($member);

    Volt::test('cleaning-items.show', ['id' => $this->item->id])
        ->assertSee($this->item->name)
        ->assertDontSee('Edit Item');
});

test('item detail page displays cleaning history', function () {
    $this->actingAs($this->user);

    CleaningLog::factory()->create([
        'cleaning_item_id' => $this->item->id,
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'dirtiness_at_clean' => 75.0,
        'coins_earned' => 100,
        'notes' => 'Deep clean',
        'cleaned_at' => now()->subHours(5),
    ]);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Cleaning History')
        ->assertSee($this->user->name)
        ->assertSee('100 coins')
        ->assertSee('Deep clean');
});

test('item detail page displays statistics', function () {
    $this->actingAs($this->user);

    CleaningLog::factory()->count(3)->create([
        'cleaning_item_id' => $this->item->id,
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
        'dirtiness_at_clean' => 80.0,
        'coins_earned' => 100,
    ]);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Statistics')
        ->assertSee('Total Cleanings')
        ->assertSee('3');
});

test('item detail page calculates most frequent cleaner', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create(['name' => 'Top Cleaner']);
    $this->group->members()->attach($otherUser, ['role' => GroupRole::Member->value]);

    CleaningLog::factory()->create([
        'cleaning_item_id' => $this->item->id,
        'user_id' => $this->user->id,
        'group_id' => $this->group->id,
    ]);

    CleaningLog::factory()->count(3)->create([
        'cleaning_item_id' => $this->item->id,
        'user_id' => $otherUser->id,
        'group_id' => $this->group->id,
    ]);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Top Cleaner')
        ->assertSee($otherUser->name);
});

test('item detail page displays sub-items', function () {
    $this->actingAs($this->user);

    $child = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'parent_id' => $this->item->id,
        'name' => 'Sub-item',
        'cleaning_frequency_hours' => 24,
    ]);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Sub-Items')
        ->assertSee($child->name);
});

test('item detail page calculates hours until fully dirty', function () {
    $this->actingAs($this->user);

    $item = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12),
    ]);

    Volt::test('cleaning-items.show', ['id' => $item->id])
        ->assertSee('Time Until 100% Dirty')
        ->assertSee('12');
});

test('item detail page shows overdue status', function () {
    $this->actingAs($this->user);

    $item = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(25),
    ]);

    $response = $this->get(route('cleaning-items.show', $item->id));

    $response->assertSee('Overdue');
});

test('item detail page displays bonus badge for high coin rewards', function () {
    $this->actingAs($this->user);

    $item = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'base_coin_reward' => 100,
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(25), // Overdue, gets 1.5x multiplier
    ]);

    $response = $this->get(route('cleaning-items.show', $item->id));

    $response->assertSee('Bonus!');
});

test('item detail page refreshes when item is cleaned', function () {
    $this->actingAs($this->user);

    $volt = Volt::test('cleaning-items.show', ['id' => $this->item->id])
        ->assertSee($this->item->name);

    // Simulate cleaning the item
    $this->item->update([
        'last_cleaned_at' => now(),
        'last_cleaned_by' => $this->user->id,
    ]);

    $volt->call('refreshItem')
        ->assertSet('item.last_cleaned_at', fn ($value) => $value !== null);
});

test('container item without cleaning frequency displays message', function () {
    $this->actingAs($this->user);

    $container = CleaningItem::factory()->create([
        'group_id' => $this->group->id,
        'name' => 'Kitchen Container',
        'cleaning_frequency_hours' => null,
    ]);

    $response = $this->get(route('cleaning-items.show', $container->id));

    $response->assertSee('This is a container item');
});

test('item detail page displays mark as cleaned button', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSeeLivewire('cleaning-items.clean-button');
});

test('item detail page displays back to group button', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Back to Group');
});

test('item detail page displays add sub-item button', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cleaning-items.show', $this->item->id));

    $response->assertSee('Add Sub-Item');
});
