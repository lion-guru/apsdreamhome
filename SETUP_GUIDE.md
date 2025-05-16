# APS Dream Home Project Setup Guide

## Prerequisites

### System Requirements
- PHP 8.1+
- MySQL 8.0+
- Composer
- Git
- Web Server (Apache/Nginx)

### Required PHP Extensions
- mysqli
- openssl
- json
- curl
- mbstring
- opcache

## Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/apsdreamhome.git
cd apsdreamhome
```

### 2. Configure Environment
1. Copy environment template
```bash
cp .env.example .env
```

2. Edit `.env` file with your configuration
- Update database credentials
- Set application secret key
- Configure email and SMS providers
- Adjust security settings

### 3. Install Dependencies
```bash
composer install
```

### 4. Database Setup
1. Create MySQL database
```sql
CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Run database migrations
```bash
php setup.php
```

### 5. Web Server Configuration

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

#### Nginx (nginx.conf)
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 6. Background Tasks
Set up cron jobs for background processing
```bash
crontab -e
```
Add the following lines:
```
*/5 * * * * php /path/to/project/scripts/process_email_queue.php
*/5 * * * * php /path/to/project/scripts/process_sms_queue.php
0 1 * * * php /path/to/project/scripts/security_cleanup.php
```

## Security Configuration

### Initial Security Setup
1. Generate strong secret key
2. Enable two-factor authentication
3. Configure IP whitelisting
4. Set up rate limiting

### Recommended Security Practices
- Use strong, unique passwords
- Enable HTTPS
- Regularly update dependencies
- Monitor security logs
- Implement IP whitelisting

## Troubleshooting

### Common Issues
- Verify PHP extensions are installed
- Check database connection settings
- Ensure proper file permissions
- Review error logs

### Debugging
```bash
php -i  # PHP information
composer diagnose  # Composer diagnostics
```

## Development vs Production

### Development Environment
- Enable debug mode in `.env`
- Use development-specific settings
- Detailed error reporting

### Production Environment
- Disable debug mode
- Use production database
- Enable caching
- Implement additional security measures

## Updating the Project
```bash
git pull origin main
composer update
php setup.php  # Run migrations
```

## Contributing
Please read our contribution guidelines before making changes.

## Support
For issues, please open a GitHub issue or contact support.

## License
See LICENSE file for details.
