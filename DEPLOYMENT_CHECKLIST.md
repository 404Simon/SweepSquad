# SweepSquad Deployment Checklist

This is a quick reference checklist for deploying SweepSquad to production. For full details, see the Deployment section in README.md.

## Pre-Deployment

- [ ] Domain name registered and DNS configured
- [ ] Server provisioned (VPS, shared hosting, etc.)
- [ ] PHP 8.4+ installed
- [ ] Composer installed
- [ ] Node.js & NPM installed
- [ ] Web server configured (Apache/Nginx)
- [ ] SSL certificate obtained (Let's Encrypt recommended)

## Initial Setup

```bash
# 1. Clone repository
git clone <repository-url> /var/www/sweepsquad
cd /var/www/sweepsquad

# 2. Copy and configure environment
cp .env.example .env
nano .env  # Edit configuration

# 3. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# 4. Generate application key
php artisan key:generate

# 5. Create database (SQLite example)
touch database/database.sqlite
chmod 664 database/database.sqlite

# 6. Run migrations
php artisan migrate --force

# 7. Build frontend
npm run build

# 8. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 9. Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Production Environment Variables

Required in `.env`:

```env
APP_NAME=SweepSquad
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=<generated-key>

SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

LOG_STACK=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=14

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

## Queue Worker Setup

```bash
# 1. Copy supervisor config
sudo cp supervisor-sweepsquad.conf.example /etc/supervisor/conf.d/sweepsquad.conf

# 2. Edit paths
sudo nano /etc/supervisor/conf.d/sweepsquad.conf

# 3. Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sweepsquad-worker:*
```

## Scheduler Setup

```bash
# Add to crontab
crontab -e

# Add this line:
* * * * * cd /var/www/sweepsquad && php artisan schedule:run >> /dev/null 2>&1
```

## Post-Deployment Verification

- [ ] Visit https://your-domain.com - homepage loads
- [ ] HTTPS working (no certificate warnings)
- [ ] Can register new account
- [ ] Can login
- [ ] Can create group
- [ ] Can create cleaning items
- [ ] Can mark items as cleaned
- [ ] Coins and streaks update correctly
- [ ] Check logs: `tail -f storage/logs/laravel.log`
- [ ] Verify queue: `php artisan queue:monitor default`
- [ ] Verify scheduler: `php artisan schedule:list`

## Deployment Updates

Use the provided script:

```bash
cd /var/www/sweepsquad
./deploy.sh
```

Or manually:

```bash
php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

## Backup Setup

Add to crontab for daily backups:

```cron
# Daily database backup at 2 AM
0 2 * * * cd /var/www/sweepsquad && cp database/database.sqlite database/backups/database-$(date +\%Y\%m\%d).sqlite

# Clean up backups older than 30 days
0 3 * * * find /var/www/sweepsquad/database/backups -name "database-*.sqlite" -mtime +30 -delete
```

## Monitoring

- [ ] Set up uptime monitoring (UptimeRobot, Pingdom, etc.)
- [ ] Configure log rotation for Laravel logs
- [ ] Monitor disk space (SQLite database growth)
- [ ] Monitor queue worker status
- [ ] Optional: Set up error tracking (Sentry, Flare)

## Troubleshooting

**500 Error:**
- Check `storage/logs/laravel.log`
- Verify file permissions
- Ensure APP_KEY is set

**Assets not loading:**
- Run `npm run build`
- Clear browser cache
- Check public/build/ directory

**Queue not processing:**
- Check supervisor: `sudo supervisorctl status`
- Restart: `php artisan queue:restart`
- Check logs: `storage/logs/worker.log`

**Database errors:**
- Verify database file exists and is writable
- Check .env DB_CONNECTION setting
- Run `php artisan migrate:status`

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] SESSION_SECURE_COOKIE=true
- [ ] HTTPS enforced
- [ ] File permissions correct (775 for storage, 755 for others)
- [ ] .env file not publicly accessible
- [ ] Database file not in public directory
- [ ] Regular backups configured
- [ ] Server firewall configured
- [ ] Only necessary ports open (80, 443, 22)

## Performance Optimization

- [ ] Opcache enabled in PHP
- [ ] Configurations cached (config, routes, views)
- [ ] Composer autoloader optimized (--optimize-autoloader)
- [ ] Frontend assets minified (npm run build)
- [ ] Database indexes on frequently queried columns
- [ ] Consider CDN for static assets (if needed)

---

For detailed documentation, see:
- README.md - Full deployment guide
- deploy.sh - Automated deployment script
- supervisor-sweepsquad.conf.example - Queue worker configuration
