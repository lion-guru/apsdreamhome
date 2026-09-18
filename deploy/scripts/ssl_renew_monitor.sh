#!/bin/bash
# APS Dream Home - SSL Certificate Auto-Renewal + Health Monitoring
# Add to crontab: 0 3 * * * /var/www/apsdreamhome/deploy/scripts/ssl_renew_monitor.sh

set -e

DOMAIN="apsdreamhome.com"
EMAIL="admin@apsdreamhome.com"
WEBROOT="/var/www/letsencrypt"
LOG_FILE="/var/log/apsdreamhome_ssl_monitor.log"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"; }

log "=== SSL Renewal + Health Check Started ==="

# 1. Renew Let's Encrypt certificates
log "🔐 Checking certificate renewal..."
certbot renew --quiet --webroot -w "$WEBROOT" --post-hook "systemctl reload nginx"

# Check certificate expiry
CERT_EXPIRY=$(openssl x509 -enddate -noout -in "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" 2>/dev/null | cut -d= -f2)
if [ -n "$CERT_EXPIRY" ]; then
    EXPIRY_EPOCH=$(date -d "$CERT_EXPIRY" +%s)
    NOW_EPOCH=$(date +%s)
    DAYS_LEFT=$(( (EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
    log "📅 Certificate expires in $DAYS_LEFT days ($CERT_EXPIRY)"
    if [ "$DAYS_LEFT" -lt 30 ]; then
        log "⚠️ WARNING: Certificate expires in less than 30 days!"
    fi
fi

# 2. Health Checks
log "🏥 Running health checks..."

# HTTP/HTTPS
for PROTO in http https; do
    URL="${PROTO}://$DOMAIN"
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$URL" 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ]; then
        log "✅ $PROTO://$DOMAIN - HTTP $HTTP_CODE"
    else
        log "❌ $PROTO://$DOMAIN - HTTP $HTTP_CODE"
    fi
done

# API Endpoint
API_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "https://$DOMAIN/api/v2/mobile/properties" 2>/dev/null || echo "000")
if [ "$API_CODE" = "200" ] || [ "$API_CODE" = "401" ]; then
    log "✅ API endpoint - HTTP $API_CODE"
else
    log "❌ API endpoint - HTTP $API_CODE"
fi

# Database Connection
DB_CHECK=$(mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SELECT 1" apsdreamhome 2>/dev/null && echo "OK" || echo "FAIL")
if [ "$DB_CHECK" = "OK" ]; then
    log "✅ Database connection"
else
    log "❌ Database connection FAILED"
fi

# Disk Space
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
log "💾 Disk usage: ${DISK_USAGE}%"
if [ "$DISK_USAGE" -gt 85 ]; then
    log "⚠️ WARNING: Disk usage above 85%"
fi

# Memory
MEM_USAGE=$(free | awk 'NR==2 {printf "%.0f", $3/$2*100}')
log "🧠 Memory usage: ${MEM_USAGE}%"
if [ "$MEM_USAGE" -gt 90 ]; then
    log "⚠️ WARNING: Memory usage above 90%"
fi

# PHP-FPM Status
PHP_FPM=$(systemctl is-active php8.3-fpm)
log "🐘 PHP-FPM: $PHP_FPM"

# Nginx Status
NGINX_STATUS=$(systemctl is-active nginx)
log "🌐 Nginx: $NGINX_STATUS"

# WebSocket Services
WS1=$(systemctl is-active apsdreamhome-websocket)
WS2=$(systemctl is-active apsdreamhome-websocket-broadcast)
log "🔌 WebSocket: $WS1, Broadcast: $WS2"

# 3. Alert on Critical Issues
CRITICAL=0
if [ "$HTTP_CODE" != "200" ]; then CRITICAL=1; fi
if [ "$DB_CHECK" != "OK" ]; then CRITICAL=1; fi
if [ "$DISK_USAGE" -gt 95 ]; then CRITICAL=1; fi
if [ "$PHP_FPM" != "active" ]; then CRITICAL=1; fi
if [ "$NGINX_STATUS" != "active" ]; then CRITICAL=1; fi

if [ "$CRITICAL" -eq 1 ]; then
    log "🚨 CRITICAL ALERT: One or more services unhealthy!"
    # Send alert (configure webhook)
    # curl -X POST "https://hooks.slack.com/services/..." -d '{"text":"🚨 APS Dream Home ALERT"}'
fi

log "=== Check Complete ==="