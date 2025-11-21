# Task 6: Mark as Cleaned Functionality

## Goal
Implement the core cleaning mechanic where users mark items as cleaned and earn coins.

## Description
Build the functionality for users to mark cleaning items as cleaned, calculate coins earned, update streaks, and log the activity.

## Components to Build
1. **CleaningLog Model**
   - Relationships to CleaningItem, User, and Group
   - Scopes: `recent()`, `forUser()`, `forItem()`, `forGroup()`

2. **MarkAsCleanedAction**
   - Calculate current dirtiness percentage
   - Calculate coins earned (with bonuses)
   - Award coins to user
   - Update item's last_cleaned_at and last_cleaned_by
   - Create CleaningLog entry
   - Update user's streak
   - Apply bonuses:
     - Streak Bonus: +10% for 7+ days, +20% for 14+ days
     - Speed Bonus: +5% if cleaned before 80% dirtiness
     - Perfect Clean: +25% if at exactly 100%
   - Wrap in DB transaction

3. **Volt Components**
   - `cleaning-items.clean-button` - Mark as cleaned button
   - `cleaning-items.clean-modal` - Confirmation modal showing:
     - Current dirtiness
     - Coins to be earned
     - Optional note field
     - Confirm/cancel buttons
   - `cleaning-items.clean-success` - Success message with animation

## Acceptance Criteria
- [ ] CleaningLog model created
- [ ] MarkAsCleanedAction implemented with all bonuses
- [ ] Coin calculation formula correct
- [ ] User coins and streak updated
- [ ] CleaningLog entry created
- [ ] Item state updated (last_cleaned_at, dirtiness reset)
- [ ] Confirmation modal shows correct information
- [ ] Success message displays coins earned
- [ ] Unit tests for coin calculation with various scenarios
- [ ] Feature tests for marking items as cleaned
- [ ] Tests pass

## Related Files
- `app/Models/CleaningLog.php`
- `app/Actions/Cleaning/MarkAsCleanedAction.php`
- `resources/views/livewire/cleaning-items/clean-button.blade.php`
- `resources/views/livewire/cleaning-items/clean-modal.blade.php`
- `tests/Unit/MarkAsCleanedTest.php`
- `tests/Feature/Cleaning/MarkAsCleanedTest.php`

## Next Steps
After completion, proceed to Task 7: Dashboard Implementation
