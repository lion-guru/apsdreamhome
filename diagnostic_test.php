<?php
/**
 * APS Dream Home - Diagnostic Test
 * Test PHP configuration, database connectivity, and server status
 */

echo "<h2>🔧 APS Dream Home - Diagnostic Test</h2>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

// Test required extensions
echo "<h3>🔍 PHP Extensions Status</h3>";
$extensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ LOADED" : "❌ NOT LOADED";
    $color = extension_loaded($ext) ? "green" : "red";
    echo "<p style='color: $color;'><strong>Extension $ext:</strong> $status</p>";
}

// Test database connection
echo "<h3>🗄️ Database Connectivity Test</h3>";
try {
    $conn = new mysqli("localhost", "root", "", "apsdreamhome");
    if ($conn->connect_error) {
        echo "<p style='color: red;'><strong>Database Connection:</strong> ❌ FAILED - " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'><strong>Database Connection:</strong> ✅ SUCCESS</p>";
        
        // Test table count
        $result = $conn->query("SHOW TABLES");
        $table_count = $result->num_rows;
        echo "<p><strong>Database Tables:</strong> $table_count tables found</p>";
        
        // Test sample data
        $user_result = $conn->query("SELECT COUNT(*) as count FROM users");
        $user_count = $user_result->fetch_assoc()['count'];
        echo "<p><strong>Sample Data:</strong> $user_count users found</p>";
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Database Connection:</strong> ❌ EXCEPTION - " . $e->getMessage() . "</p>";
}

// Test file system
echo "<h3>📁 File System Test</h3>";
$paths = [
    'app/',
    'public/',
    'config/',
    'vendor/',
    '.htaccess',
    'composer.json'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        if (is_dir($path)) {
            $items = count(scandir($path)) - 2; // Exclude . and ..
            echo "<p style='color: green;'><strong>Directory $path:</strong> ✅ EXISTS ($items items)</p>";
        } else {
            echo "<p style='color: green;'><strong>File $path:</strong> ✅ EXISTS</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>$path:</strong> ❌ NOT FOUND</p>";
    }
}

// Test application files
echo "<h3>🚀 Application Files Test</h3>";
$app_files = [
    'public/index.php',
    'app/bootstrap.php',
    'config/app.php',
    'config/database.php'
];

foreach ($app_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'><strong>$file:</strong> ✅ EXISTS</p>";
    } else {
        echo "<p style='color: red;'><strong>$file:</strong> ❌ NOT FOUND</p>";
    }
}

// Test .htaccess
echo "<h3>⚙️ .htaccess Configuration Test</h3>";
if (file_exists('.htaccess')) {
    echo "<p style='color: green;'><strong>.htaccess:</strong> ✅ EXISTS</p>";
    $htaccess_content = file_get_contents('.htaccess');
    echo "<p><strong>.htaccess Content Preview:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars(substr($htaccess_content, 0, 500));
    if (strlen($htaccess_content) > 500) {
        echo "\n... (truncated)";
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'><strong>.htaccess:</strong> ❌ NOT FOUND</p>";
}

// Test mod_rewrite
echo "<h3>🔄 Mod Rewrite Test</h3>";
if (in_array('mod_rewrite', apache_get_modules())) {
    echo "<p style='color: green;'><strong>Mod Rewrite:</strong> ✅ ENABLED</p>";
} else {
    echo "<p style='color: orange;'><strong>Mod Rewrite:</strong> ⚠️ NOT DETECTED (may still be enabled)</p>";
}

// Memory and limits
echo "<h3>💾 PHP Configuration</h3>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</p>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";

// Test error reporting
echo "<h3>🚨 Error Reporting</h3>";
echo "<p><strong>Error Reporting:</strong> " . (error_reporting() ? 'ENABLED' : 'DISABLED') . "</p>";
echo "<p><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";

echo "<hr>";
echo "<p><strong>🎯 Diagnostic Test Complete!</strong></p>";
echo "<p><small>If you see this page, PHP is working correctly.</small></p>";
?>
