<?php
/**
 * XAMPP Server Diagnostic Script
 * Comprehensive check of server configuration, services, and functionality
 */

echo "🔍 XAMPP Server Deep Diagnostic\n";
echo "================================\n\n";

// 1. Check PHP Configuration
echo "1. PHP CONFIGURATION CHECK\n";
echo "==========================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP OS: " . PHP_OS . "\n";
echo "Server API: " . PHP_SAPI . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n\n";

// 2. Check Required PHP Modules
$required_modules = [
    'mysqli', 'pdo_mysql', 'mbstring', 'openssl', 
    'curl', 'gd', 'json', 'session', 'filter'
];

echo "2. REQUIRED PHP MODULES\n";
echo "======================\n";
foreach ($required_modules as $module) {
    if (extension_loaded($module)) {
        echo "✅ $module: Enabled\n";
    } else {
        echo "❌ $module: MISSING\n";
    }
}
echo "\n";

// 3. Check File Permissions
$important_dirs = [
    '.', 'auth', 'includes', 'logs', 'uploads', 'temp'
];

echo "3. FILE PERMISSIONS CHECK\n";
echo "=========================\n";
foreach ($important_dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $writable = is_writable($path);
        $readable = is_readable($path);
        echo "📁 $dir: " . ($writable ? '✅ Writable' : '❌ Not Writable') . 
             ", " . ($readable ? '✅ Readable' : '❌ Not Readable') . "\n";
    } else {
        echo "❌ $dir: Directory not found\n";
    }
}
echo "\n";

// 4. Database Connection Test
echo "4. DATABASE CONNECTION TEST\n";
echo "==========================\n";

try {
    // Check if db_config exists
    $db_config_path = __DIR__ . '/includes/db_config.php';
    if (file_exists($db_config_path)) {
        require_once $db_config_path;
        
        // Test MySQL connection
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            echo "❌ Database Connection Failed: " . $conn->connect_error . "\n";
            echo "   Host: " . DB_HOST . "\n";
            echo "   User: " . DB_USER . "\n";
            echo "   Database: " . DB_NAME . "\n";
        } else {
            echo "✅ Database Connection: SUCCESS\n";
            echo "   Host: " . DB_HOST . "\n";
            echo "   User: " . DB_USER . "\n";
            echo "   Database: " . DB_NAME . "\n";
            
            // Check important tables
            $tables_to_check = ['users', 'mlm_agents', 'properties', 'leads'];
            foreach ($tables_to_check as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    echo "   ✅ Table '$table': EXISTS\n";
                } else {
                    echo "   ⚠️  Table '$table': NOT FOUND\n";
                }
            }
            $conn->close();
        }
    } else {
        echo "❌ Database configuration file not found: includes/db_config.php\n";
    }
} catch (Exception $e) {
    echo "❌ Database Test Exception: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Check Server Environment
echo "5. SERVER ENVIRONMENT\n";
echo "====================\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "\n";
echo "Server Addr: " . ($_SERVER['SERVER_ADDR'] ?? 'Not set') . "\n";
echo "Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'Not set') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
echo "Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'Not set') . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? '✅ Enabled' : '❌ Not enabled') . "\n";
echo "\n";

// 6. Check Important Files
echo "6. IMPORTANT FILES CHECK\n";
echo "========================\n";

$important_files = [
    '.htaccess',
    'index.php',
    'auth/login.php',
    'auth/register.php',
    'includes/db_config.php',
    'includes/security/security_functions.php',
    'admin/includes/csrf_protection.php'
];

foreach ($important_files as $file) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        $readable = is_readable($file_path);
        $writable = is_writable($file_path);
        echo "📄 $file: " . ($readable ? '✅ Readable' : '❌ Not Readable') . 
             ", " . ($writable ? '✅ Writable' : '❌ Not Writable') . "\n";
    } else {
        echo "❌ $file: File not found\n";
    }
}
echo "\n";

// 7. Check Session Configuration
echo "7. SESSION CONFIGURATION\n";
echo "========================\n";
echo "Session Save Path: " . ini_get('session.save_path') . "\n";
echo "Session Name: " . ini_get('session.name') . "\n";
echo "Session Cookie Lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "Session GC Max Lifetime: " . ini_get('session.gc_maxlifetime') . "\n";

// Test session functionality
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
echo "Session Status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n\n";

// 8. Check Security Settings
echo "8. SECURITY SETTINGS\n";
echo "====================\n";
echo "Allow URL Include: " . (ini_get('allow_url_include') ? '⚠️ Enabled' : '✅ Disabled') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? '⚠️ Enabled' : '✅ Disabled') . "\n";
echo "Register Globals: " . (ini_get('register_globals') ? '⚠️ Enabled' : '✅ Disabled') . "\n";
echo "Magic Quotes: " . (ini_get('magic_quotes_gpc') ? '⚠️ Enabled' : '✅ Disabled') . "\n\n";

// 9. Recommendations
echo "9. RECOMMENDATIONS\n";
echo "==================\n";

// Check if running on localhost
$is_localhost = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($is_localhost) {
    echo "✅ Running on localhost - Development environment\n";
} else {
    echo "⚠️  Not running on localhost - Check production settings\n";
}

// Check if safe mode is off
if (ini_get('safe_mode')) {
    echo "❌ SAFE MODE: Enabled (should be disabled)\n";
} else {
    echo "✅ SAFE MODE: Disabled\n";
}

echo "\n================================\n";
echo "DIAGNOSTIC COMPLETE\n";
echo "Next steps:\n";
echo "1. Check XAMPP Control Panel for Apache/MySQL services\n";
echo "2. Verify all required services are running\n";
echo "3. Test application functionality\n";

// Generate summary
$issues_found = 0;
$warnings = [];

// Check for critical issues
if (!extension_loaded('mysqli')) {
    $warnings[] = "❌ CRITICAL: MySQLi extension missing";
    $issues_found++;
}

if (!file_exists(__DIR__ . '/includes/db_config.php')) {
    $warnings[] = "❌ CRITICAL: Database configuration missing";
    $issues_found++;
}

if ($issues_found > 0) {
    echo "\n⚠️  CRITICAL ISSUES FOUND: $issues_found\n";
    foreach ($warnings as $warning) {
        echo "   $warning\n";
    }
} else {
    echo "\n✅ No critical issues found. Server appears healthy.\n";
}

?>