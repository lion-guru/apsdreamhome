#!/bin/bash
# APS Dream Home - Automated Database + Files Backup to Google Drive
# Requires: rclone configured with Google Drive remote named "gdrive"
# Setup: rclone config  (choose "drive", name it "gdrive")

set -e

# Configuration
DB_NAME="apsdreamhome"
DB_USER="root"
DB_PASS="${MYSQL_ROOT_PASSWORD}"  # Set in /root/.my.cnf or environment
APP_DIR="/var/www/apsdreamhome"
BACKUP_DIR="/tmp/apsdreamhome_backup_$(date +%Y%m%d_%H%M%S)"
RCLONE_REMOTE="gdrive"
RCLONE_PATH="apsdreamhome-backups"
RETENTION_DAYS=30

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"; }
warn() { echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"; }
error() { echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"; }

echo "=========================================="
echo "APS Dream Home Backup - $(date)"
echo "=========================================="

# Check dependencies
command -v rclone >/dev/null 2>&1 || { error "rclone not installed. Run: curl https://rclone.org/install.sh | bash"; exit 1; }
command -v mysqldump >/dev/null 2>&1 || { error "mysqldump not found"; exit 1; }

# Verify rclone remote
if ! rclone lsd "${RCLONE_REMOTE}:" >/dev/null 2>&1; then
    error "rclone remote '${RCLONE_REMOTE}' not configured. Run: rclone config"
    exit 1
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"
log "Backup directory: $BACKUP_DIR"

# 1. Database Backup
log "📦 Backing up database: $DB_NAME"
DB_FILE="${BACKUP_DIR}/${DB_NAME}_$(date +%Y%m%d_%H%M%S).sql.gz"
mysqldump --single-transaction --routines --triggers \
    -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$DB_FILE"

if [ ! -f "$DB_FILE" ] || [ ! -s "$DB_FILE" ]; then
    error "Database backup failed or empty"
    exit 1
fi

DB_SIZE=$(du -h "$DB_FILE" | cut -f1)
log "   Database backup: $DB_FILE ($DB_SIZE)"

# 2. Application Files Backup (storage, uploads, .env)
log "📦 Backing up application files..."
FILES_FILE="${BACKUP_DIR}/files_$(date +%Y%m%d_%H%M%S).tar.gz"
tar -czf "$FILES_FILE" -C "$APP_DIR" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/logs/*.log' \
    --exclude='*.log' \
    storage .env 2>/dev/null || true

if [ ! -f "$FILES_FILE" ] || [ ! -s "$FILES_FILE" ]; then
    warn "Files backup may be incomplete"
fi

FILES_SIZE=$(du -h "$FILES_FILE" | cut -f1)
log "   Files backup: $FILES_FILE ($FILES_SIZE)"

# 3. Upload to Google Drive
log "☁️ Uploading to Google Drive: ${RCLONE_REMOTE}:${RCLONE_PATH}"
rclone copy "$BACKUP_DIR" "${RCLONE_REMOTE}:${RCLONE_PATH}/$(date +%Y/%m/%d)" \
    --progress \
    --transfers 4 \
    --checkers 8 \
    --retries 3 \
    --low-level-retries 10

if [ $? -eq 0 ]; then
    log "✅ Upload successful"
else
    error "Upload failed"
    exit 1
fi

# 4. Cleanup old backups (local)
log "🧹 Cleaning local temp files..."
rm -rf "$BACKUP_DIR"

# 5. Cleanup old backups (remote - older than RETENTION_DAYS)
log "🗑️ Removing remote backups older than ${RETENTION_DAYS} days..."
rclone delete "${RCLONE_REMOTE}:${RCLONE_PATH}" --min-age "${RETENTION_DAYS}d" --rmdirs

# 6. Verify backup integrity (optional)
log "🔍 Verifying remote backup..."
REMOTE_COUNT=$(rclone lsf "${RCLONE_REMOTE}:${RCLONE_PATH}" --format p | wc -l)
log "   Remote backups: $REMOTE_COUNT files"

echo "=========================================="
echo "✅ BACKUP COMPLETE - $(date)"
echo "=========================================="
echo "Database: $DB_SIZE"
echo "Files: $FILES_SIZE"
echo "Remote: ${RCLONE_REMOTE}:${RCLONE_PATH}"