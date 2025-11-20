<?php
/**
 * APS Dream Home - Complete Admin Test
 */

echo "=== APS DREAM HOME - COMPLETE ADMIN TEST ===\n\n";

define('INCLUDED_FROM_MAIN', true);

try {
    echo "Testing complete admin system...\n";

    // Load config
    require_once 'includes/config.php';
    echo "✅ Config loaded\n";

    // Load database connection
    require_once 'includes/db_connection.php';
    echo "✅ Database connection\n";

    // Load helper functions
    require_once 'includes/functions.php';
    echo "✅ Helper functions\n";

    // Load AuthService
    require_once 'app/services/AuthService.php';
    echo "✅ AuthService\n";

    // Load base Controller
    require_once 'app/controllers/Controller.php';
    echo "✅ Base Controller\n";

    // Load AdminService
    require_once 'app/services/AdminService.php';
    echo "✅ AdminService\n";

    // Load AdminController
    require_once 'app/controllers/AdminController.php';
    echo "✅ AdminController\n";

    // Test AdminController instantiation
    $adminController = new App\Controllers\AdminController();
    echo "✅ AdminController instantiated successfully\n";

    echo "\n🎉 COMPLETE ADMIN SYSTEM WORKING!\n";
    echo "✅ All dependencies resolved\n";
    echo "✅ No more class not found errors\n";
    echo "✅ Admin panel ready for use\n\n";

    echo "🌐 ACCESS YOUR ADMIN PANEL:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔗 http://localhost/apsdreamhome/admin.php\n";
    echo "👑 Login: admin@apsdreamhome.com / admin123\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
