<?php

declare(strict_types=1);

namespace App\Actions\CleaningItems;

use App\Models\CleaningItem;
use App\Models\CleaningLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class MarkAsCleanedAction
{
    /**
     * Mark a cleaning item as cleaned and award coins.
     */
    public function handle(CleaningItem $item, User $user, ?string $notes = null): CleaningLog
    {
        return DB::transaction(function () use ($item, $user, $notes): CleaningLog {
            // Calculate current dirtiness percentage
            $dirtiness = $item->calculateDirtiness();

            // Calculate coins earned with bonuses
            $coinsEarned = $this->calculateCoinsWithBonuses($item, $user, $dirtiness);

            // Award coins to user
            $user->addCoins($coinsEarned);

            // Update user's streak
            $user->updateStreak();

            // Update item's last_cleaned_at and last_cleaned_by
            $item->update([
                'last_cleaned_at' => now(),
                'last_cleaned_by' => $user->id,
            ]);

            // Create CleaningLog entry
            return CleaningLog::create([
                'cleaning_item_id' => $item->id,
                'user_id' => $user->id,
                'group_id' => $item->group_id,
                'dirtiness_at_clean' => $dirtiness,
                'coins_earned' => $coinsEarned,
                'notes' => $notes,
                'cleaned_at' => now(),
            ]);
        });
    }

    /**
     * Calculate coins earned with all bonuses applied.
     */
    private function calculateCoinsWithBonuses(CleaningItem $item, User $user, float $dirtiness): int
    {
        $baseCoins = $item->base_coin_reward;
        $multiplier = 1.0;

        // Streak Bonus: +10% for 7+ days, +20% for 14+ days
        if ($user->current_streak >= 14) {
            $multiplier += 0.20;
        } elseif ($user->current_streak >= 7) {
            $multiplier += 0.10;
        }

        // Speed Bonus: +5% if cleaned before 80% dirtiness
        if ($dirtiness < 80.0) {
            $multiplier += 0.05;
        }

        // Perfect Clean: +25% if at exactly 100%
        if ($dirtiness >= 100.0) {
            $multiplier += 0.25;
        }

        return (int) round($baseCoins * $multiplier);
    }
}
