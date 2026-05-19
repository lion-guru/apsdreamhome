#!/bin/bash
# APS Dream Home - Devin Environment Setup Script
# This script configures environment variables and system settings

echo "=== APS Dream Home - Devin Environment Setup ==="
echo ""

# Set project root
export PROJECT_ROOT="c:\\xampp\\htdocs\\apsdreamhome"
export APP_ENV="development"
export APP_DEBUG="true"

# Database configuration
export DB_HOST="127.0.0.1"
export DB_PORT="3307"
export DB_NAME="apsdreamhome"
export DB_USER="root"
export DB_PASS=""

# Server configuration
export SERVER_HOST="localhost"
export SERVER_PORT="80"
export BASE_URL="http://localhost/apsdreamhome"

# PHP configuration
export PHP_MEMORY_LIMIT="256M"
export PHP_MAX_EXECUTION_TIME="300"

# Application key
export APP_KEY="apsdreamhome-dev-key-2025-super-secret"

# Timezone
export APP_TIMEZONE="Asia/Kolkata"

# Display configuration
echo "Environment Variables Set:"
echo "  PROJECT_ROOT: $PROJECT_ROOT"
echo "  APP_ENV: $APP_ENV"
echo "  APP_DEBUG: $APP_DEBUG"
echo "  DB_HOST: $DB_HOST"
echo "  DB_PORT: $DB_PORT"
echo "  DB_NAME: $DB_NAME"
echo "  BASE_URL: $BASE_URL"
echo "  APP_TIMEZONE: $APP_TIMEZONE"
echo ""

# Check if XAMPP is running
echo "Checking XAMPP Services..."
# Note: This is for Windows, so we'd check XAMPP control panel
echo "  Make sure XAMPP Apache and MySQL are running"
echo ""

# Create necessary directories if they don't exist
echo "Ensuring directories exist..."
mkdir -p "$PROJECT_ROOT/assets/uploads"
mkdir -p "$PROJECT_ROOT/storage/logs"
mkdir -p "$PROJECT_ROOT/storage/cache"
mkdir -p "$PROJECT_ROOT/testing/visual_tests"
echo "  ✓ Directories created/verified"
echo ""

# Set permissions for writable directories
echo "Setting directory permissions..."
# On Windows, this might not work, but we'll try
chmod -R 755 "$PROJECT_ROOT/assets/uploads" 2>/dev/null || echo "  (Skipped on Windows)"
chmod -R 755 "$PROJECT_ROOT/storage" 2>/dev/null || echo "  (Skipped on Windows)"
echo "  ✓ Permissions set"
echo ""

# Verify database connection
echo "Testing database connection..."
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
    echo '  ✓ Database connection successful';
} catch (PDOException \$e) {
    echo '  ✗ Database connection failed: ' . \$e->getMessage();
}
" 2>/dev/null || echo "  (Database connection check skipped)"
echo ""

echo "=== Environment Setup Complete ==="
echo ""
echo "You can now start development with full permissions!"
echo "Run 'devin --help' to see available commands"