<?php
/**
 * Test AdminController
 */

require_once 'config/bootstrap.php';

try {
    echo "Testing AdminController:\n";

    // Test admin dashboard (without authentication check for testing)
    echo "1. Testing admin dashboard...\n";
    $_SESSION['user_role'] = 'admin'; // Mock admin session

    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('admin');
    $output = ob_get_clean();

    if (strpos($output, 'Admin Dashboard') !== false) {
        echo "✅ Admin dashboard loaded successfully!\n";
    } else {
        echo "❌ Admin dashboard failed\n";
    }

    // Test admin properties
    echo "2. Testing admin properties...\n";
    ob_start();
    $router->dispatch('admin/properties');
    $output = ob_get_clean();

    if (strpos($output, 'Properties Management') !== false) {
        echo "✅ Admin properties page loaded successfully!\n";
    } else {
        echo "❌ Admin properties page failed\n";
    }

    echo "\n🎉 AdminController tests completed!\n";

} catch (Exception $e) {
    echo '❌ ERROR: ' . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
