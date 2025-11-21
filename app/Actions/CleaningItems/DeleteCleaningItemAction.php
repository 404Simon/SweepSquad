<?php

declare(strict_types=1);

namespace App\Actions\CleaningItems;

use App\Models\CleaningItem;
use Illuminate\Support\Facades\DB;

final readonly class DeleteCleaningItemAction
{
    /**
     * Execute the action.
     *
     * Deletes the item and all its children (cascade is handled by database).
     */
    public function handle(CleaningItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $item->delete();
        });
    }
}
