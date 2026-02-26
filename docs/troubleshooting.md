# APS Dream Home - Troubleshooting Guide

## Overview

This guide helps you diagnose and resolve common issues with the APS Dream Home platform. Follow the step-by-step troubleshooting procedures to quickly identify and fix problems.

---

## 📋 Quick Diagnosis

### System Health Check
Run this command to check overall system health:
```bash
# On your server
sudo bash health-check.sh

# Or manually check these components:
curl -I https://yourdomain.com
curl -s https://yourdomain.com/api/v1/health
mysql -u apsdreamhome_user -p -e "SELECT 1;"
php artisan --version
```

---

## 🔧 Common Issues & Solutions

### 1. Website Not Loading (HTTP 500 Error)

#### Symptoms
- Website shows "Internal Server Error"
- Blank white page
- Error 500 in browser

#### Causes & Solutions

**Cause 1: PHP Syntax Errors**
```bash
# Check PHP error logs
tail -f /var/log/php8.1-fpm.log
tail -f /var/log/nginx/error.log

# Check syntax of PHP files
find /var/www/apsdreamhome -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
```

**Cause 2: Database Connection Issues**
```bash
# Test database connection
mysql -u apsdreamhome_user -p -e "SELECT 1;"

# Check database credentials in .env
cat /var/www/apsdreamhome/.env | grep DB_

# Restart database service
sudo systemctl restart mysql
```

**Cause 3: File Permissions**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome/storage
sudo chmod -R 775 /var/www/apsdreamhome/storage

# Fix bootstrap cache permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome/bootstrap/cache
sudo chmod -R 775 /var/www/apsdreamhome/bootstrap/cache
```

**Cause 4: Missing Dependencies**
```bash
# Install missing PHP extensions
sudo apt install php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Clear composer autoloader
cd /var/www/apsdreamhome
composer dump-autoload
```

### 2. Login Issues

#### Symptoms
- Cannot log in with correct credentials
- "Invalid credentials" error
- Password reset not working

#### Solutions

**Check User Account Status**
```sql
-- Check if user exists and is active
SELECT id, name, email, is_active, role FROM users WHERE email = 'user@example.com';

-- Check password reset tokens
SELECT * FROM password_resets WHERE email = 'user@example.com';
```

**Clear Application Cache**
```bash
cd /var/www/apsdreamhome
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Check Session Configuration**
```bash
# Check session save path permissions
ls -la /var/lib/php/sessions/

# Clear expired sessions
find /var/lib/php/sessions/ -name "sess_*" -type f -mtime +1 -delete
```

**Email Configuration Issues**
```bash
# Test email sending
cd /var/www/apsdreamhome
php artisan tinker
# Mail::raw('Test email', function($message) { $message->to('test@example.com')->subject('Test'); });
```

### 3. Property Upload Issues

#### Symptoms
- Cannot upload property images
- File upload fails
- Large files rejected

#### Solutions

**Check Upload Directory Permissions**
```bash
# Check storage permissions
ls -la /var/www/apsdreamhome/storage/app/public/

# Fix permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome/storage
sudo chmod -R 775 /var/www/apsdreamhome/storage

# Create symbolic link if missing
cd /var/www/apsdreamhome
php artisan storage:link
```

**Check PHP Upload Limits**
```bash
# Check php.ini settings
php -i | grep -E "(upload_max_filesize|post_max_size|memory_limit|max_execution_time)"

# Update if necessary
sudo nano /etc/php/8.1/fpm/php.ini
# upload_max_filesize = 10M
# post_max_size = 12M
# memory_limit = 256M
# max_execution_time = 300

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

**Check File Type Restrictions**
```php
// Check allowed file types in config/filesystems.php
'allowed_mime_types' => [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
],
```

### 4. Database Connection Issues

#### Symptoms
- "SQLSTATE[HY000] [2002] Connection refused"
- Slow database queries
- Database connection timeout

#### Solutions

**Check Database Service**
```bash
# Check if MySQL is running
sudo systemctl status mysql

# Restart MySQL if needed
sudo systemctl restart mysql

# Check MySQL error logs
tail -f /var/log/mysql/error.log
```

**Verify Database Credentials**
```bash
# Test connection with credentials from .env
mysql -h localhost -u apsdreamhome_user -p -e "SELECT 1;"

# Check .env file
cat /var/www/apsdreamhome/.env | grep DB_
```

**Optimize Database Performance**
```sql
-- Check slow queries
SHOW PROCESSLIST;
SHOW ENGINE INNODB STATUS;

-- Analyze and optimize tables
ANALYZE TABLE users, properties, leads;
OPTIMIZE TABLE users, properties, leads;

-- Check index usage
SHOW INDEX FROM properties;
```

**Database Backup Issues**
```bash
# Test backup script
sudo bash backup-production.sh database

# Check backup directory permissions
ls -la /var/backups/apsdreamhome/

# Verify backup integrity
gzip -t /var/backups/apsdreamhome/database/backup_file.sql.gz
```

### 5. Payment Gateway Issues

#### Symptoms
- Payment processing fails
- Razorpay integration not working
- Webhook notifications not received

#### Solutions

**Check Razorpay Configuration**
```php
// Verify API credentials in .env
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxxxxxxxxxx
```

**Test Payment Integration**
```bash
# Check webhook endpoint
curl -X POST https://yourdomain.com/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -d '{"test": "webhook"}'
```

**SSL Certificate Issues**
```bash
# Check SSL certificate
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com

# Renew certificate if expired
sudo certbot renew
sudo systemctl reload nginx
```

### 6. Email Delivery Issues

#### Symptoms
- Registration emails not received
- Password reset emails not working
- Enquiry notifications failing

#### Solutions

**Check Email Configuration**
```php
// Verify email settings in .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

**Test Email Sending**
```bash
cd /var/www/apsdreamhome
php artisan tinker
# Mail::raw('Test email from APS Dream Home', function($message) { $message->to('test@example.com')->subject('Test Email'); });
```

**Check Email Logs**
```bash
# Check mail logs
tail -f /var/log/mail.log

# Check Laravel log for email errors
tail -f /var/www/apsdreamhome/storage/logs/laravel.log | grep mail
```

### 7. Performance Issues

#### Symptoms
- Website loading slowly
- High server resource usage
- Database queries timing out

#### Solutions

**Enable Caching**
```bash
cd /var/www/apsdreamhome

# Cache configuration and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache if needed
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Database Query Optimization**
```sql
-- Add missing indexes
CREATE INDEX idx_properties_status ON properties (status);
CREATE INDEX idx_properties_city ON properties (city);
CREATE INDEX idx_leads_status ON leads (status);

-- Analyze slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
SHOW PROCESSLIST;
```

**Server Optimization**
```bash
# Enable OPcache
sudo phpenmod opcache
sudo systemctl restart php8.1-fpm

# Optimize Nginx configuration
sudo nginx -t && sudo systemctl reload nginx

# Check server resources
htop
df -h
free -h
```

### 8. SSL/HTTPS Issues

#### Symptoms
- Mixed content warnings
- SSL certificate errors
- HTTPS not working

#### Solutions

**SSL Certificate Management**
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew --cert-name yourdomain.com

# Force HTTPS redirect
sudo certbot --nginx --cert-name yourdomain.com
```

**Mixed Content Issues**
```php
// Update .env file
APP_URL=https://yourdomain.com

// Update asset URLs in views
{{ asset('css/app.css') }} // Will automatically use HTTPS
{{ url('api/data') }} // Will use HTTPS
```

### 9. API Issues

#### Symptoms
- API endpoints returning errors
- Authentication failures
- Rate limiting issues

#### Solutions

**API Authentication**
```bash
# Test API authentication
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Check JWT token generation
cd /var/www/apsdreamhome
php artisan tinker
# $user = App\Models\User::first(); $token = $user->createToken('test')->plainTextToken;
```

**Rate Limiting**
```php
// Check rate limiting configuration
// config/app.php or middleware
'throttle' => [
    'api' => '1000,1', // 1000 requests per minute
],
```

### 10. File Permission Issues

#### Symptoms
- Cannot write to storage directory
- Log files not created
- Cache files not writable

#### Solutions

**Fix Directory Permissions**
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/apsdreamhome

# Set directory permissions
sudo find /var/www/apsdreamhome -type d -exec chmod 755 {} \;
sudo find /var/www/apsdreamhome -type f -exec chmod 644 {} \;

# Special permissions for Laravel
sudo chmod -R 775 /var/www/apsdreamhome/storage
sudo chmod -R 775 /var/www/apsdreamhome/bootstrap/cache

# Create .htaccess if missing
cat > /var/www/apsdreamhome/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
EOF
```

---

## 🔍 Advanced Diagnostics

### Server Logs Analysis
```bash
# Check all relevant logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.1-fpm.log
tail -f /var/log/mysql/error.log
tail -f /var/www/apsdreamhome/storage/logs/laravel.log
```

### Performance Profiling
```bash
# Enable Laravel debugbar
composer require barryvdh/laravel-debugbar --dev

# Check New Relic or similar APM tools
# Monitor database queries
# Check memory usage
```

### Database Diagnostics
```sql
-- Check table sizes
SELECT
  TABLE_NAME,
  ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'apsdreamhome_prod'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;

-- Check for orphaned records
SELECT COUNT(*) FROM leads WHERE property_id NOT IN (SELECT id FROM properties);
SELECT COUNT(*) FROM leads WHERE user_id NOT IN (SELECT id FROM users);
```

---

## 🚨 Emergency Procedures

### Complete System Restore
```bash
# Stop services
sudo systemctl stop nginx php8.1-fpm mysql

# Restore from backup
sudo bash backup-production.sh restore /path/to/backup.tar.gz

# Restart services
sudo systemctl start mysql php8.1-fpm nginx

# Run health check
sudo bash health-check.sh
```

### Emergency Contacts
- **Technical Support**: dev@apsdreamhome.com
- **System Administrator**: admin@apsdreamhome.com
- **Emergency Hotline**: +91-7007444842

---

## 📊 Monitoring & Prevention

### Proactive Monitoring
```bash
# Set up automated health checks
crontab -e
# Add: */5 * * * * /usr/local/bin/apsdreamhome-health-check full

# Monitor disk usage
df -h | awk 'NR>1 {if ($5+0 > 80) print "Warning: " $1 " is " $5 " full"}'

# Monitor services
systemctl status nginx php8.1-fpm mysql --no-pager
```

### Backup Verification
```bash
# Test backup restoration
sudo bash backup-production.sh verify

# Check backup integrity
find /var/backups/apsdreamhome -name "*.sql.gz" -exec gzip -t {} \;
```

---

## 🆘 Getting Help

### Support Resources
1. **Check Logs**: Always check error logs first
2. **Run Diagnostics**: Use health-check.sh script
3. **Search Documentation**: Refer to this troubleshooting guide
4. **Community Forums**: Check community discussions
5. **Professional Support**: Contact APS Dream Home support

### When to Contact Support
- Critical system downtime
- Data loss or corruption
- Security incidents
- Payment processing failures
- Issues affecting multiple users

---

## 📝 Maintenance Checklist

### Daily
- [ ] Check server resource usage
- [ ] Monitor error logs
- [ ] Verify backup completion
- [ ] Test critical user flows

### Weekly
- [ ] Update system packages
- [ ] Clear application caches
- [ ] Review and optimize slow queries
- [ ] Check SSL certificate expiry

### Monthly
- [ ] Full system backup verification
- [ ] Security audit and updates
- [ ] Performance optimization review
- [ ] User feedback analysis

---

**For additional support or complex issues, contact our technical support team at dev@apsdreamhome.com**

**Last updated: February 2026**
