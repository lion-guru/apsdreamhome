#!/usr/bin/env bash
# =============================================================================
# APS Dream Home - Let's Encrypt SSL Setup
#
# Installs certbot, obtains a free SSL certificate from Let's Encrypt,
# configures nginx to use it, and sets up auto-renewal via cron.
#
# Usage:
#   ./scripts/setup_ssl.sh -d example.com -e admin@example.com
#   ./scripts/setup_ssl.sh -d example.com -d www.example.com -e admin@example.com --staging
#
# Flags:
#   -d, --domain       Domain name (can be specified multiple times)
#   -e, --email        Email for Let's Encrypt registration
#   -s, --staging      Use Let's Encrypt staging server (for testing)
#   -w, --webroot      Webroot path (default: public/)
#   --no-nginx         Skip nginx configuration update
#   --no-cron          Skip cron auto-renewal setup
# =============================================================================
set -euo pipefail

# ------------------------------------------------------------------
# Defaults
# ------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_DIR}"

DOMAINS=()
EMAIL=""
STAGING=""
WEBROOT="${PROJECT_DIR}/public"
CONFIGURE_NGINX=1
SETUP_CRON=1
SSL_DIR="${PROJECT_DIR}/docker/ssl"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log()     { echo -e "${GREEN}[ssl]${NC} $(date -u +%FT%TZ)  $*"; }
warn()    { echo -e "${YELLOW}[ssl]${NC} $(date -u +%FT%TZ)  $*" >&2; }
err()     { echo -e "${RED}[ssl]${NC} $(date -u +%FT%TZ)  $*" >&2; }
section() { echo -e "\n${BLUE}========== $* ==========${NC}"; }

# ------------------------------------------------------------------
# Parse args
# ------------------------------------------------------------------
while [ $# -gt 0 ]; do
    case "$1" in
        -d|--domain)
            DOMAINS+=("$2")
            shift 2
            ;;
        -e|--email)
            EMAIL="$2"
            shift 2
            ;;
        -s|--staging)
            STAGING="--staging"
            shift
            ;;
        -w|--webroot)
            WEBROOT="$2"
            shift 2
            ;;
        --no-nginx)
            CONFIGURE_NGINX=0
            shift
            ;;
        --no-cron)
            SETUP_CRON=0
            shift
            ;;
        -h|--help)
            grep -E '^# ' "$0" | sed 's/^# //'
            exit 0
            ;;
        *)
            err "Unknown argument: $1"
            exit 1
            ;;
    esac
done

# ------------------------------------------------------------------
# Validation
# ------------------------------------------------------------------
section "Pre-flight"

if [ ${#DOMAINS[@]} -eq 0 ]; then
    err "At least one -d/--domain is required"
    echo "Example: $0 -d example.com -e admin@example.com"
    exit 1
fi

if [ -z "${EMAIL}" ]; then
    err "-e/--email is required for Let's Encrypt registration"
    exit 1
fi

if [ "$(id -u)" -ne 0 ] && ! command -v sudo >/dev/null 2>&1; then
    err "This script must be run as root or with sudo"
    exit 1
fi

PRIMARY_DOMAIN="${DOMAINS[0]}"
log "Primary domain:  ${PRIMARY_DOMAIN}"
log "All domains:     ${DOMAINS[*]}"
log "Email:           ${EMAIL}"
log "Webroot:         ${WEBROOT}"
[ -n "${STAGING}" ] && log "Mode:            STAGING (test certificate)"
[ -z "${STAGING}" ] && log "Mode:            PRODUCTION (real certificate)"

# ------------------------------------------------------------------
# Install certbot
# ------------------------------------------------------------------
section "Installing certbot"

if command -v certbot >/dev/null 2>&1; then
    log "certbot already installed: $(certbot --version 2>&1 | head -1)"
else
    log "Installing certbot via apt..."
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        case "${ID:-}" in
            ubuntu|debian)
                apt-get update -y
                apt-get install -y certbot
                ;;
            centos|rhel|rocky|almalinux|fedora)
                dnf install -y certbot || yum install -y certbot
                ;;
            *)
                warn "Unsupported distro: ${ID:-unknown}. Install certbot manually."
                exit 1
                ;;
        esac
    else
        err "Cannot detect OS - install certbot manually first"
        exit 1
    fi
fi

# ------------------------------------------------------------------
# Obtain certificate
# ------------------------------------------------------------------
section "Obtaining certificate"

mkdir -p "${SSL_DIR}"
mkdir -p "${WEBROOT}/.well-known/acme-challenge"

# Build domain args
DOMAIN_ARGS=()
for d in "${DOMAINS[@]}"; do
    DOMAIN_ARGS+=("-d" "${d}")
done

log "Running certbot certonly (webroot)..."
if certbot certonly \
    --webroot \
    --webroot-path="${WEBROOT}" \
    --email "${EMAIL}" \
    --agree-tos \
    --no-eff-email \
    --keep-until-expiring \
    ${STAGING} \
    "${DOMAIN_ARGS[@]}"; then
    log "Certificate obtained"
else
    err "certbot failed"
    exit 1
fi

# ------------------------------------------------------------------
# Copy certs to docker/ssl (nginx mount point)
# ------------------------------------------------------------------
section "Copying certificates to docker/ssl"

LE_LIVE="/etc/letsencrypt/live/${PRIMARY_DOMAIN}"
if [ ! -d "${LE_LIVE}" ]; then
    err "Cert directory not found: ${LE_LIVE}"
    exit 1
fi

cp -f "${LE_LIVE}/fullchain.pem" "${SSL_DIR}/fullchain.pem"
cp -f "${LE_LIVE}/privkey.pem"   "${SSL_DIR}/privkey.pem"
chmod 644 "${SSL_DIR}/fullchain.pem"
chmod 600 "${SSL_DIR}/privkey.pem"
log "Copied: fullchain.pem, privkey.pem"

# ------------------------------------------------------------------
# Update nginx config
# ------------------------------------------------------------------
if [ "${CONFIGURE_NGINX}" -eq 1 ]; then
    section "Configuring nginx"

    # Enable HTTPS server block
    SSL_CONF="${PROJECT_DIR}/docker/nginx/conf.d/ssl.conf"
    if [ -f "${SSL_CONF}" ]; then
        log "SSL server block already present: ${SSL_CONF}"
    else
        log "Creating ${SSL_CONF}..."
        cat > "${SSL_CONF}" <<'NGINX_EOF'
server {
    listen      443 ssl;
    listen [::]:443 ssl;
    http2       on;
    server_name _;

    ssl_certificate     /etc/ssl/certs/fullchain.pem;
    ssl_certificate_key /etc/ssl/certs/privkey.pem;

    ssl_protocols           TLSv1.2 TLSv1.3;
    ssl_ciphers             ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305;
    ssl_prefer_server_ciphers off;
    ssl_session_cache       shared:SSL:10m;
    ssl_session_timeout     1d;
    ssl_session_tickets     off;
    ssl_stapling            on;
    ssl_stapling_verify     on;
    resolver                1.1.1.1 8.8.8.8 valid=300s;
    resolver_timeout        5s;

    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Frame-Options             "SAMEORIGIN"        always;
    add_header X-Content-Type-Options      "nosniff"           always;
    add_header X-XSS-Protection            "1; mode=block"     always;
    add_header Referrer-Policy             "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy          "geolocation=(), microphone=(), camera=(), payment=()" always;

    include /etc/nginx/conf.d/app.conf;
}
NGINX_EOF
    fi

    # Enable HTTP -> HTTPS redirect
    REDIRECT_CONF="${PROJECT_DIR}/docker/nginx/conf.d/ssl-redirect.conf"
    cat > "${REDIRECT_CONF}" <<'NGINX_EOF'
server {
    listen      80;
    listen [::]:80;
    server_name _;
    return 301 https://$host$request_uri;
}
NGINX_EOF
    log "Updated ssl-redirect.conf to force HTTPS"

    # If using Docker, reload nginx
    if command -v docker >/dev/null 2>&1; then
        if docker compose ps nginx 2>/dev/null | grep -qE "Up|running"; then
            log "Reloading nginx container..."
            docker compose exec -T nginx nginx -t && docker compose exec -T nginx nginx -s reload
        else
            log "Nginx container not running - configuration will apply on next start"
        fi
    fi
fi

# ------------------------------------------------------------------
# Setup auto-renewal cron
# ------------------------------------------------------------------
if [ "${SETUP_CRON}" -eq 1 ]; then
    section "Setting up auto-renewal cron"

    RENEW_HOOK="${PROJECT_DIR}/scripts/setup_ssl.sh -d ${DOMAINS[*]} -e ${EMAIL} --no-cron --no-nginx"
    RENEW_HOOK="${RENEW_HOOK// /\\ }"
    RENEW_HOOK="$(echo "${DOMAINS[@]}" | sed 's/ / -d /g' | xargs -I{} echo "-d {}")"

    CRON_LINE="0 3 * * * certbot renew --webroot --webroot-path=${WEBROOT} --quiet && cp -f /etc/letsencrypt/live/${PRIMARY_DOMAIN}/fullchain.pem ${SSL_DIR}/fullchain.pem && cp -f /etc/letsencrypt/live/${PRIMARY_DOMAIN}/privkey.pem ${SSL_DIR}/privkey.pem && (cd ${PROJECT_DIR} && docker compose exec -T nginx nginx -s reload 2>/dev/null || true)"

    if crontab -l 2>/dev/null | grep -q "certbot renew" && crontab -l 2>/dev/null | grep -q "${PRIMARY_DOMAIN}"; then
        log "Cron entry already exists for ${PRIMARY_DOMAIN}"
    else
        log "Adding cron entry..."
        (crontab -l 2>/dev/null; echo "${CRON_LINE}") | crontab -
        log "Cron scheduled: daily at 03:00 UTC"
    fi

    log "Test renew (dry run):"
    certbot renew --dry-run 2>&1 | tail -20
fi

# ------------------------------------------------------------------
# Done
# ------------------------------------------------------------------
section "SSL SETUP COMPLETE"

log "Certificate:  /etc/letsencrypt/live/${PRIMARY_DOMAIN}/"
log "Docker copy:  ${SSL_DIR}/fullchain.pem, ${SSL_DIR}/privkey.pem"
log "Expires:      $(openssl x509 -enddate -noout -in "${SSL_DIR}/fullchain.pem" | cut -d= -f2)"
log "Auto-renew:   daily at 03:00 UTC"
log ""
log "Verify with:  curl -vI https://${PRIMARY_DOMAIN}/"
log "              https://www.ssllabs.com/ssltest/analyze.html?d=${PRIMARY_DOMAIN}"

exit 0
