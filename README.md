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

## Deployment

### Pre-Deployment Checklist

1. **Environment Configuration**
   - Copy `.env.example` to `.env`
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Set `APP_URL` to your production domain
   - Generate a strong `APP_KEY` with `php artisan key:generate`
   - Set `SESSION_SECURE_COOKIE=true` (requires HTTPS)
   - Configure database connection (SQLite, MySQL, or PostgreSQL)
   - Configure mail settings for notifications
   - Set proper `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`

2. **Database Setup**
   - For SQLite: Create the database file and set proper permissions
     ```bash
     touch database/database.sqlite
     chmod 664 database/database.sqlite
     ```
   - For MySQL/PostgreSQL: Create database and user, update `.env`
   - Run migrations:
     ```bash
     php artisan migrate --force
     ```

3. **Install Dependencies**
   ```bash
   # Install PHP dependencies (production only)
   composer install --optimize-autoloader --no-dev

   # Install JavaScript dependencies
   npm ci

   # Build frontend assets
   npm run build
   ```

4. **Optimize Application**
   ```bash
   # Cache configuration
   php artisan config:cache

   # Cache routes
   php artisan route:cache

   # Cache views
   php artisan view:cache
   ```

5. **Set File Permissions**
   ```bash
   # Ensure storage and bootstrap/cache are writable
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. **Configure Web Server**
   - Point document root to `public/` directory
   - Enable HTTPS with valid SSL certificate
   - Configure URL rewriting (see below)

### Web Server Configuration

**Apache (.htaccess included)**

The `public/.htaccess` file handles URL rewriting. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Nginx**

Add this to your server block:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/sweepsquad/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Queue Workers & Scheduler

**Queue Worker**

Use Supervisor to keep the queue worker running:

```ini
[program:sweepsquad-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/sweepsquad/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/sweepsquad/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sweepsquad-worker:*
```

**Scheduler**

Add this to your crontab (`crontab -e`):

```cron
* * * * * cd /path/to/sweepsquad && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler runs these jobs:
- `UpdateUserStreaksJob` - Daily at midnight
- `CleanupExpiredInvites` - Daily at 2am

### Monitoring & Logging

**Logs Location**: `storage/logs/laravel.log`

For production, set in `.env`:
```env
LOG_STACK=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=14
```

**Recommended Monitoring**:
- Set up uptime monitoring (e.g., UptimeRobot, Pingdom)
- Optional: Integrate error tracking (Sentry, Flare, Bugsnag)
- Monitor disk space for SQLite database growth
- Monitor queue worker status

### Backup Strategy

**Database Backups**

For SQLite:
```bash
# Backup the database file
cp database/database.sqlite database/backups/database-$(date +%Y%m%d).sqlite

# Or use SQLite's backup command
sqlite3 database/database.sqlite ".backup 'database/backups/backup-$(date +%Y%m%d).sqlite'"
```

For MySQL/PostgreSQL:
```bash
# MySQL
mysqldump -u username -p database_name > backup-$(date +%Y%m%d).sql

# PostgreSQL
pg_dump database_name > backup-$(date +%Y%m%d).sql
```

**Automated Daily Backups**

Add to crontab:
```cron
0 2 * * * cd /path/to/sweepsquad && cp database/database.sqlite database/backups/database-$(date +\%Y\%m\%d).sqlite
```

**File Backups**

If storing user uploads:
```bash
# Backup storage directory
tar -czf storage-backup-$(date +%Y%m%d).tar.gz storage/app/public
```

### Deployment Updates

When deploying updates:

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# 4. Build frontend
npm run build

# 5. Run migrations
php artisan migrate --force

# 6. Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart queue workers
php artisan queue:restart

# 8. Bring application back up
php artisan up
```

### Deployment Script

Create a `deploy.sh` script:

```bash
#!/bin/bash
set -e

echo "🚀 Deploying SweepSquad..."

# Maintenance mode
php artisan down

# Update code
git pull origin main

# Dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Database
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue
php artisan queue:restart

# Back online
php artisan up

echo "✅ Deployment complete!"
```

### SSL Certificate

**Using Let's Encrypt (Free)**:

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate (Nginx)
sudo certbot --nginx -d your-domain.com

# Obtain certificate (Apache)
sudo certbot --apache -d your-domain.com

# Auto-renewal is configured automatically
```

### Post-Deployment Verification

1. Visit your domain and verify HTTPS is working
2. Test user registration and login
3. Test creating groups and invites
4. Test marking items as cleaned
5. Verify coins and streaks are updating
6. Check logs for any errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```
7. Verify queue worker is processing jobs:
   ```bash
   php artisan queue:monitor
   ```

### Troubleshooting

**500 Internal Server Error**:
- Check storage/logs/laravel.log
- Verify file permissions on storage/ and bootstrap/cache/
- Ensure APP_KEY is set in .env
- Check web server error logs

**Assets not loading**:
- Verify npm run build completed successfully
- Check public/build/ directory exists and contains files
- Clear browser cache

**Queue not processing**:
- Verify supervisor is running: `sudo supervisorctl status`
- Check worker logs in storage/logs/
- Restart worker: `php artisan queue:restart`

**Database connection errors**:
- For SQLite: Verify file exists and is writable
- For MySQL/PostgreSQL: Check credentials in .env
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

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
