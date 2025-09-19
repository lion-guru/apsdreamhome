# Deployment Guide

This guide provides instructions for deploying the APS Dream Home system in different environments.

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Environment Setup](#environment-setup)
5. [Database Setup](#database-setup)
6. [Web Server Configuration](#web-server-configuration)
7. [SSL Configuration](#ssl-configuration)
8. [Cron Jobs](#cron-jobs)
9. [Troubleshooting](#troubleshooting)

## Server Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher / MariaDB 10.3 or higher
- Web server (Apache/Nginx)
- Composer
- Git
- SSL Certificate (for production)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-organization/aps-dream-home.git
cd aps-dream-home
```

### 2. Install Dependencies
```bash
composer install
npm install
npm run production
```

### 3. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/framework/
chmod -R 775 storage/logs/
```

## Configuration

### 1. Environment File
```bash
cp .env.example .env
php artisan key:generate
```

### 2. Update Environment Variables
Edit the `.env` file with your configuration:
```env
APP_NAME="APS Dream Home"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aps_dream_home
DB_USERNAME=db_user
DB_PASSWORD=db_password
```

## Database Setup

### 1. Create Database
```sql
CREATE DATABASE aps_dream_home CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Migrations
```bash
php artisan migrate --seed
```

### 3. Import Sample Data (Optional)
```bash
php artisan db:seed --class=SampleDataSeeder
```

## Web Server Configuration

### Apache
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/aps-dream-home/public

    <Directory /var/www/aps-dream-home/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/aps-dream-home/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## SSL Configuration

### Using Let's Encrypt
```bash
sudo certbot --apache -d your-domain.com
# or
sudo certbot --nginx -d your-domain.com
```

## Cron Jobs

Set up the following cron job:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

### Common Issues

1. **Permission Issues**
   ```bash
   sudo chown -R www-data:www-data /var/www/aps-dream-home
   sudo chmod -R 755 /var/www/aps-dream-home/storage
   ```

2. **Storage Link**
   ```bash
   php artisan storage:link
   ```

3. **Cache Clear**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

## Support

For deployment support, contact:
- Email: devops@apsdreamhome.com
- Phone: +91-XXXXXXXXXX
