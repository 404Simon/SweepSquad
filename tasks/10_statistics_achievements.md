# Task 10: User Statistics & Achievements

## Goal
Implement user statistics dashboard and achievement tracking system.

## Description
Build the gamification layer with personal stats, group leaderboards, and unlockable achievements.

## Components to Build
1. **UserAchievement Model**
   - Relationship to User
   - Achievement definitions (config or database)

2. **Achievement Definitions** (in config or enum)
   - Beginner: First Clean, Squad Member, Squad Creator
   - Progress: Coin Collector (100/500/1000/5000), Streak Master (7/14/30/90)
   - Social: Team Player, Room Owner (50 cleans), Jack of All Trades
   - Challenge: Perfectionist, Early Bird, Night Owl

3. **Actions**
   - `CheckAchievementsAction` - Check and award achievements after actions
   - `AwardAchievementAction` - Award specific achievement to user

4. **Volt Components**
   - `profile.stats` - User statistics dashboard:
     - Total coins (all-time)
     - Coins this week/month
     - Current streak
     - Total items cleaned
     - Favorite room
     - Achievements earned
   - `profile.achievements` - Achievement gallery
   - `groups.leaderboard` - Group leaderboard:
     - Top cleaners this week/month
     - Most valuable cleaner
     - Most consistent member

5. **Statistics Queries**
   - Efficient queries for all stats
   - Cache expensive calculations

## Acceptance Criteria
- [ ] UserAchievement model created
- [ ] Achievement definitions configured
- [ ] CheckAchievementsAction implemented
- [ ] Achievements automatically awarded after relevant actions
- [ ] Stats dashboard shows all metrics
- [ ] Achievement gallery displays earned/locked achievements
- [ ] Group leaderboard displays correctly
- [ ] Efficient queries (no N+1 problems)
- [ ] Responsive design
- [ ] Feature tests for achievement system
- [ ] Tests pass

## Related Files
- `app/Models/UserAchievement.php`
- `app/Actions/Achievements/`
- `config/achievements.php` or equivalent
- `resources/views/livewire/profile/stats.blade.php`
- `resources/views/livewire/profile/achievements.blade.php`
- `resources/views/livewire/groups/leaderboard.blade.php`
- `tests/Feature/Achievements/`

## Next Steps
After completion, proceed to Task 11: Background Jobs
