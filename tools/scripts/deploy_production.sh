#!/bin/bash
# APS Dream Home - Production Deployment Script
# Complete deployment automation for production server

echo "🚀 APS DREAM HOME - PRODUCTION DEPLOYMENT"
echo "=========================================="

# Configuration
APP_NAME="apsdreamhome"
DOMAIN="apsdreamhomes.com"
DB_NAME="apsdreamhome_prod"
DB_USER="aps_prod_user"
DB_PASS="your_secure_password"
ADMIN_EMAIL="admin@apsdreamhomes.com"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Pre-deployment checks
pre_deployment_checks() {
    log_info "Running pre-deployment checks..."

    # Check if required tools are installed
    command -v git >/dev/null 2>&1 || { log_error "Git is required but not installed."; exit 1; }
    command -v composer >/dev/null 2>&1 || { log_error "Composer is required but not installed."; exit 1; }
    command -v mysql >/dev/null 2>&1 || { log_error "MySQL client is required but not installed."; exit 1; }

    # Check if deployment directory exists
    if [ ! -d "/var/www/$APP_NAME" ]; then
        log_warning "Deployment directory doesn't exist. Creating..."
        sudo mkdir -p "/var/www/$APP_NAME"
        sudo chown -R www-data:www-data "/var/www/$APP_NAME"
    fi

    log_success "Pre-deployment checks completed"
}

# Backup current deployment
backup_current() {
    log_info "Creating backup of current deployment..."

    BACKUP_DIR="/var/backups/$APP_NAME/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"

    if [ -d "/var/www/$APP_NAME" ]; then
        cp -r "/var/www/$APP_NAME" "$BACKUP_DIR/"
        log_success "Backup created at $BACKUP_DIR"
    else
        log_warning "No existing deployment to backup"
    fi
}

# Deploy application
deploy_application() {
    log_info "Deploying application..."

    # Clone or pull latest code
    if [ ! -d "/var/www/$APP_NAME/.git" ]; then
        log_info "Cloning repository..."
        git clone https://github.com/your-repo/apsdreamhome.git "/var/www/$APP_NAME"
    else
        log_info "Pulling latest changes..."
        cd "/var/www/$APP_NAME"
        git pull origin main
    fi

    # Install PHP dependencies
    log_info "Installing PHP dependencies..."
    cd "/var/www/$APP_NAME"
    composer install --no-dev --optimize-autoloader

    # Set proper permissions
    log_info "Setting proper permissions..."
    sudo chown -R www-data:www-data "/var/www/$APP_NAME"
    find "/var/www/$APP_NAME" -type f -name "*.php" -exec chmod 644 {} \;
    find "/var/www/$APP_NAME" -type d -exec chmod 755 {} \;

    log_success "Application deployed successfully"
}

# Setup database
setup_database() {
    log_info "Setting up production database..."

    # Create database if not exists
    mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

    # Create database user if not exists
    mysql -u root -p -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    mysql -u root -p -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    mysql -u root -p -e "FLUSH PRIVILEGES;"

    # Import database schema
    log_info "Importing database schema..."
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < "/var/www/$APP_NAME/database_schema.sql"

    # Run migrations if any
    if [ -f "/var/www/$APP_NAME/migrate.php" ]; then
        log_info "Running database migrations..."
        php "/var/www/$APP_NAME/migrate.php"
    fi

    log_success "Database setup completed"
}

# Configure web server
configure_webserver() {
    log_info "Configuring web server..."

    # Create Nginx configuration
    cat > "/etc/nginx/sites-available/$APP_NAME" << EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    root /var/www/$APP_NAME;
    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Handle static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # Handle PHP files
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.ht {
        deny all;
    }

    # Main location block
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Error and access logs
    error_log /var/log/nginx/$APP_NAME.error.log;
    access_log /var/log/nginx/$APP_NAME.access.log;
}
EOF

    # Enable site
    ln -sf "/etc/nginx/sites-available/$APP_NAME" "/etc/nginx/sites-enabled/"
    rm -f "/etc/nginx/sites-enabled/default"

    # Test configuration
    nginx -t

    if [ $? -eq 0 ]; then
        systemctl reload nginx
        log_success "Web server configured successfully"
    else
        log_error "Nginx configuration test failed"
        exit 1
    fi
}

# Setup SSL certificate
setup_ssl() {
    log_info "Setting up SSL certificate..."

    # Install certbot if not exists
    if ! command -v certbot >/dev/null 2>&1; then
        log_info "Installing Certbot..."
        apt-get update
        apt-get install -y certbot python3-certbot-nginx
    fi

    # Obtain SSL certificate
    certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos -m $ADMIN_EMAIL

    if [ $? -eq 0 ]; then
        log_success "SSL certificate installed successfully"
    else
        log_warning "SSL certificate installation failed. You can run it manually later."
    fi
}

# Configure firewall
configure_firewall() {
    log_info "Configuring firewall..."

    # Enable UFW if not enabled
    ufw --force enable

    # Allow necessary ports
    ufw allow 'OpenSSH'
    ufw allow 'Nginx Full'

    log_success "Firewall configured"
}

# Setup monitoring
setup_monitoring() {
    log_info "Setting up monitoring..."

    # Create monitoring script
    cat > "/var/www/$APP_NAME/monitor.php" << 'EOF'
<?php
/**
 * APS Dream Home - System Monitor
 */

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => [
        'uptime' => shell_exec('uptime -p'),
        'load' => sys_getloadavg(),
        'memory' => [
            'total' => file_get_contents('/proc/meminfo'),
            'free' => shell_exec('free -h')
        ]
    ],
    'database' => [
        'connected' => false,
        'tables' => 0
    ],
    'application' => [
        'version' => '2.0.0',
        'status' => 'running'
    ]
];

// Test database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome_prod', 'aps_prod_user', 'your_password');
    $status['database']['connected'] = true;
    $status['database']['tables'] = $pdo->query('SHOW TABLES')->rowCount();
} catch (Exception $e) {
    $status['database']['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT);
?>
EOF

    log_success "Monitoring setup completed"
}

# Setup automated backups
setup_backups() {
    log_info "Setting up automated backups..."

    # Create backup script
    cat > "/var/www/$APP_NAME/backup.sh" << EOF
#!/bin/bash
# Daily backup script for APS Dream Home

BACKUP_DIR="/var/backups/$APP_NAME/daily"
mkdir -p "\$BACKUP_DIR"

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "\$BACKUP_DIR/database_\$(date +%Y%m%d).sql"

# Files backup
tar -czf "\$BACKUP_DIR/files_\$(date +%Y%m%d).tar.gz" -C /var/www $APP_NAME

# Keep only last 7 days
find "\$BACKUP_DIR" -type f -name "*.sql" -mtime +7 -delete
find "\$BACKUP_DIR" -type f -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed on \$(date)"
EOF

    chmod +x "/var/www/$APP_NAME/backup.sh"

    # Add to crontab for daily backup at 2 AM
    (crontab -l ; echo "0 2 * * * /var/www/$APP_NAME/backup.sh") | crontab -

    log_success "Automated backups configured"
}

# Main deployment process
main() {
    log_info "Starting APS Dream Home production deployment..."

    pre_deployment_checks
    backup_current
    deploy_application
    setup_database
    configure_webserver
    setup_ssl
    configure_firewall
    setup_monitoring
    setup_backups

    log_success "🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!"
    echo ""
    echo "🌐 Application URL: https://$DOMAIN"
    echo "📊 Monitoring URL: https://$DOMAIN/monitor.php"
    echo "📧 Admin Email: $ADMIN_EMAIL"
    echo ""
    echo "🔧 Next Steps:"
    echo "1. Update DNS to point to this server"
    echo "2. Test all application features"
    echo "3. Setup email notifications"
    echo "4. Configure domain email accounts"
    echo "5. Run initial marketing campaigns"
}

# Run deployment
main
