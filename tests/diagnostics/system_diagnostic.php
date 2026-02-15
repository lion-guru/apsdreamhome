<?php
/**
 * System Diagnostic Script
 * Identifies and reports system issues
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>System Diagnostic - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>🔍 System Diagnostic Report</h1>";

echo "<h2>📋 File System Check</h2>";

// Check if required files exist
$files_to_check = [
    'includes/security/security_manager.php',
    'includes/performance_manager.php',
    'includes/event_system.php',
    'includes/templates/dynamic_header.php',
    'includes/templates/dynamic_footer.php',
    'includes/templates/base_template.php',
    'includes/templates/static_header.php',
    'includes/templates/static_footer.php',
    'includes/db_config.php',
    'includes/db_settings.php',
    'api/test.php'
];

echo "<ul>";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<li class='success'>✅ $file - EXISTS</li>";
    } else {
        echo "<li class='error'>❌ $file - MISSING</li>";
    }
}
echo "</ul>";

echo "<h2>🔧 PHP Configuration Check</h2>";
echo "<ul>";
echo "<li class='info'>PHP Version: " . PHP_VERSION . "</li>";
echo "<li class='info'>Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</li>";
echo "<li class='info'>Error Reporting: " . ini_get('error_reporting') . "</li>";
echo "<li class='info'>Memory Limit: " . ini_get('memory_limit') . "</li>";
echo "<li class='info'>Max Execution Time: " . ini_get('max_execution_time') . "</li>";
echo "</ul>";

echo "<h2>🗄️ Database Connection Test</h2>";
try {
    require_once 'includes/db_config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    echo "<p class='success'>✅ Database connection successful</p>";
    $conn->close();
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>📁 Directory Permissions</h2>";
$dirs_to_check = [
    'includes/security',
    'includes/logs',
    'includes/templates',
    'api'
];

echo "<ul>";
foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        echo "<li class='success'>✅ $dir - EXISTS</li>";
        if (is_writable($dir)) {
            echo "<li class='success'>✅ $dir - WRITABLE</li>";
        } else {
            echo "<li class='warning'>⚠️ $dir - NOT WRITABLE</li>";
        }
    } else {
        echo "<li class='error'>❌ $dir - MISSING</li>";
    }
}
echo "</ul>";

echo "<h2>🔐 Security Check</h2>";
try {
    require_once 'includes/security/security_manager.php';
    $security = new SecurityManager();
    echo "<p class='success'>✅ Security Manager loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Security Manager error: " . $e->getMessage() . "</p>";
}

echo "<h2>⚡ Performance Check</h2>";
try {
    require_once 'includes/performance_manager.php';
    echo "<p class='success'>✅ Performance Manager loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Performance Manager error: " . $e->getMessage() . "</p>";
}

echo "<h2>📡 Event System Check</h2>";
try {
    require_once 'includes/event_system.php';
    echo "<p class='success'>✅ Event System loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Event System error: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Final Assessment</h2>";
echo "<p>This diagnostic report will help identify any system issues.</p>";
echo "</body>
</html>";
?>
