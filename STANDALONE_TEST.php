<?php
/**
 * Standalone Test
 * 
 * Completely independent test without any dependencies
 */

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <title>Standalone Test - APS Dream Home</title>\n";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>\n";
echo "</head>\n";
echo "<body class='bg-light'>\n";

echo "<div class='container mt-4'>\n";
echo "<div class='row'>\n";
echo "<div class='col-md-12'>\n";

echo "<div class='card shadow-lg'>\n";
echo "<div class='card-header bg-gradient bg-success text-white'>\n";
echo "<h1><i class='fas fa-rocket'></i> APS Dream Home - Standalone Test</h1>\n";
echo "<p class='mb-0'>Complete Project Preview - No Dependencies</p>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

echo "<div class='alert alert-success border-3'>\n";
echo "<h4><i class='fas fa-check-circle'></i> Server Status: RUNNING</h4>\n";
echo "<div class='row'>\n";
echo "<div class='col-md-6'>\n";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
echo "<p><strong>Memory Usage:</strong> " . round(memory_get_usage() / 1024 / 1024, 2) . " MB</p>\n";
echo "</div>\n";
echo "<div class='col-md-6'>\n";
echo "<p><strong>Project Path:</strong> " . __DIR__ . "</p>\n";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>\n";
echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row mt-4'>\n";
echo "<div class='col-md-6'>\n";
echo "<div class='card border-primary'>\n";
echo "<div class='card-header bg-primary text-white'>\n";
echo "<h5><i class='fas fa-folder'></i> Project Structure</h5>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

$directories = ['admin', 'app', 'config', 'public', 'storage'];
foreach ($directories as $dir) {
    $dirPath = __DIR__ . '/' . $dir;
    $exists = is_dir($dirPath);
    $count = $exists ? count(glob($dirPath . '/*')) : 0;
    
    echo "<div class='d-flex justify-content-between align-items-center mb-2'>\n";
    echo "<span><i class='fas fa-folder" . ($exists ? '' : '-open') . "'></i> $dir/</span>\n";
    echo "<span class='badge " . ($exists ? 'bg-success' : 'bg-danger') . "'>" . ($exists ? 'EXISTS' : 'MISSING') . "</span>\n";
    echo "</div>\n";
    
    if ($exists) {
        echo "<div class='progress mb-3' style='height: 6px;'>\n";
        echo "<div class='progress-bar bg-info' style='width: " . min($count * 2, 100) . "%'></div>\n";
        echo "</div>\n";
        echo "<small class='text-muted'>$count items</small>\n";
    }
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='col-md-6'>\n";
echo "<div class='card border-success'>\n";
echo "<div class='card-header bg-success text-white'>\n";
echo "<h5><i class='fas fa-cogs'></i> Admin Components</h5>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

$adminFiles = [
    'admin/dashboard.php' => ['📊', 'Dashboard'],
    'admin/user_management.php' => ['👥', 'User Management'],
    'admin/property_management.php' => ['🏠', 'Property Management'],
    'admin/unified_key_management.php' => ['🔑', 'Key Management']
];

foreach ($adminFiles as $file => [$icon, $label]) {
    $filePath = __DIR__ . '/' . $file;
    $exists = file_exists($filePath);
    $size = $exists ? filesize($filePath) : 0;
    
    echo "<div class='d-flex justify-content-between align-items-center mb-2'>\n";
    echo "<span>$icon $label</span>\n";
    echo "<span class='badge " . ($exists ? 'bg-success' : 'bg-warning') . "'>" . ($exists ? 'READY' : 'MISSING') . "</span>\n";
    echo "</div>\n";
    
    if ($exists) {
        echo "<small class='text-muted'>Size: " . round($size / 1024, 2) . " KB</small>\n";
    }
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row mt-4'>\n";
echo "<div class='col-md-12'>\n";
echo "<div class='card border-info'>\n";
echo "<div class='card-header bg-info text-white'>\n";
echo "<h5><i class='fas fa-code'></i> MVC Architecture Components</h5>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

$mvcComponents = [
    'app/Controllers/AdminController.php' => ['🎮', 'Admin Controller'],
    'app/Models/User.php' => ['👤', 'User Model'],
    'app/Models/Property.php' => ['🏠', 'Property Model'],
    'app/Core/Security.php' => ['🔒', 'Security Class'],
    'app/Core/Validator.php' => ['✅', 'Validator Class'],
    'app/Core/Database/Model.php' => ['🗄️', 'Database Model']
];

echo "<div class='row'>\n";
foreach ($mvcComponents as $file => [$icon, $label]) {
    $filePath = __DIR__ . '/' . $file;
    $exists = file_exists($filePath);
    
    echo "<div class='col-md-6 mb-3'>\n";
    echo "<div class='card h-100 border-light'>\n";
    echo "<div class='card-body text-center'>\n";
    echo "<h5 class='card-title'>$icon $label</h5>\n";
    echo "<p class='card-text'>\n";
    echo "<span class='badge " . ($exists ? 'bg-success' : 'bg-danger') . " fs-6'>\n";
    echo ($exists ? '✅ IMPLEMENTED' : '❌ MISSING') . "\n";
    echo "</span>\n";
    echo "</p>\n";
    if ($exists) {
        echo "<small class='text-muted'>" . basename(dirname($file)) . "/" . basename($file) . "</small>\n";
    }
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}
echo "</div>\n";

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row mt-4'>\n";
echo "<div class='col-md-12'>\n";
echo "<div class='card border-warning'>\n";
echo "<div class='card-header bg-warning text-dark'>\n";
echo "<h5><i class='fas fa-link'></i> Quick Navigation & Testing</h5>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

echo "<div class='row'>\n";
echo "<div class='col-md-8'>\n";
echo "<h6>Admin System Links:</h6>\n";
echo "<div class='btn-group-vertical d-grid gap-2' role='group'>\n";

$adminLinks = [
    'STANDALONE_TEST.php' => ['🏠', 'Home (Current)', 'primary'],
    'admin/dashboard.php' => ['📊', 'Admin Dashboard', 'success'],
    'admin/user_management.php' => ['👥', 'User Management', 'info'],
    'admin/property_management.php' => ['🏠', 'Property Management', 'warning'],
    'admin/unified_key_management.php' => ['🔑', 'Key Management', 'secondary']
];

foreach ($adminLinks as $link => [$icon, $label, $color]) {
    $filePath = __DIR__ . '/' . $link;
    $exists = file_exists($filePath);
    
    if ($exists) {
        echo "<a href='$link' class='btn btn-$color' target='_blank'>$icon $label</a>\n";
    } else {
        echo "<button class='btn btn-outline-secondary' disabled>$icon $label (Missing)</button>\n";
    }
}

echo "</div>\n";
echo "</div>\n";

echo "<div class='col-md-4'>\n";
echo "<h6>Project Statistics:</h6>\n";
echo "<div class='row g-2'>\n";

$stats = [
    'Admin Files' => count(glob(__DIR__ . '/admin/*.php')),
    'App Files' => count(glob(__DIR__ . '/app/**/*.php')),
    'Config Files' => count(glob(__DIR__ . '/config/*.php')),
    'Total PHP Files' => count(glob(__DIR__ . '/**/*.php'))
];

$colors = ['primary', 'success', 'info', 'dark'];
foreach ($stats as $label => $count) {
    $color = array_shift($colors);
    echo "<div class='col-6'>\n";
    echo "<div class='card text-center'>\n";
    echo "<div class='card-body p-2'>\n";
    echo "<h4 class='text-$color'>$count</h4>\n";
    echo "<small>$label</small>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "<div class='row mt-4'>\n";
echo "<div class='col-md-12'>\n";
echo "<div class='card border-secondary'>\n";
echo "<div class='card-header bg-secondary text-white'>\n";
echo "<h5><i class='fas fa-chart-line'></i> Project Status Summary</h5>\n";
echo "</div>\n";
echo "<div class='card-body'>\n";

$totalFiles = count(glob(__DIR__ . '/**/*.php'));
$adminFiles = count(glob(__DIR__ . '/admin/*.php'));
$appFiles = count(glob(__DIR__ . '/app/**/*.php'));
$configFiles = count(glob(__DIR__ . '/config/*.php'));

$completion = [
    'admin_system' => $adminFiles >= 4 ? 100 : ($adminFiles / 4 * 100),
    'mvc_architecture' => $appFiles >= 10 ? 100 : ($appFiles / 10 * 100),
    'configuration' => $configFiles >= 5 ? 100 : ($configFiles / 5 * 100),
    'overall' => ($totalFiles >= 50 ? 100 : ($totalFiles / 50 * 100))
];

echo "<div class='row'>\n";
foreach ($completion as $component => $percentage) {
    $label = ucwords(str_replace('_', ' ', $component));
    $color = $percentage >= 100 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
    
    echo "<div class='col-md-3 mb-3'>\n";
    echo "<div class='card text-center'>\n";
    echo "<div class='card-body'>\n";
    echo "<h6 class='card-title'>$label</h6>\n";
    echo "<div class='progress mb-2' style='height: 10px;'>\n";
    echo "<div class='progress-bar bg-$color' style='width: " . min($percentage, 100) . "%'></div>\n";
    echo "</div>\n";
    echo "<span class='badge bg-$color'>" . round($percentage, 1) . "%</span>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}
echo "</div>\n";

echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

echo "</div>\n"; // card-body
echo "</div>\n"; // card
echo "</div>\n"; // col-md-12
echo "</div>\n"; // row
echo "</div>\n"; // container

echo "<div class='container-fluid mt-4 bg-dark text-white py-3'>\n";
echo "<div class='text-center'>\n";
echo "<h6><i class='fas fa-info-circle'></i> APS Dream Home Project Status</h6>\n";
echo "<p class='mb-0'>Complete Real Estate Management System - Admin Dashboard • User Management • Property Management • Security System</p>\n";
echo "<small class='text-muted'>Generated: " . date('Y-m-d H:i:s') . " | PHP Version: " . PHP_VERSION . " | Total Files: $totalFiles</small>\n";
echo "</div>\n";
echo "</div>\n";

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>\n";
echo "</body>\n";
echo "</html>\n";
?>
