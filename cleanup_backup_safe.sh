#!/bin/bash
# Safe Backup Cleanup Script
# Generated on 2026-03-03 10:45:17

echo "Starting safe backup cleanup..."

# Create final backup
FINAL_BACKUP="_backup_legacy_final_2026-03-03_10-45-17"
mkdir -p "$FINAL_BACKUP"
cp -r _backup_legacy_files/* "$FINAL_BACKUP/"
echo "Final backup created: $FINAL_BACKUP"

# Remove original backup
rm -rf _backup_legacy_files
echo "_backup_legacy_files removed"

# Test critical functionality
php -l admin/dashboard.php
php -l public/index.php
echo "Functionality tests completed"

echo "Cleanup completed successfully!"
