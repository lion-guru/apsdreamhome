<?php
// Test bootstrap and basic functionality
echo "Testing APS Dream Home Bootstrap...\n\n";

try {
    echo "1. Loading bootstrap...\n";
    require_once __DIR__ . '/config/bootstrap.php';
    echo "✓ Bootstrap loaded\n\n";
    
    echo "2. Checking constants...\n";
    echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "\n";
    echo "APP_ROOT: " . (defined('APP_ROOT') ? APP_ROOT : 'NOT DEFINED') . "\n";
    echo "✓ Constants defined\n\n";
    
    echo "3. Testing database connection...\n";
    require_once __DIR__ . '/app/Core/Database/Database.php';
    $db = \App\Core\Database\Database::getInstance();
    echo "✓ Database connected\n\n";
    
    echo "4. Testing router...\n";
    require_once __DIR__ . '/routes/router.php';
    $router = new Router();
    echo "✓ Router instantiated\n\n";
    
    echo "5. Loading routes...\n";
    require_once __DIR__ . '/routes/web.php';
    echo "✓ Routes loaded\n\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
