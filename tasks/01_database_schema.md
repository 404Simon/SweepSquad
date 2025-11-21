# Task 1: Database Schema Setup

## Goal
Create all database migrations for the core data models of SweepSquad.

## Description
Establish the foundational database structure including users, groups, group members, group invites, cleaning items, cleaning logs, and user achievements.

## Models to Create
- **User** (extend existing)
  - Add: `total_coins`, `current_streak`, `last_cleaned_at`
- **Group**
  - Fields: `uuid`, `name`, `description`, `owner_id`, `settings` (JSON)
- **GroupMember**
  - Fields: `group_id`, `user_id`, `role` (enum: owner/admin/member), `joined_at`
  - Unique constraint: (group_id, user_id)
- **GroupInvite**
  - Fields: `uuid`, `group_id`, `created_by`, `type` (enum), `expires_at`, `used_by`, `used_at`
- **CleaningItem**
  - Fields: `group_id`, `parent_id` (self-reference), `name`, `description`, `cleaning_frequency_hours`, `base_coin_reward`, `last_cleaned_at`, `last_cleaned_by`, `order`
- **CleaningLog**
  - Fields: `cleaning_item_id`, `user_id`, `group_id`, `dirtiness_at_clean`, `coins_earned`, `notes`, `photo`, `cleaned_at`
- **UserAchievement**
  - Fields: `user_id`, `achievement_code`, `earned_at`
  - Unique constraint: (user_id, achievement_code)

## Acceptance Criteria
- [ ] All migrations created using `php artisan make:migration`
- [ ] All models created using `php artisan make:model`
- [ ] Appropriate indexes added (group_id, parent_id, last_cleaned_at)
- [ ] Foreign key constraints properly set up
- [ ] Migrations run successfully
- [ ] Models have proper relationships defined

## Related Files
- `database/migrations/`
- `app/Models/`

## Next Steps
After completion, proceed to Task 2: User Model Extensions
