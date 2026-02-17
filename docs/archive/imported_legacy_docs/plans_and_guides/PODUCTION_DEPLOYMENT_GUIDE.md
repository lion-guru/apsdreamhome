# APS Dream Home - Deployment & Setup Guide 🚀

## 🎯 Production Deployment Guide

This guide covers setting up APS Dream Home for production use with proper configuration, security, and optimization.

## 📋 Prerequisites

### System Requirements
- **PHP:** 7.4 or higher (8.0+ recommended)
- **MySQL:** 5.7 or higher (8.0+ recommended)
- **Web Server:** Apache/Nginx with PHP support
- **HTTPS:** SSL certificate for production
- **Composer:** For dependency management
- **Node.js:** For frontend assets (optional)

### Required Services
1. **WhatsApp Business API** (Optional but recommended)
2. **OpenRouter API** (For AI features)
3. **SMTP Server** (For email functionality)
4. **Database Server** (MySQL/MariaDB)

---

## 🛠️ Installation Steps

### Step 1: Server Setup

#### 1.1 PHP Configuration
```bash
# Install required PHP extensions
sudo apt-get install php php-curl php-mysql php-mbstring php-xml php-zip php-gd php-intl

# Configure PHP settings
sudo vim /etc/php/8.1/cli/php.ini
```
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

#### 1.2 Database Setup
```sql
-- Create database
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'aps_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON apsdreamhome.* TO 'aps_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 1.3 Web Server Configuration
```apache
# Apache Virtual Host
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/apsdreamhome

    <Directory /var/www/apsdreamhome>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/apsdreamhome_error.log
    CustomLog ${APACHE_LOG_DIR}/apsdreamhome_access.log combined
</VirtualHost>
```

### Step 2: File Setup

#### 2.1 Upload Files
```bash
# Upload all files to web directory
sudo cp -r /path/to/apsdreamhome/* /var/www/apsdreamhome/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome
sudo chmod -R 755 /var/www/apsdreamhome
sudo chmod -R 777 /var/www/apsdreamhome/logs/
sudo chmod -R 777 /var/www/apsdreamhome/uploads/
```

#### 2.2 Configure Environment
```bash
# Copy and configure config file
sudo cp includes/config.php includes/config.php.backup
sudo vim includes/config.php
```

### Step 3: Configuration

#### 3.1 Database Configuration
```php
// includes/config.php
$config['database'] = [
    'host' => 'localhost',
    'database' => 'apsdreamhome',
    'username' => 'aps_user',
    'password' => 'your_secure_password',
    'charset' => 'utf8mb4'
];
```

#### 3.2 WhatsApp Configuration
```php
// For WhatsApp Business API
$config['whatsapp'] = [
    'enabled' => true,
    'phone_number' => '9277121112',
    'country_code' => '91',
    'api_provider' => 'whatsapp_business_api',
    'business_account_id' => 'your_business_account_id',
    'access_token' => 'your_access_token',
    'webhook_verify_token' => 'your_secure_webhook_token'
];

// For Twilio (Alternative)
$config['whatsapp'] = [
    'enabled' => true,
    'phone_number' => '9277121112',
    'country_code' => '91',
    'api_provider' => 'twilio'
];
```

#### 3.3 AI Configuration
```php
$config['ai'] = [
    'enabled' => true,
    'provider' => 'openrouter',
    'api_key' => 'your_openrouter_api_key',
    'model' => 'qwen/qwen3-coder:free',
    'features' => [
        'property_descriptions' => true,
        'chatbot' => true,
        'code_analysis' => true,
        'development_assistance' => true
    ]
];
```

#### 3.4 Email Configuration
```php
$config['email'] = [
    'enabled' => true,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password',
    'smtp_encryption' => 'tls',
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'APS Dream Home'
];
```

### Step 4: SSL & Security Setup

#### 4.1 SSL Certificate
```bash
# Install Certbot for Let's Encrypt
sudo apt-get install certbot python3-certbot-apache

# Generate SSL certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 3 * * * certbot renew --quiet
```

#### 4.2 Security Headers
```apache
# Add to Virtual Host
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Frame-Options DENY
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com"
</IfModule>
```

#### 4.3 PHP Security
```ini
# php.ini security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
session.cookie_httponly = 1
session.use_only_cookies = 1
session.cookie_secure = 1
```

---

## 🔧 WhatsApp Business API Setup

### Step 1: Create WhatsApp Business Account
1. Go to [Facebook Business](https://business.facebook.com)
2. Create a Business Account
3. Add WhatsApp Business API

### Step 2: API Configuration
1. **Get Phone Number ID**
   - In WhatsApp Manager
   - Go to Phone Numbers
   - Copy Phone Number ID

2. **Generate Access Token**
   - Go to WhatsApp > API Setup
   - Generate Permanent Token
   - Copy Access Token

3. **Set Webhook URL**
   ```
   https://yourdomain.com/api/whatsapp_webhook.php
   ```

### Step 3: Update Configuration
```php
$config['whatsapp'] = [
    'business_account_id' => 'your_business_account_id',
    'access_token' => 'your_permanent_token',
    'webhook_verify_token' => 'your_webhook_token'
];
```

### Step 4: Verify Webhook
WhatsApp will send a GET request to verify the webhook:
```
GET /api/whatsapp_webhook.php?hub_verify_token=your_token&hub_challenge=test
```

---

## 🚀 Deployment Scripts

### Automated Deployment Script
```bash
#!/bin/bash
# deploy.sh

echo "🚀 Deploying APS Dream Home..."

# Backup current installation
cp -r /var/www/apsdreamhome /var/www/apsdreamhome.backup.$(date +%Y%m%d_%H%M%S)

# Upload new files
rsync -av --exclude='.git' /path/to/local/apsdreamhome/ /var/www/apsdreamhome/

# Set permissions
chown -R www-data:www-data /var/www/apsdreamhome
chmod -R 755 /var/www/apsdreamhome
chmod -R 777 /var/www/apsdreamhome/logs/
chmod -R 777 /var/www/apsdreamhome/uploads/

# Update database if needed
php /var/www/apsdreamhome/setup_database.php

# Clear caches
php /var/www/apsdreamhome/clear_cache.php

# Test deployment
curl -s http://localhost/apsdreamhomefinal/basic_system_test.php | grep -q "✅" && echo "✅ Deployment successful!" || echo "❌ Deployment failed!"

echo "🎉 Deployment completed!"
```

### Windows Deployment Script
```batch
@echo off
echo 🚀 Deploying APS Dream Home...

REM Backup current installation
xcopy /E /I /Y C:\xampp\htdocs\apsdreamhomefinal C:\xampp\htdocs\apsdreamhomefinal.backup.%date:~-10,4%%date:~-5,2%%date:~-2,2%_%time:~0,2%%time:~3,2%%time:~6,2%

REM Upload new files (if using different source)
REM xcopy /E /I /Y C:\path\to\new\files C:\xampp\htdocs\apsdreamhomefinal

REM Set permissions
icacls "C:\xampp\htdocs\apsdreamhomefinal" /grant "Everyone":F /T

REM Test deployment
php C:\xampp\htdocs\apsdreamhomefinal\basic_system_test.php > test_result.txt
findstr "✅" test_result.txt > nul && echo ✅ Deployment successful! || echo ❌ Deployment failed!

echo 🎉 Deployment completed!
```

---

## 📊 Monitoring & Maintenance

### Log Monitoring
```bash
# Monitor WhatsApp logs
tail -f /var/log/apsdreamhome/whatsapp.log

# Monitor email logs
tail -f /var/log/apsdreamhome/email.log

# Monitor system logs
tail -f /var/log/apsdreamhome/system.log
```

### Performance Monitoring
```php
// Add to cron for regular monitoring
// crontab -e
// Add: */15 * * * * php /var/www/apsdreamhome/monitor_performance.php
```

### Backup Strategy
```bash
#!/bin/bash
# backup.sh

# Database backup
mysqldump -u aps_user -p'password' apsdreamhome > /backup/apsdreamhome_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf /backup/apsdreamhome_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/apsdreamhome/

# Cleanup old backups (keep last 7 days)
find /backup/ -name "apsdreamhome_*" -type f -mtime +7 -delete
```

---

## 🔒 Security Hardening

### 1. File Permissions
```bash
# Secure file permissions
find /var/www/apsdreamhome -type f -exec chmod 644 {} \;
find /var/www/apsdreamhome -type d -exec chmod 755 {} \;

# Executable permissions
chmod +x /var/www/apsdreamhome/deploy.sh
chmod +x /var/www/apsdreamhome/backup.sh

# Sensitive files
chmod 600 /var/www/apsdreamhome/includes/config.php
```

### 2. Firewall Configuration
```bash
# Allow only necessary ports
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 22  # SSH (restrict to your IP)

# Enable firewall
sudo ufw enable
```

### 3. Fail2Ban Setup
```bash
# Install Fail2Ban
sudo apt-get install fail2ban

# Configure for Apache
sudo vim /etc/fail2ban/jail.local
```
```ini
[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/*error.log
maxretry = 5

[apache-noscript]
enabled = true
port = http,https
filter = apache-noscript
logpath = /var/log/apache2/*error.log
maxretry = 6
```

---

## 🚀 Performance Optimization

### PHP Optimization
```ini
# php.ini optimizations
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1

# APCu for better caching
apc.enabled=1
apc.shm_size=128M
```

### Database Optimization
```sql
-- Optimize tables
OPTIMIZE TABLE ai_user_interactions;
OPTIMIZE TABLE ai_knowledge_base;
OPTIMIZE TABLE whatsapp_logs;

-- Add indexes for better performance
CREATE INDEX idx_timestamp ON ai_user_interactions(interaction_timestamp);
CREATE INDEX idx_phone ON whatsapp_logs(recipient);
CREATE INDEX idx_email ON email_logs(recipient);
```

### Web Server Optimization
```apache
# Apache optimizations
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>
```

---

## 🔍 Testing Production Setup

### 1. System Health Check
```bash
# Test all components
curl -s http://yourdomain.com/apsdreamhomefinal/basic_system_test.php | grep -E "(✅|❌)"

# Test API endpoints
curl -X POST http://yourdomain.com/apsdreamhomefinal/api/test_whatsapp.php \
  -H "Content-Type: application/json" \
  -d '{"phone":"9876543210","message":"Test message"}'
```

### 2. Load Testing
```bash
# Install Apache Bench
sudo apt-get install apache2-utils

# Test dashboard performance
ab -n 100 -c 10 http://yourdomain.com/apsdreamhomefinal/management_dashboard.php

# Test API performance
ab -n 100 -c 10 -p post_data.json -T application/json http://yourdomain.com/apsdreamhomefinal/api/test_whatsapp.php
```

### 3. Security Testing
```bash
# Install security tools
sudo apt-get install nikto nmap

# Basic security scan
nikto -h http://yourdomain.com

# Port scan
nmap -p 80,443 yourdomain.com
```

---

## 📞 Support & Troubleshooting

### Common Issues

#### 1. WhatsApp Webhook Not Working
```bash
# Check webhook logs
tail -f /var/log/apsdreamhome/whatsapp_webhook.log

# Test webhook endpoint
curl -X GET "http://yourdomain.com/api/whatsapp_webhook.php?hub_verify_token=your_token&hub_challenge=test"
```

#### 2. Email Not Sending
```bash
# Test SMTP connection
telnet smtp.gmail.com 587

# Check email logs
tail -f /var/log/apsdreamhome/email.log

# Test email API
curl -X POST http://yourdomain.com/api/test_email.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

#### 3. AI Not Responding
```bash
# Check AI configuration
php -r "include 'includes/config.php'; echo 'AI Enabled: ' . ($config['ai']['enabled'] ? 'Yes' : 'No') . PHP_EOL;"

# Test AI API
curl -X POST http://yourdomain.com/api/ai_agent_chat.php \
  -H "Content-Type: application/json" \
  -d '{"message":"Hello","context":"test"}'
```

### Emergency Contacts
- **Technical Support:** 9277121112
- **Email:** apsdreamhomes44@gmail.com
- **Emergency Backup:** Check `management_dashboard.php`

---

## 🚀 Production Checklist

- [ ] Server setup completed
- [ ] Database configured and optimized
- [ ] SSL certificate installed
- [ ] WhatsApp Business API configured
- [ ] AI API key set up
- [ ] Email SMTP configured
- [ ] Security hardening applied
- [ ] Performance optimizations applied
- [ ] Backup system configured
- [ ] Monitoring and alerting set up
- [ ] All tests passing
- [ ] Documentation updated

---

## 📈 Scaling & Growth

### High Availability Setup
1. **Load Balancer:** Nginx/HAProxy
2. **Database Clustering:** MySQL Cluster/MariaDB Galera
3. **Redis Caching:** For session and cache storage
4. **CDN Integration:** Cloudflare/CloudFront for static assets

### Advanced Features
1. **Multi-language Support**
2. **Advanced Analytics Dashboard**
3. **Mobile App Integration**
4. **Voice AI Integration**
5. **Property Image Analysis**

### Performance Monitoring
1. **New Relic** for application monitoring
2. **Grafana** for metrics visualization
3. **ELK Stack** for log analysis
4. **Pingdom** for uptime monitoring

---

**🎉 Congratulations!** Your APS Dream Home system is now production-ready!

For ongoing support and updates, refer to the user guide and management dashboard.
