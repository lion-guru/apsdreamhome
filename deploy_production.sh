#!/bin/bash
# APS Dream Home Production Deployment Script
# Generated on: 2026-03-03 12:29:27

set -e

echo "🚀 Starting Production Deployment..."

# Variables
PROJECT_DIR="/var/www/apsdreamhome"
BACKUP_DIR="/var/backups/apsdreamhome"
GIT_REPO="https://github.com/your-username/apsdreamhome.git"
BRANCH="production"

# Create backup
echo "💾 Creating backup..."
mkdir -p $BACKUP_DIR
mysqldump -u root apsdreamhome_prod > $BACKUP_DIR/db_backup_$(date +%Y%m%d_%H%M%S).sql
tar -czf $BACKUP_DIR/files_backup_$(date +%Y%m%d_%H%M%S).tar.gz $PROJECT_DIR

# Pull latest code
echo "📥 Pulling latest code..."
cd $PROJECT_DIR
git pull origin $BRANCH

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm ci --production

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Set permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 777 $PROJECT_DIR/storage
chmod -R 777 $PROJECT_DIR/bootstrap/cache

# Restart services
echo "🔄 Restarting services..."
systemctl reload nginx
systemctl reload php8.1-fpm
systemctl restart redis-server

# Health check
echo "🏥 Running health check..."
curl -f http://localhost/health || exit 1

echo "✅ Deployment completed successfully!"
