#!/bin/bash
set -e

echo "🚀 Deploying SweepSquad..."

# Maintenance mode
echo "📦 Enabling maintenance mode..."
php artisan down --retry=60 --refresh=5

# Update code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev
npm ci

# Build frontend
echo "🔨 Building frontend assets..."
npm run build

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear old caches
echo "🧹 Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Back online
echo "🌟 Bringing application back online..."
php artisan up

echo "✅ Deployment complete!"
echo "🔍 Checking queue status..."
php artisan queue:monitor default
