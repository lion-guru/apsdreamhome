<?php
/**
 * APS Dream Home - Authentication Test
 * Tests the login functionality
 */

echo "=== APS DREAM HOME - AUTHENTICATION TEST ===\n\n";

define('INCLUDED_FROM_MAIN', true);

try {
    echo "Testing authentication components...\n";

    // Test 1: Load required files
    require_once __DIR__ . '/includes/config.php';
    echo "✅ Configuration loaded\n";

    require_once __DIR__ . '/includes/db_connection.php';
    echo "✅ Database connection\n";

    require_once __DIR__ . '/app/controllers/Controller.php';
    echo "✅ Base Controller\n";

    require_once __DIR__ . '/app/controllers/AdminController.php';
    echo "✅ AdminController loaded\n";

    // Test 2: Simulate POST data
    $_POST = [
        'email' => 'admin@apsdreamhome.com',
        'password' => 'admin123'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';

    echo "\nSimulating login form submission...\n";
    echo "Email: " . $_POST['email'] . "\n";
    echo "Password: " . $_POST['password'] . "\n";

    // Test 3: Create controller and authenticate
    $adminController = new App\Controllers\AdminController();

    // Simulate what admin.php does
    if (isset($_POST['email']) && isset($_POST['password'])) {
        echo "✅ POST data detected, calling authenticate()\n";
        $adminController->authenticate();
    }

    echo "\n🎉 AUTHENTICATION TEST COMPLETED!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
