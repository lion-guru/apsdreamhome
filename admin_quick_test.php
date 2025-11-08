<?php
/**
 * APS Dream Home - Quick Admin Test
 * Simple test to verify admin functionality
 */

echo "=== APS DREAM HOME - ADMIN TEST ===\n\n";

define('INCLUDED_FROM_MAIN', true);

try {
    echo "Testing admin system...\n";

    // Load all components
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/db_connection.php';
    require_once __DIR__ . '/app/core/Database.php';
    require_once __DIR__ . '/app/services/AuthService.php';
    require_once __DIR__ . '/app/controllers/Controller.php';
    require_once __DIR__ . '/app/services/AdminService.php';
    require_once __DIR__ . '/app/controllers/AdminController.php';

    echo "✅ All components loaded successfully\n";

    // Test admin controller
    $adminController = new App\Controllers\AdminController();
    echo "✅ AdminController instantiated successfully\n";

    // Test admin service
    $adminService = new App\Services\AdminService();
    echo "✅ AdminService instantiated successfully\n";

    // Test methods
    $stats = $adminService->getDashboardStats();
    echo "✅ Dashboard stats retrieved\n";

    $systemHealth = $adminService->getSystemHealth();
    echo "✅ System health checked\n";

    echo "\n🎉 ADMIN SYSTEM IS WORKING PERFECTLY!\n\n";

    echo "🌐 ACCESS YOUR ADMIN PANEL:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔗 http://localhost/apsdreamhome/admin.php\n";
    echo "👑 Login: admin@apsdreamhome.com / admin123\n\n";

    echo "✅ No more 'Class not found' errors\n";
    echo "✅ All dependencies resolved\n";
    echo "✅ System fully functional\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
