# Task 2: User Model Extensions

## Goal
Extend the User model with cleaning-related attributes and relationships.

## Description
Add coin tracking, streak management, and relationships to groups and cleaning activities to the existing User model.

## Requirements
- Add migration to extend users table with:
  - `total_coins` (integer, default: 0)
  - `current_streak` (integer, default: 0)
  - `last_cleaned_at` (timestamp, nullable)
- Update User model with:
  - Casts for the new fields
  - Relationships: `groups()`, `groupMemberships()`, `cleaningLogs()`, `achievements()`
  - Helper methods: `addCoins()`, `updateStreak()`, `resetStreak()`

## Acceptance Criteria
- [ ] Migration created and run successfully
- [ ] User model updated with new fields and casts
- [ ] All relationships defined (hasMany, hasManyThrough, belongsToMany)
- [ ] Helper methods implemented
- [ ] Unit tests for helper methods created
- [ ] Tests pass

## Related Files
- `database/migrations/YYYY_MM_DD_add_cleaning_fields_to_users_table.php`
- `app/Models/User.php`
- `tests/Unit/UserTest.php`

## Next Steps
After completion, proceed to Task 3: Group Management System
