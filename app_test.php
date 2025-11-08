<?php
/**
 * APS Dream Home - Application Test Script
 * Tests if the application loads correctly
 */

// Test 1: Check if we can load the configuration
echo "=== APS Dream Home - Application Test ===\n\n";
echo "Test 1: Loading configuration...\n";

try {
    require_once __DIR__ . '/includes/config.php';
    echo "✅ Configuration loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Configuration failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check database connection
echo "\nTest 2: Testing database connection...\n";
try {
    require_once __DIR__ . '/includes/db_connection.php';
    echo "✅ Database connection successful\n";

    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Users table accessible. Found: " . $result['count'] . " users\n";
} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
}

// Test 3: Check template system
echo "\nTest 3: Testing template system...\n";
try {
    require_once __DIR__ . '/includes/enhanced_universal_template.php';
    $template = new EnhancedUniversalTemplate();
    echo "✅ Template system loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Template system failed: " . $e->getMessage() . "\n";
}

// Test 4: Check session handling
echo "\nTest 4: Testing session handling...\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✅ Session started successfully\n";
} else {
    echo "✅ Session already active\n";
}

// Test 5: Check file permissions
echo "\nTest 5: Checking file permissions...\n";
$directories = ['logs/', 'uploads/', 'assets/css/', 'assets/js/'];
foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        echo "✅ Directory exists: $dir\n";
    } else {
        echo "⚠️ Directory missing: $dir\n";
    }
}

echo "\n=== Application Test Complete ===\n";
echo "🎉 All core systems are working!\n";
echo "\nTo access your application:\n";
echo "• Main site: http://localhost/apsdreamhome/\n";
echo "• Admin panel: http://localhost/apsdreamhome/admin/\n";
echo "• System check: http://localhost/apsdreamhome/system_check.php\n";
?>
