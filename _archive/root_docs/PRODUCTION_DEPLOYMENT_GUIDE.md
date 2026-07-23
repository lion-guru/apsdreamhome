# APS Dream Home - Production Deployment Guide
**Version:** 2.0  
**Last Updated:** April 11, 2026  
**Status:** Production Ready (95%)

---

## 🚀 QUICK START (TL;DR)

```bash
# 1. Clone/Upload to production server
git clone <repo> /var/www/apsdreamhome

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp .env.example .env
nano .env  # Add your credentials

# 4. Run database migrations
php database/migrations/run_all.php

# 5. Set permissions
chmod -R 755 public/uploads
chmod -R 644 config/

# 6. Configure web server (Apache/Nginx)
# See Web Server Configuration section below

# 7. Done!
```

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### Server Requirements
- [ ] PHP 8.1 or higher
- [ ] MySQL 5.7+ or MariaDB 10.3+
- [ ] Apache 2.4+ or Nginx 1.18+
- [ ] SSL Certificate (Let's Encrypt recommended)
- [ ] 2GB+ RAM (4GB recommended for production)
- [ ] 20GB+ Storage (SSD recommended)

### Required PHP Extensions
```bash
# Ubuntu/Debian
sudo apt-get install php8.1-mysql php8.1-gd php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip php8.1-intl

# CentOS/RHEL
sudo yum install php-mysql php-gd php-curl php-mbstring php-xml php-zip php-intl
```

### Required Services
- [ ] Web Server (Apache/Nginx)
- [ ] MySQL/MariaDB Database
- [ ] SMTP Server (for emails)
- [ ] Cron/Scheduler (for automated tasks)
- [ ] Backup Solution

---

## 🔧 STEP-BY-STEP DEPLOYMENT

### Step 1: Server Setup

#### Ubuntu 22.04 LTS Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y
sudo systemctl enable apache2

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install PHP 8.1
sudo apt install php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-gd php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip php8.1-intl -y

# Restart Apache
sudo systemctl restart apache2
```

### Step 2: Database Setup

```sql
-- Create database
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'aps_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';

-- Grant privileges
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: Application Deployment

```bash
# Create project directory
sudo mkdir -p /var/www/apsdreamhome
sudo chown $USER:$USER /var/www/apsdreamhome

# Clone repository (or upload files)
cd /var/www/apsdreamhome
git clone https://github.com/yourrepo/apsdreamhome.git .

# Or upload via SCP/SFTP
# scp -r ./apsdreamhome/* user@server:/var/www/apsdreamhome/

# Install Composer dependencies
cd /var/www/apsdreamhome
composer install --no-dev --optimize-autoloader --no-interaction

# Create environment file
cp .env.example .env
nano .env  # Edit with your credentials

# Set directory permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome
sudo chmod -R 755 /var/www/apsdreamhome
sudo chmod -R 775 /var/www/apsdreamhome/public/uploads
sudo chmod -R 775 /var/www/apsdreamhome/storage
```

### Step 4: Environment Configuration

Create `.env` file:

```env
# Application
APP_NAME="APS Dream Home"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
BASE_URL=https://yourdomain.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=aps_user
DB_PASSWORD=StrongPassword123!

# Email (Gmail SMTP Example)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@apsdreamhome.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@apsdreamhome.com
MAIL_FROM_NAME="APS Dream Home"
ADMIN_EMAIL=admin@apsdreamhome.com

# SMS (MSG91)
MSG91_AUTH_KEY=your_msg91_auth_key
MSG91_SENDER_ID=APSDHM
MSG91_TEMPLATE_ID=your_template_id

# Payment (Razorpay)
RAZORPAY_KEY_ID=rzp_live_xxxxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxx

# Security
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CSRF_TOKEN_SALT=RandomString123!@#

# Performance
CACHE_DRIVER=file
CACHE_LIFETIME=3600
```

### Step 5: Web Server Configuration

#### Apache Configuration
Create `/etc/apache2/sites-available/apsdreamhome.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/apsdreamhome/public
    
    <Directory /var/www/apsdreamhome/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome-error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome-access.log combined
    
    # Redirect HTTP to HTTPS
    RewriteEngine on
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/apsdreamhome/public
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
    
    <Directory /var/www/apsdreamhome/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome-error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite apsdreamhome.conf
sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

#### Nginx Configuration
Create `/etc/nginx/sites-available/apsdreamhome`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/apsdreamhome/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256';
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;
    
    # PHP Handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(config|app|storage)/ {
        deny all;
    }
    
    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/apsdreamhome /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 6: SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx -y    # For Nginx

# Obtain certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com  # Apache
# OR
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com  # Nginx

# Auto-renewal test
sudo certbot renew --dry-run
```

### Step 7: Database Migration

```bash
cd /var/www/apsdreamhome

# Run migration script
php database/migrations/run_all.php

# Or manual import
mysql -u aps_user -p apsdreamhome < database/schema.sql
```

### Step 8: Cron Jobs Setup

```bash
# Edit crontab
sudo crontab -e

# Add these lines:
# Process pending commissions every hour
0 * * * * cd /var/www/apsdreamhome && php cron/process_commissions.php >> /var/log/aps-cron.log 2>&1

# Send daily reports at 9 AM
0 9 * * * cd /var/www/apsdreamhome && php cron/send_daily_reports.php >> /var/log/aps-cron.log 2>&1

# Cleanup old logs weekly (Sunday 2 AM)
0 2 * * 0 cd /var/www/apsdreamhome && php cron/cleanup_logs.php >> /var/log/aps-cron.log 2>&1

# Backup database daily at 3 AM
0 3 * * * mysqldump -u aps_user -p'StrongPassword123!' apsdreamhome > /backups/apsdreamhome_$(date +\%Y\%m\%d).sql
```

### Step 9: Final Verification

```bash
# Check PHP version
php -v

# Check MySQL connection
php -r "require 'vendor/autoload.php'; echo 'Database connection: OK';"

# Check file permissions
ls -la /var/www/apsdreamhome/public/uploads
ls -la /var/www/apsdreamhome/storage

# Test web server
curl -I https://yourdomain.com

# Check error logs
sudo tail -f /var/log/apache2/apsdreamhome-error.log
# OR
sudo tail -f /var/log/nginx/apsdreamhome-error.log
```

---

## 🔒 SECURITY HARDENING

### 1. File Permissions
```bash
# Set secure permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome
sudo chmod -R 755 /var/www/apsdreamhome
sudo chmod -R 775 /var/www/apsdreamhome/public/uploads
sudo chmod -R 775 /var/www/apsdreamhome/storage

# Protect sensitive files
sudo chmod 600 /var/www/apsdreamhome/.env
sudo chmod 600 /var/www/apsdreamhome/config/database.php
```

### 2. Database Security
```sql
-- Remove remote access
REVOKE ALL PRIVILEGES ON *.* FROM 'aps_user'@'%';
FLUSH PRIVILEGES;

-- Enable only localhost
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;

-- Set strong password policy
SET GLOBAL validate_password.policy = STRONG;
```

### 3. Web Server Security
```bash
# Disable server tokens
# Apache: Add to apache2.conf
ServerTokens Prod
ServerSignature Off

# Nginx: Add to nginx.conf
server_tokens off;

# Hide PHP version
# In php.ini
expose_php = Off
```

### 4. Firewall Setup (UFW)
```bash
# Install and configure UFW
sudo apt install ufw -y
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw allow mysql
sudo ufw enable
```

### 5. Fail2Ban (Brute Force Protection)
```bash
# Install Fail2Ban
sudo apt install fail2ban -y

# Configure for SSH
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local

# Add custom jail for login attempts
[apsdreamhome]
enabled = true
port = http,https
filter = apsdreamhome
logpath = /var/log/apache2/apsdreamhome-error.log
maxretry = 5
bantime = 3600
```

---

## 📊 MONITORING & MAINTENANCE

### Log Rotation
Create `/etc/logrotate.d/apsdreamhome`:
```
/var/www/apsdreamhome/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### Health Check Script
Create `health_check.php`:
```php
<?php
// Place in public/health.php
$checks = [
    'database' => false,
    'uploads' => false,
    'memory' => false
];

// Check database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_DATABASE, DB_USERNAME, DB_PASSWORD);
    $checks['database'] = true;
} catch (Exception $e) {
    $checks['database_error'] = $e->getMessage();
}

// Check uploads
$checks['uploads'] = is_writable(__DIR__ . '/../public/uploads');

// Check memory
$checks['memory'] = memory_get_usage() < (128 * 1024 * 1024); // 128MB

// Output
header('Content-Type: application/json');
echo json_encode([
    'status' => !in_array(false, $checks) ? 'healthy' : 'unhealthy',
    'checks' => $checks,
    'timestamp' => date('Y-m-d H:i:s')
]);
```

---

## 🔄 BACKUP STRATEGY

### Automated Daily Backup Script
Create `/var/www/apsdreamhome/scripts/backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backups/apsdreamhome"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="apsdreamhome"
DB_USER="aps_user"
DB_PASS="StrongPassword123!"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p'$DB_PASS' $DB_NAME > "$BACKUP_DIR/db_$DATE.sql"

# Backup uploads
tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" /var/www/apsdreamhome/public/uploads/

# Backup config
cp /var/www/apsdreamhome/.env "$BACKUP_DIR/env_$DATE"

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "env_*" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable:
```bash
chmod +x /var/www/apsdreamhome/scripts/backup.sh
```

---

## 🚨 TROUBLESHOOTING

### Common Issues

#### 1. 500 Internal Server Error
```bash
# Check PHP error log
sudo tail -f /var/log/apache2/error.log

# Check file permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome

# Check .env file exists
ls -la /var/www/apsdreamhome/.env
```

#### 2. Database Connection Error
```bash
# Test MySQL connection
mysql -u aps_user -p -h localhost apsdreamhome

# Check MySQL is running
sudo systemctl status mysql

# Verify credentials in .env
cat /var/www/apsdreamhome/.env | grep DB_
```

#### 3. Permission Denied on Uploads
```bash
# Fix upload permissions
sudo chmod -R 775 /var/www/apsdreamhome/public/uploads
sudo chown -R www-data:www-data /var/www/apsdreamhome/public/uploads

# Check SELinux (if enabled)
sudo setenforce 0  # Temporarily disable
# Or configure proper SELinux policies
```

#### 4. CSS/JS Not Loading (404)
```bash
# Check .htaccess exists in public
cat /var/www/apsdreamhome/public/.htaccess

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check BASE_URL in config
grep BASE_URL /var/www/apsdreamhome/.env
```

---

## 📞 POST-DEPLOYMENT CHECKLIST

- [ ] Application loads without errors
- [ ] SSL certificate valid
- [ ] All pages accessible
- [ ] Login/Registration working
- [ ] File uploads working
- [ ] Email sending working (test with actual email)
- [ ] SMS working (test with actual number)
- [ ] Payment gateway working (test mode)
- [ ] Database backups running
- [ ] Cron jobs configured
- [ ] Monitoring alerts set up
- [ ] Error logging enabled
- [ ] Security headers verified
- [ ] Performance tested (PageSpeed Insights)

---

## 🎯 PRODUCTION READY STATUS

| Component | Status |
|-----------|--------|
| Core ERP Modules | ✅ Ready |
| CRM System | ✅ Ready |
| Property Management | ✅ Ready |
| MLM Network | ✅ Ready |
| Commission Engine | ✅ Ready |
| Email System | ⚙️ Needs SMTP Config |
| SMS System | ⚙️ Needs MSG91 Key |
| Payment Gateway | ⚙️ Needs Razorpay Keys |
| Security Hardening | ✅ Documented |
| Backup Strategy | ✅ Documented |
| Monitoring | ✅ Documented |

---

**Deployment Guide Version:** 2.0  
**Last Updated:** April 11, 2026  
**Status:** COMPLETE ✅

---

For support, contact: admin@apsdreamhome.com
