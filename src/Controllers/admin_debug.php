<?php
// Comprehensive Admin Access Diagnostic Script

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Server and Request Information
echo "<h1>Admin Access Diagnostic</h1>";
echo "<h2>Server Information</h2>";
echo "<pre>";
print_r([
    'PHP_VERSION' => PHP_VERSION,
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A'
]);
echo "</pre>";

// Directory Checks
echo "<h2>Directory Checks</h2>";
$adminDir = __DIR__;
$parentDir = dirname(__DIR__);

echo "<h3>Current Directory: $adminDir</h3>";
echo "<pre>";
echo "Directory Exists: " . (is_dir($adminDir) ? 'Yes' : 'No') . "\n";
echo "Readable: " . (is_readable($adminDir) ? 'Yes' : 'No') . "\n";
echo "Writable: " . (is_writable($adminDir) ? 'Yes' : 'No') . "\n";

echo "\nDirectory Contents:\n";
$files = scandir($adminDir);
print_r($files);
echo "</pre>";

// File Permissions
echo "<h2>Key File Permissions</h2>";
$keyFiles = [
    'index.php',
    'admin_login_handler.php',
    '.htaccess'
];

echo "<pre>";
foreach ($keyFiles as $file) {
    $fullPath = "$adminDir/$file";
    echo "$file:\n";
    echo "  Exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
    echo "  Readable: " . (is_readable($fullPath) ? 'Yes' : 'No') . "\n";
    echo "  Writable: " . (is_writable($fullPath) ? 'Yes' : 'No') . "\n\n";
}
echo "</pre>";

// Session and Authentication Check
echo "<h2>Session and Authentication</h2>";
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Configuration File Check
echo "<h2>Configuration Files</h2>";
$configFiles = [
    '../includes/config/db_config.php',
    'includes/csrf_protection.php'
];

echo "<pre>";
foreach ($configFiles as $file) {
    $fullPath = "$adminDir/$file";
    echo "$file:\n";
    echo "  Exists: " . (file_exists($fullPath) ? 'Yes' : 'No') . "\n";
    if (file_exists($fullPath)) {
        $contents = file_get_contents($fullPath);
        echo "  File Size: " . strlen($contents) . " bytes\n";
    }
    echo "\n";
}
echo "</pre>";

// Apache Rewrite Rules Test
echo "<h2>Rewrite Rules Test</h2>";
echo "<p>Current Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Current Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
?>
