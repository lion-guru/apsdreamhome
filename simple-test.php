<?php
// Simple test without database dependency
echo "<h1>✅ PHP is working!</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server info: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<hr>";

echo "<h2>🔍 System Check:</h2>";
echo "<ul>";

// Check if required files exist
$files = [
    'includes/db_connection.php',
    'includes/templates/dynamic_header.php',
    'includes/templates/dynamic_footer.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<li>✅ $file exists</li>";
    } else {
        echo "<li>❌ $file missing</li>";
    }
}

echo "</ul>";

echo "<hr>";

echo "<h2>🚀 Next Steps:</h2>";
echo "<ol>";
echo "<li><strong>Start XAMPP:</strong> Open XAMPP Control Panel</li>";
echo "<li><strong>Start Services:</strong> Click START for Apache and MySQL</li>";
echo "<li><strong>Clear Cache:</strong> Press Ctrl+F5 in browser</li>";
echo "<li><strong>Visit:</strong> <a href='http://localhost/apsdreamhome/index.php'>http://localhost/apsdreamhome/index.php</a></li>";
echo "</ol>";

echo "<p><a href='http://localhost/apsdreamhome/index.php' class='btn btn-primary'>Go to Main Site</a></p>";
?>
