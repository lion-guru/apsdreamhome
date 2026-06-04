#!/bin/sh
# =============================================================================
# WebSocket Container Startup Script
# Waits for app + db healthchecks, then starts the Ratchet WebSocket server
# =============================================================================
set -eu

# Paths
APP_DIR="/var/www/html"
LOG_DIR="/var/log"
WS_SERVER="${APP_DIR}/websocket_server.php"

echo "[$(date +%FT%T)] WebSocket container starting..."

# ---------------------------------------------------------------------------
# 1. Wait for database to be ready (max 60s)
# ---------------------------------------------------------------------------
if [ -n "${DB_HOST:-}" ]; then
    echo "[$(date +%FT%T)] Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
    i=0
    while [ $i -lt 60 ]; do
        if nc -z "${DB_HOST}" "${DB_PORT:-3306}" 2>/dev/null; then
            echo "[$(date +%FT%T)] Database port is reachable."
            break
        fi
        i=$((i + 1))
        sleep 1
    done
    if [ $i -ge 60 ]; then
        echo "[$(date +%FT%T)] ERROR: Database not reachable after 60s. Starting anyway..."
    fi
fi

# ---------------------------------------------------------------------------
# 2. Wait for the app container to be healthy (best-effort HTTP probe)
# ---------------------------------------------------------------------------
if [ -n "${APP_HOST:-}" ]; then
    echo "[$(date +%FT%T)] Waiting for app container ${APP_HOST} to be healthy..."
    i=0
    while [ $i -lt 30 ]; do
        if nc -z "${APP_HOST}" 80 2>/dev/null; then
            echo "[$(date +%FT%T)] App port is reachable."
            break
        fi
        i=$((i + 1))
        sleep 2
    done
fi

# ---------------------------------------------------------------------------
# 3. Verify websocket_server.php exists
# ---------------------------------------------------------------------------
if [ ! -f "${WS_SERVER}" ]; then
    echo "[$(date +%FT%T)] FATAL: ${WS_SERVER} not found!"
    exit 1
fi

# ---------------------------------------------------------------------------
# 4. Verify vendor/autoload.php exists (composer install)
# ---------------------------------------------------------------------------
if [ ! -f "${APP_DIR}/vendor/autoload.php" ]; then
    echo "[$(date +%FT%T)] vendor/ missing - running composer install..."
    composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --optimize-autoloader \
        --prefer-dist \
        --no-progress \
        --ignore-platform-reqs \
    || echo "[$(date +%FT%T)] composer install failed, continuing anyway"
fi

# ---------------------------------------------------------------------------
# 5. Start the WebSocket server
# ---------------------------------------------------------------------------
echo "[$(date +%FT%T)] Starting Ratchet WebSocket server on 0.0.0.0:8080..."
cd "${APP_DIR}"

# Use exec to make PHP the PID 1 process (proper signal handling)
exec php "${WS_SERVER}" 2>&1 | tee -a "${LOG_DIR}/websocket.log"
