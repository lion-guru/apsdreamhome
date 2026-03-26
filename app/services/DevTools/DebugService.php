<?php
// Debug file to test routing
echo "<h1>Debug Information</h1>";
echo "<p>REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "</p>";
echo "<p>SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "</p>";
echo "<p>HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "</p>";
echo "<p>REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "</p>";

// Test if routes are loaded
echo "<h2>Routes Test</h2>";
try {
    require_once __DIR__ . '/../routes/web.php';
    echo "<p>Routes loaded successfully</p>";
    echo "<p>Total routes: " . $router->getRoutesCount() . "</p>";
} catch (Exception $e) {
    echo "<p>Error loading routes: " . $e->getMessage() . "</p>";
}

// Test if HomeController exists
echo "<h2>HomeController Test</h2>";
try {
    require_once __DIR__ . '/../app/Http/Controllers/HomeController.php';
    $controller = new \App\Http\Controllers\HomeController();
    echo "<p>HomeController created successfully</p>";
} catch (Exception $e) {
    echo "<p>Error creating HomeController: " . $e->getMessage() . "</p>";
}
?>
