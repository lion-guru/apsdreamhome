# APS Dream Home - Backup and Recovery Guide

## 📋 Overview

This guide provides comprehensive backup and recovery procedures for the APS Dream Home application, including database backups, file backups, automated backups, and disaster recovery procedures.

---

## 💾 Backup Strategy

### Backup Types

#### 1. Database Backups
- **Frequency:** Daily automated + manual on-demand
- **Retention:** 30 days (daily), 12 weeks (weekly), 12 months (monthly)
- **Location:** `/var/backups/apsdreamhome/database/`
- **Format:** Compressed SQL files (.sql.gz)

#### 2. Application File Backups
- **Frequency:** Weekly automated + pre-deployment manual
- **Retention:** 4 weeks
- **Location:** `/var/backups/apsdreamhome/files/`
- **Format:** Compressed tar archives (.tar.gz)

#### 3. Log Backups
- **Frequency:** Weekly
- **Retention:** 8 weeks
- **Location:** `/var/backups/apsdreamhome/logs/`
- **Format:** Compressed log files (.tar.gz)

#### 4. Configuration Backups
- **Frequency:** Pre-deployment
- **Retention:** 6 months
- **Location:** `/var/backups/apsdreamhome/config/`
- **Format:** Encrypted archives

---

## 🔄 Automated Backup Scripts

### Daily Database Backup Script

**File:** `scripts/backup_database_daily.sh`

```bash
#!/bin/bash
# APS Dream Home - Daily Database Backup Script
# Location: /var/www/apsdreamhome/scripts/backup_database_daily.sh

# Configuration
DB_NAME="apsdreamhome"
DB_USER="apsdreamhome"
DB_PASSWORD="your_password"
BACKUP_DIR="/var/backups/apsdreamhome/database"
DATE=$(date +%Y%m%d)
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
KEEP_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Perform database backup
echo "Starting database backup: $TIMESTAMP"
mysqldump -u $DB_USER -p$DB_PASSWORD \
    --single-transaction \
    --quick \
    --lock-tables=false \
    $DB_NAME | gzip > $BACKUP_DIR/apsdreamhome_$TIMESTAMP.sql.gz

# Verify backup
if [ -f "$BACKUP_DIR/apsdreamhome_$TIMESTAMP.sql.gz" ]; then
    SIZE=$(du -h "$BACKUP_DIR/apsdreamhome_$TIMESTAMP.sql.gz" | cut -f1)
    echo "✅ Backup completed successfully: $SIZE"
    
    # Log backup
    echo "[$TIMESTAMP] Database backup successful ($SIZE)" >> $BACKUP_DIR/backup_log.txt
    
    # Remove old backups (keep last 30 days)
    find $BACKUP_DIR -name "apsdreamhome_*.sql.gz" -mtime +$KEEP_DAYS -delete
    echo "🗑️ Old backups cleaned (keeping last $KEEP_DAYS days)"
else
    echo "❌ Backup failed!"
    echo "[$TIMESTAMP] Database backup FAILED" >> $BACKUP_DIR/backup_log.txt
    # Send alert (configure your notification system)
    # mail -s "APS Dream Home Backup Failed" admin@example.com <<< "Database backup failed on $(date)"
    exit 1
fi

echo "Backup process completed: $TIMESTAMP"
```

### Weekly Full Application Backup Script

**File:** `scripts/backup_full_weekly.sh`

```bash
#!/bin/bash
# APS Dream Home - Weekly Full Backup Script
# Location: /var/www/apsdreamhome/scripts/backup_full_weekly.sh

# Configuration
APP_DIR="/var/www/apsdreamhome"
BACKUP_DIR="/var/backups/apsdreamhome/files"
DATE=$(date +%Y%m%d)
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
KEEP_WEEKS=4

# Create backup directory
mkdir -p $BACKUP_DIR

# Perform full application backup
echo "Starting full application backup: $TIMESTAMP"

# Backup application files (excluding cache and logs)
tar -czf $BACKUP_DIR/app_full_$TIMESTAMP.tar.gz \
    --exclude="$APP_DIR/storage/cache" \
    --exclude="$APP_DIR/storage/logs" \
    --exclude="$APP_DIR/node_modules" \
    --exclude="$APP_DIR/vendor" \
    --exclude="$APP_DIR/.git" \
    $APP_DIR

# Backup configuration files separately
tar -czf $BACKUP_DIR/config_$TIMESTAMP.tar.gz \
    $APP_DIR/.env \
    $APP_DIR/config/

# Backup cache and logs
tar -czf $BACKUP_DIR/logs_$TIMESTAMP.tar.gz \
    $APP_DIR/storage/logs \
    $APP_DIR/storage/cache

# Verify backups
for backup in app_full_$TIMESTAMP.tar.gz config_$TIMESTAMP.tar.gz logs_$TIMESTAMP.tar.gz; do
    if [ -f "$BACKUP_DIR/$backup" ]; then
        SIZE=$(du -h "$BACKUP_DIR/$backup" | cut -f1)
        echo "✅ $backup completed: $SIZE"
        echo "[$TIMESTAMP] $backup successful ($SIZE)" >> $BACKUP_DIR/backup_log.txt
    else
        echo "❌ $backup failed!"
        echo "[$TIMESTAMP] $backup FAILED" >> $BACKUP_DIR/backup_log.txt
    fi
done

# Remove old backups (keep last 4 weeks)
find $BACKUP_DIR -name "*.tar.gz" -mtime +$(($KEEP_WEEKS * 7)) -delete
echo "🗑️ Old backups cleaned (keeping last $KEEP_WEEKS weeks)"

echo "Full backup process completed: $TIMESTAMP"
```

### Quick Backup Script (Manual Use)

**File:** `scripts/backup_quick.sh`

```bash
#!/bin/bash
# Quick Backup Script - For manual use before deployments
# Usage: ./scripts/backup_quick.sh [optional-comment]

COMMENT="${1:-manual_backup}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/apsdreamhome/quick"

mkdir -p $BACKUP_DIR

# Quick database backup
mysqldump -u apsdreamhome -ppassword apsdreamhome | \
    gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# Quick file backup
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz \
    --exclude="storage/cache" \
    --exclude="storage/logs" \
    .

echo "✅ Quick backup completed: $TIMESTAMP ($COMMENT)"
echo "📍 Location: $BACKUP_DIR"
```

---

## ⏰ Cron Job Setup

### Automated Backup Schedules

```bash
# Edit crontab
crontab -e

# Add the following lines:

# Daily database backup at 2:00 AM
0 2 * * * /var/www/apsdreamhome/scripts/backup_database_daily.sh >> /var/log/apsdreamhome-backup.log 2>&1

# Weekly full backup on Sunday at 3:00 AM
0 3 * * 0 /var/www/apsdreamhome/scripts/backup_full_weekly.sh >> /var/log/apsdreamhome-backup.log 2>&1

# Log rotation daily
0 4 * * * /usr/sbin/logrotate /etc/logrotate.d/apsdreamhome

# Database optimization weekly
0 5 * * 0 /var/www/apsdreamhome/scripts/optimize_database.sh >> /var/log/apsdreamhome-maintenance.log 2>&1
```

---

## 🛠️ Manual Backup Procedures

### Pre-Deployment Backup

**Procedure:**
1. Run quick backup script
2. Document current version
3. Test backup integrity

```bash
# Step 1: Quick backup
cd /var/www/apsdreamhome
./scripts/backup_quick.sh pre-deployment-$(git rev-parse --short HEAD)

# Step 2: Document current state
echo "Pre-deployment backup for version $(git rev-parse --short HEAD)" >> backup_log.txt
git log -1 --oneline >> backup_log.txt

# Step 3: Verify backup
ls -lh /var/backups/apsdreamhome/quick/
```

### Manual Database Backup

```bash
# Specific table backup
mysqldump -u apsdreamhome -p apsdreamhome user_properties | \
    gzip > /var/backups/apsdreamhome/manual/user_properties_$(date +%Y%m%d).sql.gz

# Multiple tables backup
mysqldump -u apsdreamhome -p apsdreamhome \
    users inquiries leads properties | \
    gzip > /var/backups/apsdreamhome/manual/critical_tables_$(date +%Y%m%d).sql.gz

# Database structure only
mysqldump -u apsdreamhome -p apsdreamhome \
    --no-data apsdreamhome > /var/backups/apsdreamhome/manual/schema_$(date +%Y%m%d).sql
```

### Manual File Backup

```bash
# Backup specific directories
tar -czf /var/backups/apsdreamhome/manual/uploads_$(date +%Y%m%d).tar.gz \
    public/uploads

# Backup configuration
tar -czf /var/backups/apsdreamhome/manual/config_$(date +%Y%m%d).tar.gz \
    .env config/

# Backup logs only
tar -czf /var/backups/apsdreamhome/manual/logs_$(date +%Y%m%d).tar.gz \
    storage/logs
```

---

## 🔄 Recovery Procedures

### Database Recovery

#### Full Database Recovery

**Procedure:**
1. Stop web server (recommended)
2. Backup current database (safety measure)
3. Restore from backup
4. Verify integrity
5. Restart services

```bash
# Step 1: Stop services
sudo systemctl stop apache2
# or
sudo systemctl stop nginx

# Step 2: Backup current database
mysqldump -u apsdreamhome -p apsdreamhome > /tmp/current_backup.sql

# Step 3: Select backup to restore
ls -lh /var/backups/apsdreamhome/database/
# Choose the appropriate backup file

# Step 4: Restore database
gunzip < /var/backups/apsdreamhome/database/apsdreamhome_YYYYMMDD_HHMMSS.sql.gz | \
    mysql -u apsdreamhome -p apsdreamhome

# Step 5: Verify restoration
mysql -u apsdreamhome -p apsdreamhome -e "SELECT COUNT(*) FROM users;"
mysql -u apsdreamhome -p apsdreamhome -e "SELECT COUNT(*) FROM properties;"

# Step 6: Restart services
sudo systemctl start apache2
# or
sudo systemctl start nginx

# Step 7: Test application
curl -I https://yourdomain.com
```

#### Single Table Recovery

```bash
# Drop table (be careful!)
mysql -u apsdreamhome -p apsdreamhome -e "DROP TABLE IF EXISTS user_properties_temp;"

# Restore single table
gunzip < /var/backups/apsdreamhome/database/backup.sql.gz | \
    mysql -u apsdreamhome -p apsdreamhome --tables=user_properties

# Verify
mysql -u apsdreamhome -p apsdreamhome -e "SELECT COUNT(*) FROM user_properties;"
```

#### Point-in-Time Recovery (MySQL Binary Logs)

```bash
# First, restore full backup
gunzip < /var/backups/apsdreamhome/database/full_backup.sql.gz | \
    mysql -u apsdreamhome -p apsdreamhome

# Then apply binary logs
mysqlbinlog --start-datetime="2026-05-17 14:00:00" \
    --stop-datetime="2026-05-17 16:00:00" \
    /var/log/mysql/mysql-bin.000123 | \
    mysql -u apsdreamhome -p apsdreamhome
```

### File Recovery

#### Full Application Restore

```bash
# Step 1: Stop web server
sudo systemctl stop apache2

# Step 2: Backup current files
tar -czf /var/www/apsdreamhome.backup_before_restore.tar.gz /var/www/apsdreamhome

# Step 3: Restore application files
tar -xzf /var/backups/apsdreamhome/files/app_full_YYYYMMDD_HHMMSS.tar.gz \
    -C /var/www/apsdreamhome

# Step 4: Restore configuration
tar -xzf /var/backups/apsdreamhome/files/config_YYYYMMDD_HHMMSS.tar.gz \
    -C /var/www/apsdreamhome

# Step 5: Set permissions
chown -R www-data:www-data /var/www/apsdreamhome
chmod -R 755 /var/www/apsdreamhome
chmod -R 777 /var/www/apsdreamhome/storage
chmod -R 777 /var/www/apsdreamhome/public/uploads

# Step 6: Clear cache
rm -rf /var/www/apsdreamhome/storage/cache/*.cache

# Step 7: Restart services
sudo systemctl start apache2

# Step 8: Test application
curl -I https://yourdomain.com
```

#### Selective File Restore

```bash
# Restore uploads only
tar -xzf /var/backups/apsdreamhome/files/app_full_YYYYMMDD_HHMMSS.tar.gz \
    --wildcards "public/uploads/*" -C /var/www/apsdreamhome

# Restore configuration only
tar -xzf /var/backups/apsdreamhome/files/config_YYYYMMDD_HHMMSS.tar.gz \
    -C /var/www/apsdreamhome

# Restore specific file from backup
tar -xzf /var/backups/apsdreamhome/files/app_full_YYYYMMDD_HHMMSS.tar.gz \
    "path/to/specific/file.php" -C /var/www/apsdreamhome
```

---

## 🚨 Disaster Recovery

### Complete System Recovery Procedure

**Scenario:** Complete server failure or data corruption

#### Step 1: Assessment
```bash
# Determine extent of damage
ls -la /var/www/apsdreamhome
mysql -u root -p -e "SHOW DATABASES;"
df -h
```

#### Step 2: Fresh Server Setup (if needed)
```bash
# Install required software
sudo apt update
sudo apt install apache2 mysql-server php8.0 php8.0-mysql php8.0-mbstring \
    php8.0-json php8.0-curl php8.0-gd php8.0-zip php8.0-xml php8.0-openssl \
    composer git

# Configure PHP
sudo nano /etc/php/8.0/apache2/php.ini
# Set memory_limit, upload_max_filesize, post_max_size

# Configure MySQL
sudo mysql_secure_installation
```

#### Step 3: Database Recovery
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'apsdreamhome'@'localhost' IDENTIFIED BY 'strong_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'apsdreamhome'@'localhost';"

# Restore from most recent backup
gunzip < /backup_location/database/latest.sql.gz | mysql -u apsdreamhome -p apsdreamhome
```

#### Step 4: Application Recovery
```bash
# Restore application files
tar -xzf /backup_location/files/app_full_latest.tar.gz -C /var/www/

# Set permissions
chown -R www-data:www-data /var/www/apsdreamhome
chmod -R 755 /var/www/apsdreamhome
chmod -R 777 /var/www/apsdreamhome/storage

# Configure environment
cp .env.example .env
nano .env
# Update with production values
```

#### Step 5: Dependency Installation
```bash
cd /var/www/apsdreamhome
composer install --no-dev --optimize-autoloader
```

#### Step 6: Database Optimization
```bash
# Apply performance indexes
php scripts/apply_performance_indexes.php

# Optimize tables
mysql -u apsdreamhome -p apsdreamhome -e "OPTIMIZE TABLE user_properties, projects, users, inquiries;"
```

#### Step 7: Service Configuration
```bash
# Configure Apache/Nginx
sudo cp config/apache.conf /etc/apache2/sites-available/apsdreamhome.conf
sudo a2ensite apsdreamhome.conf
sudo systemctl restart apache2

# Configure SSL (if using)
sudo certbot --apache -d yourdomain.com
```

#### Step 8: Verification
```bash
# Test database connection
php -r "try { new PDO('mysql:host=localhost;dbname=apsdreamhome', 'apsdreamhome', 'password'); echo 'DB OK'; } catch(PDOException $e) { echo 'DB FAIL: ' . $e->getMessage(); }"

# Test application
curl -I https://yourdomain.com

# Test critical functionality
# - Homepage loads
# - Database queries work
# - User authentication works
# - File uploads work
```

### Partial Recovery Procedures

#### Database Corruption Recovery

```bash
# Check table integrity
mysql -u apsdreamhome -p apsdreamhome -e "CHECK TABLE users;"
mysql -u apsdreamhome -p apsdreamhome -e "CHECK TABLE properties;"

# Repair corrupted tables
mysql -u apsdreamhome -p apsdreamhome -e "REPAIR TABLE corrupted_table;"

# If repair fails, restore from backup
gunzip < /var/backups/apsdreamhome/database/latest.sql.gz | mysql -u apsdreamhome -p
```

#### File System Recovery

```bash
# Check file system integrity
fsck /dev/sda1

# Recover deleted files (if using ext4)
extundelete /dev/sda1 --restore-file path/to/deleted/file.php

# Restore from backup if file system corruption detected
tar -xzf /var/backups/apsdreamhome/files/latest.tar.gz -C /var/www/apsdreamhome
```

---

## 🔍 Backup Verification

### Automated Backup Verification

**File:** `scripts/verify_backups.sh`

```bash
#!/bin/bash
# Backup Verification Script
# Verifies backup integrity and sends alerts if needed

BACKUP_DIR="/var/backups/apsdreamhome"
ALERT_EMAIL="admin@apsdreamhome.com"

echo "Starting backup verification: $(date)"

# Function to check and verify backup
check_backup() {
    local backup_file=$1
    local backup_type=$2
    
    if [ -f "$backup_file" ]; then
        # Check file size
        SIZE=$(stat -f%z "$backup_file" 2>/dev/null || stat -c%s "$backup_file")
        
        # Verify gzip integrity
        if gzip -t "$backup_file" 2>/dev/null; then
            echo "✅ $backup_type backup verified: $backup_file ($SIZE bytes)"
            return 0
        else
            echo "❌ $backup_type backup CORRUPT: $backup_file"
            # Send alert
            echo "Backup corruption detected in $backup_type" | \
                mail -s "Backup Alert: $backup_type corrupt" $ALERT_EMAIL
            return 1
        fi
    else
        echo "❌ $backup_type backup MISSING: $backup_file"
        # Send alert
        echo "Backup missing: $backup_type" | \
            mail -s "Backup Alert: $backup_type missing" $ALERT_EMAIL
        return 1
    fi
}

# Check most recent database backup
LATEST_DB_BACKUP=$(ls -t $BACKUP_DIR/database/apsdreamhome_*.sql.gz 2>/dev/null | head -1)
check_backup "$LATEST_DB_BACKUP" "Database"

# Check most recent full backup
LATEST_FULL_BACKUP=$(ls -t $BACKUP_DIR/files/app_full_*.tar.gz 2>/dev/null | head -1)
check_backup "$LATEST_FULL_BACKUP" "Full Application"

# Check configuration backup
LATEST_CONFIG_BACKUP=$(ls -t $BACKUP_DIR/files/config_*.tar.gz 2>/dev/null | head -1)
check_backup "$LATEST_CONFIG_BACKUP" "Configuration"

echo "Backup verification completed: $(date)"
```

### Manual Backup Testing

```bash
# Test database backup integrity
gunzip -t /var/backups/apsdreamhome/database/latest.sql.gz
echo "Exit code: $?" (0 = success)

# Test file backup integrity
tar -tzf /var/backups/apsdreamhome/files/latest.tar.gz > /dev/null
echo "Exit code: $?" (0 = success)

# List backup contents
tar -tzf /var/backups/apsdreamhome/files/latest.tar.gz | head -20

# Test restore to temporary database
mysql -u root -p -e "CREATE DATABASE test_restore;"
gunzip < /var/backups/apsdreamhome/database/latest.sql.gz | mysql -u root -p test_restore
mysql -u root -p -e "SELECT COUNT(*) FROM test_restore.users;"
mysql -u root -p -e "DROP DATABASE test_restore;"
```

---

## 📊 Backup Monitoring

### Backup Statistics

**View backup status:**
```bash
# Database backups
ls -lh /var/backups/apsdreamhome/database/ | tail -20

# File backups
ls -lh /var/backups/apsdreamhome/files/ | tail -20

# Backup logs
cat /var/backups/apsdreamhome/database/backup_log.txt | tail -20
```

### Backup Size Monitoring

```bash
# Calculate total backup size
du -sh /var/backups/apsdreamhome/

# Check disk space usage
df -h /var/backups

# Alert if disk space > 80%
DISK_USAGE=$(df /var/backups | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 80 ]; then
    echo "⚠️ Warning: Backup disk usage at ${DISK_USAGE}%"
fi
```

---

## 🔐 Backup Security

### Encryption

**Encrypt sensitive backups:**
```bash
# Encrypt database backup
gpg --encrypt --recipient admin@apsdreamhome.com \
    /var/backups/apsdreamhome/database/latest.sql.gz

# Decrypt when needed
gpg --decrypt --output restored.sql.gz \
    /var/backups/apsdreamhome/database/latest.sql.gz.gpg
```

### Access Control

```bash
# Set proper backup directory permissions
sudo chmod 700 /var/backups/apsdreamhome
sudo chown root:www-data /var/backups/apsdreamhome

# Restrict access to backup files
sudo chmod 600 /var/backups/apsdreamhome/database/*.sql.gz
```

---

## 📞 Recovery Support Contact

### Emergency Contact
- **Primary:** admin@apsdreamhome.com
- **Emergency:** emergency@apsdreamhome.com
- **Phone:** +91 92771 21112

### Backup Location
- **Primary:** /var/backups/apsdreamhome/
- **Off-site:** s3://apsdreamhome-backups (configure AWS S3)

---

**Backup Guide Version:** 2.0  
**Last Updated:** 2026-05-17  
**Next Review:** 2026-08-17