<?php
/**
 * Test PropertyController and property pages
 */

require_once 'config/bootstrap.php';

try {
    echo "Testing PropertyController:\n";

    // Test property listing
    echo "1. Testing property listing page...\n";
    $router = new App\Core\Router();
    ob_start();
    $router->dispatch('properties');
    $output = ob_get_clean();

    if (strpos($output, 'Properties - APS Dream Home') !== false) {
        echo "✅ Property listing page loaded successfully!\n";
    } else {
        echo "❌ Property listing page failed\n";
    }

    // Test property detail page (using a sample ID)
    echo "2. Testing property detail page...\n";
    $_GET['id'] = 1; // Sample property ID
    ob_start();
    $router->dispatch('property');
    $output = ob_get_clean();

    if (strpos($output, 'Property Not Found') === false) {
        echo "✅ Property detail page loaded successfully!\n";
    } else {
        echo "❌ Property detail page failed (expected if property doesn't exist)\n";
    }

    echo "\n🎉 PropertyController tests completed!\n";

} catch (Exception $e) {
    echo '❌ ERROR: ' . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
