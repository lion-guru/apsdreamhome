# 🚀 APS Dream Home - Production Deployment Guide

## 📋 Complete Deployment Instructions

This guide provides everything you need to deploy your APS Dream Home system to a production server.

---

## ✅ System Requirements

### Server Requirements
- **Operating System**: Linux (Ubuntu 20.04+ recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Version**: 7.4 - 8.1
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 10GB minimum, SSD recommended

### Required PHP Extensions
```bash
php7.4-cli php7.4-common php7.4-curl php7.4-gd
php7.4-mbstring php7.4-mysql php7.4-opcache
php7.4-xml php7.4-zip php7.4-bcmath
```

---

## 🛠️ Deployment Steps

### Step 1: Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apache2 mysql-server php7.4 php7.4-mysql php7.4-curl php7.4-gd php7.4-mbstring php7.4-xml php7.4-zip

# Enable Apache modules
sudo a2enmod rewrite headers ssl

# Restart Apache
sudo systemctl restart apache2
```

### Step 2: Database Setup
```sql
-- Connect to MySQL
sudo mysql -u root

-- Create database
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create production user
CREATE USER 'aps_prod_user'@'localhost' IDENTIFIED BY 'YourStrongProdPassword123!';

-- Grant privileges
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_prod_user'@'localhost';

-- Create read-only user for reports
CREATE USER 'aps_readonly'@'localhost' IDENTIFIED BY 'ReadOnlyPassword456!';
GRANT SELECT ON apsdreamhome.* TO 'aps_readonly'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

### Step 3: File Deployment
```bash
# Create project directory
sudo mkdir -p /var/www/apsdreamhome
sudo chown -R www-data:www-data /var/www/apsdreamhome

# Copy files (adjust path as needed)
sudo cp -r /path/to/your/local/apsdreamhome/* /var/www/apsdreamhome/

# Set permissions
sudo chmod -R 755 /var/www/apsdreamhome
sudo chmod -R 777 /var/www/apsdreamhome/uploads
sudo chmod -R 777 /var/www/apsdreamhome/backups
sudo chmod -R 777 /var/www/apsdreamhome/logs
```

### Step 4: Configuration Updates

**Database Configuration** (`includes/db_connection.php`):
```php
$host = 'localhost';
$db = 'apsdreamhome';
$user = 'aps_prod_user';
$pass = 'YourStrongProdPassword123!';
$charset = 'utf8mb4';
```

**Base URL Configuration** (`includes/config/config.php`):
```php
define('BASE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'APS Dream Home');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ENVIRONMENT', 'production');
```

---

## 🔒 Security Setup

### SSL Certificate (Let's Encrypt)
```bash
# Install certbot
sudo apt install -y certbot python3-certbot-apache

# Generate certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Security Headers
Update `.htaccess`:
```apache
# Enhanced Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

### Firewall Configuration
```bash
# Enable UFW
sudo ufw enable

# Allow specific ports
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 22

# Allow specific IP for admin access (optional)
sudo ufw allow from YOUR_ADMIN_IP to any port 80
sudo ufw allow from YOUR_ADMIN_IP to any port 443
```

---

## 📊 Monitoring Setup

### Apache Configuration
```apache
# Enhanced logging
ErrorLog ${APACHE_LOG_DIR}/apsdreamhome_error.log
CustomLog ${APACHE_LOG_DIR}/apsdreamhome_access.log combined

# Log rotation
LogLevel warn
```

### Database Monitoring
```sql
-- Create monitoring queries
CREATE TABLE system_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100),
    metric_value DECIMAL(10,2),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample metrics
INSERT INTO system_metrics (metric_name, metric_value) VALUES
('total_properties', (SELECT COUNT(*) FROM properties)),
('total_users', (SELECT COUNT(*) FROM users)),
('total_leads', (SELECT COUNT(*) FROM leads));
```

---

## 🔧 Maintenance Scripts

### Backup Script
```bash
#!/bin/bash
# /usr/local/bin/backup-apsdreamhome

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/apsdreamhome"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u aps_prod_user -p'YourStrongProdPassword123!' apsdreamhome > $BACKUP_DIR/db_backup_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/apsdreamhome --exclude=/var/www/apsdreamhome/uploads/temp

# Clean old backups (keep 7 days)
find $BACKUP_DIR -type f -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -type f -name "*.tar.gz" -mtime +7 -delete

# Log backup
echo "$(date): Backup completed" >> /var/log/backup.log
```

### Performance Monitoring Script
```bash
#!/bin/bash
# /usr/local/bin/monitor-apsdreamhome

# Check database connectivity
mysql -u aps_readonly -p'ReadOnlyPassword456!' -e "SELECT 1" apsdreamhome > /dev/null 2>&1
DB_STATUS=$?

# Check disk usage
DISK_USAGE=$(df /var/www | awk 'NR==2 {print $5}' | sed 's/%//')

# Check memory usage
MEM_USAGE=$(free | awk 'NR==2 {printf "%.1f", $3*100/$2}')

# Log metrics
echo "$(date): DB=$DB_STATUS, Disk=$DISK_USAGE%, Mem=$MEM_USAGE%" >> /var/log/system_metrics.log
```

---

## 🚀 Post-Deployment Verification

### Step 1: Basic Connectivity
```bash
# Test database connection
mysql -u aps_prod_user -p apsdreamhome -e "SHOW TABLES;"

# Test web server
curl -I https://yourdomain.com

# Test admin panel
curl -I https://yourdomain.com/admin.php
```

### Step 2: Functionality Testing
1. **Admin Login**: Test with production credentials
2. **Property Management**: Add/edit/delete properties
3. **User Management**: Create and manage users
4. **Lead Processing**: Test lead workflow
5. **Report Generation**: Verify report functionality
6. **Backup Creation**: Test backup system

### Step 3: Performance Testing
```bash
# Install testing tools
sudo apt install -y apache2-utils

# Load testing
ab -n 100 -c 10 https://yourdomain.com/

# Database performance
mysql -u aps_readonly -p apsdreamhome -e "SELECT COUNT(*) FROM properties; SELECT COUNT(*) FROM leads;"
```

---

## 📞 Production Support

### Emergency Contacts
- **System Administrator**: [Your Name/Phone]
- **Development Team**: [Contact Information]
- **Hosting Provider**: [Provider Support]

### Monitoring Alerts
Set up alerts for:
- **High CPU Usage**: >80%
- **Low Disk Space**: <20% free
- **Database Connection**: Connection failures
- **Failed Backups**: Backup errors

### Regular Maintenance Schedule
- **Daily**: Log review, backup verification
- **Weekly**: Performance monitoring, security scans
- **Monthly**: Software updates, full system backup test
- **Quarterly**: Security audit, performance optimization

---

## 🔧 Troubleshooting Guide

### Common Issues & Solutions

**Problem**: Admin panel shows "Direct access not permitted"
**Solution**: Check .htaccess file and Apache mod_rewrite configuration

**Problem**: Database connection failed
**Solution**: Verify database credentials and user privileges

**Problem**: File uploads not working
**Solution**: Check upload directory permissions (must be 777)

**Problem**: Slow page loading
**Solution**: Enable PHP opcache, optimize database queries, check server resources

**Problem**: Email notifications not working
**Solution**: Configure SMTP settings in admin panel

### Debug Mode (Emergency Use Only)
```php
// In includes/config/config.php
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📚 Additional Resources

### Documentation Files
- **Admin User Guide**: `ADMIN_USER_GUIDE.md`
- **API Documentation**: [Future]
- **Security Guidelines**: [Future]
- **Performance Tuning**: [Future]

### Online Resources
- **PHP Documentation**: https://php.net/docs
- **MySQL Documentation**: https://dev.mysql.com/doc
- **Apache Documentation**: https://httpd.apache.org/docs

---

## 🎉 Deployment Checklist

- [ ] Server setup completed
- [ ] Database configured and tested
- [ ] Files uploaded and permissions set
- [ ] SSL certificate installed
- [ ] Security headers configured
- [ ] Monitoring setup completed
- [ ] Backup system tested
- [ ] Admin panel accessible
- [ ] All features tested
- [ ] Performance verified
- [ ] Documentation provided to users

---

**🎊 Congratulations! Your APS Dream Home system is now live in production!**

Your real estate management system is ready to help grow your business. Monitor performance, maintain security, and enjoy the benefits of a professional management platform.

**Need help? Contact the development team for support!** 🚀🏠
