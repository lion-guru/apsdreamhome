#!/usr/bin/env bash
# =============================================================================
# APS Dream Home - Pre-deploy Backup
#
# Creates a mysqldump of the production database, gzips it, optionally
# uploads to S3, and keeps the last 30 days of local backups.
#
# Usage:
#   ./scripts/backup_before_deploy.sh                    # local backup
#   S3_BUCKET=s3://my-bucket ./scripts/backup_before_deploy.sh   # + S3 upload
#   BACKUP_RETENTION_DAYS=60 ./scripts/backup_before_deploy.sh   # custom retention
#
# Environment variables:
#   S3_BUCKET             - S3 bucket URL (enables S3 upload)
#   AWS_ACCESS_KEY_ID     - AWS credentials (or use IAM role on EC2)
#   AWS_SECRET_ACCESS_KEY
#   BACKUP_RETENTION_DAYS - how many days of local backups to keep (default 30)
#   DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# =============================================================================
set -euo pipefail

# ------------------------------------------------------------------
# Configuration
# ------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_DIR}"

# Load production.env if present
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

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-apsdreamhome}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-${DB_PASSWORD}}"

BACKUP_DIR="${PROJECT_DIR}/backups"
BACKUP_RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"
TIMESTAMP="$(date -u +%Y%m%d_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/pre_deploy_${TIMESTAMP}.sql.gz"
LOG_FILE="${BACKUP_DIR}/backup.log"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()    { echo -e "${GREEN}[backup]${NC} $(date -u +%FT%TZ)  $*" | tee -a "${LOG_FILE}"; }
warn()   { echo -e "${YELLOW}[backup]${NC} $(date -u +%FT%TZ)  $*" | tee -a "${LOG_FILE}"; }
err()    { echo -e "${RED}[backup]${NC} $(date -u +%FT%TZ)  $*" | tee -a "${LOG_FILE}" >&2; }

# ------------------------------------------------------------------
# Pre-flight
# ------------------------------------------------------------------
mkdir -p "${BACKUP_DIR}"

if ! command -v mysqldump >/dev/null 2>&1; then
    err "mysqldump not found in PATH"
    err "Install: apt install -y mysql-client (Ubuntu) or yum install -y mysql (RHEL)"
    exit 1
fi

if ! command -v gzip >/dev/null 2>&1; then
    err "gzip not found in PATH"
    exit 1
fi

# ------------------------------------------------------------------
# Detect mysqldump connection method
# Try Docker container first (production), then local socket/TCP
# ------------------------------------------------------------------
DOCKER_COMPOSE=""
if [ -f docker-compose.yml ] && command -v docker >/dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
fi

run_mysqldump() {
    if [ -n "${DOCKER_COMPOSE}" ] && ${DOCKER_COMPOSE} ps db 2>/dev/null | grep -qE "Up|running"; then
        log "Using Docker container db for mysqldump"
        # shellcheck disable=SC2086
        ${DOCKER_COMPOSE} exec -T db mysqldump \
            -u root \
            -p"${MYSQL_ROOT_PASSWORD}" \
            --single-transaction \
            --quick \
            --routines \
            --triggers \
            --events \
            --hex-blob \
            --default-character-set=utf8mb4 \
            "${DB_DATABASE}"
    else
        log "Using local mysqldump (host=${DB_HOST} port=${DB_PORT})"
        mysqldump \
            -h "${DB_HOST}" \
            -P "${DB_PORT}" \
            -u "${DB_USERNAME}" \
            -p"${DB_PASSWORD}" \
            --single-transaction \
            --quick \
            --routines \
            --triggers \
            --events \
            --hex-blob \
            --default-character-set=utf8mb4 \
            "${DB_DATABASE}"
    fi
}

# ------------------------------------------------------------------
# Run backup
# ------------------------------------------------------------------
log "=========================================="
log "Pre-deploy backup starting"
log "=========================================="
log "Database:  ${DB_DATABASE} @ ${DB_HOST}:${DB_PORT}"
log "Output:    ${BACKUP_FILE}"

START_TS=$(date +%s)
if run_mysqldump | gzip -9 > "${BACKUP_FILE}"; then
    END_TS=$(date +%s)
    DURATION=$((END_TS - START_TS))
    SIZE=$(du -h "${BACKUP_FILE}" | cut -f1)
    log "Backup complete: ${SIZE} in ${DURATION}s"
else
    rm -f "${BACKUP_FILE}"
    err "Backup FAILED"
    exit 1
fi

# Verify backup is not empty
if [ ! -s "${BACKUP_FILE}" ]; then
    err "Backup file is empty"
    rm -f "${BACKUP_FILE}"
    exit 1
fi

# Verify gzip integrity
if ! gzip -t "${BACKUP_FILE}" 2>/dev/null; then
    err "Backup file is corrupt (gzip test failed)"
    exit 1
fi
log "Gzip integrity OK"

# ------------------------------------------------------------------
# Encrypt (if BACKUP_ENCRYPTION_KEY is set)
# ------------------------------------------------------------------
if [ -n "${BACKUP_ENCRYPTION_KEY:-}" ] && command -v openssl >/dev/null 2>&1; then
    log "Encrypting with openssl..."
    openssl enc -aes-256-cbc -salt -pbkdf2 -iter 100000 \
        -in "${BACKUP_FILE}" \
        -out "${BACKUP_FILE}.enc" \
        -pass "pass:${BACKUP_ENCRYPTION_KEY}"
    rm -f "${BACKUP_FILE}"
    mv "${BACKUP_FILE}.enc" "${BACKUP_FILE}"
    log "Encrypted: ${BACKUP_FILE}"
fi

# ------------------------------------------------------------------
# Upload to S3 (if configured)
# ------------------------------------------------------------------
if [ -n "${S3_BUCKET:-}" ]; then
    if command -v aws >/dev/null 2>&1; then
        log "Uploading to S3: ${S3_BUCKET}"
        if aws s3 cp "${BACKUP_FILE}" "${S3_BUCKET}/pre-deploy/${TIMESTAMP}/$(basename "${BACKUP_FILE}")" \
            --storage-class STANDARD_IA \
            --only-show-errors; then
            log "S3 upload complete"
        else
            warn "S3 upload FAILED (local backup preserved)"
        fi
    else
        warn "aws CLI not installed - skipping S3 upload"
    fi
fi

# ------------------------------------------------------------------
# Cleanup old local backups (keep last BACKUP_RETENTION_DAYS days)
# ------------------------------------------------------------------
log "Cleaning up local backups older than ${BACKUP_RETENTION_DAYS} days..."
DELETED=$(find "${BACKUP_DIR}" -name "pre_deploy_*.sql.gz" -type f -mtime "+${BACKUP_RETENTION_DAYS}" -delete -print | wc -l)
log "Deleted ${DELETED} old backup files"

# ------------------------------------------------------------------
# Show summary
# ------------------------------------------------------------------
log "=========================================="
log "BACKUP SUCCESSFUL"
log "=========================================="
log "File:     ${BACKUP_FILE}"
log "Size:     $(du -h "${BACKUP_FILE}" | cut -f1)"
log "Duration: ${DURATION}s"
log ""
log "Recent backups:"
ls -lh "${BACKUP_DIR}"/pre_deploy_*.sql.gz 2>/dev/null | tail -5 | awk '{print "  " $9 " (" $5 ")"}'

exit 0
