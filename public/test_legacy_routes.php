<?php
// Test legacy route processing
require_once __DIR__ . '/../app/core/autoload.php';

use App\Core\App;

// Create app instance
$app = new App(__DIR__ . '/..');

// Load legacy routes manually
$legacyRoutesFile = __DIR__ . '/../routes/web.php';
if (file_exists($legacyRoutesFile)) {
    echo "=== Loading legacy routes ===\n";
    $legacyRoutes = [];
    require $legacyRoutesFile;
    
    echo "Legacy routes structure:\n";
    echo "Public GET routes: " . (isset($legacyRoutes['public']['GET']) ? count($legacyRoutes['public']['GET']) : 0) . "\n";
    echo "Public POST routes: " . (isset($legacyRoutes['public']['POST']) ? count($legacyRoutes['public']['POST']) : 0) . "\n";
    
    if (isset($legacyRoutes['public']['GET']['/test/error/404'])) {
        echo "Found /test/error/404 route: " . $legacyRoutes['public']['GET']['/test/error/404'] . "\n";
    } else {
        echo "Route /test/error/404 not found in legacy routes\n";
    }
}