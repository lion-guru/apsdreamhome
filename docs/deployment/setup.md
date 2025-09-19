# Deployment Guide

This guide provides step-by-step instructions for deploying the APS Dream Home application to a production environment.

## Prerequisites

### Server Requirements
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Composer
- Git

### Required PHP Extensions
- PDO
- pdo_mysql
- mbstring
- json
- openssl
- tokenizer
- ctype
- XML

## Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/apsdreamhome.git
cd apsdreamhome
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
1. Create a new MySQL database
2. Update `.env` with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=apsdreamhome
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

### 5. Run Migrations and Seeders
```bash
php artisan migrate --seed
```

### 6. Set Up Storage Link
```bash
php artisan storage:link
```

### 7. Set File Permissions
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## Web Server Configuration

### Apache
Ensure your `.htaccess` file is properly configured and `mod_rewrite` is enabled.

### Nginx
Add this to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Scheduled Tasks
Set up a cron job to run the scheduler:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Workers
For processing background jobs, run:
```bash
php artisan queue:work --daemon
```

## Monitoring
- Set up error logging
- Monitor server resources
- Set up backups

## Security
- Set `APP_ENV=production` in `.env`
- Set `APP_DEBUG=false` in `.env`
- Use HTTPS
- Keep dependencies updated
- Regularly backup your database

## Updating
To update your installation:
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan cache:clear
php artisan view:clear
```

## Troubleshooting

### Common Issues
1. **500 Server Error**
   - Check storage and bootstrap/cache permissions
   - Verify .env configuration
   - Check error logs

2. **Database Connection Issues**
   - Verify database credentials
   - Check if MySQL is running
   - Ensure database user has correct permissions

3. **File Upload Issues**
   - Check storage directory permissions
   - Verify upload_max_filesize in php.ini
   - Check post_max_size in php.ini

## Support
For additional help, please contact support@apsdreamhome.com
