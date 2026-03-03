# APS Dream Home Production Deployment Guide

Generated on: 2026-03-03 12:29:27

## Overview
This guide covers the complete production deployment setup for APS Dream Home.

## Prerequisites
- Ubuntu 20.04+ or CentOS 8+
- Nginx or Apache web server
- PHP 8.1+
- MySQL 8.0+
- Redis server
- SSL certificate

## Deployment Steps

### 1. Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install nginx mysql-server redis-server php8.1-fpm php8.1-mysql php8.1-redis -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Database Setup
```bash
# Create database and user
mysql -u root -p < database_production_setup.sql
```

### 3. Application Setup
```bash
# Clone repository
git clone https://github.com/your-username/apsdreamhome.git /var/www/apsdreamhome
cd /var/www/apsdreamhome

# Copy production environment
cp .env.production .env

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production

# Set permissions
sudo chown -R www-data:www-data /var/www/apsdreamhome
sudo chmod -R 755 /var/www/apsdreamhome
```

### 4. Web Server Configuration
- Copy `.htaccess.production` to `.htaccess` for Apache
- Copy `nginx.production.conf` to nginx config for Nginx
- Restart web server

### 5. SSL Certificate
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d www.apsdreamhome.com -d apsdreamhome.com
```

### 6. Deployment
```bash
# Run deployment script
./deploy_production.sh
```

## Monitoring
- Access monitoring dashboard: /admin/monitoring_dashboard.php
- Check logs: /var/log/apsdreamhome/
- Health check: /health

## Security
- All security headers configured
- HTTPS enforced
- Rate limiting enabled
- CSRF protection enabled
- Input sanitization implemented

## Backup
- Database backups: Daily at 2 AM
- File backups: Daily at 3 AM
- Retention: 30 days for DB, 7 days for files

## Support
- Email: support@apsdreamhome.com
- Phone: +91-7007444842
