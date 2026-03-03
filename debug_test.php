<?php
/**
 * Debug Test Page
 * 
 * Very basic test to check PHP functionality
 */

// Disable error display for clean output
ini_set('display_errors', 0);
error_reporting(0);

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Debug Test</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container mt-4'>\n";
echo "<div class='card'>\n";
echo "<div class='card-header bg-success text-white'>\n";
echo "<h2>🚀 APS Dream Home - Project Preview</h2>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

echo "<div class='alert alert-info'>\n";
echo "<h4>✅ PHP Server Running Successfully!</h4>\n";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>\n";
echo "<p>PHP Version: " . PHP_VERSION . "</p>\n";
echo "<p>Project Path: " . __DIR__ . "</p>\n";
echo "</div>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-6'>\n";
echo "<h5>📁 Project Structure</h5>\n";
echo "<ul class='list-group'>\n";

$dirs = ['admin', 'app', 'config', 'public'];
foreach ($dirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "<li class='list-group-item list-group-item-success'>✅ $dir/</li>\n";
    } else {
        echo "<li class='list-group-item list-group-item-danger'>❌ $dir/</li>\n";
    }
}

echo "</ul>\n";
echo "</div>\n";

echo "<div class='col-md-6'>\n";
echo "<h5>🔧 Admin Components</h5>\n";
echo "<ul class='list-group'>\n";

$files = [
    'admin/dashboard.php',
    'admin/user_management.php',
    'admin/property_management.php',
    'admin/unified_key_management.php'
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<li class='list-group-item list-group-item-success'>✅ " . basename($file) . "</li>\n";
    } else {
        echo "<li class='list-group-item list-group-item-danger'>❌ " . basename($file) . "</li>\n";
    }
}

echo "</ul>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";
echo "<h5>🎮 MVC Architecture</h5>\n";
echo "<div class='row'>\n";

$mvcFiles = [
    'app/Controllers/AdminController.php',
    'app/Models/User.php',
    'app/Models/Property.php',
    'app/Core/Security.php',
    'app/Core/Validator.php'
];

foreach ($mvcFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "<div class='col-md-6 mb-2'>\n";
    echo "<span class='badge " . ($exists ? 'bg-success' : 'bg-danger') . "'>\n";
    echo ($exists ? '✅' : '❌') . " " . basename(dirname($file)) . "/" . basename($file);
    echo "</span>\n";
    echo "</div>\n";
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";
echo "<h5>🔗 Quick Access Links</h5>\n";
echo "<div class='d-grid gap-2 d-md-flex'>\n";

echo "<a href='debug_test.php' class='btn btn-primary'>🏠 Home</a>\n";

if (file_exists(__DIR__ . '/admin/dashboard.php')) {
    echo "<a href='admin/dashboard.php' class='btn btn-success'>📊 Dashboard</a>\n";
}
if (file_exists(__DIR__ . '/admin/user_management.php')) {
    echo "<a href='admin/user_management.php' class='btn btn-info'>👥 Users</a>\n";
}
if (file_exists(__DIR__ . '/admin/property_management.php')) {
    echo "<a href='admin/property_management.php' class='btn btn-warning'>🏠 Properties</a>\n";
}
if (file_exists(__DIR__ . '/admin/unified_key_management.php')) {
    echo "<a href='admin/unified_key_management.php' class='btn btn-secondary'>🔑 Keys</a>\n";
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='text-center'>\n";
echo "<h6>📊 Project Statistics</h6>\n";
echo "<div class='row'>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card'>\n";
echo "<div class='card-body text-center'>\n";
echo "<h3 class='text-primary'>" . count(glob(__DIR__ . '/admin/*.php')) . "</h3>\n";
echo "<small>Admin Files</small>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card'>\n";
echo "<div class='card-body text-center'>\n";
echo "<h3 class='text-success'>" . count(glob(__DIR__ . '/app/**/*.php')) . "</h3>\n";
echo "<small>App Files</small>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card'>\n";
echo "<div class='card-body text-center'>\n";
echo "<h3 class='text-warning'>" . count(glob(__DIR__ . '/config/*.php')) . "</h3>\n";
echo "<small>Config Files</small>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card'>\n";
echo "<div class='card-body text-center'>\n";
echo "<h3 class='text-info'>" . count(glob(__DIR__ . '/**/*.php')) . "</h3>\n";
echo "<small>Total Files</small>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n"; // card-body
echo "</div>\n"; // card
echo "</div>\n"; // container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "</body>\n";
echo "</html>\n";
?>
