<?php
/**
 * Comprehensive Error Diagnostic
 * Identifies the exact cause of the Internal Server Error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Error Diagnostic - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 30px; }
        .error { color: red; background: #ffe6e6; padding: 15px; margin: 10px 0; border-left: 4px solid #ff4444; border-radius: 5px; }
        .success { color: green; background: #e6ffe6; padding: 15px; margin: 10px 0; border-left: 4px solid #44ff44; border-radius: 5px; }
        .warning { color: orange; background: #fff5e6; padding: 15px; margin: 10px 0; border-left: 4px solid #ffaa44; border-radius: 5px; }
        .info { color: blue; background: #e6f3ff; padding: 15px; margin: 10px 0; border-left: 4px solid #4488ff; border-radius: 5px; }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 COMPREHENSIVE ERROR DIAGNOSTIC</h1>
            <p>APS Dream Home - Internal Server Error Analysis</p>
        </div>";

echo "<div class='info'>";
echo "<h3>🔧 System Information</h3>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>📋 File Existence Check</h3>";

// Check critical files
$critical_files = [
    'index.php',
    'includes/db_config.php',
    'includes/db_settings.php',
    'includes/security/security_manager.php',
    'includes/performance_manager.php',
    'includes/event_system.php',
    'includes/templates/dynamic_header.php',
    'includes/templates/dynamic_footer.php'
];

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px;'>";
foreach ($critical_files as $file) {
    $exists = file_exists($file);
    $status = $exists ? 'success' : 'error';
    echo "<div class='$status'>$file - " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "</div>";
}
echo "</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>🗄️ Database Connection Test</h3>";
try {
    require_once 'includes/db_config.php';

    echo "<div class='success'>✅ Database config loaded successfully</div>";

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<div class='success'>✅ Database connection established</div>";
    echo "<div class='info'>Database: $DB_NAME on $DB_HOST</div>";

    $conn->close();
} catch (Exception $e) {
    echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h3>🔐 Security Manager Test</h3>";
try {
    require_once 'includes/security/security_manager.php';
    $security = new SecurityManager();
    echo "<div class='success'>✅ Security Manager instantiated successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Security Manager Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Error occurred in: " . $e->getFile() . " at line " . $e->getLine() . "</div>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h3>📁 Directory Permissions</h3>";
$directories = ['logs', 'includes', 'includes/security', 'includes/templates', 'api', 'uploads'];
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;'>";
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        $status = $writable ? 'success' : 'warning';
        echo "<div class='$status'>$dir - " . ($writable ? '✅ WRITABLE' : '⚠️ NOT WRITABLE') . "</div>";
    } else {
        echo "<div class='error'>$dir - ❌ MISSING</div>";
    }
}
echo "</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>🎯 Index.php Load Test</h3>";
try {
    ob_start();
    include 'index.php';
    $output = ob_get_clean();

    if (strlen($output) > 0) {
        echo "<div class='success'>✅ Index.php loaded successfully</div>";
        echo "<div class='info'>Output size: " . number_format(strlen($output)) . " characters</div>";

        // Show first 500 characters of output
        echo "<div class='info'>";
        echo "<h4>Output Preview (first 500 chars):</h4>";
        echo "<div class='code'>" . htmlspecialchars(substr($output, 0, 500)) . "...</div>";
        echo "</div>";
    } else {
        echo "<div class='warning'>⚠️ Index.php loaded but produced no output</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Index.php Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Error in file: " . $e->getFile() . " at line " . $e->getLine() . "</div>";
    echo "<div class='code'>Stack trace: " . $e->getTraceAsString() . "</div>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h3>⚙️ PHP Configuration</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 8px; border: 1px solid #ddd;'>Setting</th><th style='padding: 8px; border: 1px solid #ddd;'>Value</th></tr>";

$php_settings = [
    'display_errors',
    'error_reporting',
    'memory_limit',
    'max_execution_time',
    'post_max_size',
    'upload_max_filesize',
    'file_uploads',
    'session.save_path',
    'include_path'
];

foreach ($php_settings as $setting) {
    $value = ini_get($setting);
    echo "<tr><td style='padding: 8px; border: 1px solid #ddd;'>$setting</td><td style='padding: 8px; border: 1px solid #ddd;'>$value</td></tr>";
}
echo "</table>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>🔍 Error Log Analysis</h3>";
try {
    $error_log = file_get_contents('c:/xampp/apache/logs/error.log');
    $lines = explode("\n", $error_log);
    $recent_errors = array_slice($lines, -20);

    echo "<div class='info'>";
    echo "<h4>Recent Apache Errors (last 20 lines):</h4>";
    echo "<div class='code'>" . htmlspecialchars(implode("\n", $recent_errors)) . "</div>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='warning'>⚠️ Could not read error log: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h3>🎯 Final Diagnosis</h3>";
echo "<div class='info'>";
echo "<p>Based on the diagnostic results above, the most likely causes of the Internal Server Error are:</p>";
echo "<ol>";
echo "<li><strong>Database Connection Issues:</strong> Check if all database files are properly configured</li>";
echo "<li><strong>Missing Dependencies:</strong> Ensure all required files exist and are accessible</li>";
echo "<li><strong>File Permissions:</strong> Verify that all directories have proper write permissions</li>";
echo "<li><strong>PHP Configuration:</strong> Check PHP settings for any restrictions</li>";
echo "<li><strong>Memory/Timeout Issues:</strong> Increase memory limit or execution time if needed</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>🛠️ Recommended Solutions</h3>";
echo "<div class='success'>";
echo "<h4>Quick Fixes to Try:</h4>";
echo "<ol>";
echo "<li><strong>Check Database:</strong> Ensure 'aps_dream_home' database exists and is accessible</li>";
echo "<li><strong>Verify Files:</strong> Make sure all required PHP files are present</li>";
echo "<li><strong>Check Permissions:</strong> Ensure directories are writable</li>";
echo "<li><strong>Restart XAMPP:</strong> Restart Apache and MySQL services</li>";
echo "<li><strong>Clear Cache:</strong> Clear browser cache and try again</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
