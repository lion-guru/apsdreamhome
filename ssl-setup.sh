#!/bin/bash

# APS Dream Home - SSL Certificate Setup Script
# This script automates SSL certificate setup using Let's Encrypt

set -e  # Exit on any error

# Configuration
DOMAIN="yourdomain.com"
EMAIL="admin@yourdomain.com"
WEBROOT="/var/www/apsdreamhome/public"
NGINX_SITES_AVAILABLE="/etc/nginx/sites-available"
NGINX_SITES_ENABLED="/etc/nginx/sites-enabled"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARN:${NC} $1"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"
}

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root (sudo)"
        exit 1
    fi
}

# Install Certbot if not installed
install_certbot() {
    log_step "Installing Certbot..."

    if ! command -v certbot &> /dev/null; then
        # Add Certbot repository
        add-apt-repository ppa:certbot/certbot -y
        apt update
        apt install -y certbot python3-certbot-nginx

        log_info "Certbot installed successfully"
    else
        log_info "Certbot is already installed"
    fi
}

# Check domain DNS resolution
check_dns() {
    log_step "Checking DNS resolution for $DOMAIN..."

    # Check if domain resolves to this server
    local server_ip=$(curl -s https://api.ipify.org)
    local domain_ip=$(dig +short "$DOMAIN" | tail -n1)

    if [[ -z "$domain_ip" ]]; then
        log_error "Domain $DOMAIN does not resolve to any IP address"
        log_error "Please ensure DNS is properly configured"
        exit 1
    fi

    if [[ "$domain_ip" != "$server_ip" ]]; then
        log_warn "Domain $DOMAIN resolves to $domain_ip but server IP is $server_ip"
        log_warn "SSL certificate may not work until DNS is updated"
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    else
        log_info "DNS resolution verified ✓"
    fi
}

# Create temporary HTTP configuration for certificate validation
create_temp_http_config() {
    log_step "Creating temporary HTTP configuration..."

    # Backup existing configuration
    if [[ -f "$NGINX_SITES_AVAILABLE/apsdreamhome" ]]; then
        cp "$NGINX_SITES_AVAILABLE/apsdreamhome" "$NGINX_SITES_AVAILABLE/apsdreamhome.backup"
    fi

    # Create temporary HTTP-only configuration
    cat > "$NGINX_SITES_AVAILABLE/apsdreamhome" << EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    root $WEBROOT;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    # Allow access to .well-known for Let's Encrypt
    location ~ /\.well-known {
        allow all;
        try_files \$uri =404;
    }
}
EOF

    # Enable site if not already enabled
    if [[ ! -L "$NGINX_SITES_ENABLED/apsdreamhome" ]]; then
        ln -sf "$NGINX_SITES_AVAILABLE/apsdreamhome" "$NGINX_SITES_ENABLED/"
    fi

    # Test and reload nginx
    if nginx -t; then
        systemctl reload nginx
        log_info "Nginx configuration updated ✓"
    else
        log_error "Nginx configuration test failed"
        exit 1
    fi
}

# Obtain SSL certificate
obtain_certificate() {
    log_step "Obtaining SSL certificate for $DOMAIN..."

    # Obtain certificate
    if certbot certonly \
        --webroot \
        --webroot-path="$WEBROOT" \
        --email="$EMAIL" \
        --agree-tos \
        --no-eff-email \
        -d "$DOMAIN" \
        -d "www.$DOMAIN"; then

        log_info "SSL certificate obtained successfully ✓"

        # Verify certificate files exist
        if [[ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]] && \
           [[ -f "/etc/letsencrypt/live/$DOMAIN/privkey.pem" ]]; then
            log_info "Certificate files verified ✓"
        else
            log_error "Certificate files not found"
            exit 1
        fi
    else
        log_error "Failed to obtain SSL certificate"
        exit 1
    fi
}

# Update nginx configuration for HTTPS
update_nginx_config() {
    log_step "Updating Nginx configuration for HTTPS..."

    # Create full HTTPS configuration
    cat > "$NGINX_SITES_AVAILABLE/apsdreamhome" << EOF
# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    return 301 https://\$server_name\$request_uri;
}

# Main HTTPS Server Block
server {
    listen 443 ssl http2;
    server_name $DOMAIN www.$DOMAIN;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/$DOMAIN/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/$DOMAIN/privkey.pem;

    # SSL Security Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Root directory
    root $WEBROOT;
    index index.php index.html;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css text/javascript application/javascript application/json;

    # PHP Configuration
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTPS on;
    }

    # Static assets with caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # Laravel routes
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Deny access to sensitive files
    location ~ /(storage/logs|bootstrap/cache|\.env|\.git) {
        deny all;
        return 404;
    }

    # Logs
    access_log /var/log/nginx/apsdreamhome_access.log;
    error_log /var/log/nginx/apsdreamhome_error.log;
}
EOF

    # Test and reload nginx
    if nginx -t; then
        systemctl reload nginx
        log_info "Nginx HTTPS configuration updated ✓"
    else
        log_error "Nginx configuration test failed"
        exit 1
    fi
}

# Set up automatic certificate renewal
setup_auto_renewal() {
    log_step "Setting up automatic certificate renewal..."

    # Enable and start certbot timer
    systemctl enable certbot.timer
    systemctl start certbot.timer

    # Test renewal process
    certbot renew --dry-run

    log_info "Automatic renewal configured ✓"
}

# Verify SSL configuration
verify_ssl() {
    log_step "Verifying SSL configuration..."

    # Wait a moment for nginx to fully reload
    sleep 2

    # Test HTTPS connection
    if curl -s -I "https://$DOMAIN" | grep -q "HTTP/2 200"; then
        log_info "HTTPS connection verified ✓"
    else
        log_warn "HTTPS connection test failed - this may be normal if application isn't fully set up yet"
    fi

    # Check SSL certificate details
    echo | openssl s_client -servername "$DOMAIN" -connect "$DOMAIN:443" 2>/dev/null | openssl x509 -noout -dates 2>/dev/null || true

    # Check SSL rating (requires ssllabs-scan tool)
    if command -v ssllabs-scan &> /dev/null; then
        log_info "SSL Labs rating check (this may take a few minutes)..."
        ssllabs-scan --grade "$DOMAIN" || true
    fi
}

# Create renewal hook script
create_renewal_hook() {
    log_step "Creating certificate renewal hook..."

    mkdir -p /etc/letsencrypt/renewal-hooks/post

    cat > /etc/letsencrypt/renewal-hooks/post/reload-nginx.sh << 'EOF'
#!/bin/bash
# Reload Nginx after certificate renewal
systemctl reload nginx
echo "Nginx reloaded after SSL certificate renewal" >> /var/log/letsencrypt-renewal.log
EOF

    chmod +x /etc/letsencrypt/renewal-hooks/post/reload-nginx.sh

    log_info "Renewal hook created ✓"
}

# Main SSL setup process
main() {
    echo "=========================================="
    echo "🔒 APS Dream Home SSL Certificate Setup"
    echo "=========================================="
    echo ""

    check_root
    install_certbot
    check_dns
    create_temp_http_config
    obtain_certificate
    update_nginx_config
    setup_auto_renewal
    create_renewal_hook
    verify_ssl

    echo ""
    echo "=========================================="
    echo "🎉 SSL SETUP COMPLETED SUCCESSFULLY!"
    echo "=========================================="
    echo ""
    echo "🌐 Your site is now secured with HTTPS:"
    echo "   https://$DOMAIN"
    echo ""
    echo "🔧 Certificate Details:"
    echo "   Certificate Path: /etc/letsencrypt/live/$DOMAIN/"
    echo "   Auto-renewal: Enabled (certbot.timer)"
    echo "   Nginx Config: /etc/nginx/sites-available/apsdreamhome"
    echo ""
    echo "📋 Next Steps:"
    echo "   1. Update your .env file: APP_URL=https://$DOMAIN"
    echo "   2. Test your application thoroughly"
    echo "   3. Update any hardcoded HTTP URLs to HTTPS"
    echo "   4. Set up monitoring for certificate expiration"
    echo ""
}

# Handle command line arguments
case "${1:-}" in
    "check")
        check_dns
        echo "DNS check completed"
        ;;
    "renew")
        certbot renew
        systemctl reload nginx
        echo "SSL certificate renewed"
        ;;
    "verify")
        verify_ssl
        ;;
    *)
        main
        ;;
esac
