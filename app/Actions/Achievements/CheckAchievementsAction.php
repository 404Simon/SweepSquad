<?php

declare(strict_types=1);

namespace App\Actions\Achievements;

use App\Achievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CheckAchievementsAction
{
    public function __construct(
        private AwardAchievementAction $awardAchievement,
    ) {}

    /**
     * Check and award all eligible achievements for a user.
     *
     * @return array<UserAchievement> Newly awarded achievements
     */
    public function handle(User $user): array
    {
        $awarded = [];

        // Refresh user to get latest data
        $user->refresh();

        // Check beginner achievements
        $awarded = array_merge($awarded, $this->checkBeginnerAchievements($user));

        // Check progress achievements
        $awarded = array_merge($awarded, $this->checkProgressAchievements($user));

        // Check social achievements
        $awarded = array_merge($awarded, $this->checkSocialAchievements($user));

        // Check challenge achievements
        $awarded = array_merge($awarded, $this->checkChallengeAchievements($user));

        return array_filter($awarded);
    }

    /**
     * Check beginner achievements.
     */
    private function checkBeginnerAchievements(User $user): array
    {
        $awarded = [];

        // First Clean - completed at least one cleaning
        if ($user->cleaningLogs()->count() >= 1) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::FirstClean);
        }

        // Squad Member - joined at least one group
        if ($user->groupMemberships()->count() >= 1) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::SquadMember);
        }

        // Squad Creator - created at least one group
        if ($user->ownedGroups()->count() >= 1) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::SquadCreator);
        }

        return $awarded;
    }

    /**
     * Check progress achievements (coins and streaks).
     */
    private function checkProgressAchievements(User $user): array
    {
        $awarded = [];

        // Coin Collector achievements
        $totalCoins = $user->total_coins;

        if ($totalCoins >= 5000) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::CoinCollector5000);
        }
        if ($totalCoins >= 1000) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::CoinCollector1000);
        }
        if ($totalCoins >= 500) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::CoinCollector500);
        }
        if ($totalCoins >= 100) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::CoinCollector100);
        }

        // Streak Master achievements
        $currentStreak = $user->current_streak;

        if ($currentStreak >= 90) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::StreakMaster90);
        }
        if ($currentStreak >= 30) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::StreakMaster30);
        }
        if ($currentStreak >= 14) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::StreakMaster14);
        }
        if ($currentStreak >= 7) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::StreakMaster7);
        }

        return $awarded;
    }

    /**
     * Check social achievements.
     */
    private function checkSocialAchievements(User $user): array
    {
        $awarded = [];

        // Team Player - 10 cleanings in group spaces
        $groupCleanings = $user->cleaningLogs()
            ->whereNotNull('group_id')
            ->count();

        if ($groupCleanings >= 10) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::TeamPlayer);
        }

        // Room Owner - 50 cleanings of any room
        $totalCleanings = $user->cleaningLogs()->count();

        if ($totalCleanings >= 50) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::RoomOwner);
        }

        // Jack of All Trades - clean 5 different types of rooms
        $distinctRooms = $user->cleaningLogs()
            ->join('cleaning_items', 'cleaning_logs.cleaning_item_id', '=', 'cleaning_items.id')
            ->distinct('cleaning_items.parent_id')
            ->count(DB::raw('DISTINCT cleaning_items.parent_id'));

        if ($distinctRooms >= 5) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::JackOfAllTrades);
        }

        return $awarded;
    }

    /**
     * Check challenge achievements.
     */
    private function checkChallengeAchievements(User $user): array
    {
        $awarded = [];

        // Perfectionist - 10 items at exactly 100% dirtiness
        $perfectCleans = $user->cleaningLogs()
            ->where('dirtiness_at_clean', '>=', 100.0)
            ->count();

        if ($perfectCleans >= 10) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::Perfectionist);
        }

        // Early Bird - 10 cleanings before 9 AM
        $earlyCleans = $user->cleaningLogs()
            ->whereRaw("CAST(strftime('%H', cleaned_at) AS INTEGER) < 9")
            ->count();

        if ($earlyCleans >= 10) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::EarlyBird);
        }

        // Night Owl - 10 cleanings after 9 PM
        $lateCleans = $user->cleaningLogs()
            ->whereRaw("CAST(strftime('%H', cleaned_at) AS INTEGER) >= 21")
            ->count();

        if ($lateCleans >= 10) {
            $awarded[] = $this->awardAchievement->handle($user, Achievement::NightOwl);
        }

        return $awarded;
    }
}
