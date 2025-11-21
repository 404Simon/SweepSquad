<?php

declare(strict_types=1);

namespace App\Actions\CleaningItems;

use App\Models\CleaningItem;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class CreateCleaningItemAction
{
    /**
     * Execute the action.
     */
    public function handle(
        Group $group,
        string $name,
        ?string $description = null,
        ?int $cleaningFrequencyHours = null,
        int $baseCoinReward = 0,
        ?int $parentId = null,
    ): CleaningItem {
        return DB::transaction(function () use ($group, $name, $description, $cleaningFrequencyHours, $baseCoinReward, $parentId): CleaningItem {
            // Validate parent belongs to same group if provided
            if ($parentId !== null) {
                $parent = CleaningItem::query()->findOrFail($parentId);
                if ($parent->group_id !== $group->id) {
                    throw new InvalidArgumentException('Parent item must belong to the same group.');
                }
            }

            // Get the next order value for items with the same parent
            $maxOrder = CleaningItem::query()
                ->where('group_id', $group->id)
                ->where('parent_id', $parentId)
                ->max('order') ?? -1;

            return CleaningItem::query()->create([
                'group_id' => $group->id,
                'parent_id' => $parentId,
                'name' => $name,
                'description' => $description,
                'cleaning_frequency_hours' => $cleaningFrequencyHours,
                'base_coin_reward' => $baseCoinReward,
                'order' => $maxOrder + 1,
            ]);
        });
    }
}
