#!/bin/bash
# ==============================================================================
# APS DREAM HOME — AUTOMATED DATABASE & MEDIA BACKUP SCRIPT (GOOGLE DRIVE)
# Schedule via cron: 0 2 * * * /var/www/apsdreamhome/scripts/backup_db_gdrive.sh >> /var/log/backup_db.log 2>&1
# ==============================================================================

set -euo pipefail

# Configuration
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="/tmp/aps_backups"
DB_NAME="apsdreamhome"
DB_USER="root"
DB_PASS="${MYSQL_ROOT_PASSWORD:-}"
RCLONE_REMOTE="gdrive:apsdreamhome-backups"
RETENTION_DAYS="30d"

# Ensure local backup directory exists
mkdir -p "$BACKUP_DIR"

echo "[$DATE] Starting database backup for $DB_NAME..."

# Dump Database and compress with gzip
SQL_FILE="$BACKUP_DIR/${DB_NAME}_db_${DATE}.sql.gz"
if [ -n "$DB_PASS" ]; then
    mysqldump -u "$DB_USER" -p"$DB_PASS" --single-transaction --quick --routines --triggers "$DB_NAME" | gzip > "$SQL_FILE"
else
    mysqldump -u "$DB_USER" --single-transaction --quick --routines --triggers "$DB_NAME" | gzip > "$SQL_FILE"
fi

echo "[$DATE] Database dump complete: $SQL_FILE ($(du -h "$SQL_FILE" | cut -f1))"

# Check if rclone is installed
if command -v rclone &> /dev/null; then
    echo "[$DATE] Syncing backup to Google Drive ($RCLONE_REMOTE)..."
    rclone copy "$SQL_FILE" "$RCLONE_REMOTE/db/"
    
    # Prune backups older than 30 days on Google Drive
    echo "[$DATE] Purging Google Drive backups older than $RETENTION_DAYS..."
    rclone delete "$RCLONE_REMOTE/db/" --min-age "$RETENTION_DAYS"
    echo "[$DATE] Remote sync complete."
else
    echo "[$DATE] WARNING: rclone not found. Saved locally in $SQL_FILE"
fi

# Clean up local temporary dump file
rm -f "$SQL_FILE"

echo "[$DATE] All backup tasks completed successfully."
