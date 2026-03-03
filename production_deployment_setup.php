<?php
/**
 * APS Dream Home - Production Deployment Setup
 * Complete production-ready deployment configuration
 */

echo "🚀 Production Deployment Setup\n";
echo "===============================\n\n";

$projectRoot = __DIR__;
$deploymentConfig = [];
$securityConfig = [];

// 1. Production Environment Configuration
echo "🌍 Setting Up Production Environment...\n";

$productionEnv = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => 'https://www.apsdreamhome.com',
    'DB_HOST' => 'localhost',
    'DB_DATABASE' => 'apsdreamhome_prod',
    'DB_USERNAME' => 'apsdreamhome_user',
    'DB_PASSWORD' => 'secure_password_here',
    'CACHE_DRIVER' => 'redis',
    'SESSION_DRIVER' => 'redis',
    'QUEUE_CONNECTION' => 'redis',
    'MAIL_DRIVER' => 'smtp',
    'MAIL_HOST' => 'smtp.apsdreamhome.com',
    'MAIL_PORT' => '587',
    'MAIL_USERNAME' => 'noreply@apsdreamhome.com',
    'MAIL_PASSWORD' => 'email_password_here',
    'MAIL_ENCRYPTION' => 'tls',
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PASSWORD' => 'redis_password_here',
    'REDIS_PORT' => '6379'
];

$productionEnvContent = "# Production Environment Configuration\n";
$productionEnvContent .= "# Generated on: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($productionEnv as $key => $value) {
    $productionEnvContent .= "$key=$value\n";
}

file_put_contents($projectRoot . '/.env.production', $productionEnvContent);
echo "✅ Production environment file created\n";
$deploymentConfig[] = "Production environment configured";

// 2. Security Configuration
echo "\n🔒 Setting Up Security Configuration...\n";

$securitySettings = [
    'force_https' => true,
    'disable_php_errors' => true,
    'secure_headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
    ],
    'csrf_protection' => true,
    'rate_limiting' => [
        'api' => 100,
        'login' => 5,
        'general' => 1000
    ],
    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true
    ],
    'session_security' => [
        'lifetime' => 7200,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]
];

file_put_contents($projectRoot . '/config/security_production.json', json_encode($securitySettings, JSON_PRETTY_PRINT));
echo "✅ Security configuration created\n";
$securityConfig[] = "Security settings configured";

// 3. Web Server Configuration
echo "\n🌐 Setting Up Web Server Configuration...\n";

// Apache .htaccess for production
$apacheConfig = "
# Production Apache Configuration
# Generated on: " . date('Y-m-d H:i:s') . "

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>

# Hide PHP Version
<IfModule mod_php7.c>
    php_flag expose_php off
</IfModule>

# Disable PHP Errors in Production
<IfModule mod_php7.c>
    php_flag display_errors off
    php_flag log_errors on
    php_value error_log /var/log/apsdreamhome/php_errors.log
</IfModule>

# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg \"access plus 1 year\"
    ExpiresByType image/jpeg \"access plus 1 year\"
    ExpiresByType image/gif \"access plus 1 year\"
    ExpiresByType image/png \"access plus 1 year\"
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/pdf \"access plus 1 month\"
    ExpiresByType text/javascript \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType application/x-shockwave-flash \"access plus 1 month\"
    ExpiresByType image/x-icon \"access plus 1 year\"
</IfModule>

# Block Suspicious Requests
<IfModule mod_rewrite.c>
    RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|DELETE|TRACK) [NC]
    RewriteRule ^(.*)$ - [F,L]
    
    # Block bad bots
    RewriteCond %{HTTP_USER_AGENT} ^.*(bot|crawl|spider|scraper).*$ [NC]
    RewriteRule ^(.*)$ - [F,L]
</IfModule>

# Protect Sensitive Files
<FilesMatch \"^(config|\.env|\.htaccess)\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect Backup Files
<FilesMatch \"\\.(bak|backup|old|tmp|log)\">
    Order allow,deny
    Deny from all
</FilesMatch>
";

file_put_contents($projectRoot . '/.htaccess.production', $apacheConfig);
echo "✅ Apache configuration created\n";

// Nginx configuration
$nginxConfig = "
# Production Nginx Configuration
# Generated on: " . date('Y-m-d H:i:s') . "

server {
    listen 80;
    server_name www.apsdreamhome.com apsdreamhome.com;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.apsdreamhome.com apsdreamhome.com;
    root /var/www/apsdreamhome/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/apsdreamhome.crt;
    ssl_certificate_key /etc/ssl/private/apsdreamhome.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options SAMEORIGIN always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection \"1; mode=block\" always;
    add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains\" always;
    add_header Referrer-Policy \"strict-origin-when-cross-origin\" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Browser Caching
    location ~* \\.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control \"public, immutable\";
    }

    # PHP Processing
    location ~ \\.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\\.php)(/.+)\$;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Pretty URLs
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Block Suspicious Requests
    if (\$request_method ~ ^(HEAD|TRACE|DELETE|TRACK)\$ ) {
        return 403;
    }

    # Protect Sensitive Files
    location ~* \\.(env|config|htaccess|log|bak|backup|old|tmp)\$ {
        deny all;
    }
}
";

file_put_contents($projectRoot . '/nginx.production.conf', $nginxConfig);
echo "✅ Nginx configuration created\n";
$deploymentConfig[] = "Web server configurations created";

// 4. Database Setup Script
echo "\n🗄️ Creating Database Setup Script...\n";

$databaseSetup = "
-- Production Database Setup
-- Generated on: " . date('Y-m-d H:i:s') . "

-- Create production database
CREATE DATABASE IF NOT EXISTS apsdreamhome_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create production user
CREATE USER IF NOT EXISTS 'apsdreamhome_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON apsdreamhome_prod.* TO 'apsdreamhome_user'@'localhost';
FLUSH PRIVILEGES;

-- Use production database
USE apsdreamhome_prod;

-- Create tables (if not exists)
-- These would be your existing table creation scripts
-- Example:
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(12,2),
    location VARCHAR(255),
    type VARCHAR(50),
    bedrooms INT,
    bathrooms INT,
    area INT,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'sold') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_property_type (type),
    INDEX idx_property_location (location),
    INDEX idx_property_featured (featured),
    INDEX idx_property_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add more table creation scripts as needed
";

file_put_contents($projectRoot . '/database_production_setup.sql', $databaseSetup);
echo "✅ Database setup script created\n";
$deploymentConfig[] = "Database setup script created";

// 5. Deployment Script
echo "\n🚀 Creating Deployment Script...\n";

$deploymentScript = "#!/bin/bash
# APS Dream Home Production Deployment Script
# Generated on: " . date('Y-m-d H:i:s') . "

set -e

echo \"🚀 Starting Production Deployment...\"

# Variables
PROJECT_DIR=\"/var/www/apsdreamhome\"
BACKUP_DIR=\"/var/backups/apsdreamhome\"
GIT_REPO=\"https://github.com/your-username/apsdreamhome.git\"
BRANCH=\"production\"

# Create backup
echo \"💾 Creating backup...\"
mkdir -p \$BACKUP_DIR
mysqldump -u root apsdreamhome_prod > \$BACKUP_DIR/db_backup_\$(date +%Y%m%d_%H%M%S).sql
tar -czf \$BACKUP_DIR/files_backup_\$(date +%Y%m%d_%H%M%S).tar.gz \$PROJECT_DIR

# Pull latest code
echo \"📥 Pulling latest code...\"
cd \$PROJECT_DIR
git pull origin \$BRANCH

# Install dependencies
echo \"📦 Installing dependencies...\"
composer install --no-dev --optimize-autoloader
npm ci --production

# Run database migrations
echo \"🗄️ Running database migrations...\"
php artisan migrate --force

# Clear caches
echo \"🧹 Clearing caches...\"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Set permissions
echo \"🔒 Setting permissions...\"
chown -R www-data:www-data \$PROJECT_DIR
chmod -R 755 \$PROJECT_DIR
chmod -R 777 \$PROJECT_DIR/storage
chmod -R 777 \$PROJECT_DIR/bootstrap/cache

# Restart services
echo \"🔄 Restarting services...\"
systemctl reload nginx
systemctl reload php8.1-fpm
systemctl restart redis-server

# Health check
echo \"🏥 Running health check...\"
curl -f http://localhost/health || exit 1

echo \"✅ Deployment completed successfully!\"
";

file_put_contents($projectRoot . '/deploy_production.sh', $deploymentScript);
chmod($projectRoot . '/deploy_production.sh', 0755);
echo "✅ Deployment script created\n";
$deploymentConfig[] = "Deployment script created";

// 6. Monitoring and Logging Setup
echo "\n📊 Setting Up Monitoring and Logging...\n";

$monitoringConfig = [
    'error_reporting' => [
        'level' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT',
        'display_errors' => false,
        'log_errors' => true,
        'error_log' => '/var/log/apsdreamhome/php_errors.log'
    ],
    'logging' => [
        'daily' => true,
        'level' => 'error',
        'max_files' => 30,
        'path' => '/var/log/apsdreamhome/'
    ],
    'monitoring' => [
        'uptime_check' => 'https://www.apsdreamhome.com/health',
        'performance_monitoring' => true,
        'error_alerts' => true,
        'slack_webhook' => 'https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK'
    ],
    'backup' => [
        'database' => [
            'enabled' => true,
            'schedule' => '0 2 * * *',
            'retention' => 30
        ],
        'files' => [
            'enabled' => true,
            'schedule' => '0 3 * * *',
            'retention' => 7
        ]
    ]
];

file_put_contents($projectRoot . '/config/monitoring_production.json', json_encode($monitoringConfig, JSON_PRETTY_PRINT));
echo "✅ Monitoring configuration created\n";
$deploymentConfig[] = "Monitoring system configured";

// 7. Generate Deployment Documentation
echo "\n📋 Generating Deployment Documentation...\n";

$deploymentDoc = "# APS Dream Home Production Deployment Guide\n\n";
$deploymentDoc .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
$deploymentDoc .= "## Overview\n";
$deploymentDoc .= "This guide covers the complete production deployment setup for APS Dream Home.\n\n";
$deploymentDoc .= "## Prerequisites\n";
$deploymentDoc .= "- Ubuntu 20.04+ or CentOS 8+\n";
$deploymentDoc .= "- Nginx or Apache web server\n";
$deploymentDoc .= "- PHP 8.1+\n";
$deploymentDoc .= "- MySQL 8.0+\n";
$deploymentDoc .= "- Redis server\n";
$deploymentDoc .= "- SSL certificate\n\n";
$deploymentDoc .= "## Deployment Steps\n\n";
$deploymentDoc .= "### 1. Server Setup\n";
$deploymentDoc .= "```bash\n";
$deploymentDoc .= "# Update system\n";
$deploymentDoc .= "sudo apt update && sudo apt upgrade -y\n\n";
$deploymentDoc .= "# Install required packages\n";
$deploymentDoc .= "sudo apt install nginx mysql-server redis-server php8.1-fpm php8.1-mysql php8.1-redis -y\n\n";
$deploymentDoc .= "# Install Composer\n";
$deploymentDoc .= "curl -sS https://getcomposer.org/installer | php\n";
$deploymentDoc .= "sudo mv composer.phar /usr/local/bin/composer\n\n";
$deploymentDoc .= "# Install Node.js\n";
$deploymentDoc .= "curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -\n";
$deploymentDoc .= "sudo apt-get install -y nodejs\n";
$deploymentDoc .= "```\n\n";
$deploymentDoc .= "### 2. Database Setup\n";
$deploymentDoc .= "```bash\n";
$deploymentDoc .= "# Create database and user\n";
$deploymentDoc .= "mysql -u root -p < database_production_setup.sql\n";
$deploymentDoc .= "```\n\n";
$deploymentDoc .= "### 3. Application Setup\n";
$deploymentDoc .= "```bash\n";
$deploymentDoc .= "# Clone repository\n";
$deploymentDoc .= "git clone https://github.com/your-username/apsdreamhome.git /var/www/apsdreamhome\n";
$deploymentDoc .= "cd /var/www/apsdreamhome\n\n";
$deploymentDoc .= "# Copy production environment\n";
$deploymentDoc .= "cp .env.production .env\n\n";
$deploymentDoc .= "# Install dependencies\n";
$deploymentDoc .= "composer install --no-dev --optimize-autoloader\n";
$deploymentDoc .= "npm ci --production\n\n";
$deploymentDoc .= "# Set permissions\n";
$deploymentDoc .= "sudo chown -R www-data:www-data /var/www/apsdreamhome\n";
$deploymentDoc .= "sudo chmod -R 755 /var/www/apsdreamhome\n";
$deploymentDoc .= "```\n\n";
$deploymentDoc .= "### 4. Web Server Configuration\n";
$deploymentDoc .= "- Copy `.htaccess.production` to `.htaccess` for Apache\n";
$deploymentDoc .= "- Copy `nginx.production.conf` to nginx config for Nginx\n";
$deploymentDoc .= "- Restart web server\n\n";
$deploymentDoc .= "### 5. SSL Certificate\n";
$deploymentDoc .= "```bash\n";
$deploymentDoc .= "# Install Certbot\n";
$deploymentDoc .= "sudo apt install certbot python3-certbot-nginx -y\n\n";
$deploymentDoc .= "# Get SSL certificate\n";
$deploymentDoc .= "sudo certbot --nginx -d www.apsdreamhome.com -d apsdreamhome.com\n";
$deploymentDoc .= "```\n\n";
$deploymentDoc .= "### 6. Deployment\n";
$deploymentDoc .= "```bash\n";
$deploymentDoc .= "# Run deployment script\n";
$deploymentDoc .= "./deploy_production.sh\n";
$deploymentDoc .= "```\n\n";
$deploymentDoc .= "## Monitoring\n";
$deploymentDoc .= "- Access monitoring dashboard: /admin/monitoring_dashboard.php\n";
$deploymentDoc .= "- Check logs: /var/log/apsdreamhome/\n";
$deploymentDoc .= "- Health check: /health\n\n";
$deploymentDoc .= "## Security\n";
$deploymentDoc .= "- All security headers configured\n";
$deploymentDoc .= "- HTTPS enforced\n";
$deploymentDoc .= "- Rate limiting enabled\n";
$deploymentDoc .= "- CSRF protection enabled\n";
$deploymentDoc .= "- Input sanitization implemented\n\n";
$deploymentDoc .= "## Backup\n";
$deploymentDoc .= "- Database backups: Daily at 2 AM\n";
$deploymentDoc .= "- File backups: Daily at 3 AM\n";
$deploymentDoc .= "- Retention: 30 days for DB, 7 days for files\n\n";
$deploymentDoc .= "## Support\n";
$deploymentDoc .= "- Email: support@apsdreamhome.com\n";
$deploymentDoc .= "- Phone: +91-7007444842\n";

file_put_contents($projectRoot . '/DEPLOYMENT.md', $deploymentDoc);
echo "✅ Deployment documentation created\n";

// 8. Generate Final Report
echo "\n📊 PRODUCTION DEPLOYMENT REPORT\n";
echo "===============================\n\n";

echo "✅ Deployment Configuration:\n";
foreach ($deploymentConfig as $config) {
    echo "  - $config\n";
}

echo "\n✅ Security Configuration:\n";
foreach ($securityConfig as $config) {
    echo "  - $config\n";
}

echo "\n🚀 Production Features:\n";
echo "  - HTTPS enforcement\n";
echo "  - Security headers\n";
echo "  - Rate limiting\n";
echo "  - CSRF protection\n";
echo "  - Input sanitization\n";
echo "  - Error handling\n";
echo "  - Performance optimization\n";
echo "  - Monitoring and logging\n";
echo "  - Automated backups\n";
echo "  - Health checks\n";
echo "  - Deployment automation\n";

echo "\n📁 Generated Files:\n";
echo "  - .env.production - Production environment\n";
echo "  - .htaccess.production - Apache config\n";
echo "  - nginx.production.conf - Nginx config\n";
echo "  - database_production_setup.sql - Database setup\n";
echo "  - deploy_production.sh - Deployment script\n";
echo "  - config/security_production.json - Security config\n";
echo "  - config/monitoring_production.json - Monitoring config\n";
echo "  - DEPLOYMENT.md - Deployment guide\n";

echo "\n🎯 Next Steps:\n";
echo "  1. Review all configuration files\n";
echo "  2. Update passwords and secrets\n";
echo "  3. Set up production server\n";
echo "  4. Run deployment script\n";
echo "  5. Test all functionality\n";
echo "  6. Set up monitoring alerts\n";
echo "  7. Configure backup schedules\n";

// Save deployment report
$deploymentReport = [
    'timestamp' => date('Y-m-d H:i:s'),
    'deployment_config' => $deploymentConfig,
    'security_config' => $securityConfig,
    'production_features' => [
        'HTTPS enforcement',
        'Security headers',
        'Rate limiting',
        'CSRF protection',
        'Input sanitization',
        'Error handling',
        'Performance optimization',
        'Monitoring and logging',
        'Automated backups',
        'Health checks',
        'Deployment automation'
    ],
    'generated_files' => [
        '.env.production' => 'Production environment configuration',
        '.htaccess.production' => 'Apache web server configuration',
        'nginx.production.conf' => 'Nginx web server configuration',
        'database_production_setup.sql' => 'Database setup script',
        'deploy_production.sh' => 'Automated deployment script',
        'config/security_production.json' => 'Security configuration',
        'config/monitoring_production.json' => 'Monitoring configuration',
        'DEPLOYMENT.md' => 'Complete deployment guide'
    ],
    'next_steps' => [
        'Review all configuration files',
        'Update passwords and secrets',
        'Set up production server',
        'Run deployment script',
        'Test all functionality',
        'Set up monitoring alerts',
        'Configure backup schedules'
    ]
];

file_put_contents($projectRoot . '/production_deployment_report.json', json_encode($deploymentReport, JSON_PRETTY_PRINT));
echo "\n✅ Production deployment report saved\n";

echo "\n🎉 Production Deployment Setup Complete!\n";
echo "🚀 APS Dream Home is ready for production deployment!\n";
?>
