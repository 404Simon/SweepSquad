# Task 13: Testing & Documentation

## Goal
Achieve comprehensive test coverage with feature tests, unit tests, and browser tests using Pest 4.

## Description
Write thorough tests for all features and create documentation for developers and users. This application uses Pest 4 with browser testing for end-to-end UI testing.

## Browser Testing with Pest 4 (REQUIRED)
- Use `visit()`, `click()`, `fill()`, `assertSee()`, `assertNoSmoke()`, `assertPathIs()` etc.
- Browser tests MUST cover all critical user flows
- See example browser tests provided by the user
- Browser tests live in `tests/Browser/`
- Example pattern:
```php
test('user can do something', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    visit('/path')
        ->assertNoSmoke()
        ->assertSee('Expected Text')
        ->click('Button Text')
        ->assertPathIs('/new-path');
});
```

## Testing Tasks
1. **Browser Tests (PRIMARY TESTING METHOD)**
   - All critical user workflows must have browser tests
   - Group creation and management flows
   - Cleaning item management flows
   - Mark as cleaned flow with coin earning
   - Invite acceptance flows
   - Dashboard and navigation
   - Authentication flows
   - Settings and profile updates
   - Mobile responsive testing

2. **Feature Tests**
   - All CRUD operations for all models
   - Authentication flows (supplement browser tests)
   - Group creation and management
   - Invite system (all types)
   - Cleaning item hierarchy
   - Mark as cleaned flow
   - Achievement awarding
   - Leaderboard calculations

3. **Unit Tests**
   - User model methods (addCoins, updateStreak, etc.)
   - CleaningItem dirtiness calculations
   - Coin calculation with all bonuses
   - Achievement checking logic
   - Job logic

4. **Test Coverage Goals**
   - Aim for 80%+ code coverage
   - All happy paths covered with browser tests
   - All error cases covered
   - Edge cases tested

## Documentation Tasks
1. **Code Documentation**
   - PHPDoc blocks for all public methods
   - Complex logic explained with comments
   - README updated with:
     - Installation instructions
     - Development setup (SQLite, no Redis)
     - Testing instructions (especially browser tests)
     - Contribution guidelines

2. **API Documentation** (if applicable)
   - Not needed for Phase 1 MVP

3. **User Guide** (Optional for MVP)
   - Basic usage instructions
   - Can be in-app tooltips instead

## Acceptance Criteria
- [ ] Browser tests for ALL critical user flows (authentication, groups, cleaning, invites)
- [ ] All feature tests written and passing
- [ ] All unit tests written and passing
- [ ] Test coverage > 80%
- [ ] No failing tests
- [ ] README.md updated and clear
- [ ] Code well-documented
- [ ] Installation instructions verified
- [ ] Setup process documented (emphasizing SQLite simplicity)

## Related Files
- All files in `tests/`, especially `tests/Browser/`
- `README.md`
- PHPDoc blocks in all models and actions

## Next Steps
After completion, proceed to Task 14: Security & Performance Audit
