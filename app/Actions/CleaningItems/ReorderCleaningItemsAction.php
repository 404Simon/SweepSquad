<?php

declare(strict_types=1);

namespace App\Actions\CleaningItems;

use App\Models\CleaningItem;
use Illuminate\Support\Facades\DB;

final readonly class ReorderCleaningItemsAction
{
    /**
     * Execute the action.
     *
     * @param  array<int, int>  $orderMap  Map of item ID to new order value
     */
    public function handle(array $orderMap): void
    {
        DB::transaction(function () use ($orderMap): void {
            foreach ($orderMap as $itemId => $newOrder) {
                CleaningItem::query()
                    ->where('id', $itemId)
                    ->update(['order' => $newOrder]);
            }
        });
    }
}
