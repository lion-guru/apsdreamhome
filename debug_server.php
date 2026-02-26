<?php
// Debug script to check Apache/XAMPP setup
echo "🔍 APS Dream Home - Server Debug Tool\n";
echo "=====================================\n\n";

echo "📊 Server Information:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "\n\n";

echo "📁 File Checks:\n";
$files = [
    'public/index.php',
    'index.php', 
    '.htaccess',
    'app/Core/App.php',
    'app/Core/Routing/Router.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS\n";
    } else {
        echo "❌ $file: MISSING\n";
    }
}

echo "\n🔧 Configuration Checks:\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "\n";
echo "Error Reporting: " . (ini_get('error_reporting') ? 'ON' : 'OFF') . "\n";
echo "Allow URL Include: " . (ini_get('allow_url_include') ? 'ON' : 'OFF') . "\n";

echo "\n🌐 URL Test:\n";
echo "Current URL: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";

echo "\n✅ Debug Complete!\n";
?>
