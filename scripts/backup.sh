#!/bin/bash
# Daily backup script for APS Dream Homes
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_DIR="/backups/apsdreamhomefinal/$DATE"
mkdir -p "$BACKUP_DIR"
cp -r /var/www/apsdreamhomefinal "$BACKUP_DIR/code"
mysqldump -u root -p apsdreamhomefinal > "$BACKUP_DIR/db.sql"
echo "Backup completed at $DATE"
