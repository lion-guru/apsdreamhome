#!/bin/bash
# Project Cleanup Script
# Generated on 2026-03-03 10:36:54

echo "Starting project cleanup..."

# Create backup
BACKUP_DIR="_cleanup_backup_2026-03-03_10-36-54"
mkdir -p "$BACKUP_DIR"

# rm -rf apsdreamhome_deployment_package_fallback/
if [ -d " apsdreamhome_deployment_package_fallback/" ]; then
    mv " apsdreamhome_deployment_package_fallback/" "$BACKUP_DIR/"
fi

# rm -rf deployment_package/
if [ -d " deployment_package/" ]; then
    mv " deployment_package/" "$BACKUP_DIR/"
fi

# mv _backup_legacy_files/ backups/legacy/
mv _backup_legacy_files/ backups/legacy/

# rm admin-test.html
rm admin-test.html

echo "Cleanup completed!"
