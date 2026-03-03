<?php
/**
 * Simple Test Page
 * 
 * Basic functionality test without complex dependencies
 */

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>APS Dream Home - Simple Test</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body class='bg-light'>\n";

echo "<div class='container mt-4'>\n";
echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";

echo "<div class='card shadow'>\n";
echo "<div class='card-header bg-primary text-white'>\n";
echo "<h1><i class='fas fa-home'></i> APS Dream Home - Project Preview</h1>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-6'>\n";
echo "<h3><i class='fas fa-check-circle text-success'></i> System Status</h3>\n";
echo "<ul class='list-group'>\n";
echo "<li class='list-group-item'><i class='fas fa-server text-success'></i> PHP Server: Running</li>\n";
echo "<li class='list-group-item'><i class='fas fa-code text-success'></i> PHP Version: " . PHP_VERSION . "</li>\n";
echo "<li class='list-group-item'><i class='fas fa-folder text-success'></i> Project Path: " . __DIR__ . "</li>\n";
echo "<li class='list-group-item'><i class='fas fa-clock text-success'></i> Current Time: " . date('Y-m-d H:i:s') . "</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<div class='col-md-6'>\n";
echo "<h3><i class='fas fa-folder-open text-info'></i> Project Structure</h3>\n";
echo "<ul class='list-group'>\n";

// Check project structure
$directories = ['admin', 'app', 'config', 'public'];
foreach ($directories as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "<li class='list-group-item'><i class='fas fa-check text-success'></i> $dir/ directory exists</li>\n";
    } else {
        echo "<li class='list-group-item'><i class='fas fa-times text-danger'></i> $dir/ directory missing</li>\n";
    }
}

echo "</ul>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";
echo "<h3><i class='fas fa-cogs text-warning'></i> Admin System Components</h3>\n";
echo "<div class='row'>\n";

// Check admin files
$adminFiles = [
    'admin/dashboard.php' => '📊 Dashboard',
    'admin/user_management.php' => '👥 User Management',
    'admin/property_management.php' => '🏠 Property Management',
    'admin/unified_key_management.php' => '🔑 Key Management'
];

foreach ($adminFiles as $file => $label) {
    echo "<div class='col-md-3 mb-3'>\n";
    echo "<div class='card'>\n";
    echo "<div class='card-body text-center'>\n";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<h5 class='card-title text-success'>$label</h5>\n";
        echo "<p class='card-text'><i class='fas fa-check-circle text-success'></i> Available</p>\n";
        echo "<a href='$file' class='btn btn-success btn-sm' target='_blank'>Open</a>\n";
    } else {
        echo "<h5 class='card-title text-danger'>$label</h5>\n";
        echo "<p class='card-text'><i class='fas fa-times-circle text-danger'></i> Missing</p>\n";
        echo "<button class='btn btn-danger btn-sm' disabled>N/A</button>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";
echo "<h3><i class='fas fa-code text-primary'></i> MVC Architecture Components</h3>\n";
echo "<div class='row'>\n";

// Check MVC files
$mvcFiles = [
    'app/Controllers/AdminController.php' => '🎮 Admin Controller',
    'app/Models/User.php' => '👤 User Model',
    'app/Models/Property.php' => '🏠 Property Model',
    'app/Core/Security.php' => '🔒 Security Class',
    'app/Core/Validator.php' => '✅ Validator Class'
];

foreach ($mvcFiles as $file => $label) {
    echo "<div class='col-md-6 mb-3'>\n";
    echo "<div class='card'>\n";
    echo "<div class='card-body'>\n";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<h6 class='card-title text-success'>$label</h6>\n";
        echo "<p class='card-text'><i class='fas fa-check-circle text-success'></i> Implemented</p>\n";
    } else {
        echo "<h6 class='card-title text-danger'>$label</h6>\n";
        echo "<p class='card-text'><i class='fas fa-times-circle text-danger'></i> Missing</p>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";
echo "<h3><i class='fas fa-link text-info'></i> Quick Navigation</h3>\n";
echo "<div class='btn-group' role='group'>\n";
echo "<a href='simple_test.php' class='btn btn-primary active'>🏠 Home</a>\n";
echo "<a href='admin/dashboard.php' class='btn btn-success'>📊 Admin Dashboard</a>\n";
echo "<a href='admin/user_management.php' class='btn btn-info'>👥 Users</a>\n";
echo "<a href='admin/property_management.php' class='btn btn-warning'>🏠 Properties</a>\n";
echo "<a href='admin/unified_key_management.php' class='btn btn-secondary'>🔑 Keys</a>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<hr>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";
echo "<h3><i class='fas fa-chart-line text-success'></i> Project Statistics</h3>\n";
echo "<div class='row'>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card text-center'>\n";
echo "<div class='card-body'>\n";
echo "<h4 class='text-primary'>" . count(glob(__DIR__ . '/admin/*.php')) . "</h4>\n";
echo "<p class='card-text'>Admin Files</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card text-center'>\n";
echo "<div class='card-body'>\n";
echo "<h4 class='text-success'>" . count(glob(__DIR__ . '/app/**/*.php')) . "</h4>\n";
echo "<p class='card-text'>App Files</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card text-center'>\n";
echo "<div class='card-body'>\n";
echo "<h4 class='text-warning'>" . count(glob(__DIR__ . '/config/*.php')) . "</h4>\n";
echo "<p class='card-text'>Config Files</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "<div class='col-md-3'>\n";
echo "<div class='card text-center'>\n";
echo "<div class='card-body'>\n";
echo "<h4 class='text-info'>" . count(glob(__DIR__ . '/**/*.php', GLOB_NOSORT)) . "</h4>\n";
echo "<p class='card-text'>Total PHP Files</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n"; // Close card-body
echo "</div>\n"; // Close card
echo "</div>\n"; // Close col-md-12
echo "</div>\n"; // Close row
echo "</div>\n"; // Close container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "</body>\n";
echo "</html>\n";
?>
