# 🚀 APS Dream Home - Complete Deployment Guide

## 📋 Table of Contents
- [Quick Start](#quick-start)
- [Prerequisites](#prerequisites)
- [Local Development Setup](#local-development-setup)
- [Production Deployment](#production-deployment)
- [Server Configuration](#server-configuration)
- [Database Setup](#database-setup)
- [Security Checklist](#security-checklist)
- [Performance Optimization](#performance-optimization)
- [Monitoring & Maintenance](#monitoring--maintenance)
- [Troubleshooting](#troubleshooting)

---

## 🚀 Quick Start

### 1. Local Development (Windows/XAMPP)
```bash
# 1. Install XAMPP
# 2. Clone the project to htdocs
git clone <repository-url> C:/xampp/htdocs/apsdreamhomefinal/

# 3. Start XAMPP services (Apache + MySQL)

# 4. Access the application
http://localhost/apsdreamhomefinal/

# 5. Create admin user
php create_admin.php

# 6. Login as admin
Email: admin@apsdreamhome.com
Password: Admin@123456
```

### 2. Production Deployment (Linux Server)
```bash
# 1. Upload files to web server
# 2. Configure database
# 3. Set permissions
# 4. Run deployment script
```

---

## 📋 Prerequisites

### System Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache/Nginx
- **Memory**: 512MB minimum, 1GB recommended
- **Disk Space**: 2GB minimum

### Required PHP Extensions
```bash
php8.1-cli php8.1-common php8.1-curl php8.1-gd php8.1-mbstring
php8.1-mysql php8.1-xml php8.1-zip php8.1-bcmath php8.1-intl
php8.1-redis php8.1-memcached (optional, for caching)
```

---

## 🏠 Local Development Setup

### 1. XAMPP Installation
1. Download XAMPP from https://www.apachefriends.org/
2. Install to `C:\xampp\`
3. Start XAMPP Control Panel
4. Start Apache and MySQL services

### 2. Project Setup
1. Copy project files to `C:\xampp\htdocs\apsdreamhomefinal\`
2. Create database:
   ```sql
   CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### 3. Database Import
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Import the database structure:
   ```bash
   # Use one of these files:
   database/complete_setup.sql
   database/database_structure.sql
   ```

### 4. Admin User Creation
```bash
php create_admin.php
```

### 5. Environment Configuration
Create `.env` file in project root:
```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/apsdreamhomefinal

DB_HOST=localhost
DB_NAME=apsdreamhome
DB_USER=root
DB_PASS=

CACHE_DRIVER=file
SESSION_DRIVER=file
```

---

## 🌐 Production Deployment

### 1. Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apache2 mysql-server php8.1 php8.1-cli php8.1-common
sudo apt install -y php8.1-curl php8.1-gd php8.1-mbstring php8.1-mysql
sudo apt install -y php8.1-xml php8.1-zip php8.1-bcmath php8.1-intl
sudo apt install -y php8.1-redis php8.1-memcached

# Enable Apache modules
sudo a2enmod rewrite headers ssl

# Restart services
sudo systemctl restart apache2 mysql
```

### 2. Database Setup
```sql
-- Create production database
CREATE DATABASE apsdreamhome_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create production user
CREATE USER 'apsdreamhome_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON apsdreamhome_prod.* TO 'apsdreamhome_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. File Upload & Configuration
```bash
# Upload project files
# Set proper permissions
sudo chown -R www-data:www-data /var/www/apsdreamhomefinal/
sudo find /var/www/apsdreamhomefinal/ -type f -exec chmod 644 {} \;
sudo find /var/www/apsdreamhomefinal/ -type d -exec chmod 755 {} \;

# Create uploads directory
sudo mkdir -p /var/www/apsdreamhomefinal/uploads/
sudo chown www-data:www-data /var/www/apsdreamhomefinal/uploads/
sudo chmod 755 /var/www/apsdreamhomefinal/uploads/

# Create cache directories
sudo mkdir -p /var/www/apsdreamhomefinal/app/cache/
sudo chown www-data:www-data /var/www/apsdreamhomefinal/app/cache/
```

### 4. Apache Configuration
Create `/etc/apache2/sites-available/apsdreamhomefinal.conf`:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/apsdreamhomefinal

    <Directory /var/www/apsdreamhomefinal>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Security headers
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
    </Directory>

    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/apsdreamhomefinal_error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhomefinal_access.log combined

    # PHP settings
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite apsdreamhomefinal
sudo a2dissite 000-default
sudo systemctl reload apache2
```

### 5. SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 🔒 Security Checklist

### ✅ Completed Security Features
- [x] Password hashing with Argon2ID
- [x] CSRF protection
- [x] XSS prevention
- [x] SQL injection protection
- [x] Security headers in .htaccess
- [x] Session security settings
- [x] File upload validation
- [x] Input sanitization

### 🔐 Additional Security Measures
1. **Change default passwords** after installation
2. **Enable HTTPS** in production
3. **Set up firewall** rules
4. **Regular security updates**
5. **Monitor access logs**
6. **Backup encryption**

---

## ⚡ Performance Optimization

### ✅ Implemented Optimizations
- [x] Database query caching
- [x] Prepared statement pooling
- [x] Performance monitoring
- [x] File-based caching system
- [x] Redis/Memcached support
- [x] Image optimization
- [x] CSS/JS minification

### 📈 Additional Performance Tips
1. **Enable PHP OPcache**
2. **Configure MySQL query cache**
3. **Use CDN for static assets**
4. **Implement browser caching**
5. **Database indexing**
6. **Regular performance audits**

---

## 📊 Monitoring & Maintenance

### Health Checks
```bash
# Database connectivity
php -r "new PDO('mysql:host=localhost;dbname=apsdreamhome', 'user', 'pass'); echo 'DB OK\n';"

# File permissions
ls -la /var/www/apsdreamhomefinal/

# Cache status
php -r "echo 'Cache: '; var_dump(Cache::getInstance()->getStats());"
```

### Log Monitoring
- Apache logs: `/var/log/apache2/`
- Application logs: `/app/logs/`
- Database slow queries: `SHOW PROCESSLIST;`

### Backup Strategy
```bash
# Daily database backup
mysqldump apsdreamhome > backup_$(date +%Y%m%d).sql

# Weekly full backup
tar -czf backup_weekly_$(date +%Y%m%d).tar.gz /var/www/apsdreamhomefinal/

# Monthly archive
# Keep 30 days of daily backups
# Keep 12 weeks of weekly backups
# Keep 12 months of monthly backups
```

---

## 🚨 Troubleshooting

### Common Issues & Solutions

#### 1. **Admin Panel Not Accessible**
```bash
# Check if admin user exists
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    \$stmt = \$pdo->query('SELECT COUNT(*) as count FROM users WHERE role=\"admin\"');
    \$result = \$stmt->fetch(PDO::FETCH_ASSOC);
    echo 'Admin users: ' . \$result['count'] . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

#### 2. **Database Connection Issues**
- Check MySQL service status
- Verify database credentials
- Check firewall settings
- Test with: `php database/db_connect_test.php`

#### 3. **Permission Errors**
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/apsdreamhomefinal/
sudo find /var/www/apsdreamhomefinal/ -type f -exec chmod 644 {} \;
sudo find /var/www/apsdreamhomefinal/ -type d -exec chmod 755 {} \;
```

#### 4. **Performance Issues**
- Enable query caching: `SET GLOBAL query_cache_size = 268435456;`
- Check slow queries: `SHOW PROCESSLIST;`
- Monitor with: `php app/core/Database.php` (getPerformanceStats)

#### 5. **Memory Issues**
```ini
# Add to php.ini
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
```

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks
1. **Daily**: Monitor logs, check backups
2. **Weekly**: Update dependencies, run security audit
3. **Monthly**: Performance review, database optimization
4. **Quarterly**: Full system review, feature updates

### Getting Help
- **Documentation**: `/docs/` directory
- **API Docs**: `/docs/api/`
- **Support Email**: support@apsdreamhome.com
- **Admin Panel**: `/admin` (after login)

---

## 🎯 Next Steps

### Immediate Actions (Post-Deployment)
1. [ ] Change admin password
2. [ ] Configure email settings
3. [ ] Set up payment gateway
4. [ ] Add Google Analytics
5. [ ] Configure backup automation

### Future Enhancements
1. [ ] Mobile app development
2. [ ] Advanced AI features
3. [ ] Multi-language support
4. [ ] Advanced analytics dashboard
5. [ ] API rate limiting
6. [ ] Real-time notifications

---

## 📚 Additional Resources

- **API Documentation**: Available at `/docs/api/`
- **Database Schema**: `database/database_structure.sql`
- **Security Guide**: `docs/security.md`
- **Performance Guide**: `docs/performance.md`

---

**🎉 Deployment Complete! Your APS Dream Home platform is now ready for production use.**

For any issues or questions, please refer to the troubleshooting section or contact support.

**Happy Deploying! 🚀**
