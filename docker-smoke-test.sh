#!/bin/bash
# =============================================================================
# APS Dream Home - Docker Setup Smoke Test
# Verifies the Docker stack is correctly configured and all services are healthy
# Run AFTER `make up` succeeds
# =============================================================================
set -eu

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

PASS=0
FAIL=0

ok()   { echo -e "  ${GREEN}✓${NC} $*"; PASS=$((PASS+1)); }
fail() { echo -e "  ${RED}✗${NC} $*"; FAIL=$((FAIL+1)); }
warn() { echo -e "  ${YELLOW}!${NC} $*"; }
hdr()  { echo -e "\n${YELLOW}=== $* ===${NC}"; }

# ----- 1. Containers running -----
hdr "1. Container Status"
EXPECTED="apsdreamhome_app apsdreamhome_websocket apsdreamhome_db apsdreamhome_redis apsdreamhome_nginx"
for c in $EXPECTED; do
    if docker ps --format '{{.Names}}' | grep -q "^${c}$"; then
        ok "Container ${c} is running"
    else
        fail "Container ${c} is NOT running"
    fi
done

# ----- 2. Healthchecks -----
hdr "2. Healthchecks"
for c in $EXPECTED; do
    cid=$(docker ps -q -f name="^${c}$")
    if [ -n "${cid}" ]; then
        health=$(docker inspect --format='{{.State.Health.Status}}' "${cid}" 2>/dev/null || echo "no-check")
        case "${health}" in
            healthy)    ok "${c}: healthy" ;;
            starting)   warn "${c}: still starting" ;;
            unhealthy)  fail "${c}: unhealthy" ;;
            *)          warn "${c}: ${health}" ;;
        esac
    fi
done

# ----- 3. Network -----
hdr "3. Network"
if docker network ls --format '{{.Name}}' | grep -q "apsdreamhome_network"; then
    ok "Network apsdreamhome_network exists"
else
    fail "Network apsdreamhome_network missing"
fi

# ----- 4. Web app reachable -----
hdr "4. Web Application"
if curl -fsS -m 5 http://localhost/ -o /dev/null; then
    ok "http://localhost/ responds 2xx/3xx"
else
    fail "http://localhost/ does not respond"
fi

# ----- 5. Health endpoint -----
hdr "5. /health Endpoint"
if [ "$(curl -fsS -m 5 -o /dev/null -w '%{http_code}' http://localhost/health)" = "200" ]; then
    ok "GET /health returns 200"
else
    fail "GET /health does not return 200"
fi

# ----- 6. WebSocket -----
hdr "6. WebSocket"
if nc -z localhost 8080 2>/dev/null; then
    ok "Port 8080 (WebSocket) is open"
else
    fail "Port 8080 (WebSocket) is not reachable"
fi

# ----- 7. Database -----
hdr "7. Database"
DB_ROOT_PASS="${MYSQL_ROOT_PASSWORD:-rootroot}"
DB_USER="${DB_USERNAME:-apsdream}"
DB_PASS="${DB_PASSWORD:-changeme}"
DB_NAME="${DB_DATABASE:-apsdreamhome}"
if docker exec apsdreamhome_db mysqladmin ping -h 127.0.0.1 -u root -p"${DB_ROOT_PASS}" 2>/dev/null | grep -q "alive"; then
    ok "MySQL is alive"
else
    fail "MySQL ping failed"
fi
if docker exec -e MYSQL_PWD="${DB_PASS}" apsdreamhome_db mysql -u "${DB_USER}" -e "USE ${DB_NAME}; SELECT 1;" 2>/dev/null | grep -q "1"; then
    ok "Application user can query database"
else
    fail "Application user cannot query database"
fi

# ----- 8. Redis -----
hdr "8. Redis"
if docker exec apsdreamhome_redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
    ok "Redis responds to PING"
else
    fail "Redis does not respond"
fi

# ----- 9. Composer autoload -----
hdr "9. Application"
if docker exec apsdreamhome_app test -f /var/www/html/vendor/autoload.php; then
    ok "vendor/autoload.php exists in app container"
else
    fail "vendor/autoload.php is missing (composer install failed?)"
fi
if docker exec apsdreamhome_app test -f /var/www/html/public/index.php; then
    ok "public/index.php exists in app container"
else
    fail "public/index.php is missing"
fi
if docker exec apsdreamhome_app test -f /var/www/html/websocket_server.php; then
    ok "websocket_server.php exists in app container"
else
    fail "websocket_server.php is missing"
fi

# ----- 10. Storage permissions -----
hdr "10. Storage Permissions"
if docker exec apsdreamhome_app test -w /var/www/html/storage; then
    ok "storage/ is writable"
else
    fail "storage/ is not writable"
fi
if docker exec apsdreamhome_app test -w /var/www/html/public/uploads; then
    ok "public/uploads/ is writable"
else
    fail "public/uploads/ is not writable"
fi

# ----- Summary -----
hdr "Summary"
echo -e "  ${GREEN}Passed:${NC}  ${PASS}"
echo -e "  ${RED}Failed:${NC}  ${FAIL}"
echo ""

if [ ${FAIL} -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed - stack is healthy!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some checks failed - run 'make logs' for details.${NC}"
    exit 1
fi
