<?php
/**
 * Test Index - Direct Homepage Loading
 */

// Define constants
define('APS_ROOT', dirname(__DIR__));
define('BASE_URL', 'http://localhost:8000');

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>APS Dream Home - Test</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }";
echo ".test-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }";
echo ".success { color: #28a745; }";
echo ".error { color: #dc3545; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🏠 APS Dream Home - Testing Mode</h1>";

// Test 1: Basic PHP
echo "<div class='test-section'>";
echo "<h3>✅ PHP Server Test</h3>";
echo "<p class='success'>PHP is working correctly!</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "</div>";

// Test 2: File Structure
echo "<div class='test-section'>";
echo "<h3>📁 File Structure Test</h3>";
$homepageView = APS_ROOT . '/app/views/pages/index.php';
if (file_exists($homepageView)) {
    echo "<p class='success'>✅ Homepage view exists: " . basename($homepageView) . "</p>";
} else {
    echo "<p class='error'>❌ Homepage view missing: " . $homepageView . "</p>";
}

$homeController = APS_ROOT . '/app/Http/Controllers/HomeController.php';
if (file_exists($homeController)) {
    echo "<p class='success'>✅ HomeController exists</p>";
} else {
    echo "<p class='error'>❌ HomeController missing</p>";
}
echo "</div>";

// Test 3: Direct Homepage Content
echo "<div class='test-section'>";
echo "<h3>🏠 Homepage Content Test</h3>";
try {
    // Simulate homepage data
    $page_title = 'Welcome to APS Dream Home';
    $page_description = 'Discover premium properties and find your dream home';
    
    // Load the homepage view
    $viewFile = APS_ROOT . '/app/views/pages/index.php';
    if (file_exists($viewFile)) {
        echo "<p class='success'>✅ Loading homepage view...</p>";
        include $viewFile;
    } else {
        echo "<p class='error'>❌ Cannot load homepage view</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Navigation Test
echo "<div class='test-section'>";
echo "<h3>🔗 Navigation Test</h3>";
echo "<p><a href='" . BASE_URL . "' class='btn btn-primary'>🏠 Homepage</a></p>";
echo "<p><a href='" . BASE_URL . "/admin/dashboard' class='btn btn-secondary'>⚙️ Admin Dashboard</a></p>";
echo "<p><a href='" . BASE_URL . "/properties' class='btn btn-info'>🏢 Properties</a></p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
