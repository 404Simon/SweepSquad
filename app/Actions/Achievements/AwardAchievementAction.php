<?php

declare(strict_types=1);

namespace App\Actions\Achievements;

use App\Achievement;
use App\Models\User;
use App\Models\UserAchievement;

final readonly class AwardAchievementAction
{
    /**
     * Award an achievement to a user if they don't already have it.
     */
    public function handle(User $user, Achievement $achievement): ?UserAchievement
    {
        // Check if user already has this achievement
        if ($user->hasAchievement($achievement)) {
            return null;
        }

        // Award the achievement
        return UserAchievement::create([
            'user_id' => $user->id,
            'achievement_code' => $achievement->value,
            'earned_at' => now(),
        ]);
    }
}
