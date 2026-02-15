<?php
/**
 * Test Admin Reports Script
 * Tests the new admin reports and analytics system
 */

require_once 'config/bootstrap.php';

echo "🧪 Testing Admin Reports System:\n\n";

try {
    // Test 1: Check if admin reports controller loads
    echo "1. Testing Admin Reports Dashboard...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('admin/reports');
    $output = ob_get_clean();

    if (strpos($output, 'Reports & Analytics') !== false) {
        echo "   ✅ Admin reports dashboard loads successfully!\n";
    } else {
        echo "   ❌ Admin reports dashboard failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading admin reports: " . $e->getMessage() . "\n";
}

try {
    // Test 2: Test property reports
    echo "2. Testing Property Reports...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('admin/reports/properties');
    $output = ob_get_clean();

    if (strpos($output, 'Property Performance Reports') !== false) {
        echo "   ✅ Property reports loads successfully!\n";
    } else {
        echo "   ❌ Property reports failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading property reports: " . $e->getMessage() . "\n";
}

try {
    // Test 3: Test user analytics
    echo "3. Testing User Analytics...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('admin/reports/users');
    $output = ob_get_clean();

    if (strpos($output, 'User Analytics Reports') !== false) {
        echo "   ✅ User analytics loads successfully!\n";
    } else {
        echo "   ❌ User analytics failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading user analytics: " . $e->getMessage() . "\n";
}

try {
    // Test 4: Test financial reports
    echo "4. Testing Financial Reports...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('admin/reports/financial');
    $output = ob_get_clean();

    if (strpos($output, 'Financial Reports') !== false) {
        echo "   ✅ Financial reports loads successfully!\n";
    } else {
        echo "   ❌ Financial reports failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading financial reports: " . $e->getMessage() . "\n";
}

try {
    // Test 5: Test inquiry analytics
    echo "5. Testing Inquiry Analytics...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('admin/reports/inquiries');
    $output = ob_get_clean();

    if (strpos($output, 'Inquiry Analytics') !== false) {
        echo "   ✅ Inquiry analytics loads successfully!\n";
    } else {
        echo "   ❌ Inquiry analytics failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading inquiry analytics: " . $e->getMessage() . "\n";
}

echo "\n🎉 Admin Reports Testing completed!\n";
?>
