<?php
/**
 * Test AuthController
 */

require_once 'config/bootstrap.php';

try {
    echo "Testing AuthController:\n";

    // Test login page
    echo "1. Testing login page...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('login');
    $output = ob_get_clean();

    if (strpos($output, 'Welcome Back') !== false) {
        echo "✅ Login page loaded successfully!\n";
    } else {
        echo "❌ Login page failed\n";
    }

    // Test register page
    echo "2. Testing register page...\n";
    ob_start();
    $router->dispatch('register');
    $output = ob_get_clean();

    if (strpos($output, 'Join APS Dream Home') !== false) {
        echo "✅ Register page loaded successfully!\n";
    } else {
        echo "❌ Register page failed\n";
    }

    echo "\n🎉 AuthController tests completed!\n";

} catch (Exception $e) {
    echo '❌ ERROR: ' . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
