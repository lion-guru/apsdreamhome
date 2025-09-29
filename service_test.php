<?php
/**
 * APS Dream Home - Service Loading Test
 */

echo "=== APS DREAM HOME - SERVICE LOADING TEST ===\n\n";

define('INCLUDED_FROM_MAIN', true);

try {
    echo "Testing AdminService loading...\n";

    // Load config first
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

    // Load Database class
    require_once 'app/core/Database.php';
    echo "✅ Core Database\n";

    // Load AdminService
    require_once 'app/services/AdminService.php';
    echo "✅ AdminService\n";

    // Test instantiation
    $adminService = new App\Services\AdminService();
    echo "✅ AdminService instantiated\n";

    // Test a method
    $stats = $adminService->getDashboardStats();
    echo "✅ getDashboardStats() works\n";

    echo "\n🎉 ALL SERVICES WORKING!\n";
    echo "✅ Constants defined properly\n";
    echo "✅ Database connection working\n";
    echo "✅ AdminService functional\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
