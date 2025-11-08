<?php
// Comprehensive Routing Diagnostic Script

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to print colored output
function coloredOutput($message, $color = 'black') {
    echo "<div style='color: $color; font-family: monospace;'>$message</div>";
}

// Routing Information
coloredOutput("🔍 Routing Diagnostic Report", 'blue');
coloredOutput("----------------------------", 'blue');

// Server Variables
coloredOutput("\n📡 Server Variables:", 'green');
$serverVars = [
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'N/A',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A'
];

foreach ($serverVars as $key => $value) {
    coloredOutput("$key: $value", 'purple');
}

// Current Directory Structure
coloredOutput("\n📂 Current Directory Structure:", 'green');
function listDirectoryContents($dir, $depth = 0) {
    $indent = str_repeat('  ', $depth);
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            coloredOutput("{$indent}📁 $file/", 'blue');
            listDirectoryContents($path, $depth + 1);
        } else {
            coloredOutput("{$indent}📄 $file", 'purple');
        }
    }
}

listDirectoryContents($_SERVER['DOCUMENT_ROOT'] . '/apsdreamhome/admin');

// .htaccess Diagnostic
coloredOutput("\n🔒 .htaccess Diagnostic:", 'green');
$htaccessPath = $_SERVER['DOCUMENT_ROOT'] . '/apsdreamhome/admin/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    coloredOutput("File Exists: Yes", 'blue');
    coloredOutput("Content Preview:", 'blue');
    highlight_string($htaccessContent);
} else {
    coloredOutput("File Not Found: $htaccessPath", 'red');
}

// Apache Module Check
coloredOutput("\n🌐 Apache Modules:", 'green');
$requiredModules = ['mod_rewrite', 'mod_php'];
$enabledModules = apache_get_modules();

foreach ($requiredModules as $module) {
    $status = in_array($module, $enabledModules) ? '✅ Enabled' : '❌ Disabled';
    coloredOutput("$module: $status", $status === '✅ Enabled' ? 'green' : 'red');
}

// Potential Routing Configurations
coloredOutput("\n🚦 Potential Routing Configurations:", 'green');
$possibleRoutes = [
    '/admin/index.php',
    '/apsdreamhome/admin/index.php',
    '/admin/login.php',
    '/apsdreamhome/admin/login.php'
];

foreach ($possibleRoutes as $route) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $route;
    $exists = file_exists($fullPath);
    coloredOutput("$route: " . ($exists ? '✅ Exists' : '❌ Not Found'), $exists ? 'green' : 'red');
}

// PHP Configuration
coloredOutput("\n⚙️ PHP Configuration:", 'green');
$phpInfo = [
    'PHP Version' => phpversion(),
    'Server API' => php_sapi_name(),
    'Display Errors' => ini_get('display_errors'),
    'Error Reporting' => ini_get('error_reporting')
];

foreach ($phpInfo as $key => $value) {
    coloredOutput("$key: $value", 'purple');
}
?>
