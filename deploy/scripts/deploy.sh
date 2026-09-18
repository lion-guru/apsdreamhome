#!/bin/bash
# APS Dream Home - Production Deploy Script
# Usage: ./deploy.sh [branch] (default: main)

set -e

BRANCH="${1:-main}"
APP_DIR="/var/www/apsdreamhome"
REPO_URL="https://github.com/lion-guru/apsdreamhome.git"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/apsdreamhome"

echo "=========================================="
echo "APS Dream Home Deploy - $TIMESTAMP"
echo "Branch: $BRANCH"
echo "=========================================="

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup current release (if exists)
if [ -d "$APP_DIR" ]; then
    echo "📦 Backing up current release..."
    tar -czf "$BACKUP_DIR/apsdreamhome_${TIMESTAMP}.tar.gz" -C "$APP_DIR" . 2>/dev/null || true
    echo "   Backup saved: $BACKUP_DIR/apsdreamhome_${TIMESTAMP}.tar.gz"
fi

# Clone or pull latest code
if [ ! -d "$APP_DIR/.git" ]; then
    echo "📥 Cloning repository..."
    git clone -b "$BRANCH" "$REPO_URL" "$APP_DIR"
else
    echo "🔄 Pulling latest changes..."
    cd "$APP_DIR"
    git fetch origin
    git checkout "$BRANCH"
    git pull origin "$BRANCH"
fi

cd "$APP_DIR"

# Install/update dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Generate optimized autoloader
composer dump-autoload --optimize --classmap-authoritative

# Laravel-style optimizations (if applicable)
if [ -f "artisan" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Custom framework cache clear
if [ -f "public/index.php" ]; then
    # Clear any cached views/config
    rm -rf storage/framework/cache/* 2>/dev/null || true
    rm -rf storage/framework/views/* 2>/dev/null || true
fi

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# Run database migrations (if using migration system)
if [ -f "scripts/migrate.php" ]; then
    echo "🗄️ Running database migrations..."
    php scripts/migrate.php
fi

# Clear OPcache
echo "🧹 Clearing OPcache..."
php -r 'if (function_exists("opcache_reset")) opcache_reset();' 2>/dev/null || true

# Reload PHP-FPM and Nginx
echo "🔄 Reloading services..."
systemctl reload php8.3-fpm
systemctl reload nginx

# Health check
echo "🏥 Running health check..."
sleep 2
HEALTH=$(curl -s -o /dev/null -w "%{http_code}" https://apsdreamhome.com/ || echo "000")
if [ "$HEALTH" = "200" ]; then
    echo "✅ Health check PASSED (HTTP 200)"
else
    echo "❌ Health check FAILED (HTTP $HEALTH)"
    exit 1
fi

echo "=========================================="
echo "✅ DEPLOY COMPLETE - $TIMESTAMP"
echo "=========================================="
echo "Site: https://apsdreamhome.com"
echo "Branch: $BRANCH"
echo "Commit: $(git rev-parse --short HEAD)"