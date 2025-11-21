<?php

declare(strict_types=1);

namespace App\Actions\CleaningItems;

use App\Models\CleaningItem;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCleaningItemAction
{
    /**
     * Execute the action.
     */
    public function handle(
        CleaningItem $item,
        string $name,
        ?string $description = null,
        ?int $cleaningFrequencyHours = null,
        int $baseCoinReward = 0,
    ): CleaningItem {
        return DB::transaction(function () use ($item, $name, $description, $cleaningFrequencyHours, $baseCoinReward): CleaningItem {
            $item->update([
                'name' => $name,
                'description' => $description,
                'cleaning_frequency_hours' => $cleaningFrequencyHours,
                'base_coin_reward' => $baseCoinReward,
            ]);

            return $item->fresh();
        });
    }
}
