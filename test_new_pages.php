<?php
/**
 * Test New Pages Script
 * Tests the new services and team pages
 */

require_once 'config/bootstrap.php';

echo "🧪 Testing New Pages:\n\n";

try {
    // Test services page
    echo "1. Testing Services Page...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('services');
    $output = ob_get_clean();

    if (strpos($output, 'Our Services') !== false) {
        echo "   ✅ Services page loaded successfully!\n";
    } else {
        echo "   ❌ Services page failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading services page: " . $e->getMessage() . "\n";
}

try {
    // Test team page
    echo "2. Testing Team Page...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('team');
    $output = ob_get_clean();

    if (strpos($output, 'Meet Our Team') !== false) {
        echo "   ✅ Team page loaded successfully!\n";
    } else {
        echo "   ❌ Team page failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading team page: " . $e->getMessage() . "\n";
}

echo "\n🎉 Testing completed!\n";
?>
