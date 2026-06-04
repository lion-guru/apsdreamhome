#!/bin/bash
# =============================================================================
# APS Dream Home - Production Deployment Script
#
# Pulls latest code, builds new images, runs migrations, then does a
# rolling restart of the stateless services (app, websocket) to minimize
# downtime. Stateful services (db, redis) are NOT restarted.
#
# Usage:
#   ./deploy-to-production.sh
#   ./deploy-to-production.sh --skip-migrate
#   ./deploy-to-production.sh --no-cache
#
# Environment variables:
#   DEPLOY_BRANCH       - git branch to deploy (default: production)
#   APP_VERSION         - version tag for the images (default: timestamp)
#   NOTIFY_WEBHOOK_URL  - Slack/Discord/Teams webhook to notify on success/fail
#   HEALTHCHECK_URL     - URL to ping after deploy (default: $APP_URL/health)
#   BACKUP_BEFORE_DEPLOY - if "1", do a DB backup before the deploy
# =============================================================================
set -euo pipefail

# ------------------------------------------------------------------
# Configuration
# ------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "${SCRIPT_DIR}"

DEPLOY_BRANCH="${DEPLOY_BRANCH:-production}"
APP_VERSION="${APP_VERSION:-$(date -u +%Y%m%d.%H%M%S)}"
BACKUP_BEFORE_DEPLOY="${BACKUP_BEFORE_DEPLOY:-1}"
COMPOSE="docker compose"
LOG_FILE="${SCRIPT_DIR}/logs/deploy_$(date -u +%Y%m%d_%H%M%S).log"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# ------------------------------------------------------------------
# Helpers
# ------------------------------------------------------------------
log()     { echo -e "${GREEN}[deploy]${NC} $(date -u +%FT%TZ)  $*" | tee -a "${LOG_FILE}"; }
warn()    { echo -e "${YELLOW}[deploy]${NC} $(date -u +%FT%TZ)  $*" | tee -a "${LOG_FILE}"; }
err()     { echo -e "${RED}[deploy]${NC} $(date -u +%FT%TZ)  $*" | tee -a "${LOG_FILE}" >&2; }
section() { echo -e "\n${BLUE}========== $* ==========${NC}" | tee -a "${LOG_FILE}"; }

notify() {
    local status="$1"
    local message="$2"
    if [ -n "${NOTIFY_WEBHOOK_URL:-}" ]; then
        local color="good"
        [ "${status}" = "failure" ] && color="danger"
        local payload
        payload=$(cat <<EOF
{
  "attachments": [{
    "color": "${color}",
    "blocks": [
      { "type": "header", "text": { "type": "plain_text", "text": "APS Dream Home Deploy" } },
      { "type": "section", "fields": [
        { "type": "mrkdwn", "text": "*Status:*\n${status}" },
        { "type": "mrkdwn", "text": "*Version:*\n${APP_VERSION}" },
        { "type": "mrkdwn", "text": "*Branch:*\n${DEPLOY_BRANCH}" },
        { "type": "mrkdwn", "text": "*Host:*\n$(hostname)" }
      ]},
      { "type": "section", "text": { "type": "mrkdwn", "text": "${message}" } }
    ]
  }]
}
EOF
)
        curl -fsS -X POST -H "Content-Type: application/json" \
            -d "${payload}" "${NOTIFY_WEBHOOK_URL}" >/dev/null 2>&1 \
            || warn "Notification webhook failed"
    fi
}

cleanup() {
    local exit_code=$?
    if [ ${exit_code} -ne 0 ]; then
        err "DEPLOY FAILED (exit ${exit_code})"
        notify "failure" "Deploy of version *${APP_VERSION}* failed. See logs: \`${LOG_FILE}\`"
    fi
    exit ${exit_code}
}
trap cleanup EXIT

# ------------------------------------------------------------------
# Pre-flight checks
# ------------------------------------------------------------------
section "Pre-flight"

mkdir -p logs

# Source .env / production.env if present
if [ -f .env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./.env
    set +a
fi
if [ -f production.env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./production.env
    set +a
fi

# Required tools
for tool in git docker curl; do
    if ! command -v "${tool}" >/dev/null 2>&1; then
        err "Required tool not found: ${tool}"
        exit 1
    fi
done

# Docker running?
if ! docker info >/dev/null 2>&1; then
    err "Docker is not running or you don't have access"
    exit 1
fi

# production.env present?
if [ ! -f production.env ]; then
    warn "production.env not found - falling back to .env"
    warn "It is strongly recommended to have a production.env in production"
fi

log "Pre-flight checks passed"

# ------------------------------------------------------------------
# 1. Pull latest code
# ------------------------------------------------------------------
section "1/7 - Pulling latest code"

# Stash any uncommitted local changes
if ! git diff --quiet HEAD 2>/dev/null; then
    warn "Local changes detected - stashing"
    git stash push -m "deploy-stash-$(date -u +%s)" >/dev/null
fi

# Check we're on the right branch
current_branch=$(git rev-parse --abbrev-ref HEAD)
if [ "${current_branch}" != "${DEPLOY_BRANCH}" ]; then
    log "Currently on '${current_branch}', switching to '${DEPLOY_BRANCH}'"
    git fetch origin "${DEPLOY_BRANCH}"
    git checkout "${DEPLOY_BRANCH}"
fi

log "Pulling from origin/${DEPLOY_BRANCH}"
git pull --rebase --autostash origin "${DEPLOY_BRANCH}"

COMMIT_HASH=$(git rev-parse --short HEAD)
log "Now at commit ${COMMIT_HASH}"

# ------------------------------------------------------------------
# 2. Database backup (before deploy)
# ------------------------------------------------------------------
section "2/7 - Backing up database"

if [ "${BACKUP_BEFORE_DEPLOY}" = "1" ]; then
    if ${COMPOSE} ps db 2>/dev/null | grep -q "Up\|running"; then
        log "Creating pre-deploy backup..."
        mkdir -p backups
        if ${COMPOSE} exec -T db sh -c "mysqldump -u root -p\"${MYSQL_ROOT_PASSWORD:-rootroot}\" ${DB_DATABASE:-apsdreamhome}" 2>/dev/null | gzip > "backups/pre_deploy_${COMMIT_HASH}_$(date -u +%Y%m%d_%H%M%S).sql.gz"; then
            log "Backup saved to backups/pre_deploy_${COMMIT_HASH}_*.sql.gz"
        else
            warn "Backup script returned non-zero - continuing anyway"
        fi
    else
        warn "DB container not running - skipping backup"
    fi
else
    log "BACKUP_BEFORE_DEPLOY=${BACKUP_BEFORE_DEPLOY} - skipping backup"
fi

# ------------------------------------------------------------------
# 3. Build new images
# ------------------------------------------------------------------
section "3/7 - Building images"

NO_CACHE=""
if [ "${1:-}" = "--no-cache" ]; then
    NO_CACHE="--no-cache"
    log "Building with --no-cache"
fi

log "Building app and websocket images (version ${APP_VERSION})..."
APP_VERSION="${APP_VERSION}" ${COMPOSE} build ${NO_CACHE} app websocket 2>&1 | tee -a "${LOG_FILE}"

# ------------------------------------------------------------------
# 4. Run database migrations
# ------------------------------------------------------------------
section "4/7 - Running database migrations"

if [ "${1:-}" = "--skip-migrate" ] || [ "${SKIP_MIGRATE:-}" = "1" ]; then
    log "Migrations skipped (--skip-migrate)"
else
    if ${COMPOSE} ps app 2>/dev/null | grep -q "Up\|running"; then
        log "Running migrations on running app container..."
        ${COMPOSE} exec -T app php scripts/create_migrations_table.php 2>&1 | tee -a "${LOG_FILE}" || warn "create_migrations_table failed"
        ${COMPOSE} exec -T app php scripts/track_migration.php 2>&1 | tee -a "${LOG_FILE}" || warn "track_migration failed"
        log "Migrations done"
    else
        log "App not running - starting it briefly to run migrations"
        ${COMPOSE} up -d app
        sleep 10
        ${COMPOSE} exec -T app php scripts/create_migrations_table.php 2>&1 | tee -a "${LOG_FILE}" || warn "create_migrations_table failed"
        ${COMPOSE} exec -T app php scripts/track_migration.php 2>&1 | tee -a "${LOG_FILE}" || warn "track_migration failed"
    fi
fi

# ------------------------------------------------------------------
# 5. Rolling restart of stateless services (app + websocket)
# ------------------------------------------------------------------
section "5/7 - Rolling restart (zero-downtime)"

# Step 5a - scale app to 2 (temporarily) so there's always one running
log "Scaling app to 2 instances for rolling restart..."
${COMPOSE} up -d --no-deps --scale app=2 app 2>&1 | tee -a "${LOG_FILE}" || warn "Scaling to 2 failed (continuing)"

# Wait for the new instance to be healthy
sleep 10
new_app_id=$(${COMPOSE} ps -q app | tail -n 1)
if [ -n "${new_app_id}" ]; then
    log "Waiting for new app instance (${new_app_id:0:12}) to be healthy..."
    for i in {1..30}; do
        if docker inspect --format='{{.State.Health.Status}}' "${new_app_id}" 2>/dev/null | grep -q "healthy"; then
            log "New app instance is healthy"
            break
        fi
        sleep 2
    done
fi

# Step 5b - stop the old instance
old_app_id=$(${COMPOSE} ps -q app | head -n 1)
if [ -n "${old_app_id}" ] && [ "${old_app_id}" != "${new_app_id}" ]; then
    log "Stopping old app instance (${old_app_id:0:12})..."
    docker stop "${old_app_id}" 2>&1 | tee -a "${LOG_FILE}" || warn "stop old failed"
fi

# Step 5c - scale back to 1
${COMPOSE} up -d --no-deps --scale app=1 app 2>&1 | tee -a "${LOG_FILE}" || warn "Scaling back to 1 failed"

# Step 5d - restart websocket (single instance, brief downtime acceptable)
log "Restarting websocket container..."
${COMPOSE} up -d --no-deps --no-recreate websocket 2>&1 | tee -a "${LOG_FILE}"

# Step 5e - reload nginx (no downtime - just reload)
log "Reloading nginx configuration..."
${COMPOSE} exec -T nginx nginx -s reload 2>&1 | tee -a "${LOG_FILE}" || warn "nginx reload failed"

# ------------------------------------------------------------------
# 6. Health checks
# ------------------------------------------------------------------
section "6/7 - Health checks"

log "Waiting 15s for services to settle..."
sleep 15

log "Container status:"
${COMPOSE} ps 2>&1 | tee -a "${LOG_FILE}"

log "Per-service health:"
for svc in db redis app websocket nginx; do
    cid=$(${COMPOSE} ps -q "${svc}" 2>/dev/null | head -n 1)
    if [ -n "${cid}" ]; then
        health=$(docker inspect --format='{{.State.Health.Status}}' "${cid}" 2>/dev/null || echo "no-check")
        log "  ${svc}: ${health}"
    fi
done

# HTTP healthcheck
HEALTHCHECK_URL="${HEALTHCHECK_URL:-${APP_URL:-http://localhost}/health}"
log "HTTP health check: ${HEALTHCHECK_URL}"
for i in {1..15}; do
    if curl -fsS -m 10 "${HEALTHCHECK_URL}" >/dev/null 2>&1; then
        log "HTTP health check PASSED"
        HTTP_OK=1
        break
    fi
    log "  attempt ${i}/15..."
    sleep 4
done
if [ "${HTTP_OK:-0}" != "1" ]; then
    err "HTTP health check FAILED - check 'make logs' for details"
    ${COMPOSE} logs --tail=100 app 2>&1 | tee -a "${LOG_FILE}"
    exit 1
fi

# WebSocket health check
WS_URL="${WEBSOCKET_HEALTH_URL:-ws://localhost:8080}"
log "WebSocket reachability check: ${WS_URL}"
if nc -z localhost 8080 2>/dev/null; then
    log "WebSocket port 8080 is open"
else
    warn "WebSocket port 8080 is NOT reachable"
fi

# ------------------------------------------------------------------
# 7. Cleanup old images
# ------------------------------------------------------------------
section "7/7 - Cleanup"

log "Removing dangling images..."
docker image prune -f 2>&1 | tee -a "${LOG_FILE}" || true

# ------------------------------------------------------------------
# Done
# ------------------------------------------------------------------
section "DEPLOY COMPLETE"

log "  Version:   ${APP_VERSION}"
log "  Commit:    ${COMMIT_HASH}"
log "  Branch:    ${DEPLOY_BRANCH}"
log "  Time:      $(date -u +%FT%TZ)"
log "  Log file:  ${LOG_FILE}"
log ""
log "Useful next steps:"
log "  - make logs       (tail all logs)"
log "  - make logs-app   (tail app logs)"
log "  - make health     (check health of all services)"

notify "success" "Deploy of *${APP_VERSION}* (${COMMIT_HASH}) succeeded. URL: ${APP_URL:-http://localhost}"

exit 0
