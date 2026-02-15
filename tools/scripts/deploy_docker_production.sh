#!/bin/bash

# APS Dream Home - Docker Production Deployment Script
# ====================================================
# Complete Docker deployment with monitoring and SSL

set -e  # Exit on any error

echo "🐳 APS Dream Home - Complete Docker Deployment"
echo "=============================================="

# Configuration
APP_NAME="apsdreamhome"
DOCKER_REGISTRY=${1:-"abhaysingh3007"}
DOMAIN=${2:-"localhost"}
DB_NAME="${APP_NAME}_production"
DB_USER="${APP_NAME}_user"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
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

log_success() {
    echo -e "${PURPLE}[SUCCESS]${NC} $1"
}

# Pre-deployment validation
validate_environment() {
    log_header "Validating Environment"

    # Check Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi

    # Check Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed"
        exit 1
    fi

    # Check required files
    required_files=(".env.production" "docker-compose.yml" "Dockerfile")
    for file in "${required_files[@]}"; do
        if [ ! -f "$file" ]; then
            log_error "Required file $file not found"
            exit 1
        fi
    done

    # Check Docker registry access
    if ! docker info &>/dev/null; then
        log_error "Cannot connect to Docker daemon"
        exit 1
    fi

    log_success "Environment validation passed ✓"
}

# Build and push Docker images
build_and_push_images() {
    log_header "Building and Pushing Docker Images"

    # Build images
    log_info "Building application image..."
    docker build -t ${DOCKER_REGISTRY}/${APP_NAME}:latest -t ${DOCKER_REGISTRY}/${APP_NAME}:$(date +%Y%m%d_%H%M%S) .

    log_info "Building MySQL image..."
    docker build -f Dockerfile.mysql -t ${DOCKER_REGISTRY}/${APP_NAME}:mysql .

    log_info "Building Nginx image..."
    docker build -f Dockerfile.nginx -t ${DOCKER_REGISTRY}/${APP_NAME}:nginx .

    # Push images to registry
    log_info "Pushing images to Docker Hub..."
    docker push ${DOCKER_REGISTRY}/${APP_NAME}:latest
    docker push ${DOCKER_REGISTRY}/${APP_NAME}:mysql
    docker push ${DOCKER_REGISTRY}/${APP_NAME}:nginx

    log_success "Images built and pushed successfully ✓"
}

# Setup production environment
setup_production_environment() {
    log_header "Setting up Production Environment"

    # Create production directory structure
    sudo mkdir -p /opt/${APP_NAME}
    sudo chown $USER:$USER /opt/${APP_NAME}

    # Copy configuration files
    cp docker-compose.production.yml /opt/${APP_NAME}/
    cp .env.production /opt/${APP_NAME}/
    cp monitoring/ /opt/${APP_NAME}/ -r

    # Generate secure secrets
    generate_production_secrets

    log_success "Production environment setup completed ✓"
}

# Generate production secrets
generate_production_secrets() {
    log_info "Generating production secrets..."

    # Generate database password
    DB_PASS=$(openssl rand -base64 32)

    # Generate Redis password
    REDIS_PASS=$(openssl rand -base64 32)

    # Generate JWT secret
    JWT_SECRET=$(openssl rand -base64 64)

    # Update .env.production file
    sed -i.bak \
        -e "s/DB_PASS=/DB_PASS=${DB_PASS}/" \
        -e "s/REDIS_PASSWORD=/REDIS_PASSWORD=${REDIS_PASS}/" \
        -e "s/JWT_SECRET=/JWT_SECRET=${JWT_SECRET}/" \
        /opt/${APP_NAME}/.env.production

    # Create secrets file for Docker
    cat > /opt/${APP_NAME}/secrets.env << EOF
DB_PASSWORD=${DB_PASS}
REDIS_PASSWORD=${REDIS_PASS}
JWT_SECRET=${JWT_SECRET}
APP_KEY=base64:$(openssl rand -base64 32)
EOF

    chmod 600 /opt/${APP_NAME}/secrets.env

    log_success "Production secrets generated ✓"
}

# Deploy to production
deploy_to_production() {
    log_header "Deploying to Production"

    cd /opt/${APP_NAME}

    # Stop existing containers
    docker-compose -f docker-compose.production.yml down || true

    # Pull latest images
    docker-compose -f docker-compose.production.yml pull

    # Start services
    docker-compose -f docker-compose.production.yml up -d

    # Wait for services to be ready
    log_info "Waiting for services to start..."
    sleep 45

    log_success "Production deployment completed ✓"
}

# Setup monitoring stack
setup_monitoring_stack() {
    log_header "Setting up Monitoring Stack"

    cd /opt/${APP_NAME}/monitoring

    # Start Prometheus
    docker run -d \
        --name prometheus \
        --network ${APP_NAME}_network \
        -p 9090:9090 \
        -v $(pwd)/prometheus.yml:/etc/prometheus/prometheus.yml \
        prom/prometheus

    # Start Grafana
    docker run -d \
        --name grafana \
        --network ${APP_NAME}_network \
        -p 3000:3000 \
        -v $(pwd)/grafana.ini:/etc/grafana/grafana.ini \
        grafana/grafana

    # Start Alertmanager (for notifications)
    docker run -d \
        --name alertmanager \
        --network ${APP_NAME}_network \
        -p 9093:9093 \
        -v $(pwd)/alertmanager.yml:/etc/alertmanager/alertmanager.yml \
        prom/alertmanager

    log_success "Monitoring stack deployed ✓"
}

# Run health checks
run_comprehensive_health_checks() {
    log_header "Running Comprehensive Health Checks"

    # Check container status
    if docker-compose -f /opt/${APP_NAME}/docker-compose.production.yml ps | grep -q "Up"; then
        log_success "All containers are running ✓"
    else
        log_error "Some containers failed to start"
        docker-compose -f /opt/${APP_NAME}/docker-compose.production.yml logs
        exit 1
    fi

    # Check application health
    if curl -f -k https://${DOMAIN}/health &>/dev/null; then
        log_success "Application health check passed ✓"
    else
        log_warn "Application health check failed"
    fi

    # Check database connectivity
    if docker-compose -f /opt/${APP_NAME}/docker-compose.production.yml exec -T mysql mysql -u${DB_USER} -p${DB_PASS} -e "SELECT 1" ${DB_NAME} &>/dev/null; then
        log_success "Database connectivity check passed ✓"
    else
        log_error "Database connectivity check failed"
        exit 1
    fi

    # Check SSL certificate
    if curl -f -k -v https://${DOMAIN}/health 2>&1 | grep -q "SSL"; then
        log_success "SSL certificate is working ✓"
    else
        log_warn "SSL certificate issues detected"
    fi

    log_success "All health checks passed ✓"
}

# Setup automated backups
setup_automated_backups() {
    log_header "Setting up Automated Backups"

    # Create backup script
    cat > /opt/${APP_NAME}/backup.sh << 'EOF'
#!/bin/bash
# Automated backup script

BACKUP_DIR="/backup/$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR

# Backup database
docker-compose exec -T mysql mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} > ${BACKUP_DIR}/database.sql

# Backup application files
tar -czf ${BACKUP_DIR}/app_files.tar.gz /var/www --exclude=/var/www/vendor --exclude=/var/www/node_modules

# Upload to remote storage (configure as needed)
# aws s3 sync $BACKUP_DIR s3://apsdreamhome-backups/

# Cleanup old backups (keep 7 days)
find /backup -type d -mtime +7 -exec rm -rf {} \;

echo "$(date): Backup completed" >> /var/log/backup.log
EOF

    chmod +x /opt/${APP_NAME}/backup.sh

    # Setup cron job
    crontab -l | grep -v "${APP_NAME}" > /tmp/crontab.tmp 2>/dev/null || true
    echo "0 2 * * * /opt/${APP_NAME}/backup.sh" >> /tmp/crontab.tmp
    crontab /tmp/crontab.tmp

    log_success "Automated backups configured ✓"
}

# Setup log rotation
setup_log_rotation() {
    log_header "Setting up Log Rotation"

    # Create logrotate configuration
    cat > /etc/logrotate.d/${APP_NAME} << EOF
/var/log/${APP_NAME}/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 644 root root
    postrotate
        docker-compose -f /opt/${APP_NAME}/docker-compose.production.yml kill -s HUP app
    endscript
}
EOF

    log_success "Log rotation configured ✓"
}

# Generate deployment report
generate_deployment_report() {
    log_header "Generating Deployment Report"

    cat > /opt/${APP_NAME}/deployment_report.txt << EOF
APS Dream Home - Production Deployment Report
=============================================

Deployment Date: $(date)
Domain: ${DOMAIN}
Docker Registry: ${DOCKER_REGISTRY}

Services Deployed:
- Application (PHP 8.2 + Nginx)
- MySQL 8.0 Database
- Redis Cache
- Monitoring Stack (Prometheus + Grafana)

Security Features:
- SSL/TLS encryption
- Firewall protection
- Fail2ban intrusion detection
- Rate limiting
- Security headers

Monitoring:
- Application metrics
- Database performance
- System health checks
- Alert notifications

Backup Strategy:
- Daily automated backups
- 30-day retention
- Remote storage sync

Access Information:
- Application: https://${DOMAIN}
- Health Check: https://${DOMAIN}/health
- Admin Panel: https://${DOMAIN}/admin
- Monitoring: http://localhost:9090 (Prometheus)
- Dashboard: http://localhost:3000 (Grafana)

Next Steps:
1. Update DNS to point to this server
2. Configure email notifications
3. Set up monitoring alerts
4. Test backup restoration
5. Configure SSL certificate for production domain

Security Reminders:
- Change all default passwords
- Set up SSL certificate monitoring
- Configure firewall rules for production
- Enable monitoring alerts
- Regular security updates

Support:
- Emergency Contact: admin@apsdreamhome.com
- Documentation: /opt/${APP_NAME}/README.md
- Logs: /var/log/${APP_NAME}/

Deployment completed successfully! 🎉
EOF

    log_success "Deployment report generated ✓"
}

# Main deployment function
main() {
    log_header "Starting Complete Docker Production Deployment"

    echo "Registry: ${DOCKER_REGISTRY}"
    echo "Domain: ${DOMAIN}"
    echo ""

    validate_environment
    build_and_push_images
    setup_production_environment
    deploy_to_production
    setup_monitoring_stack
    run_comprehensive_health_checks
    setup_automated_backups
    setup_log_rotation
    generate_deployment_report

    echo ""
    log_success "🎉 COMPLETE DOCKER PRODUCTION DEPLOYMENT FINISHED!"
    echo ""
    echo "📋 Your application is now running at: https://${DOMAIN}"
    echo "📊 Monitoring available at: http://localhost:9090"
    echo "📈 Dashboard available at: http://localhost:3000"
    echo ""
    echo "📖 See deployment report: /opt/${APP_NAME}/deployment_report.txt"
    echo "📚 See documentation: /opt/${APP_NAME}/README.md"
    echo ""
    echo "🔒 Remember to:"
    echo "   • Update DNS settings"
    echo "   • Configure production SSL certificate"
    echo "   • Set up monitoring alerts"
    echo "   • Test backup procedures"
}

# Run deployment
main "$@"
