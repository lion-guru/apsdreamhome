#!/bin/bash
# =============================================================================
# APS Dream Home - Docker Entrypoint
# 1. Wait for the database to be ready
# 2. Run migrations and seed data (first boot only)
# 3. Set permissions
# 4. Start cron (for scheduled tasks)
# 5. Exec the given command (default: apache2-foreground)
# =============================================================================
set -e

# Paths
APP_DIR="/var/www/html"
STORAGE_DIR="${APP_DIR}/storage"
LOG_DIR="/var/log/apache2"
PHP_ERROR_LOG="${LOG_DIR}/php_error.log"

# Colors for nicer output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${GREEN}[entrypoint]${NC} $(date +%FT%T) $*"; }
warn()  { echo -e "${YELLOW}[entrypoint]${NC} $(date +%FT%T) $*"; }
error() { echo -e "${RED}[entrypoint]${NC} $(date +%FT%T) $*" >&2; }

# ---------------------------------------------------------------------------
# 1. Wait for the database
# ---------------------------------------------------------------------------
if [ -n "${DB_HOST:-}" ]; then
    log "Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
    i=0
    max_attempts=60
    while [ $i -lt $max_attempts ]; do
        if (echo > "/dev/tcp/${DB_HOST}/${DB_PORT:-3306}") 2>/dev/null; then
            log "Database port is reachable."
            break
        fi
        i=$((i + 1))
        if [ $((i % 5)) -eq 0 ]; then
            warn "Still waiting for database... (${i}/${max_attempts})"
        fi
        sleep 1
    done

    if [ $i -ge $max_attempts ]; then
        error "Database not reachable after ${max_attempts}s. Continuing anyway."
    fi

    # Give MySQL a few more seconds to finish initializing
    sleep 2
fi

# ---------------------------------------------------------------------------
# 2. Wait for Redis
# ---------------------------------------------------------------------------
if [ -n "${REDIS_HOST:-}" ]; then
    log "Waiting for Redis ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    i=0
    while [ $i -lt 30 ]; do
        if (echo > "/dev/tcp/${REDIS_HOST}/${REDIS_PORT:-6379}") 2>/dev/null; then
            log "Redis port is reachable."
            break
        fi
        i=$((i + 1))
        sleep 1
    done
fi

# ---------------------------------------------------------------------------
# 3. Composer install (fallback if vendor/ is empty)
# ---------------------------------------------------------------------------
if [ ! -f "${APP_DIR}/vendor/autoload.php" ]; then
    log "vendor/ missing - running composer install..."
    cd "${APP_DIR}"
    composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --optimize-autoloader \
        --prefer-dist \
        --no-progress \
        --ignore-platform-reqs \
    || warn "composer install had issues, continuing..."
fi

# ---------------------------------------------------------------------------
# 4. Run database migrations (first-boot only - guarded by sentinel file)
# ---------------------------------------------------------------------------
SENTINEL_FILE="${STORAGE_DIR}/.migrated"
if [ -n "${DB_HOST:-}" ] && [ ! -f "${SENTINEL_FILE}" ]; then
    log "First boot detected - running database migrations..."

    cd "${APP_DIR}"

    # Make all scripts executable
    find scripts -maxdepth 1 -type f -name "*.sh" -exec chmod +x {} \; 2>/dev/null || true

    # Run the application's migration system
    if [ -f "scripts/create_migrations_table.php" ]; then
        php scripts/create_migrations_table.php 2>&1 | sed 's/^/  /' || warn "create_migrations_table failed"
    fi

    if [ -f "scripts/track_migration.php" ]; then
        php scripts/track_migration.php 2>&1 | sed 's/^/  /' || warn "track_migration failed"
    fi

    # Seed default data
    for seed_script in scripts/seed_*.php; do
        if [ -f "$seed_script" ]; then
            log "Running seed: $seed_script"
            php "$seed_script" 2>&1 | sed 's/^/  /' || warn "seed $seed_script had errors"
        fi
    done

    # Mark migrations as complete
    touch "${SENTINEL_FILE}"
    log "Migrations complete (sentinel written to ${SENTINEL_FILE})"
else
    log "Migrations already applied (or DB host not set) - skipping"
fi

# ---------------------------------------------------------------------------
# 5. Set permissions
# ---------------------------------------------------------------------------
log "Setting file permissions..."
mkdir -p \
    "${STORAGE_DIR}/logs" \
    "${STORAGE_DIR}/cache" \
    "${STORAGE_DIR}/uploads" \
    "${STORAGE_DIR}/sessions" \
    "${LOG_DIR}"

chown -R www-data:www-data "${STORAGE_DIR}" 2>/dev/null || true
chown -R www-data:www-data "${APP_DIR}/public/uploads" 2>/dev/null || true
chown -R www-data:www-data "${APP_DIR}/public/assets/uploads" 2>/dev/null || true
chown -R www-data:www-data "${LOG_DIR}" 2>/dev/null || true

chmod -R 775 "${STORAGE_DIR}" 2>/dev/null || true
chmod -R 775 "${APP_DIR}/public/uploads" 2>/dev/null || true
chmod -R 775 "${APP_DIR}/public/assets/uploads" 2>/dev/null || true

touch "${PHP_ERROR_LOG}" 2>/dev/null || true
chown www-data:www-data "${PHP_ERROR_LOG}" 2>/dev/null || true

# ---------------------------------------------------------------------------
# 6. Install cron schedule (if present)
# ---------------------------------------------------------------------------
if [ -f /etc/cron.d/apsdream ]; then
    log "Installing cron schedule..."
    chmod 0644 /etc/cron.d/apsdream
    crontab -u www-data /etc/cron.d/apsdream 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 7. Clear / warm up caches
# ---------------------------------------------------------------------------
log "Warming up opcache and clearing cache files..."
find "${STORAGE_DIR}/cache" -type f -name "*.php" -delete 2>/dev/null || true

# ---------------------------------------------------------------------------
# 8. Print a friendly banner
# ---------------------------------------------------------------------------
log "=========================================================="
log "  APS Dream Home - Container Ready"
log "  ENV: ${APP_ENV:-production}  DEBUG: ${APP_DEBUG:-false}"
log "  DB:  ${DB_HOST:-?} / ${DB_DATABASE:-?}"
log "  APP: $([ "${APP_DEBUG:-false}" = "true" ] && echo 'DEBUG MODE' || echo 'PRODUCTION')"
log "=========================================================="

# ---------------------------------------------------------------------------
# 9. Start cron in the background (if not running)
# ---------------------------------------------------------------------------
if [ -z "${SKIP_CRON:-}" ]; then
    service cron status >/dev/null 2>&1 || service cron start >/dev/null 2>&1 || cron
    log "Cron service started"
fi

# ---------------------------------------------------------------------------
# 10. Exec the given command
# ---------------------------------------------------------------------------
log "Starting: $*"
exec "$@"
