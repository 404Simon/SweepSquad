# SweepSquad

A gamified household cleaning tracker that makes chores fun through coins, streaks, and achievements.

## Features

- **Group Management**: Create and manage household groups with multiple members
- **Cleaning Items**: Hierarchical organization of cleaning tasks (rooms -> specific tasks)
- **Gamification**: Earn coins, maintain streaks, and unlock achievements
- **Smart Bonuses**: Bonus coins for overdue items, perfect timing, and long streaks
- **Leaderboards**: Compete with household members
- **Invite System**: Multiple invite types (permanent, single-use, time-limited)
- **Mobile Responsive**: Works great on phones, tablets, and desktops

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: Livewire 3, Volt, Flux UI
- **Styling**: Tailwind CSS 4
- **Database**: SQLite (default), supports MySQL/PostgreSQL
- **Testing**: Pest 4 with Browser Testing
- **Code Quality**: Rector, Laravel Pint

## Installation

### Requirements

- PHP 8.4+
- Composer
- Node.js & NPM
- SQLite extension enabled (default) or MySQL/PostgreSQL

### Setup Steps

1. Clone the repository:
```bash
git clone <repository-url>
cd SweepSquad
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Copy the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Create SQLite database (default):
```bash
touch database/database.sqlite
```

7. Run migrations:
```bash
php artisan migrate
```

8. Build frontend assets:
```bash
npm run build
# OR for development with hot reload:
npm run dev
```

9. Start the development server:
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Development

### Running the Application

For development, run both the Laravel server and the Vite dev server:

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite Dev Server (hot reload)
npm run dev
```

Or use the combined command:
```bash
composer run dev
```

### Database

The application uses SQLite by default for simplicity. The database file is located at `database/database.sqlite`.

To reset and reseed the database:
```bash
php artisan migrate:fresh --seed
```

### Code Quality

The project uses Rector and Laravel Pint for code quality:

```bash
# Refactor code with Rector
vendor/bin/rector

# Format code with Pint
vendor/bin/pint
```

Always run these before committing:
```bash
vendor/bin/rector
vendor/bin/pint
php artisan test
```

## Testing

The application has comprehensive test coverage using Pest 4, including browser tests.

### Running Tests

Run all tests:
```bash
php artisan test
```

Run specific test suites:
```bash
# Browser tests (end-to-end)
php artisan test tests/Browser/

# Feature tests
php artisan test tests/Feature/

# Unit tests
php artisan test tests/Unit/
```

Run a specific test file:
```bash
php artisan test tests/Browser/AuthenticationTest.php
```

Filter tests by name:
```bash
php artisan test --filter="user can register"
```

### Test Structure

- **Browser Tests** (`tests/Browser/`): End-to-end tests using real browser
  - AuthenticationTest.php
  - DashboardTest.php
  - GroupsTest.php
  - CleaningItemsTest.php
  - MarkAsCleanedTest.php
  - InviteFlowsTest.php
  - LeaderboardAndAchievementsTest.php

- **Feature Tests** (`tests/Feature/`): Integration tests
  - Auth/
  - Groups/
  - CleaningItems/
  - Invites/
  - Settings/

- **Unit Tests** (`tests/Unit/`): Isolated component tests
  - Model methods
  - Jobs
  - Calculations

### Browser Testing

Browser tests use Pest 4's built-in browser testing capabilities. They run in a real browser and test the full user experience.

Example:
```php
test('user can create a group', function () {
    $user = User::factory()->create();
    actingAs($user);

    visit('/groups/create')
        ->assertNoSmoke()
        ->fill('name', 'My Household')
        ->click('Create Group')
        ->assertPathIs('/groups/*')
        ->assertSee('My Household');
});
```

## Architecture

### Models

- **User**: Authentication, coins, streaks
- **Group**: Household groups
- **GroupMember**: Pivot model with roles
- **CleaningItem**: Cleaning tasks (hierarchical)
- **CleaningLog**: History of cleanings
- **GroupInvite**: Invitation system
- **UserAchievement**: Achievement tracking

### Actions Pattern

Business logic is organized using the Actions pattern. Actions are single-responsibility classes that handle complex operations:

```php
final readonly class CreateGroupAction
{
    public function handle(User $owner, string $name, ?string $description = null): Group
    {
        return DB::transaction(function () use ($owner, $name, $description): Group {
            // Create group and add owner as member
        });
    }
}
```

Actions are located in `app/Actions/` organized by domain:
- `Actions/Groups/` - Group management
- `Actions/CleaningItems/` - Cleaning item operations
- `Actions/Invites/` - Invite system
- `Actions/Achievements/` - Achievement checking

### Livewire Components

The application uses Livewire Volt for interactive components. Volt components combine PHP logic and Blade templates in single files:

- Class-based components in `resources/views/livewire/`
- Support both functional and class-based syntax
- Real-time updates with wire:model.live
- Event-driven communication between components

### Frontend

- **Flux UI**: Component library for Livewire
- **Tailwind CSS 4**: Utility-first styling
- **Alpine.js**: Included with Livewire for interactivity

## Key Features Explained

### Coin System

Users earn coins by cleaning items. Coin amounts are calculated with bonuses:

- **Base Reward**: Set per cleaning item
- **Dirtiness Bonus**: 
  - 80-99% dirty: +20% coins
  - 100%+ dirty (overdue): +50% coins
- **Streak Bonus**:
  - 7+ day streak: +10% coins
  - 14+ day streak: +20% coins
- **Speed Bonus**: Clean before 80% dirty: +5% coins

### Streak System

- Cleaning any item counts toward your daily streak
- Streak increments when you clean on consecutive days
- Streak resets if you skip more than one day

### Achievements

Automatic achievement detection for:
- First cleaning
- Milestone coin totals
- Streak milestones
- Group activities

### Invite System

**How to Invite Group Members:**

1. Navigate to your group's page
2. As an owner or admin, you'll see an "Invite Links" section
3. Click "Create Invite" button
4. Choose invite type:
   - **Permanent**: Never expires, unlimited uses
   - **Single-Use**: Expires after one person joins  
   - **Time-Limited**: Set expiration in days (1-365)
5. Click "Create Invite" to generate the link
6. Click "Copy Link" to copy the invite URL
7. Share the link with people you want to invite
8. They'll click the link and join your group!

**Managing Invites:**
- View all active and expired invites in the "Invite Links" section
- Copy invite links with the "Copy Link" button
- Revoke invites at any time with the "Revoke" button
- See invite status (active, used, or expired)

## Contributing

1. Follow existing code conventions
2. Write tests for new features
3. Run code quality tools before committing:
   ```bash
   vendor/bin/rector
   vendor/bin/pint
   php artisan test
   ```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
