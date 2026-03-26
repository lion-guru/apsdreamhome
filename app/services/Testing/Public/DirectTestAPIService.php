<?php
// Direct test without routing
echo "<h1>Direct Test - No Routing</h1>";
echo "<p>This file loads directly without going through the router</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test if we can include the HomeController
echo "<h2>Testing HomeController</h2>";
try {
    define('APS_ROOT', dirname(__DIR__));
    define('BASE_URL', 'http://localhost:8000');
    
    // Include BaseController first
    require_once APS_ROOT . '/app/Http/Controllers/BaseController.php';
    
    // Then include HomeController
    require_once APS_ROOT . '/app/Http/Controllers/HomeController.php';
    
    $homeController = new \App\Http\Controllers\HomeController();
    echo "<p>✅ HomeController loaded successfully</p>";
    
    // Test the index method
    ob_start();
    $homeController->index();
    $output = ob_get_clean();
    
    echo "<h2>HomeController Output</h2>";
    echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
    echo $output;
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
