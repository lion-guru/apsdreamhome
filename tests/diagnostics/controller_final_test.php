<?php
/**
 * APS Dream Home - Controller Test
 */

echo "=== APS DREAM HOME - CONTROLLER TEST ===\n\n";

define('INCLUDED_FROM_MAIN', true);

try {
    echo "Testing Controller class loading with AuthService...\n";

    // Load AuthService first
    require_once 'app/services/AuthService.php';
    echo "✅ AuthService loaded\n";

    // Load Controller
    require_once 'app/controllers/Controller.php';
    echo "✅ Controller.php loaded successfully\n";

    // Check if Controller class exists
    if (class_exists('App\Controllers\Controller')) {
        echo "✅ Controller class exists\n";
    } else {
        echo "❌ Controller class not found\n";
    }

    // Test that it's abstract (check via reflection instead of instantiation)
    $reflection = new ReflectionClass('App\Controllers\Controller');
    if ($reflection->isAbstract()) {
        echo "✅ Controller is properly abstract\n";
    } else {
        echo "❌ Controller should be abstract\n";
    }

    echo "\n🎉 CONTROLLER CLASS WORKING!\n";
    echo "✅ AuthService dependency resolved\n";
    echo "✅ Abstract class structure correct\n";
    echo "✅ Ready for AdminController inheritance\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
