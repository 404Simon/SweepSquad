<?php

declare(strict_types=1);

namespace App\Actions\CleaningItems;

use App\Models\CleaningItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class MoveCleaningItemAction
{
    /**
     * Execute the action.
     *
     * Move an item to a different parent (or make it a root item).
     */
    public function handle(CleaningItem $item, ?int $newParentId = null): CleaningItem
    {
        return DB::transaction(function () use ($item, $newParentId): CleaningItem {
            // Validate new parent if provided
            if ($newParentId !== null) {
                $newParent = CleaningItem::query()->findOrFail($newParentId);

                // Ensure new parent belongs to same group
                if ($newParent->group_id !== $item->group_id) {
                    throw new InvalidArgumentException('Cannot move item to a parent in a different group.');
                }

                // Prevent circular references
                if ($newParentId === $item->id) {
                    throw new InvalidArgumentException('Cannot move item to itself.');
                }

                // Check if new parent is a descendant of the item being moved
                if ($this->isDescendant($item, $newParent)) {
                    throw new InvalidArgumentException('Cannot move item to one of its descendants.');
                }
            }

            // Get the next order value in the new location
            $maxOrder = CleaningItem::query()
                ->where('group_id', $item->group_id)
                ->where('parent_id', $newParentId)
                ->max('order') ?? -1;

            $item->update([
                'parent_id' => $newParentId,
                'order' => $maxOrder + 1,
            ]);

            return $item->fresh();
        });
    }

    /**
     * Check if a potential parent is a descendant of the item.
     */
    private function isDescendant(CleaningItem $item, CleaningItem $potentialDescendant): bool
    {
        $current = $potentialDescendant;

        while ($current->parent_id !== null) {
            if ($current->parent_id === $item->id) {
                return true;
            }
            $current = CleaningItem::query()->find($current->parent_id);
            if ($current === null) {
                break;
            }
        }

        return false;
    }
}
