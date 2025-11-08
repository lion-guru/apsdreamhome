#!/bin/bash

# APS Dream Home - Enhanced Production Deployment Script
# ====================================================
# Complete production deployment with monitoring and SSL

set -e  # Exit on any error

echo "🚀 APS Dream Home - Enhanced Production Deployment"
echo "=================================================="

# Configuration
APP_NAME="apsdreamhome"
DOMAIN=${1:-"yourdomain.com"}
DB_NAME="${APP_NAME}_production"
DB_USER="${APP_NAME}_user"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

log_header() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Pre-deployment checks
pre_deployment_checks() {
    log_header "Running Pre-deployment Checks"

    # Check if Docker is installed
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed. Please install Docker first."
        exit 1
    fi

    # Check if Docker Compose is installed
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi

    # Check if required files exist
    required_files=(".env.production" "docker-compose.yml" "Dockerfile")
    for file in "${required_files[@]}"; do
        if [ ! -f "$file" ]; then
            log_error "Required file $file not found. Please run setup first."
            exit 1
        fi
    done

    log_info "Pre-deployment checks passed ✓"
}

# Generate secure keys and passwords
generate_secrets() {
    log_header "Generating Security Secrets"

    # Generate application key
    if ! grep -q "APP_KEY=base64:" .env.production; then
        APP_KEY="base64:$(openssl rand -base64 32)"
        sed -i.bak "s/APP_KEY=GENERATE_NEW_KEY_HERE/APP_KEY=${APP_KEY}/" .env.production
        log_info "Generated new APP_KEY"
    fi

    # Generate database password
    if ! grep -q "DB_PASS=" .env.production; then
        DB_PASS=$(openssl rand -base64 32)
        echo "DB_PASS=${DB_PASS}" >> .env.production
        log_info "Generated database password"
    fi

    # Generate Redis password
    if ! grep -q "REDIS_PASSWORD=" .env.production; then
        REDIS_PASS=$(openssl rand -base64 32)
        echo "REDIS_PASSWORD=${REDIS_PASS}" >> .env.production
        log_info "Generated Redis password"
    fi
}

# Setup SSL certificates
setup_ssl() {
    log_header "Setting up SSL Certificates"

    if [ ! -f "ssl/certificate.crt" ] || [ ! -f "ssl/private.key" ]; then
        log_info "SSL certificates not found. Generating self-signed certificates..."
        bash setup_ssl.sh

        if [ $? -ne 0 ]; then
            log_warn "SSL setup failed. Using HTTP for now. Set up proper SSL certificates later."
        fi
    else
        log_info "SSL certificates already exist ✓"
    fi
}

# Deploy application
deploy_application() {
    log_header "Deploying Application"

    # Stop existing containers
    log_info "Stopping existing containers..."
    docker-compose down || true

    # Create backup of current .env
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

    # Copy production environment
    cp .env.production .env

    # Build and start containers
    log_info "Building containers..."
    docker-compose build --no-cache

    log_info "Starting containers..."
    docker-compose up -d

    # Wait for services to be ready
    log_info "Waiting for services to start..."
    sleep 45

    log_info "Application deployed successfully ✓"
}

# Run database setup
setup_database() {
    log_header "Setting up Database"

    # Wait for MySQL to be ready
    log_info "Waiting for MySQL to be ready..."
    timeout=120
    while ! docker-compose exec -T mysql mysql -u${DB_USER} -p${DB_PASS} -e "SELECT 1" ${DB_NAME} &>/dev/null; do
        sleep 5
        timeout=$((timeout - 5))
        if [ $timeout -le 0 ]; then
            log_error "MySQL failed to start within timeout"
            exit 1
        fi
    done

    # Run database setup script
    if docker-compose exec -T app ls setup_database.php &>/dev/null; then
        log_info "Running database setup script..."
        docker-compose exec -T app php setup_database.php
    else
        log_warn "Database setup script not found, skipping"
    fi

    log_info "Database setup completed ✓"
}

# Health checks
run_health_checks() {
    log_header "Running Health Checks"

    # Check container status
    if docker-compose ps | grep -q "Up"; then
        log_info "All containers are running ✓"
    else
        log_error "Some containers failed to start"
        docker-compose logs
        exit 1
    fi

    # Check application health
    if curl -f -k https://localhost/health &>/dev/null; then
        log_info "Application health check passed ✓"
    else
        log_warn "Application health check failed. Check logs:"
        docker-compose logs app
    fi

    # Check database connectivity
    if docker-compose exec -T mysql mysql -u${DB_USER} -p${DB_PASS} -e "SELECT 1" ${DB_NAME} &>/dev/null; then
        log_info "Database connectivity check passed ✓"
    else
        log_error "Database connectivity check failed"
        exit 1
    fi

    log_info "All health checks passed ✓"
}

# Setup monitoring
setup_monitoring() {
    log_header "Setting up Monitoring"

    # Create monitoring directory structure
    mkdir -p monitoring/{prometheus,grafana/{provisioning/{datasources,dashboards},dashboards}}

    # Copy monitoring configurations if available
    if [ -f "monitoring/prometheus.yml" ]; then
        log_info "Starting Prometheus..."
        docker run -d \
            --name prometheus \
            -p 9090:9090 \
            -v $(pwd)/monitoring/prometheus.yml:/etc/prometheus/prometheus.yml \
            prom/prometheus
    fi

    if [ -f "monitoring/grafana.ini" ]; then
        log_info "Starting Grafana..."
        docker run -d \
            --name grafana \
            -p 3000:3000 \
            -v $(pwd)/monitoring/grafana.ini:/etc/grafana/grafana.ini \
            grafana/grafana
    fi

    log_info "Monitoring setup completed ✓"
}

# Post-deployment tasks
post_deployment_tasks() {
    log_header "Running Post-deployment Tasks"

    # Set proper file permissions
    log_info "Setting file permissions..."
    docker-compose exec -T app chown -R www-data:www-data /var/www
    docker-compose exec -T app chmod -R 755 /var/www/storage
    docker-compose exec -T app chmod -R 755 /var/www/bootstrap/cache

    # Clear caches
    log_info "Clearing application caches..."
    docker-compose exec -T app php artisan config:cache || true
    docker-compose exec -T app php artisan route:cache || true
    docker-compose exec -T app php artisan view:cache || true

    # Run performance optimizations
    log_info "Running performance optimizations..."
    docker-compose exec -T app php artisan optimize || true

    log_info "Post-deployment tasks completed ✓"
}

# Display deployment summary
deployment_summary() {
    log_header "Deployment Summary"

    echo ""
    echo "🎉 Production deployment completed successfully!"
    echo ""
    echo "📋 Access Information:"
    echo "   🌐 Application: https://${DOMAIN}"
    echo "   📊 Health Check: https://${DOMAIN}/health"
    echo "   📝 Admin Panel: https://${DOMAIN}/admin"
    echo ""
    echo "🔧 Services Running:"
    docker-compose ps
    echo ""
    echo "📊 Monitoring (if enabled):"
    echo "   📈 Prometheus: http://localhost:9090"
    echo "   📊 Grafana: http://localhost:3000"
    echo ""
    echo "📖 Next Steps:"
    echo "1. Update DNS to point ${DOMAIN} to this server"
    echo "2. Replace self-signed SSL with production certificate"
    echo "3. Configure email settings in .env.production"
    echo "4. Set up monitoring alerts"
    echo "5. Configure automated backups"
    echo ""
    echo "📚 Documentation:"
    echo "   - See PRODUCTION_DEPLOYMENT_GUIDE.md"
    echo "   - See MAINTENANCE_MONITORING_GUIDE.md"
    echo ""
    echo "🔒 Security Reminders:"
    echo "   - Change all default passwords"
    echo "   - Set up SSL certificate for production"
    echo "   - Configure firewall rules"
    echo "   - Enable monitoring alerts"
    echo ""
    echo "🚀 Happy deploying! 🎉"
}

# Main deployment function
main() {
    log_header "Starting Enhanced Production Deployment"

    echo "Domain: ${DOMAIN}"
    echo "Database: ${DB_NAME}"
    echo ""

    pre_deployment_checks
    generate_secrets
    setup_ssl
    deploy_application
    setup_database
    run_health_checks
    setup_monitoring
    post_deployment_tasks
    deployment_summary
}

# Run deployment
main "$@"
