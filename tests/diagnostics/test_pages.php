<?php
/**
 * Test About and Contact pages
 */

require_once 'config/bootstrap.php';

try {
    echo "Testing About Page:\n";
    $router = new App\Core\Router();
    $router->dispatch('about');
    echo "✅ About page loaded successfully!\n\n";

    echo "Testing Contact Page:\n";
    $router->dispatch('contact');
    echo "✅ Contact page loaded successfully!\n\n";

    echo "🎉 All pages working correctly!\n";

} catch (Exception $e) {
    echo '❌ ERROR: ' . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
