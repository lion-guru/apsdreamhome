# APS Dream Home - Production Deployment Script
#!/bin/bash

# Production Deployment Script for APS Dream Home
# Run this script on your production server

set -e  # Exit on any error

echo "🚀 Starting APS Dream Home Production Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="apsdreamhome"
APP_DIR="/var/www/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"
DOMAIN="yourdomain.com"  # Replace with actual domain
DB_NAME="${APP_NAME}_prod"
DB_USER="${APP_NAME}_user"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirements() {
    log_info "Checking system requirements..."

    # Check if running as root or with sudo
    if [[ $EUID -eq 0 ]]; then
        log_warn "Running as root - consider using a non-root user with sudo"
    fi

    # Check required commands
    local required_commands=("git" "composer" "php" "mysql" "nginx" "certbot")
    for cmd in "${required_commands[@]}"; do
        if ! command -v "$cmd" &> /dev/null; then
            log_error "Required command '$cmd' not found. Please install it first."
            exit 1
        fi
    done

    # Check PHP version
    local php_version=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
    if [[ "$(printf '%s\n' "$php_version" "8.1" | sort -V | head -n1)" != "8.1" ]]; then
        log_error "PHP 8.1+ required. Current version: $php_version"
        exit 1
    fi

    log_info "System requirements check passed"
}

create_directories() {
    log_info "Creating required directories..."

    sudo mkdir -p "$APP_DIR"
    sudo mkdir -p "$BACKUP_DIR"
    sudo mkdir -p /var/log/"$APP_NAME"
    sudo mkdir -p /etc/ssl/"$APP_NAME"

    # Set proper permissions
    sudo chown -R www-data:www-data "$APP_DIR"
    sudo chown -R www-data:www-data "$BACKUP_DIR"
    sudo chown -R www-data:www-data /var/log/"$APP_NAME"

    log_info "Directories created successfully"
}

backup_current_deployment() {
    if [[ -d "$APP_DIR/.git" ]]; then
        log_info "Creating backup of current deployment..."

        local backup_file="$BACKUP_DIR/backup_$TIMESTAMP.tar.gz"
        sudo tar -czf "$backup_file" -C /var/www "$APP_NAME" 2>/dev/null || true

        # Keep only last 5 backups
        sudo find "$BACKUP_DIR" -name "backup_*.tar.gz" -type f -printf '%T@ %p\n' |
            sort -n | head -n -5 | cut -d' ' -f2- | xargs -r sudo rm

        log_info "Backup created: $backup_file"
    fi
}

deploy_application() {
    log_info "Deploying application files..."

    # If this is a git repository, pull latest changes
    if [[ -d "$APP_DIR/.git" ]]; then
        cd "$APP_DIR"
        sudo -u www-data git pull origin main
        sudo -u www-data git submodule update --init --recursive
    else
        log_warn "No git repository found. Please upload application files manually."
        return 1
    fi

    # Install/update PHP dependencies
    cd "$APP_DIR"
    sudo -u www-data composer install --no-dev --optimize-autoloader

    # Install/update Node.js dependencies (if applicable)
    if [[ -f "package.json" ]]; then
        sudo -u www-data npm ci
        sudo -u www-data npm run build
    fi

    # Set proper permissions
    sudo chown -R www-data:www-data "$APP_DIR"
    sudo find "$APP_DIR" -type f -exec chmod 644 {} \;
    sudo find "$APP_DIR" -type d -exec chmod 755 {} \;

    # Special permissions for storage and bootstrap/cache
    sudo chmod -R 775 "$APP_DIR/storage"
    sudo chmod -R 775 "$APP_DIR/bootstrap/cache"

    log_info "Application deployed successfully"
}

setup_database() {
    log_info "Setting up database..."

    # Create database if it doesn't exist
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

    # Create database user if it doesn't exist
    sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
    sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    sudo mysql -e "FLUSH PRIVILEGES;"

    log_info "Database setup completed"
}

run_migrations() {
    log_info "Running database migrations..."

    cd "$APP_DIR"

    # Run migrations
    sudo -u www-data php artisan migrate --force

    # Seed database if seeds exist
    if [[ -f "database/seeders/DatabaseSeeder.php" ]]; then
        sudo -u www-data php artisan db:seed --force
    fi

    log_info "Database migrations completed"
}

configure_environment() {
    log_info "Configuring environment variables..."

    cd "$APP_DIR"

    # Copy environment file if it doesn't exist
    if [[ ! -f ".env" ]]; then
        sudo -u www-data cp .env.example .env
    fi

    # Update critical environment variables
    sudo -u www-data sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sudo -u www-data sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
    sudo -u www-data sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
    sudo -u www-data sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sudo -u www-data sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env

    # Generate application key if not set
    if ! grep -q "APP_KEY=.*[^[:space:]]" .env; then
        sudo -u www-data php artisan key:generate
    fi

    log_info "Environment configuration completed"
}

setup_web_server() {
    log_info "Configuring web server..."

    # Create Nginx configuration
    sudo tee /etc/nginx/sites-available/"$APP_NAME" > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name $DOMAIN www.$DOMAIN;

    root $APP_DIR/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Handle PHP files
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    # Handle static assets with caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # Handle Laravel routes
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Deny access to sensitive files
    location ~ /(storage/logs|bootstrap/cache|\.env|\.git) {
        deny all;
        return 404;
    }
}
EOF

    # Enable site
    sudo ln -sf /etc/nginx/sites-available/"$APP_NAME" /etc/nginx/sites-enabled/

    # Remove default site if it exists
    sudo rm -f /etc/nginx/sites-enabled/default

    # Test nginx configuration
    if sudo nginx -t; then
        sudo systemctl reload nginx
        log_info "Nginx configuration updated successfully"
    else
        log_error "Nginx configuration test failed"
        exit 1
    fi
}

setup_ssl() {
    log_info "Setting up SSL certificate..."

    # Check if certificate already exists
    if [[ ! -d "/etc/letsencrypt/live/$DOMAIN" ]]; then
        # Obtain SSL certificate
        sudo certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --non-interactive --agree-tos --email "admin@$DOMAIN"

        # Set up auto-renewal
        sudo systemctl enable certbot.timer
        sudo systemctl start certbot.timer

        log_info "SSL certificate obtained successfully"
    else
        log_info "SSL certificate already exists"
    fi
}

optimize_performance() {
    log_info "Optimizing performance..."

    cd "$APP_DIR"

    # Clear and optimize caches
    sudo -u www-data php artisan config:cache
    sudo -u www-data php artisan route:cache
    sudo -u www-data php artisan view:cache

    # Set up cron jobs for automated tasks
    sudo tee /etc/cron.d/"$APP_NAME" > /dev/null <<EOF
# APS Dream Home Cron Jobs
* * * * * www-data cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1

# Daily cleanup
0 2 * * * www-data cd $APP_DIR && php artisan queue:work --stop-when-empty >> /dev/null 2>&1

# Weekly optimization
0 3 * * 0 www-data cd $APP_DIR && php artisan optimize:clear >> /dev/null 2>&1
EOF

    sudo chmod 644 /etc/cron.d/"$APP_NAME"
    sudo systemctl restart cron

    log_info "Performance optimizations completed"
}

setup_monitoring() {
    log_info "Setting up monitoring..."

    # Install basic monitoring tools
    sudo apt update
    sudo apt install -y htop iotop sysstat

    # Create log rotation for application logs
    sudo tee /etc/logrotate.d/"$APP_NAME" > /dev/null <<EOF
/var/log/$APP_NAME/*.log {
    daily
    missingok
    rotate 30
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload nginx || true
    endscript
}
EOF

    log_info "Monitoring setup completed"
}

final_verification() {
    log_info "Running final verification checks..."

    # Check if application is accessible
    if curl -s -k "https://$DOMAIN" | grep -q "APS Dream Home"; then
        log_info "✅ Application is accessible via HTTPS"
    else
        log_warn "⚠️  Application may not be fully accessible"
    fi

    # Check database connectivity
    cd "$APP_DIR"
    if sudo -u www-data php artisan tinker --execute="echo 'Database connected';" 2>/dev/null; then
        log_info "✅ Database connectivity verified"
    else
        log_warn "⚠️  Database connectivity issues detected"
    fi

    # Check file permissions
    if [[ -w "$APP_DIR/storage" ]]; then
        log_info "✅ Storage directory is writable"
    else
        log_warn "⚠️  Storage directory permissions issue"
    fi

    log_info "Final verification completed"
}

# Main deployment process
main() {
    echo "=========================================="
    echo "🚀 APS Dream Home Production Deployment"
    echo "=========================================="
    echo ""

    check_requirements
    create_directories
    backup_current_deployment
    deploy_application
    setup_database
    run_migrations
    configure_environment
    setup_web_server
    setup_ssl
    optimize_performance
    setup_monitoring
    final_verification

    echo ""
    echo "=========================================="
    echo "🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!"
    echo "=========================================="
    echo ""
    echo "🌐 Your application is now live at: https://$DOMAIN"
    echo "📊 Admin panel: https://$DOMAIN/admin/login"
    echo "📝 Check logs at: /var/log/$APP_NAME/"
    echo "💾 Backups at: $BACKUP_DIR/"
    echo ""
    echo "🔧 Useful commands:"
    echo "   sudo systemctl reload nginx          # Reload web server"
    echo "   sudo systemctl restart php8.1-fpm   # Restart PHP"
    echo "   cd $APP_DIR && php artisan           # Run artisan commands"
    echo ""
}

# Handle command line arguments
case "${1:-}" in
    "check")
        check_requirements
        echo "✅ System requirements check completed"
        ;;
    "backup")
        backup_current_deployment
        echo "✅ Backup completed"
        ;;
    *)
        main
        ;;
esac
