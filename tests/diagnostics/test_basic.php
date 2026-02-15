<?php
echo "<h1>Basic PHP Test</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";

try {
    require_once __DIR__ . '/includes/config/config.php';
    echo "<p style='color: green;'>✅ Main config loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Config error: " . $e->getMessage() . "</p>";
}

if (isset($conn) && $conn instanceof mysqli) {
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection: FAILED</p>";
}

echo "<hr>";
echo "<a href='admin/'>Go to Admin Panel</a> | ";
echo "<a href='index.php'>Main Site</a>";
?>
