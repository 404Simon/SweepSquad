<?php

declare(strict_types=1);

namespace App;

enum Achievement: string
{
    // Beginner Achievements
    case FirstClean = 'first_clean';
    case SquadMember = 'squad_member';
    case SquadCreator = 'squad_creator';

    // Progress Achievements - Coin Collector
    case CoinCollector100 = 'coin_collector_100';
    case CoinCollector500 = 'coin_collector_500';
    case CoinCollector1000 = 'coin_collector_1000';
    case CoinCollector5000 = 'coin_collector_5000';

    // Progress Achievements - Streak Master
    case StreakMaster7 = 'streak_master_7';
    case StreakMaster14 = 'streak_master_14';
    case StreakMaster30 = 'streak_master_30';
    case StreakMaster90 = 'streak_master_90';

    // Social Achievements
    case TeamPlayer = 'team_player';
    case RoomOwner = 'room_owner';
    case JackOfAllTrades = 'jack_of_all_trades';

    // Challenge Achievements
    case Perfectionist = 'perfectionist';
    case EarlyBird = 'early_bird';
    case NightOwl = 'night_owl';

    /**
     * Get the human-readable name of the achievement.
     */
    public function name(): string
    {
        return match ($this) {
            self::FirstClean => 'First Clean',
            self::SquadMember => 'Squad Member',
            self::SquadCreator => 'Squad Creator',
            self::CoinCollector100 => 'Coin Collector: Bronze',
            self::CoinCollector500 => 'Coin Collector: Silver',
            self::CoinCollector1000 => 'Coin Collector: Gold',
            self::CoinCollector5000 => 'Coin Collector: Platinum',
            self::StreakMaster7 => 'Streak Master: Week',
            self::StreakMaster14 => 'Streak Master: Fortnight',
            self::StreakMaster30 => 'Streak Master: Month',
            self::StreakMaster90 => 'Streak Master: Quarter',
            self::TeamPlayer => 'Team Player',
            self::RoomOwner => 'Room Owner',
            self::JackOfAllTrades => 'Jack of All Trades',
            self::Perfectionist => 'Perfectionist',
            self::EarlyBird => 'Early Bird',
            self::NightOwl => 'Night Owl',
        };
    }

    /**
     * Get the description of the achievement.
     */
    public function description(): string
    {
        return match ($this) {
            self::FirstClean => 'Complete your first cleaning',
            self::SquadMember => 'Join your first cleaning squad',
            self::SquadCreator => 'Create your own cleaning squad',
            self::CoinCollector100 => 'Earn 100 total coins',
            self::CoinCollector500 => 'Earn 500 total coins',
            self::CoinCollector1000 => 'Earn 1,000 total coins',
            self::CoinCollector5000 => 'Earn 5,000 total coins',
            self::StreakMaster7 => 'Maintain a 7-day cleaning streak',
            self::StreakMaster14 => 'Maintain a 14-day cleaning streak',
            self::StreakMaster30 => 'Maintain a 30-day cleaning streak',
            self::StreakMaster90 => 'Maintain a 90-day cleaning streak',
            self::TeamPlayer => 'Complete 10 cleanings in group spaces',
            self::RoomOwner => 'Complete 50 cleanings of any room',
            self::JackOfAllTrades => 'Clean 5 different types of rooms',
            self::Perfectionist => 'Clean 10 items at exactly 100% dirtiness',
            self::EarlyBird => 'Complete 10 cleanings before 9 AM',
            self::NightOwl => 'Complete 10 cleanings after 9 PM',
        };
    }

    /**
     * Get the icon for the achievement.
     */
    public function icon(): string
    {
        return match ($this) {
            self::FirstClean => 'sparkles',
            self::SquadMember => 'user-group',
            self::SquadCreator => 'star',
            self::CoinCollector100, self::CoinCollector500, self::CoinCollector1000, self::CoinCollector5000 => 'currency-dollar',
            self::StreakMaster7, self::StreakMaster14, self::StreakMaster30, self::StreakMaster90 => 'fire',
            self::TeamPlayer => 'hand-raised',
            self::RoomOwner => 'home',
            self::JackOfAllTrades => 'wrench-screwdriver',
            self::Perfectionist => 'check-badge',
            self::EarlyBird => 'sun',
            self::NightOwl => 'moon',
        };
    }

    /**
     * Get the category of the achievement.
     */
    public function category(): string
    {
        return match ($this) {
            self::FirstClean, self::SquadMember, self::SquadCreator => 'Beginner',
            self::CoinCollector100, self::CoinCollector500, self::CoinCollector1000, self::CoinCollector5000,
            self::StreakMaster7, self::StreakMaster14, self::StreakMaster30, self::StreakMaster90 => 'Progress',
            self::TeamPlayer, self::RoomOwner, self::JackOfAllTrades => 'Social',
            self::Perfectionist, self::EarlyBird, self::NightOwl => 'Challenge',
        };
    }
}
