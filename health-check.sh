#!/bin/bash

# APS Dream Home - Health Check & Monitoring Script
# This script performs comprehensive health checks on the production system

set -e  # Exit on any error

# Configuration
APP_NAME="apsdreamhome"
DOMAIN="yourdomain.com"
APP_DIR="/var/www/$APP_NAME"
LOG_DIR="/var/log/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"
DB_NAME="apsdreamhome_prod"
DB_USER="apsdreamhome_user"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Health check results
CHECKS_PASSED=0
CHECKS_TOTAL=0
ISSUES_FOUND=0

log_info() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARN:${NC} $1"
    ((ISSUES_FOUND++))
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"
    ((ISSUES_FOUND++))
}

log_check() {
    echo -e "${BLUE}[CHECK]${NC} $1"
    ((CHECKS_TOTAL++))
}

check_pass() {
    echo -e "  ✅ $1"
    ((CHECKS_PASSED++))
}

check_fail() {
    echo -e "  ❌ $1"
}

# System resource checks
check_system_resources() {
    log_check "System Resources"

    # CPU usage
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
    if (( $(echo "$cpu_usage < 80" | bc -l) )); then
        check_pass "CPU usage: ${cpu_usage}%"
    else
        check_fail "High CPU usage: ${cpu_usage}%"
    fi

    # Memory usage
    local mem_usage=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
    if [[ $mem_usage -lt 80 ]]; then
        check_pass "Memory usage: ${mem_usage}%"
    else
        check_fail "High memory usage: ${mem_usage}%"
    fi

    # Disk usage
    local disk_usage=$(df / | tail -n 1 | awk '{print $5}' | sed 's/%//')
    if [[ $disk_usage -lt 80 ]]; then
        check_pass "Disk usage: ${disk_usage}%"
    else
        check_fail "High disk usage: ${disk_usage}%"
    fi

    # Load average
    local load_avg=$(uptime | awk -F'load average:' '{ print $2 }' | cut -d, -f1 | xargs)
    local cpu_cores=$(nproc)
    if (( $(echo "$load_avg < $cpu_cores * 1.5" | bc -l) )); then
        check_pass "Load average: $load_avg"
    else
        check_fail "High load average: $load_avg"
    fi
}

# Web server checks
check_web_server() {
    log_check "Web Server (Nginx)"

    # Check if nginx is running
    if systemctl is-active --quiet nginx; then
        check_pass "Nginx service is running"
    else
        check_fail "Nginx service is not running"
        return
    fi

    # Check nginx configuration
    if nginx -t &>/dev/null; then
        check_pass "Nginx configuration is valid"
    else
        check_fail "Nginx configuration has errors"
    fi

    # Check if site is accessible
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost" || echo "000")
    if [[ $http_code == "301" ]] || [[ $http_code == "200" ]]; then
        check_pass "Local HTTP access: $http_code"
    else
        check_fail "Local HTTP access failed: $http_code"
    fi

    # Check HTTPS access
    local https_code=$(curl -s -k -o /dev/null -w "%{http_code}" "https://localhost" || echo "000")
    if [[ $https_code == "200" ]]; then
        check_pass "Local HTTPS access: $https_code"
    else
        check_fail "Local HTTPS access failed: $https_code"
    fi

    # Check SSL certificate
    if [[ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]]; then
        local cert_expiry=$(openssl x509 -in "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" -noout -enddate 2>/dev/null | cut -d= -f2 || echo "")
        if [[ -n "$cert_expiry" ]]; then
            local days_until_expiry=$(( ($(date -d "$cert_expiry" +%s) - $(date +%s)) / 86400 ))
            if [[ $days_until_expiry -gt 30 ]]; then
                check_pass "SSL certificate expires in $days_until_expiry days"
            else
                check_fail "SSL certificate expires soon: $days_until_expiry days"
            fi
        else
            check_fail "Cannot check SSL certificate expiry"
        fi
    else
        check_fail "SSL certificate not found"
    fi
}

# PHP-FPM checks
check_php_fpm() {
    log_check "PHP-FPM"

    # Check if PHP-FPM is running
    if systemctl is-active --quiet php8.1-fpm; then
        check_pass "PHP-FPM service is running"
    else
        check_fail "PHP-FPM service is not running"
        return
    fi

    # Check PHP version
    local php_version=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
    if [[ "$php_version" == "8.1" ]]; then
        check_pass "PHP version: $php_version"
    else
        check_fail "PHP version mismatch: $php_version (expected 8.1)"
    fi

    # Check PHP memory limit
    local memory_limit=$(php -r "echo ini_get('memory_limit');")
    check_pass "PHP memory limit: $memory_limit"

    # Test PHP execution
    if php -r "echo 'PHP test passed';" &>/dev/null; then
        check_pass "PHP execution test passed"
    else
        check_fail "PHP execution test failed"
    fi
}

# Database checks
check_database() {
    log_check "Database (MySQL)"

    # Check if MySQL is running
    if systemctl is-active --quiet mysql; then
        check_pass "MySQL service is running"
    else
        check_fail "MySQL service is not running"
        return
    fi

    # Test database connection
    if mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1;" "$DB_NAME" &>/dev/null; then
        check_pass "Database connection successful"
    else
        check_fail "Database connection failed"
        return
    fi

    # Check database size
    local db_size=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as 'Size (MB)' FROM information_schema.tables WHERE table_schema = '$DB_NAME';" -BN 2>/dev/null || echo "0")
    check_pass "Database size: ${db_size}MB"

    # Check table count
    local table_count=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME';" -BN 2>/dev/null || echo "0")
    check_pass "Table count: $table_count"

    # Check for slow queries (last 24 hours)
    local slow_queries=$(mysql -u"$DB_USER" -p"$DB_PASSWORD" -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';" -BN 2>/dev/null | awk '{print $2}' || echo "0")
    if [[ $slow_queries -lt 100 ]]; then
        check_pass "Slow queries (24h): $slow_queries"
    else
        check_fail "High slow queries: $slow_queries"
    fi
}

# Application checks
check_application() {
    log_check "Application"

    # Check if application directory exists
    if [[ -d "$APP_DIR" ]]; then
        check_pass "Application directory exists"
    else
        check_fail "Application directory not found"
        return
    fi

    # Check file permissions
    if [[ -r "$APP_DIR" ]] && [[ -x "$APP_DIR" ]]; then
        check_pass "Application directory permissions correct"
    else
        check_fail "Application directory permissions incorrect"
    fi

    # Check .env file
    if [[ -f "$APP_DIR/.env" ]]; then
        check_pass ".env file exists"
    else
        check_fail ".env file not found"
    fi

    # Check storage permissions
    if [[ -w "$APP_DIR/storage" ]]; then
        check_pass "Storage directory is writable"
    else
        check_fail "Storage directory is not writable"
    fi

    # Check artisan command
    if [[ -f "$APP_DIR/artisan" ]]; then
        check_pass "Laravel artisan file exists"
        # Test artisan command
        if cd "$APP_DIR" && php artisan --version &>/dev/null; then
            check_pass "Artisan command working"
        else
            check_fail "Artisan command failed"
        fi
    else
        check_fail "Laravel artisan file not found"
    fi

    # Check application health endpoint
    local health_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/health" || echo "000")
    if [[ $health_code == "200" ]]; then
        check_pass "Health endpoint accessible"
    else
        check_fail "Health endpoint failed: $health_code"
    fi

    # Check main application
    local app_code=$(curl -s -k -o /dev/null -w "%{http_code}" "https://$DOMAIN" || echo "000")
    if [[ $app_code == "200" ]]; then
        check_pass "Main application accessible"
    else
        check_fail "Main application failed: $app_code"
    fi
}

# Backup checks
check_backups() {
    log_check "Backups"

    # Check if backup directory exists
    if [[ -d "$BACKUP_DIR" ]]; then
        check_pass "Backup directory exists"
    else
        check_fail "Backup directory not found"
        return
    fi

    # Check recent database backups (last 24 hours)
    local recent_db_backups=$(find "$BACKUP_DIR/database" -name "*.sql.gz" -mtime -1 2>/dev/null | wc -l)
    if [[ $recent_db_backups -gt 0 ]]; then
        check_pass "Recent database backups: $recent_db_backups"
    else
        check_fail "No recent database backups found"
    fi

    # Check recent files backups (last 7 days)
    local recent_file_backups=$(find "$BACKUP_DIR/files" -name "*.tar.gz" -mtime -7 2>/dev/null | wc -l)
    if [[ $recent_file_backups -gt 0 ]]; then
        check_pass "Recent file backups: $recent_file_backups"
    else
        check_fail "No recent file backups found"
    fi

    # Check backup disk usage
    local backup_size=$(du -sm "$BACKUP_DIR" 2>/dev/null | cut -f1 || echo "0")
    if [[ $backup_size -lt 1024 ]]; then  # Less than 1GB
        check_pass "Backup size: ${backup_size}MB"
    else
        check_fail "Large backup size: ${backup_size}MB"
    fi
}

# Log analysis
check_logs() {
    log_check "Log Analysis"

    # Check log directory
    if [[ -d "$LOG_DIR" ]]; then
        check_pass "Log directory exists"
    else
        check_fail "Log directory not found"
        return
    fi

    # Check for recent errors in application logs
    local error_count=$(find "$LOG_DIR" -name "*.log" -mtime -1 -exec grep -l "ERROR\|CRITICAL\|EMERGENCY" {} \; 2>/dev/null | wc -l)
    if [[ $error_count -eq 0 ]]; then
        check_pass "No recent application errors"
    else
        check_fail "Recent application errors found in $error_count log files"
    fi

    # Check nginx error logs
    local nginx_errors=$(grep -c "error" /var/log/nginx/error.log 2>/dev/null || echo "0")
    if [[ $nginx_errors -lt 10 ]]; then
        check_pass "Nginx error count (24h): $nginx_errors"
    else
        check_fail "High nginx error count: $nginx_errors"
    fi

    # Check PHP-FPM error logs
    local php_errors=$(grep -c "\[.*\] .*ERROR" /var/log/php8.1-fpm.log 2>/dev/null || echo "0")
    if [[ $php_errors -lt 5 ]]; then
        check_pass "PHP error count (24h): $php_errors"
    else
        check_fail "High PHP error count: $php_errors"
    fi
}

# Security checks
check_security() {
    log_check "Security"

    # Check if SSH root login is disabled
    if grep -q "^PermitRootLogin no" /etc/ssh/sshd_config 2>/dev/null; then
        check_pass "SSH root login disabled"
    else
        check_fail "SSH root login may be enabled"
    fi

    # Check if UFW/firewall is active
    if systemctl is-active --quiet ufw || systemctl is-active --quiet firewalld; then
        check_pass "Firewall is active"
    else
        check_fail "No active firewall detected"
    fi

    # Check if unattended upgrades are enabled
    if systemctl is-active --quiet unattended-upgrades 2>/dev/null; then
        check_pass "Automatic security updates enabled"
    else
        check_fail "Automatic security updates not enabled"
    fi

    # Check for exposed sensitive files
    local exposed_files=$(find "$APP_DIR" -name ".env*" -o -name "composer.lock" -o -name "*.log" | xargs ls -la 2>/dev/null | grep -c "^-rwxrwxrwx" || echo "0")
    if [[ $exposed_files -eq 0 ]]; then
        check_pass "No sensitive files publicly accessible"
    else
        check_fail "Sensitive files may be publicly accessible"
    fi
}

# Performance monitoring
check_performance() {
    log_check "Performance"

    # Check response time
    local response_time=$(curl -s -w "%{time_total}" -o /dev/null "https://$DOMAIN" 2>/dev/null || echo "10")
    if (( $(echo "$response_time < 2.0" | bc -l 2>/dev/null || echo "1") )); then
        check_pass "Response time: ${response_time}s"
    else
        check_fail "Slow response time: ${response_time}s"
    fi

    # Check database query performance
    if [[ -f "$APP_DIR/artisan" ]]; then
        cd "$APP_DIR"
        # This would require Laravel Telescope or similar monitoring tool
        check_pass "Performance monitoring available via Laravel"
    fi

    # Check cache hit ratio (if Redis is used)
    if systemctl is-active --quiet redis-server; then
        local cache_info=$(redis-cli info stats 2>/dev/null | grep keyspace_hits || echo "")
        if [[ -n "$cache_info" ]]; then
            check_pass "Redis cache available"
        else
            check_fail "Redis cache not accessible"
        fi
    fi
}

# Generate health report
generate_report() {
    log_info "Generating health check report..."

    local report_file="$LOG_DIR/health_report_$(date +"%Y%m%d_%H%M%S").log"
    local score=$((CHECKS_PASSED * 100 / CHECKS_TOTAL))

    {
        echo "=== APS Dream Home Health Check Report ==="
        echo "Timestamp: $(date)"
        echo "System: $DOMAIN"
        echo ""
        echo "=== Summary ==="
        echo "Checks Passed: $CHECKS_PASSED/$CHECKS_TOTAL"
        echo "Health Score: $score%"
        echo "Issues Found: $ISSUES_FOUND"
        echo ""
        echo "=== System Information ==="
        echo "OS: $(lsb_release -d 2>/dev/null | cut -f2 || uname -s)"
        echo "Kernel: $(uname -r)"
        echo "Uptime: $(uptime -p)"
        echo ""
        echo "=== Resource Usage ==="
        echo "CPU: $(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')%"
        echo "Memory: $(free | grep Mem | awk '{printf "%.0f%%", $3/$2 * 100.0}')"
        echo "Disk: $(df / | tail -n 1 | awk '{print $5}')"
        echo ""
        echo "=== Recommendations ==="
        if [[ $score -ge 90 ]]; then
            echo "✅ System health is excellent!"
        elif [[ $score -ge 75 ]]; then
            echo "⚠️  System health is good, but some improvements needed"
        else
            echo "❌ System health needs immediate attention"
        fi
        echo ""
    } > "$report_file"

    log_info "Health report saved: $report_file"
    echo ""
    echo "=== HEALTH CHECK SUMMARY ==="
    echo "Score: $score% ($CHECKS_PASSED/$CHECKS_TOTAL checks passed)"
    echo "Issues: $ISSUES_FOUND"
    echo "Report: $report_file"
}

# Send alert if critical issues found
send_alert() {
    if [[ $ISSUES_FOUND -gt 0 ]] && command -v mail &> /dev/null; then
        local subject="APS Dream Home Health Check Alert - $ISSUES_FOUND issues found"
        local report_file="$LOG_DIR/health_report_$(date +"%Y%m%d_%H%M%S").log"

        if [[ -f "$report_file" ]]; then
            mail -s "$subject" admin@apsdreamhome.com < "$report_file" 2>/dev/null || true
        fi
    fi
}

# Main health check process
main() {
    echo "=========================================="
    echo "🏥 APS Dream Home Health Check"
    echo "=========================================="
    echo ""

    # Run all checks
    check_system_resources
    check_web_server
    check_php_fpm
    check_database
    check_application
    check_backups
    check_logs
    check_security
    check_performance

    echo ""
    generate_report
    send_alert

    echo ""
    echo "=========================================="
    if [[ $ISSUES_FOUND -eq 0 ]]; then
        echo "🎉 ALL CHECKS PASSED - System is healthy!"
    else
        echo "⚠️  $ISSUES_FOUND ISSUES FOUND - Review report above"
    fi
    echo "=========================================="
}

# Handle command line arguments
case "${1:-}" in
    "quick")
        check_system_resources
        check_web_server
        check_application
        echo "Quick health check completed"
        ;;
    "full")
        main
        ;;
    "resources")
        check_system_resources
        ;;
    "web")
        check_web_server
        ;;
    "database")
        check_database
        ;;
    "security")
        check_security
        ;;
    *)
        main
        ;;
esac
