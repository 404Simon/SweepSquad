<?php

declare(strict_types=1);

use App\Models\CleaningItem;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('calculates dirtiness as 0 when no cleaning frequency is set', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => null,
        'last_cleaned_at' => now()->subDays(10),
    ]);

    expect($item->calculateDirtiness())->toBe(0.0);
});

test('calculates dirtiness as 100 when never cleaned', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => null,
    ]);

    expect($item->calculateDirtiness())->toBe(100.0);
});

test('calculates dirtiness percentage correctly', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12),
    ]);

    expect($item->calculateDirtiness())->toBeGreaterThanOrEqual(49.0)
        ->and($item->calculateDirtiness())->toBeLessThanOrEqual(51.0);
});

test('caps dirtiness at 100 percent', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subDays(10),
    ]);

    expect($item->calculateDirtiness())->toBe(100.0);
});

test('is overdue when dirtiness is 100 or more', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subDays(2),
    ]);

    expect($item->is_overdue)->toBeTrue();
});

test('is not overdue when dirtiness is less than 100', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12),
    ]);

    expect($item->is_overdue)->toBeFalse();
});

test('needs attention when dirtiness is 80 or more', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(20),
    ]);

    expect($item->needs_attention)->toBeTrue();
});

test('does not need attention when dirtiness is less than 80', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(10),
    ]);

    expect($item->needs_attention)->toBeFalse();
});

test('is freshly clean when dirtiness is less than 20', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(4),
    ]);

    expect($item->is_freshly_clean)->toBeTrue();
});

test('is not freshly clean when dirtiness is 20 or more', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12),
    ]);

    expect($item->is_freshly_clean)->toBeFalse();
});

test('calculates coins with 1.5x multiplier when overdue', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'base_coin_reward' => 100,
        'last_cleaned_at' => now()->subDays(2),
    ]);

    expect($item->getCoinsAvailable())->toBe(150);
});

test('calculates coins with 1.2x multiplier when needs attention', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'base_coin_reward' => 100,
        'last_cleaned_at' => now()->subHours(20),
    ]);

    expect($item->getCoinsAvailable())->toBe(120);
});

test('calculates coins with 1x multiplier when clean', function () {
    $item = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'base_coin_reward' => 100,
        'last_cleaned_at' => now()->subHours(10),
    ]);

    expect($item->getCoinsAvailable())->toBe(100);
});

test('roots scope returns only items without parent', function () {
    $group = Group::factory()->create();
    $root1 = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => null]);
    $root2 = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => null]);
    $child = CleaningItem::factory()->create(['group_id' => $group->id, 'parent_id' => $root1->id]);

    $roots = CleaningItem::query()->roots()->get();

    expect($roots)->toHaveCount(2)
        ->and($roots->pluck('id'))->toContain($root1->id, $root2->id)
        ->and($roots->pluck('id'))->not->toContain($child->id);
});

test('overdue scope returns overdue items', function () {
    $overdue = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subDays(2),
    ]);

    $notOverdue = CleaningItem::factory()->create([
        'cleaning_frequency_hours' => 24,
        'last_cleaned_at' => now()->subHours(12),
    ]);

    $overdueItems = CleaningItem::query()->overdue()->get();

    expect($overdueItems->pluck('id'))->toContain($overdue->id)
        ->and($overdueItems->pluck('id'))->not->toContain($notOverdue->id);
});

test('children are ordered by order field', function () {
    $parent = CleaningItem::factory()->create();
    $child1 = CleaningItem::factory()->create(['parent_id' => $parent->id, 'order' => 2]);
    $child2 = CleaningItem::factory()->create(['parent_id' => $parent->id, 'order' => 1]);
    $child3 = CleaningItem::factory()->create(['parent_id' => $parent->id, 'order' => 3]);

    $children = $parent->children;

    expect($children->pluck('id')->toArray())->toBe([$child2->id, $child1->id, $child3->id]);
});
