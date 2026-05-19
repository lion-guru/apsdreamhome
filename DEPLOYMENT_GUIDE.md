# APS Dream Home - Production Deployment Guide

## 📋 Prerequisites

### Server Requirements

- **OS:** Linux (Ubuntu 20.04 LTS recommended) or Windows Server
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** 8.0 or higher
- **Database:** MySQL 8.0+ or MariaDB 10.5+
- **RAM:** Minimum 4GB (8GB recommended)
- **Storage:** Minimum 20GB (SSD recommended)
- **PHP Extensions:**
  - pdo_mysql
  - mbstring
  - json
  - curl
  - gd
  - zip
  - xml
  - openssl
  - session

### Development Tools

- Git
- Composer
- Node.js 16+ (for asset compilation if needed)
- PHP CLI

---

## 🔧 Environment Setup

### 1. Clone Repository

```bash
git clone <repository-url> /var/www/apsdreamhome
cd /var/www/apsdreamhome
```

### 2. Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Set File Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/apsdreamhome

# Set permissions
chmod -R 755 /var/www/apsdreamhome
chmod -R 777 /var/www/apsdreamhome/storage
chmod -R 777 /var/www/apsdreamhome/public/uploads
```

### 4. Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit environment variables
nano .env
```

### 5. Generate Application Key

```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

## 🗄️ Database Setup

### 1. Create Database

```sql
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'apsdreamhome'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'apsdreamhome'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Import Database Schema

```bash
mysql -u apsdreamhome -p apsdreamhome < database/schema.sql
```

### 3. Run Migrations (if applicable)

```bash
php artisan migrate --force
```

### 4. Apply Database Optimizations

```bash
php scripts/apply_performance_indexes.php
```

---

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Application
APP_NAME="APS Dream Home"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=apsdreamhome
DB_PASSWORD=strong_password

# Cache
CACHE_DRIVER=file
CACHE_PREFIX=apsdreamhome_
CACHE_TTL=3600

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Security
APP_KEY=<generated-32-char-key>
ENCRYPTION_KEY=<additional-encryption-key>

# Email (optional)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# API (optional)
API_RATE_LIMIT=1000
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/apsdreamhome/public

    <Directory /var/www/apsdreamhome/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # Security headers
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"

        # Enable compression
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript

        # Cache static assets
        <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$">
            ExpiresActive On
            ExpiresDefault "access plus 1 year"
        </FilesMatch>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome-error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome-access.log combined
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/apsdreamhome/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_types text/html text/plain text/xml text/css text/javascript application/javascript;

    # Handle PHP files
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static file caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Block access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(storage|\.env|composer\.json) {
        deny all;
    }

    error_log /var/log/nginx/apsdreamhome-error.log;
    access_log /var/log/nginx/apsdreamhome-access.log;
}
```

---

## 🚀 Deployment Process

### 1. Pre-Deployment Checklist

- [ ] Environment variables configured
- [ ] Database connection tested
- [ ] File permissions set correctly
- [ ] SSL certificate configured
- [ ] Backup of existing site (if updating)
- [ ] Cache directory writable
- [ ] Logs directory writable

### 2. Deployment Steps

```bash
# 1. Backup current deployment
tar -czf backup-$(date +%Y%m%d).tar.gz /var/www/apsdreamhome

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Clear cache
php -r "file_put_contents('storage/cache/.gitkeep', '');"
rm -rf storage/cache/*.cache

# 5. Set permissions
chown -R www-data:www-data /var/www/apsdreamhome
chmod -R 755 /var/www/apsdreamhome
chmod -R 777 storage public/uploads

# 6. Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx

# 7. Restart PHP-FPM (if using)
sudo systemctl restart php8.0-fpm
```

### 3. Post-Deployment Verification

```bash
# Test application
curl -I https://yourdomain.com

# Check logs
tail -f /var/log/apache2/apsdreamhome-error.log
# or
tail -f /var/log/nginx/apsdreamhome-error.log

# Check database connection
php -r "try { new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'apsdreamhome', 'password'); echo 'DB OK'; } catch(PDOException $e) { echo 'DB FAIL: ' . $e->getMessage(); }"
```

---

## 🔒 Security Configuration

### 1. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Generate certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### 2. Firewall Configuration

```bash
# UFW firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# MySQL remote access (if needed)
sudo ufw allow from your.ip.address to any port 3306
```

### 3. Secure Configuration

```bash
# Disable directory browsing
# Already handled in Apache/Nginx config

# Block access to sensitive files
# Already handled in Apache/Nginx config

# Set up fail2ban (optional)
sudo apt install fail2ban
sudo systemctl enable fail2ban
```

---

## 📊 Monitoring Setup

### 1. Application Logging

```php
// Configure logging in app/Core/Logger.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/apsdreamhome/storage/logs/error.log');
```

### 2. Performance Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop

# Set up log rotation
sudo nano /etc/logrotate.d/apsdreamhome

/var/www/apsdreamhome/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### 3. Database Monitoring

```bash
# Enable MySQL slow query log
# Add to my.cnf:
[mysqld]
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

---

## 💾 Backup Strategy

### Automated Backup Script

```bash
#!/bin/bash
# /var/www/apsdreamhome/scripts/backup.sh

DATE=$(date +%Y%m%d)
BACKUP_DIR="/var/backups/apsdreamhome"
APP_DIR="/var/www/apsdreamhome"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u apsdreamhome -p'password' apsdreamhome | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Backup application files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $APP_DIR

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

### Setup Cron Job

```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * /var/www/apsdreamhome/scripts/backup.sh >> /var/log/apsdreamhome-backup.log 2>&1
```

---

## 🧪 Testing Checklist

### 1. Functional Testing

- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] User login works
- [ ] Property search functions
- [ ] Admin login works
- [ ] Dashboard loads
- [ ] Property posting works
- [ ] Mobile responsive design
- [ ] All API endpoints functional

### 2. Security Testing

- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] CSRF protection
- [ ] Secure file uploads
- [ ] Input validation
- [ ] Authentication working
- [ ] Authorization working

### 3. Performance Testing

- [ ] Page load time < 3 seconds
- [ ] Database queries optimized
- [ ] Caching working
- [ ] Images optimized
- [ ] CDN working (if applicable)

---

## 🐛 Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

```bash
# Check error logs
tail -f /var/log/apache2/apsdreamhome-error.log

# Check permissions
ls -la /var/www/apsdreamhome/storage
ls -la /var/www/apsdreamhome/public/uploads

# Fix permissions
chmod -R 777 storage public/uploads
```

#### 2. Database Connection Failed

```bash
# Test MySQL connection
mysql -u apsdreamhome -p -h localhost

# Check .env file
cat /var/www/apsdreamhome/.env

# Restart MySQL
sudo systemctl restart mysql
```

#### 3. Cache Not Working

```bash
# Clear cache
rm -rf storage/cache/*.cache

# Check cache directory permissions
ls -la storage/cache

# Recreate cache directory
mkdir -p storage/cache
chmod 777 storage/cache
```

#### 4. Slow Performance

```bash
# Check slow query log
tail -f /var/log/mysql/slow-query.log

# Optimize tables
mysql -u apsdreamhome -p apsdreamhome -e "OPTIMIZE TABLE user_properties, projects, users;"

# Check server resources
htop
```

---

## 📈 Performance Optimization

### 1. PHP Configuration (php.ini)

```ini
; Production settings
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20

; OPcache for performance
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. MySQL Configuration (my.cnf)

```ini
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 128M
query_cache_type = 1
max_connections = 200
```

### 3. Web Server Optimization

```apache
# Apache: Enable mod_cache
LoadModule cache_module modules/mod_cache.so
LoadModule cache_disk_module modules/mod_cache_disk.so

<IfModule mod_cache_disk.c>
    CacheRoot /var/cache/apache/mod_cache_disk
    CacheDirLevels 2
    CacheDirLength 1
</IfModule>
```

---

## 🔧 Maintenance Procedures

### 1. Weekly Maintenance

```bash
# Clear old cache files
find storage/cache -name "*.cache" -mtime +7 -delete

# Clear old logs
find storage/logs -name "*.log" -mtime +30 -delete

# Optimize database tables
mysql -u apsdreamhome -p apsdreamhome -e "OPTIMIZE TABLE user_properties, projects, users, inquiries;"
```

### 2. Monthly Maintenance

```bash
# Full backup verification
# Check backup integrity
gunzip -c /var/backups/apsdreamhome/database_*.sql.gz | mysql -u root -p test_restore

# Update dependencies
composer update

# Security updates
sudo apt update && sudo apt upgrade
```

### 3. Emergency Procedures

```bash
# Quick database restore
gunzip < /var/backups/apsdreamhome/database_latest.sql.gz | mysql -u apsdreamhome -p apsdreamhome

# Quick file restore
tar -xzf /var/backups/apsdreamhome/files_latest.tar.gz -C /

# Emergency rollback
cd /var/www
rm -rf apsdreamhome
tar -xzf backup-YYYYMMDD.tar.gz
```

---

## 📞 Support Contact

### Technical Support

- **Email:** support@apsdreamhome.com
- **Emergency Contact:** emergency@apsdreamhome.com
- **Documentation:** `/docs`
- **API Documentation:** `/api/docs`

---

## 🎯 Deployment Success Criteria

✅ **Application loads correctly**  
✅ **All features functional**  
✅ **Database connection stable**  
✅ **Cache system working**  
✅ **Security measures active**  
✅ **Mobile responsive**  
✅ **Performance acceptable**  
✅ **Monitoring configured**  
✅ **Backup procedures tested**

---

**Deployment Status:** ✅ Ready for Production  
**Last Updated:** 2026-05-17  
**Version:** 2.0 (Production Ready)
