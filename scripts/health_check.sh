#!/usr/bin/env bash
# =============================================================================
# APS Dream Home - Production Health Check
#
# Verifies that all services are up, healthy, and the system is in a
# good state. Outputs JSON for monitoring tools (Prometheus, Datadog, etc.)
#
# Usage:
#   ./scripts/health_check.sh                 # human-readable output
#   ./scripts/health_check.sh --json         # JSON output (for monitoring)
#   ./scripts/health_check.sh --quiet        # exit code only (0=ok, !=0=fail)
#   ./scripts/health_check.sh --url=https://example.com
#
# Exit codes:
#   0 = all healthy
#   1 = one or more critical services down
#   2 = warnings (non-critical)
#   3 = script error
# =============================================================================
set -uo pipefail

# ------------------------------------------------------------------
# Configuration
# ------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_DIR}"

# Load env
if [ -f production.env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./production.env
    set +a
elif [ -f .env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./.env
    set +a
fi

APP_URL="${APP_URL:-http://localhost}"
HTTP_PORT="${HTTP_PORT:-80}"
HTTPS_PORT="${HTTPS_PORT:-443}"
WS_PORT="${WEBSOCKET_PORT:-8080}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-apsdreamhome}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
REDIS_PORT="${REDIS_PORT:-6379}"
REDIS_PASSWORD="${REDIS_PASSWORD:-}"
BACKUP_DIR="${PROJECT_DIR}/backups"
DISK_THRESHOLD_WARN=80
DISK_THRESHOLD_CRIT=90

# Parse args
OUTPUT_MODE="human"
while [ $# -gt 0 ]; do
    case "$1" in
        --json)    OUTPUT_MODE="json"; shift ;;
        --quiet)   OUTPUT_MODE="quiet"; shift ;;
        --url=*)   APP_URL="${1#*=}"; shift ;;
        -h|--help)
            grep -E '^# ' "$0" | sed 's/^# //'
            exit 0
            ;;
        *) shift ;;
    esac
done

# ------------------------------------------------------------------
# Helpers
# ------------------------------------------------------------------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Counters
TOTAL=0
OK=0
WARN=0
CRIT=0

declare -A RESULTS

check() {
    local name="$1"
    local status="$2"  # ok | warn | crit
    local detail="$3"
    TOTAL=$((TOTAL + 1))
    RESULTS["$name"]="${status}|${detail}"
    case "$status" in
        ok)   OK=$((OK + 1)) ;;
        warn) WARN=$((WARN + 1)) ;;
        crit) CRIT=$((CRIT + 1)) ;;
    esac
}

# ------------------------------------------------------------------
# 1. HTTP endpoint
# ------------------------------------------------------------------
HTTP_STATUS=$(curl -fsS -o /dev/null -w "%{http_code}" -m 10 "${APP_URL}/health" 2>/dev/null || echo "000")
if [ "${HTTP_STATUS}" = "200" ]; then
    check "http" "ok" "HTTP ${HTTP_STATUS} on ${APP_URL}/health"
elif [ "${HTTP_STATUS}" = "000" ]; then
    check "http" "crit" "Cannot reach ${APP_URL}/health"
else
    check "http" "crit" "HTTP ${HTTP_STATUS} on ${APP_URL}/health"
fi

# ------------------------------------------------------------------
# 2. Database
# ------------------------------------------------------------------
if command -v docker >/dev/null 2>&1 && docker compose ps db 2>/dev/null | grep -qE "Up|running"; then
    DB_OK=$(docker compose exec -T db mysqladmin ping -h 127.0.0.1 -u root -p"${MYSQL_ROOT_PASSWORD:-${DB_PASSWORD}}" 2>/dev/null && echo "yes" || echo "no")
elif command -v mysqladmin >/dev/null 2>&1; then
    DB_OK=$(mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" 2>/dev/null && echo "yes" || echo "no")
else
    DB_OK="no-client"
fi

if [ "${DB_OK}" = "yes" ]; then
    # Get DB size and table count
    if [ -n "${MYSQL_ROOT_PASSWORD:-}" ]; then
        DB_STATS=$(docker compose exec -T db mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -N -B -e \
            "SELECT table_count, size_mb FROM (SELECT COUNT(*) AS table_count, ROUND(SUM(data_length+index_length)/1024/1024, 1) AS size_mb FROM information_schema.tables WHERE table_schema='${DB_DATABASE}') t;" 2>/dev/null | head -1 || echo "n/a")
    else
        DB_STATS="n/a"
    fi
    check "database" "ok" "MySQL responsive (${DB_STATS})"
else
    check "database" "crit" "MySQL not responding at ${DB_HOST}:${DB_PORT}"
fi

# ------------------------------------------------------------------
# 3. Redis
# ------------------------------------------------------------------
if command -v docker >/dev/null 2>&1 && docker compose ps redis 2>/dev/null | grep -qE "Up|running"; then
    REDIS_OK=$(docker compose exec -T redis redis-cli ping 2>/dev/null | grep -q PONG && echo "yes" || echo "no")
elif command -v redis-cli >/dev/null 2>&1; then
    if [ -n "${REDIS_PASSWORD}" ]; then
        REDIS_OK=$(redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" -a "${REDIS_PASSWORD}" ping 2>/dev/null | grep -q PONG && echo "yes" || echo "no")
    else
        REDIS_OK=$(redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" ping 2>/dev/null | grep -q PONG && echo "yes" || echo "no")
    fi
else
    REDIS_OK="no-client"
fi

if [ "${REDIS_OK}" = "yes" ]; then
    # Get Redis memory usage
    REDIS_INFO=$(redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" info memory 2>/dev/null | grep "used_memory_human" | cut -d: -f2 | tr -d '\r' || echo "n/a")
    check "redis" "ok" "Redis responsive (memory: ${REDIS_INFO})"
else
    check "redis" "crit" "Redis not responding at ${REDIS_HOST}:${REDIS_PORT}"
fi

# ------------------------------------------------------------------
# 4. WebSocket
# ------------------------------------------------------------------
WS_DOMAIN=$(echo "${APP_URL}" | sed -E 's|^https?://||' | cut -d/ -f1)
WS_PROTO="ws"
echo "${APP_URL}" | grep -q "^https" && WS_PROTO="wss"
WS_URL="${WS_PROTO}://${WS_DOMAIN}:${WS_PORT}"

# Test port reachable
if command -v nc >/dev/null 2>&1; then
    if nc -z -w 3 "${WS_DOMAIN}" "${WS_PORT}" 2>/dev/null; then
        WS_OK="yes"
    else
        WS_OK="no"
    fi
elif command -v bash >/dev/null 2>&1; then
    # Use bash /dev/tcp
    if timeout 3 bash -c "echo > /dev/tcp/${WS_DOMAIN}/${WS_PORT}" 2>/dev/null; then
        WS_OK="yes"
    else
        WS_OK="no"
    fi
else
    WS_OK="unknown"
fi

if [ "${WS_OK}" = "yes" ]; then
    check "websocket" "ok" "WebSocket port ${WS_PORT} open (${WS_URL})"
elif [ "${WS_OK}" = "unknown" ]; then
    check "websocket" "warn" "Cannot test (no nc/bash) - port ${WS_PORT}"
else
    check "websocket" "crit" "WebSocket port ${WS_PORT} not reachable"
fi

# ------------------------------------------------------------------
# 5. Disk space
# ------------------------------------------------------------------
DISK_PCT=$(df -P "${PROJECT_DIR}" 2>/dev/null | awk 'NR==2 {gsub("%","",$5); print $5}' || echo "0")
DISK_FREE=$(df -h "${PROJECT_DIR}" 2>/dev/null | awk 'NR==2 {print $4}' || echo "n/a")

if [ "${DISK_PCT}" -ge "${DISK_THRESHOLD_CRIT}" ] 2>/dev/null; then
    check "disk" "crit" "${DISK_PCT}% full (free: ${DISK_FREE})"
elif [ "${DISK_PCT}" -ge "${DISK_THRESHOLD_WARN}" ] 2>/dev/null; then
    check "disk" "warn" "${DISK_PCT}% full (free: ${DISK_FREE})"
else
    check "disk" "ok" "${DISK_PCT}% full (free: ${DISK_FREE})"
fi

# ------------------------------------------------------------------
# 6. Recent backups
# ------------------------------------------------------------------
if [ -d "${BACKUP_DIR}" ]; then
    LATEST_BACKUP=$(find "${BACKUP_DIR}" -name "*.sql.gz" -type f -printf '%T@ %p\n' 2>/dev/null | sort -nr | head -1 | cut -d' ' -f2-)
    if [ -n "${LATEST_BACKUP}" ]; then
        BACKUP_AGE_HOURS=$(( ($(date +%s) - $(stat -c %Y "${LATEST_BACKUP}" 2>/dev/null || stat -f %m "${LATEST_BACKUP}" 2>/dev/null)) / 3600 ))
        if [ "${BACKUP_AGE_HOURS}" -gt 48 ]; then
            check "backup" "warn" "Latest backup is ${BACKUP_AGE_HOURS}h old (${LATEST_BACKUP##*/})"
        else
            check "backup" "ok" "Latest backup: ${BACKUP_AGE_HOURS}h old (${LATEST_BACKUP##*/})"
        fi
    else
        check "backup" "warn" "No backups found in ${BACKUP_DIR}"
    fi
else
    check "backup" "warn" "Backup directory missing: ${BACKUP_DIR}"
fi

# ------------------------------------------------------------------
# 7. SSL certificate (if HTTPS URL)
# ------------------------------------------------------------------
if echo "${APP_URL}" | grep -q "^https"; then
    SSL_DOMAIN=$(echo "${APP_URL}" | sed -E 's|^https?://||' | cut -d/ -f1)
    if command -v openssl >/dev/null 2>&1; then
        SSL_EXPIRY=$(echo | timeout 5 openssl s_client -servername "${SSL_DOMAIN}" -connect "${SSL_DOMAIN}:443" 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
        if [ -n "${SSL_EXPIRY}" ]; then
            SSL_EXPIRY_EPOCH=$(date -d "${SSL_EXPIRY}" +%s 2>/dev/null || echo "0")
            NOW_EPOCH=$(date +%s)
            DAYS_LEFT=$(( (SSL_EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
            if [ "${DAYS_LEFT}" -lt 7 ]; then
                check "ssl" "crit" "Expires in ${DAYS_LEFT} days"
            elif [ "${DAYS_LEFT}" -lt 30 ]; then
                check "ssl" "warn" "Expires in ${DAYS_LEFT} days"
            else
                check "ssl" "ok" "Valid for ${DAYS_LEFT} days (expires: ${SSL_EXPIRY})"
            fi
        else
            check "ssl" "warn" "Could not parse SSL cert for ${SSL_DOMAIN}"
        fi
    else
        check "ssl" "warn" "openssl not installed"
    fi
fi

# ------------------------------------------------------------------
# 8. Docker container health (if applicable)
# ------------------------------------------------------------------
if command -v docker >/dev/null 2>&1; then
    for svc in app websocket nginx db redis; do
        CID=$(docker compose ps -q "${svc}" 2>/dev/null | head -1)
        if [ -n "${CID}" ]; then
            HEALTH=$(docker inspect --format='{{.State.Health.Status}}' "${CID}" 2>/dev/null || echo "none")
            if [ "${HEALTH}" = "healthy" ]; then
                check "container.${svc}" "ok" "${HEALTH}"
            elif [ "${HEALTH}" = "none" ]; then
                check "container.${svc}" "ok" "running (no healthcheck)"
            else
                check "container.${svc}" "crit" "${HEALTH}"
            fi
        fi
    done
fi

# ------------------------------------------------------------------
# Output
# ------------------------------------------------------------------
EXIT_CODE=0
[ "${CRIT}" -gt 0 ] && EXIT_CODE=1
[ "${WARN}" -gt 0 ] && [ "${CRIT}" -eq 0 ] && EXIT_CODE=2

if [ "${OUTPUT_MODE}" = "json" ]; then
    echo "{"
    echo "  \"status\": \"$([ "${CRIT}" -eq 0 ] && [ "${WARN}" -eq 0 ] && echo "healthy" || ([ "${CRIT}" -gt 0 ] && echo "unhealthy" || echo "degraded"))\","
    echo "  \"timestamp\": \"$(date -u +%FT%TZ)\","
    echo "  \"summary\": { \"total\": ${TOTAL}, \"ok\": ${OK}, \"warn\": ${WARN}, \"crit\": ${CRIT} },"
    echo "  \"checks\": {"
    FIRST=1
    for name in "${!RESULTS[@]}"; do
        IFS='|' read -r status detail <<< "${RESULTS[$name]}"
        [ "${FIRST}" -eq 0 ] && echo ","
        FIRST=0
        printf "    \"%s\": { \"status\": \"%s\", \"detail\": \"%s\" }" \
            "${name}" "${status}" "${detail//\"/\\\"}"
    done
    echo ""
    echo "  }"
    echo "}"
elif [ "${OUTPUT_MODE}" = "quiet" ]; then
    exit "${EXIT_CODE}"
else
    echo "================================================================"
    echo " APS Dream Home - Health Check"
    echo " $(date -u +%FT%TZ)"
    echo "================================================================"
    for name in $(echo "${!RESULTS[@]}" | tr ' ' '\n' | sort); do
        IFS='|' read -r status detail <<< "${RESULTS[$name]}"
        case "${status}" in
            ok)   ICON="${GREEN}OK${NC}" ;;
            warn) ICON="${YELLOW}WARN${NC}" ;;
            crit) ICON="${RED}CRIT${NC}" ;;
        esac
        printf "  [%b] %-20s %s\n" "${ICON}" "${name}" "${detail}"
    done
    echo "----------------------------------------------------------------"
    printf " Summary: %d ok, %d warn, %d crit (total: %d)\n" "${OK}" "${WARN}" "${CRIT}" "${TOTAL}"
    if [ "${CRIT}" -gt 0 ]; then
        echo -e " STATUS: ${RED}UNHEALTHY${NC} - one or more critical issues"
    elif [ "${WARN}" -gt 0 ]; then
        echo -e " STATUS: ${YELLOW}DEGRADED${NC} - non-critical warnings"
    else
        echo -e " STATUS: ${GREEN}HEALTHY${NC} - all checks passed"
    fi
    echo "================================================================"
fi

exit "${EXIT_CODE}"
