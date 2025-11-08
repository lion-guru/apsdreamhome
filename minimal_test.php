<?php
/**
 * Minimal Security Headers Test
 */

// Include the security headers
require_once __DIR__ . '/includes/security_headers.php';

// Set content type
header('Content-Type: text/plain; charset=utf-8');

echo "=== Security Headers Test ===\n\n";

// Get all response headers
$headers = [];
foreach (headers_list() as $header) {
    list($name, $value) = explode(':', $header, 2);
    $headers[trim($name)] = trim($value);
}

// Display all headers
echo "Response Headers:\n";
echo str_repeat("-", 80) . "\n";
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}

echo "\nHeaders sent successfully!\n";
// Minimal Test - APS Dream Home
// Tests the absolute minimum to identify the issue

echo "<!DOCTYPE html>
<html>
<head>
    <title>Minimal Test</title>
</head>
<body>
    <h1>Minimal Test</h1>
    <p>Testing basic functionality...</p>";

try {
    echo "<p>✅ PHP is working</p>";

    // Test 1: Check if index.php exists
    if (file_exists('index.php')) {
        echo "<p>✅ index.php exists</p>";
    } else {
        echo "<p>❌ index.php missing</p>";
    }

    // Test 2: Check database config
    if (file_exists('includes/db_config.php')) {
        echo "<p>✅ Database config exists</p>";
        require_once 'includes/db_config.php';
        echo "<p>✅ Database config loaded</p>";
    } else {
        echo "<p>❌ Database config missing</p>";
    }

    // Test 3: Check security manager
    if (file_exists('includes/security/security_manager.php')) {
        echo "<p>✅ Security manager exists</p>";
        require_once 'includes/security/security_manager.php';
        echo "<p>✅ Security manager loaded</p>";
    } else {
        echo "<p>❌ Security manager missing</p>";
    }

    // Test 4: Try to load index.php
    echo "<h2>Loading index.php...</h2>";
    ob_start();
    include 'index.php';
    $output = ob_get_clean();

    if (strlen($output) > 0) {
        echo "<p>✅ index.php loaded successfully (" . strlen($output) . " chars)</p>";
        echo "<p>Preview: " . substr($output, 0, 100) . "...</p>";
    } else {
        echo "<p>⚠️ index.php loaded but no output</p>";
    }

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

echo "</body>
</html>";
?>
