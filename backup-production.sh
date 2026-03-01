#!/bin/bash

# APS Dream Home - Production Backup Script
# This script creates automated backups of database and files

set -e  # Exit on any error

# Configuration
APP_NAME="apsdreamhome"
BACKUP_ROOT="/var/backups/$APP_NAME"
APP_DIR="/var/www/$APP_NAME"
DB_NAME="apsdreamhome_prod"
DB_USER="apsdreamhome_user"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_RETENTION_DAYS=30

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_ROOT/database"
mkdir -p "$BACKUP_ROOT/files"
mkdir -p "$BACKUP_ROOT/logs"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARN:${NC} $1"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"
}

# Database backup function
backup_database() {
    log_info "Starting database backup..."

    local db_backup_file="$BACKUP_ROOT/database/${DB_NAME}_${TIMESTAMP}.sql.gz"

    # Create database dump with compression
    mysqldump \
        --user="$DB_USER" \
        --password="$DB_PASSWORD" \
        --host=localhost \
        --single-transaction \
        --routines \
        --triggers \
        --all-databases \
        "$DB_NAME" | gzip > "$db_backup_file"

    # Verify backup integrity
    if [[ -f "$db_backup_file" ]] && [[ -s "$db_backup_file" ]]; then
        local backup_size=$(du -h "$db_backup_file" | cut -f1)
        log_info "Database backup completed: $db_backup_file ($backup_size)"

        # Test backup integrity
        if gzip -t "$db_backup_file" 2>/dev/null; then
            log_info "Database backup integrity verified"
        else
            log_error "Database backup integrity check failed"
            return 1
        fi
    else
        log_error "Database backup failed or file is empty"
        return 1
    fi

    return 0
}

# Files backup function
backup_files() {
    log_info "Starting files backup..."

    local files_backup_file="$BACKUP_ROOT/files/${APP_NAME}_files_${TIMESTAMP}.tar.gz"

    # Create files backup, excluding unnecessary directories
    tar -czf "$files_backup_file" \
        --exclude="$APP_DIR/vendor" \
        --exclude="$APP_DIR/node_modules" \
        --exclude="$APP_DIR/storage/logs" \
        --exclude="$APP_DIR/storage/framework/cache" \
        --exclude="$APP_DIR/storage/framework/sessions" \
        --exclude="$APP_DIR/storage/framework/views" \
        --exclude="$APP_DIR/.git" \
        --exclude="$APP_DIR/*.log" \
        -C /var/www "$APP_NAME"

    # Verify backup integrity
    if [[ -f "$files_backup_file" ]] && [[ -s "$files_backup_file" ]]; then
        local backup_size=$(du -h "$files_backup_file" | cut -f1)
        log_info "Files backup completed: $files_backup_file ($backup_size)"
    else
        log_error "Files backup failed or file is empty"
        return 1
    fi

    return 0
}

# Configuration backup function
backup_config() {
    log_info "Starting configuration backup..."

    local config_backup_file="$BACKUP_ROOT/config_${TIMESTAMP}.tar.gz"

    # Backup important configuration files
    tar -czf "$config_backup_file" \
        /etc/nginx/sites-available/"$APP_NAME" \
        /etc/php/8.1/fpm/pool.d/www.conf \
        /etc/mysql/mysql.conf.d/mysqld.cnf \
        /etc/cron.d/"$APP_NAME" \
        "$APP_DIR/.env" \
        2>/dev/null || true

    if [[ -f "$config_backup_file" ]]; then
        local backup_size=$(du -h "$config_backup_file" | cut -f1)
        log_info "Configuration backup completed: $config_backup_file ($backup_size)"
    else
        log_warn "Configuration backup failed or no config files found"
    fi
}

# Cleanup old backups
cleanup_old_backups() {
    log_info "Cleaning up old backups (older than $BACKUP_RETENTION_DAYS days)..."

    local deleted_count=0

    # Clean up database backups
    while IFS= read -r -d '' file; do
        rm -f "$file"
        ((deleted_count++))
    done < <(find "$BACKUP_ROOT/database" -name "*.sql.gz" -type f -mtime +$BACKUP_RETENTION_DAYS -print0 2>/dev/null)

    # Clean up files backups
    while IFS= read -r -d '' file; do
        rm -f "$file"
        ((deleted_count++))
    done < <(find "$BACKUP_ROOT/files" -name "*.tar.gz" -type f -mtime +$BACKUP_RETENTION_DAYS -print0 2>/dev/null)

    # Clean up config backups
    while IFS= read -r -d '' file; do
        rm -f "$file"
        ((deleted_count++))
    done < <(find "$BACKUP_ROOT" -name "config_*.tar.gz" -type f -mtime +$BACKUP_RETENTION_DAYS -print0 2>/dev/null)

    if [[ $deleted_count -gt 0 ]]; then
        log_info "Cleaned up $deleted_count old backup files"
    else
        log_info "No old backups to clean up"
    fi
}

# Generate backup report
generate_report() {
    log_info "Generating backup report..."

    local report_file="$BACKUP_ROOT/logs/backup_report_$TIMESTAMP.log"

    {
        echo "=== APS Dream Home Backup Report ==="
        echo "Timestamp: $(date)"
        echo "Backup Type: $BACKUP_TYPE"
        echo ""

        echo "=== Disk Usage ==="
        df -h "$BACKUP_ROOT" | tail -n 1
        echo ""

        echo "=== Backup Directory Size ==="
        du -sh "$BACKUP_ROOT"/*
        echo ""

        echo "=== Recent Database Backups ==="
        ls -la "$BACKUP_ROOT/database/" | head -10
        echo ""

        echo "=== Recent Files Backups ==="
        ls -la "$BACKUP_ROOT/files/" | head -10
        echo ""

        echo "=== Backup Retention Policy ==="
        echo "Retention Period: $BACKUP_RETENTION_DAYS days"
        echo "Total Database Backups: $(find "$BACKUP_ROOT/database" -name "*.sql.gz" | wc -l)"
        echo "Total Files Backups: $(find "$BACKUP_ROOT/files" -name "*.tar.gz" | wc -l)"
        echo ""

        echo "=== System Health Check ==="
        echo "Application Status: $(curl -s -o /dev/null -w "%{http_code}" http://localhost/health || echo "N/A")"
        echo "Disk Usage: $(df / | tail -n 1 | awk '{print $5}')"
        echo "Memory Usage: $(free | grep Mem | awk '{printf "%.0f%%", $3/$2 * 100.0}')"
        echo ""

    } > "$report_file"

    log_info "Backup report generated: $report_file"
}

# Send notification (optional - requires mail setup)
send_notification() {
    if command -v mail &> /dev/null; then
        local subject="APS Dream Home Backup Report - $(date +'%Y-%m-%d')"
        local report_file="$BACKUP_ROOT/logs/backup_report_$TIMESTAMP.log"

        if [[ -f "$report_file" ]]; then
            mail -s "$subject" admin@apsdreamhome.com < "$report_file" 2>/dev/null || true
        fi
    fi
}

# Main backup process
main() {
    log_info "Starting APS Dream Home backup process..."

    # Determine backup type from command line or default to full
    BACKUP_TYPE="${1:-full}"

    case "$BACKUP_TYPE" in
        "database")
            backup_database
            ;;
        "files")
            backup_files
            ;;
        "config")
            backup_config
            ;;
        "full")
            backup_database
            backup_files
            backup_config
            ;;
        *)
            log_error "Invalid backup type: $BACKUP_TYPE. Use: database, files, config, or full"
            exit 1
            ;;
    esac

    # Always cleanup old backups
    cleanup_old_backups

    # Generate report
    generate_report

    # Send notification
    send_notification

    log_info "Backup process completed successfully! ✅"

    # Display backup summary
    echo ""
    echo "=== Backup Summary ==="
    echo "Type: $BACKUP_TYPE"
    echo "Timestamp: $TIMESTAMP"
    echo "Database backups: $(find "$BACKUP_ROOT/database" -name "*.sql.gz" | wc -l) files"
    echo "Files backups: $(find "$BACKUP_ROOT/files" -name "*.tar.gz" | wc -l) files"
    echo "Total backup size: $(du -sh "$BACKUP_ROOT" | cut -f1)"
    echo ""
}

# Handle command line arguments
case "${1:-}" in
    "database"|"files"|"config"|"full")
        main "$1"
        ;;
    "cleanup")
        cleanup_old_backups
        log_info "Cleanup completed"
        ;;
    "report")
        generate_report
        ;;
    *)
        echo "Usage: $0 [database|files|config|full|cleanup|report]"
        echo ""
        echo "Commands:"
        echo "  database - Backup only database"
        echo "  files    - Backup only application files"
        echo "  config   - Backup only configuration files"
        echo "  full     - Backup everything (default)"
        echo "  cleanup  - Remove old backups"
        echo "  report   - Generate backup report"
        echo ""
        exit 1
        ;;
esac
