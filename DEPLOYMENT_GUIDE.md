# 🚀 APS Dream Home - Production Deployment Guide
**Version:** 3.0 Ultimate Edition  
**Date:** May 2026

---

## 📋 Pre-Deployment Checklist

### ✅ System Requirements

| Component | Requirement | Status |
|-----------|-------------|--------|
| **PHP** | 8.0+ | ✅ Required |
| **MySQL** | 8.0+ or MariaDB 10.5+ | ✅ Required |
| **Web Server** | Apache/Nginx | ✅ Required |
| **RAM** | 4GB minimum | ✅ Recommended |
| **Storage** | 50GB SSD | ✅ Recommended |
| **Redis** | Optional (for caching) | ⚠️ Optional |
| **Composer** | 2.0+ | ✅ Required |

---

## 🛠️ Installation Steps

### Step 1: Clone/Upload Project

```bash
# Clone from repository (if using git)
git clone <repository-url> apsdreamhome

# OR upload files via FTP/SFTP to:
# /var/www/html/apsdreamhome (Linux)
# C:\xampp\htdocs\apsdreamhome (Windows)
```

### Step 2: Install Dependencies

```bash
cd apsdreamhome

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set permissions (Linux/Mac)
chmod -R 755 .
chmod -R 777 storage/
chmod -R 777 public/uploads/
```

### Step 3: Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema (if using full SQL dump)
mysql -u root -p apsdreamhome < database/apsdreamhome.sql

# OR run migrations
php database/migrations/create_enterprise_features.php
php database/migrations/create_notification_analytics_tables.php
php database/migrations/create_scheduler_file_tables.php
php database/migrations/create_loyalty_tables.php
```

### Step 4: Environment Configuration

Create `.env` file in project root:

```env
# Application
APP_NAME="APS Dream Home"
APP_ENV=production
APP_URL=https://yourdomain.com
APP_DEBUG=false

# Database
DB_HOST=localhost
DB_PORT=3307
DB_DATABASE=apsdreamhome
DB_USERNAME=root
DB_PASSWORD=your_password

# Security
JWT_SECRET=your-secure-jwt-secret-key-here
ENCRYPTION_KEY=your-encryption-key

# Payment Gateways
RAZORPAY_KEY_ID=rzp_live_your_key
RAZORPAY_KEY_SECRET=your_secret
PAYU_MERCHANT_KEY=your_key
PAYU_SALT=your_salt
STRIPE_PUBLIC_KEY=pk_live_your_key
STRIPE_SECRET_KEY=sk_live_your_key

# Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# SMS (Twilio/MSG91)
SMS_PROVIDER=msg91
SMS_API_KEY=your_api_key

# Google Maps
GOOGLE_MAPS_API_KEY=your_api_key

# Cache (optional)
CACHE_DRIVER=file
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Queue
QUEUE_DRIVER=database

# Storage
STORAGE_PATH=/var/www/html/apsdreamhome/storage
```

### Step 5: Web Server Configuration

#### Apache (.htaccess)
File: `public/.htaccess`
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security Headers
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/apsdreamhome/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    # Security
    location ~ /\.(git|env) { deny all; }
    location ~ ^/(storage|config|vendor) { deny all; }
}
```

---

## 🔧 Post-Installation Setup

### 1. Create Admin User

```bash
php scripts/create_admin.php
# OR use database seeders
```

### 2. Configure Scheduled Tasks (Cron)

Edit crontab:
```bash
crontab -e
```

Add:
```bash
# Run scheduler every minute
* * * * * cd /var/www/html/apsdreamhome && php scripts/scheduler.php >> /dev/null 2>&1

# Daily backup at 2 AM
0 2 * * * cd /var/www/html/apsdreamhome && php scripts/backup.php >> /var/log/apsdreamhome/backup.log 2>&1

# Cleanup logs weekly
0 3 * * 0 cd /var/www/html/apsdreamhome && php scripts/cleanup.php >> /dev/null 2>&1
```

### 3. SSL Certificate (Let's Encrypt)

```bash
# Install certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

### 4. Configure Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw allow 3307/tcp  # MySQL (if remote access needed)
sudo ufw enable
```

---

## 🎯 Production Optimizations

### 1. Enable OPcache (php.ini)

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. MySQL Optimization (my.cnf)

```ini
[mysqld]
# Performance
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 128M
max_connections = 500

# Security
bind-address = 127.0.0.1
local_infile = 0
```

### 3. Enable Compression

#### Apache
```apache
# Enable mod_deflate
LoadModule deflate_module modules/mod_deflate.so

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript
</IfModule>
```

#### Nginx
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript;
gzip_min_length 1000;
```

---

## 📱 Mobile App Configuration

### Flutter App Setup

```dart
// lib/config/api_config.dart
class ApiConfig {
  static const String baseUrl = 'https://yourdomain.com/api';
  static const String apiVersion = 'v2';
  static const int timeoutSeconds = 30;
}

// lib/config/auth_config.dart
class AuthConfig {
  static const String jwtSecret = 'your-jwt-secret'; // Same as backend
  static const int tokenRefreshThreshold = 300; // 5 minutes
}
```

---

## 🔒 Security Hardening

### 1. File Permissions

```bash
# Linux/Mac
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 777 storage/
chmod -R 777 public/uploads/
chmod 600 .env
```

### 2. Disable Debug Mode

```env
APP_DEBUG=false
APP_ENV=production
```

### 3. Rate Limiting

Already configured in:
- `app/Http/Middleware/RateLimitMiddleware.php`
- 60 requests per minute per IP

### 4. CORS Configuration

Already configured for mobile app access.

---

## 📊 Monitoring Setup

### 1. Application Health Check

```bash
# Create health check endpoint
curl https://yourdomain.com/api/health
```

Expected response:
```json
{
  "status": "healthy",
  "database": "connected",
  "cache": "working",
  "queue": "operational"
}
```

### 2. Log Rotation

```bash
# /etc/logrotate.d/apsdreamhome
/var/www/html/apsdreamhome/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### 3. Error Reporting

Configure in `config/app.php`:
```php
'log' => 'daily',
'log_level' => 'error',
```

---

## 🚀 Deployment Commands

### One-Click Deploy Script

```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting Deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php database/migrations/run_all.php

# Clear caches
php scripts/clear_cache.php

# Restart services
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
sudo systemctl restart php8.0-fpm

echo "✅ Deployment Complete!"
```

---

## 🧪 Post-Deployment Testing

### Quick Tests

```bash
# Test database
mysql -u root -p -e "SELECT COUNT(*) FROM apsdreamhome.users;"

# Test API
curl https://yourdomain.com/api/health

# Test login page
curl -I https://yourdomain.com/login

# Test mobile API
curl -X POST https://yourdomain.com/api/v2/mobile/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

---

## 📞 Support & Troubleshooting

### Common Issues

#### 1. Database Connection Failed
```bash
# Check MySQL is running
sudo systemctl status mysql

# Check port
netstat -tlnp | grep 3307

# Fix: Start MySQL
sudo systemctl start mysql
```

#### 2. Permission Denied
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/html/apsdreamhome
sudo chmod -R 755 /var/www/html/apsdreamhome
```

#### 3. 500 Internal Server Error
```bash
# Check logs
tail -f storage/logs/error.log

# Check PHP errors
tail -f /var/log/apache2/error.log
```

#### 4. CORS Errors (Mobile App)
```bash
# Check CORS configuration
# Ensure mobile app domain is in allowed origins
```

---

## 🎉 Success!

After completing these steps, your APS Dream Home platform will be:

✅ **Live on production domain**  
✅ **SSL secured**  
✅ **Optimized for performance**  
✅ **Monitoring enabled**  
✅ **Backup automated**  
✅ **Mobile app ready**  

**Your world-class real estate platform is now LIVE!** 🚀

---

**Need Help?**
- Check logs in `storage/logs/`
- Review `COMPREHENSIVE_TEST_REPORT.md`
- Contact support: support@apsdreamhome.com
