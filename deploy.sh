#!/bin/bash
# ==============================================================================
# APS DREAM HOME — ZERO-DOWNTIME ONE-COMMAND DEPLOYMENT SCRIPT
# Run on VPS: ./deploy.sh
# ==============================================================================

set -euo pipefail

APP_DIR="/var/www/apsdreamhome"
BRANCH="${1:-main}"

echo "================================================================="
echo "   APS DREAM HOME — DEPLOYING REVISION FROM $BRANCH"
echo "================================================================="

cd "$APP_DIR"

# 1. Maintenance Mode (Optional, if needed)
# touch storage/framework/down

# 2. Pull latest code
echo ">>> Pulling latest code from Git..."
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull origin "$BRANCH"

# 3. Install composer dependencies (optimized for production)
echo ">>> Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# 4. Run database migrations / updates if applicable
echo ">>> Running database updates / index checks..."
if [ -f "scripts/apply_critical_indexes.php" ]; then
    php scripts/apply_critical_indexes.php || true
fi

# 5. Fix permissions for storage and upload directories
echo ">>> Setting directory permissions..."
mkdir -p storage/sessions storage/cache storage/logs storage/reports public/uploads
chown -R www-data:www-data storage public/uploads logs
chmod -R 775 storage public/uploads logs

# 6. Restart services
echo ">>> Reloading PHP-FPM and Nginx..."
if systemctl is-active --quiet php8.3-fpm; then
    systemctl reload php8.3-fpm
elif systemctl is-active --quiet php8.2-fpm; then
    systemctl reload php8.2-fpm
fi

if systemctl is-active --quiet apsdreamhome-websocket; then
    systemctl restart apsdreamhome-websocket
fi

systemctl reload nginx

# 7. Verification Probe
echo ">>> Running Health Check..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://apsdreamhome.com/ || curl -s -o /dev/null -w "%{http_code}" http://localhost/)
echo "Deploy complete! Health Check HTTP Response: $HTTP_STATUS"
