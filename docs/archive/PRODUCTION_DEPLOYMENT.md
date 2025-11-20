# 🚀 APS Dream Home - Deployment Guide

## 📋 Overview
This guide provides comprehensive instructions for deploying the APS Dream Home real estate management system to a production environment.

## ✅ Prerequisites
- **Web Server**: Apache/Nginx with PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **PHP Extensions**: PDO, mbstring, curl, gd, zip
- **SSL Certificate**: For HTTPS (recommended)
- **Domain Name**: Pointing to your server

---

## 🛠️ Server Configuration

### Apache Configuration
Create or update your virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/apsdreamhome

    <Directory /var/www/apsdreamhome>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome_error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/apsdreamhome

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/apsdreamhome>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### PHP Configuration
Update your `php.ini` file:

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
display_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On
error_log = /var/log/php/apsdreamhome_errors.log
```

---

## 📦 Deployment Steps

### Step 1: Server Setup
```bash
# Create project directory
sudo mkdir -p /var/www/apsdreamhome
sudo chown -R www-data:www-data /var/www/apsdreamhome

# Set proper permissions
sudo chmod -R 755 /var/www/apsdreamhome
sudo chmod -R 777 /var/www/apsdreamhome/uploads
sudo chmod -R 777 /var/www/apsdreamhome/backups
```

### Step 2: Database Setup
```sql
-- Create database
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'apsuser'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';

-- Grant privileges
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'apsuser'@'localhost';

-- Update connection details in includes/db_connection.php
-- Change:
-- $user = 'root';
-- $pass = '';
-- TO:
-- $user = 'apsuser';
-- $pass = 'YourStrongPassword123!';
```

### Step 3: File Upload
```bash
# Upload files to server
# Option 1: Git clone (if using Git)
git clone https://github.com/yourusername/apsdreamhome.git /var/www/apsdreamhome

# Option 2: FTP/SFTP upload
# Upload all files from your local apsdreamhome directory
```

### Step 4: Dependencies Installation
```bash
# Install Composer dependencies (if any)
composer install --no-dev --optimize-autoloader

# Set up directories
mkdir -p uploads properties backups logs cache
chmod -R 777 uploads backups logs cache
```

### Step 5: Environment Configuration
Update configuration files:

**includes/db_connection.php:**
```php
$host = 'localhost';
$db = 'apsdreamhome';
$user = 'apsuser';
$pass = 'YourStrongPassword123!';
```

**includes/config/config.php:**
```php
define('BASE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'APS Dream Home');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
```

---

## 🔒 Security Hardening

### File Permissions
```bash
# Set secure permissions
find /var/www/apsdreamhome -type f -name "*.php" -exec chmod 644 {} \;
find /var/www/apsdreamhome -type d -exec chmod 755 {} \;
chmod -R 777 uploads/ backups/ logs/ cache/
```

### Security Headers
Update `.htaccess` with security headers:

```apache
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains"
    Header always set Content-Security-Policy "default-src 'self'"
</IfModule>
```

### Database Security
```sql
-- Remove dangerous privileges
REVOKE FILE ON *.* FROM 'apsuser'@'localhost';

-- Create read-only user for reports
CREATE USER 'apsro'@'localhost' IDENTIFIED BY 'ReadOnlyPassword123!';
GRANT SELECT ON apsdreamhome.* TO 'apsro'@'localhost';
```

---

## 🌐 Domain & SSL Setup

### DNS Configuration
```
Type: A Record
Name: @
Value: YOUR_SERVER_IP

Type: CNAME Record
Name: www
Value: @
```

### SSL Certificate (Let's Encrypt)
```bash
# Install certbot
sudo apt-get install certbot python3-certbot-apache

# Generate certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 📊 Monitoring & Maintenance

### Log Rotation
```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/apsdreamhome

/var/log/apache2/apsdreamhome_*log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### Backup Strategy
```bash
# Create backup script
sudo nano /usr/local/bin/backup-apsdreamhome

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u apsuser -p'YourStrongPassword123!' apsdreamhome > /var/backups/apsdreamhome_$DATE.sql
tar -czf /var/backups/apsdreamhome_files_$DATE.tar.gz /var/www/apsdreamhome --exclude=/var/www/apsdreamhome/uploads/temp

# Make executable
sudo chmod +x /usr/local/bin/backup-apsdreamhome

# Add to crontab
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-apsdreamhome
```

---

## 🚀 Post-Deployment Tasks

### Step 1: Initial Testing
1. **Access the site**: `https://yourdomain.com`
2. **Test admin panel**: `https://yourdomain.com/admin.php`
3. **Verify database connectivity**
4. **Test all admin functions**

### Step 2: Content Customization
1. **Update site settings** via admin panel
2. **Add your properties**
3. **Configure email settings**
4. **Set up payment gateways**

### Step 3: User Training
1. **Create agent accounts**
2. **Train staff on system usage**
3. **Set up operational procedures**

---

## 🔧 Troubleshooting

### Common Issues

**Problem**: "Direct access not permitted"
**Solution**: Check .htaccess file and Apache mod_rewrite

**Problem**: Database connection failed
**Solution**: Verify database credentials and connectivity

**Problem**: File upload not working
**Solution**: Check upload directory permissions (777)

**Problem**: Admin panel not accessible
**Solution**: Check file permissions and routing

### Debug Mode
Enable debug mode temporarily:

```php
// In includes/config/config.php
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
```

---

## 📞 Support & Maintenance

### Regular Tasks
- **Daily**: Check system logs
- **Weekly**: Review backup logs
- **Monthly**: Security updates, performance review
- **Quarterly**: Full system backup test

### Emergency Contacts
- **Development Team**: [Your Contact Info]
- **Hosting Provider**: [Provider Contact]
- **SSL Certificate**: Let's Encrypt

---

## 📚 Additional Resources

- **Admin Manual**: `/admin.php` (Login required)
- **API Documentation**: [Future Enhancement]
- **Security Guidelines**: [Future Enhancement]
- **Performance Tuning**: [Future Enhancement]

---

**🎉 Congratulations! Your APS Dream Home system is now live in production!**

For technical support or feature requests, please contact the development team.
