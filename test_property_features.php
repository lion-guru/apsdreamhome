<?php
/**
 * Test Property Features Script
 * Tests the new property favorites and inquiry system
 */

require_once 'config/bootstrap.php';

echo "🧪 Testing Property Features System:\n\n";

try {
    // Test 1: Check if favorites controller loads
    echo "1. Testing Favorites Controller...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('favorites');
    $output = ob_get_clean();

    if (strpos($output, 'My Favorite Properties') !== false) {
        echo "   ✅ Favorites page loads successfully!\n";
    } else {
        echo "   ❌ Favorites page failed to load\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error loading favorites page: " . $e->getMessage() . "\n";
}

try {
    // Test 2: Test inquiry controller
    echo "2. Testing Inquiry System...\n";

    // Create a test inquiry
    $_POST = [
        'property_id' => '1',
        'subject' => 'Test Inquiry',
        'message' => 'This is a test inquiry message',
        'inquiry_type' => 'general'
    ];

    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('inquiry/submit');
    $output = ob_get_clean();

    if (strpos($output, 'success') !== false) {
        echo "   ✅ Inquiry submission works!\n";
    } else {
        echo "   ❌ Inquiry submission failed\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error with inquiry system: " . $e->getMessage() . "\n";
}

echo "\n🎉 Property Features Testing completed!\n";
?>
