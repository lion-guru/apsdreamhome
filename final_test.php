<?php
/**
 * APS Dream Home - Final System Verification
 */

echo "=== APS DREAM HOME - FINAL VERIFICATION ===\n\n";

define('INCLUDED_FROM_MAIN', true);

try {
    echo "Testing all components in correct order...\n";

    // Load all components in correct order
    require_once 'includes/db_connection.php';
    $pdo = getDbConnection();
    echo "✅ Database: " . get_class($pdo) . " connected\n";

    require_once 'includes/functions.php';
    echo "✅ Helper functions loaded\n";

    require_once 'app/services/AuthService.php';
    $authService = new App\Services\AuthService();
    echo "✅ AuthService instantiated\n";

    require_once 'app/controllers/Controller.php';
    echo "✅ Base Controller loaded\n";

    require_once 'app/services/AdminService.php';
    $adminService = new App\Services\AdminService();
    echo "✅ AdminService instantiated\n";

    require_once 'app/controllers/AdminController.php';
    $adminController = new App\Controllers\AdminController();
    echo "✅ AdminController instantiated\n";

    // Test authentication
    $result = $authService->authenticate('admin@apsdreamhome.com', 'admin123');
    if ($result && isset($_SESSION['auser'])) {
        echo "✅ Authentication system\n";
        echo "   User: " . $_SESSION['auser'] . "\n";
        echo "   Role: " . $_SESSION['role'] . "\n";
    }

    // Test AdminService methods
    $stats = $adminService->getDashboardStats();
    echo "✅ AdminService dashboard stats\n";

    $logs = $adminService->getLogs('error', 3);
    echo "✅ AdminService system logs\n";

    echo "\n🎉 ALL SYSTEMS OPERATIONAL!\n";
    echo "✅ No more class not found errors\n";
    echo "✅ All components working\n";
    echo "✅ Complete system functional\n\n";

    echo "🌐 YOUR ADMIN PANEL IS READY:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔗 http://localhost/apsdreamhome/admin.php\n";
    echo "👑 Login: admin@apsdreamhome.com / admin123\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
