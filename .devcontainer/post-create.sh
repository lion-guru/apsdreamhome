#!/bin/bash
# =============================================================================
# APS Dream Home - Codespaces / Docker Dev Post-Create Setup
# =============================================================================
set -e

echo "=== APS Dream Home - Environment Setup ==="

# 1. Wait for MySQL
echo "[1/5] Waiting for MySQL..."
for i in $(seq 1 30); do
    mysqladmin ping -h db -P 3307 --silent 2>/dev/null && echo "  MySQL ready!" && break
    sleep 1
done

# 2. Import database if not already populated
echo "[2/5] Checking database..."
TABLE_COUNT=$(mysql -h db -P 3307 -u root -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='apsdreamhome'" 2>/dev/null || echo "0")
if [ "$TABLE_COUNT" -lt 100 ]; then
    echo "  Database needs import (~${TABLE_COUNT} tables found)..."
    if [ -f database/apsdreamhome_latest.sql ]; then
        mysql -h db -P 3307 -u root apsdreamhome < database/apsdreamhome_latest.sql 2>&1 || true
        echo "  Database imported!"
    else
        echo "  No SQL dump found. Run 'php scripts/export_database.php' on your PC to create one."
    fi
else
    echo "  Database OK ($TABLE_COUNT tables)"
fi

# 3. Create writable directories
echo "[3/5] Setting permissions..."
mkdir -p storage/logs storage/cache storage/sessions logs
chmod -R 775 storage/ logs/ public/uploads 2>/dev/null || true

# 4. Install Node.js deps for E2E
echo "[4/5] Setting up E2E tests..."
if [ -f testing/visual_tests/E2E_MASTER_TEST.mjs ]; then
    npx playwright install --with-deps chromium 2>/dev/null || echo "  Playwright install skipped"
fi

# 5. Summary
echo "[5/5] Done!"
echo ""
echo "Access:"
echo "  Web:  http://localhost"
echo "  Admin: http://localhost/admin/login?test_login=1"
echo "  DB:   mysql -h db -P 3307 -u root apsdreamhome"
echo "  Tests: node testing/visual_tests/E2E_MASTER_TEST.mjs"
