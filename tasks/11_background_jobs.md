# Task 11: Background Jobs

## Goal
Implement scheduled background jobs for streak management and cleanup tasks.

## Description
Create queue jobs that run periodically to maintain data consistency and update user streaks.

## Jobs to Build
1. **UpdateUserStreaksJob**
   - Run daily at midnight
   - Check each user's last_cleaned_at
   - If > 24 hours, reset streak to 0
   - If within 24 hours, maintain streak

2. **CleanupExpiredInvitesJob**
   - Run daily
   - Delete expired invites (where expires_at < now and used_at is null)

3. **UpdateGroupStatisticsJob** (Optional)
   - Run nightly
   - Pre-calculate group statistics for performance
   - Cache results

## Scheduling
- Configure in `routes/console.php` or `bootstrap/app.php`
- Use Laravel's task scheduling (`php artisan schedule:work` for development)
- Queue driver is set to `database` (no Redis required)
- Run queue worker: `php artisan queue:work`

## Notes
- This application uses database-backed queues (no Redis or external queue services)
- The database queue driver is simple and effective for this use case
- In production, ensure the scheduler and queue worker are running (e.g., via supervisor or systemd)

## Acceptance Criteria
- [ ] All jobs created using `php artisan make:job`
- [ ] UpdateUserStreaksJob correctly updates/resets streaks
- [ ] CleanupExpiredInvitesJob removes expired invites
- [ ] Jobs scheduled to run at appropriate times using Laravel Scheduler
- [ ] Jobs handle errors gracefully
- [ ] Queue worker can process jobs with database driver
- [ ] Unit tests for job logic
- [ ] Feature tests for scheduled tasks
- [ ] Tests pass

## Related Files
- `app/Jobs/UpdateUserStreaksJob.php`
- `app/Jobs/CleanupExpiredInvitesJob.php`
- `routes/console.php`
- `tests/Unit/Jobs/`

## Next Steps
After completion, proceed to Task 12: Mobile Responsiveness & Polish
