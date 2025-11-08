# APS Dream Home - Deployment Guide

## 🚀 Production Deployment

This guide will help you deploy APS Dream Home to a production server.

### Prerequisites

- Web server (Apache/Nginx)
- PHP 7.4+ with PDO extension
- MySQL 5.7+
- SSL certificate (recommended)
- Domain name

### 1. Server Setup

#### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/apsdreamhome

    <Directory /var/www/apsdreamhome>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/apsdreamhome;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
}
```

### 2. File Upload

#### Upload Files

```bash
# Create directory on server
sudo mkdir -p /var/www/apsdreamhome
sudo chown -R www-data:www-data /var/www/apsdreamhome

# Upload files
rsync -avz --exclude='.git' --exclude='node_modules' . user@server:/var/www/apsdreamhome/
```

#### Set Permissions

```bash
# Set correct permissions
sudo find /var/www/apsdreamhome -type f -exec chmod 644 {} \;
sudo find /var/www/apsdreamhome -type d -exec chmod 755 {} \;

# Set writable directories
sudo chmod -R 775 /var/www/apsdreamhome/uploads
sudo chmod -R 775 /var/www/apsdreamhome/cache
sudo chmod -R 775 /var/www/apsdreamhome/logs
sudo chmod -R 775 /var/www/apsdreamhome/backups
```

### 3. Database Setup

#### Create Database

```sql
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'apsdreamhome'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'apsdreamhome'@'localhost';
FLUSH PRIVILEGES;
```

#### Import Database

```bash
mysql -u apsdreamhome -p apsdreamhome < setup_complete_database.sql
```

### 4. Environment Configuration

#### Update .env file

```env
APP_NAME=APS Dream Home
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=apsdreamhome
DB_USERNAME=apsdreamhome
DB_PASSWORD=your_secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=admin@yourdomain.com
```

#### Generate APP_KEY

```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

### 5. SSL Certificate

#### Using Let's Encrypt (Recommended)

```bash
# Install certbot
sudo apt install certbot python3-certbot-apache

# Generate certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

#### Manual SSL

```bash
# Generate CSR
openssl req -new -newkey rsa:2048 -nodes -keyout yourdomain.com.key -out yourdomain.com.csr

# Get certificate from CA
# Install certificate files
sudo cp yourdomain.com.crt /etc/ssl/certs/
sudo cp yourdomain.com.key /etc/ssl/private/
```

### 6. Domain Configuration

#### DNS Settings

```
Type: A
Name: @
Value: YOUR_SERVER_IP

Type: A
Name: www
Value: YOUR_SERVER_IP

Type: MX
Name: @
Value: mail.yourdomain.com (priority 10)
```

#### Email Configuration

```bash
# Install Postfix
sudo apt install postfix

# Configure for Gmail relay
sudo nano /etc/postfix/main.cf
```

### 7. Security Setup

#### Firewall Configuration

```bash
# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 22

# Enable firewall
sudo ufw enable
```

#### PHP Security

```ini
# /etc/php/7.4/fpm/php.ini
expose_php = Off
display_errors = Off
error_reporting = E_ERROR | E_PARSE
```

### 8. Performance Optimization

#### PHP-FPM Configuration

```ini
# /etc/php/7.4/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 2
pm.max_spare_servers = 10
```

#### Apache Optimization

```apache
# /etc/apache2/apache2.conf
StartServers 5
MinSpareServers 5
MaxSpareServers 10
MaxRequestWorkers 150
MaxConnectionsPerChild 1000
```

### 9. Monitoring Setup

#### Install Monitoring Tools

```bash
# Install Monit
sudo apt install monit

# Configure Monit
sudo nano /etc/monit/conf.d/apsdreamhome
```

#### Log Rotation

```bash
# /etc/logrotate.d/apsdreamhome
/var/www/apsdreamhome/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### 10. Backup Setup

#### Database Backup Script

```bash
#!/bin/bash
# /usr/local/bin/backup-apsdreamhome.sh

BACKUP_DIR="/var/backups/apsdreamhome"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u apsdreamhome -p'PASSWORD' apsdreamhome > $BACKUP_DIR/db_backup_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/apsdreamhome --exclude=/var/www/apsdreamhome/uploads/temp

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -type f -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -type f -mtime +7 -delete
```

### 11. Testing

#### Post-Deployment Tests

```bash
# Test website
curl -I https://yourdomain.com/

# Test admin panel
curl -I https://yourdomain.com/admin/

# Test API
curl -I https://yourdomain.com/api/properties

# Test database connection
php -r "
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'apsdreamhome', 'PASSWORD');
    echo 'Database connection: OK' . PHP_EOL;
} catch (Exception $e) {
    echo 'Database connection: FAILED' . PHP_EOL;
}
"
```

### 12. Maintenance

#### Regular Tasks

1. **Daily**: Check logs, backup verification
2. **Weekly**: Security updates, performance monitoring
3. **Monthly**: Full backup, system optimization
4. **Quarterly**: Dependency updates, feature testing

#### Monitoring Checklist

- [ ] Website response time < 2 seconds
- [ ] Database connection healthy
- [ ] SSL certificate valid
- [ ] No PHP errors in logs
- [ ] Backup files exist and are recent
- [ ] Disk space > 20% free
- [ ] Memory usage < 80%

### 13. Troubleshooting

#### Common Issues

**Website not loading:**
```bash
# Check Apache status
sudo systemctl status apache2

# Check PHP-FPM status
sudo systemctl status php7.4-fpm

# Check file permissions
ls -la /var/www/apsdreamhome/index.php
```

**Database connection error:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection manually
mysql -u apsdreamhome -p -e "SELECT 1"
```

**Email not sending:**
```bash
# Check mail logs
tail -f /var/log/mail.log

# Test SMTP connection
telnet smtp.gmail.com 587
```

### 14. Support

#### Emergency Contacts

- **Development Team**: support@apsdreamhome.com
- **Server Admin**: admin@yourdomain.com
- **Emergency Phone**: +91-XXXXXXXXXX

#### Documentation

- [API Documentation](MOBILE_API_DOCUMENTATION.md)
- [Admin User Guide](ADMIN_USER_GUIDE.md)
- [User Guide](COMPLETE_USER_GUIDE.md)

---

## 🎉 Deployment Complete!

Your APS Dream Home is now live and ready to serve customers! 🚀

**Next Steps:**
1. Monitor system performance
2. Set up regular backups
3. Configure monitoring alerts
4. Plan feature updates
5. Train staff on admin panel usage
