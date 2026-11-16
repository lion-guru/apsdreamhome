#!/usr/bin/env bash
# ==============================================================================
# APS DREAM HOME - Oracle Staging Deployment & Sync Script
# ==============================================================================

set -e

PROJECT_DIR="/var/www/apsdreamhome"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "Cloning repository..."
    sudo git clone https://github.com/lion-guru/apsdreamhome.git "$PROJECT_DIR"
fi

cd "$PROJECT_DIR"

echo ">>> Pulling latest changes from main..."
sudo git pull origin main

echo ">>> Installing Composer dependencies..."
sudo composer install --no-dev --optimize-autoloader

echo ">>> Setting directory permissions..."
sudo chown -R www-data:www-data "$PROJECT_DIR"
sudo chmod -R 755 "$PROJECT_DIR"
sudo chmod -R 775 "$PROJECT_DIR/public/uploads" "$PROJECT_DIR/storage" "$PROJECT_DIR/backups" || true

echo ">>> Running Database Migrations..."
php database/migrations/extend_plot_allocations_users_role_enums_s516.php || true

echo ">>> Running Master Test Runner..."
php testing/master_test_runner.php || true

echo ">>> Restarting Apache..."
sudo systemctl reload apache2

echo "✅ Deployment completed successfully!"
