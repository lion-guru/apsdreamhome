<?php
/**
 * Local Server Check Script
 * Verifies XAMPP services and project setup
 */

header('Content-Type: text/plain');

echo "=== LOCAL SERVER DIAGNOSTICS ===\n\n";

// 1. Check PHP
echo "1. PHP Status:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP Working: " . (function_exists('phpinfo') ? 'YES' : 'NO') . "\n\n";

// 2. Check MySQL Connection
echo "2. MySQL Connection:\n";
try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome', 'root', '');
    echo "MySQL Connection: SUCCESS\n";
    
    // Check tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables Count: " . count($tables) . "\n";
    
} catch (PDOException $e) {
    echo "MySQL Connection: FAILED - " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Check Web Server
echo "3. Web Server Status:\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n\n";

// 4. Check Project Files
echo "4. Project Structure:\n";
$projectRoot = __DIR__;
echo "Project Root: $projectRoot\n";
echo "Config Folder: " . (is_dir($projectRoot . '/config') ? 'EXISTS' : 'MISSING') . "\n";
echo "App Folder: " . (is_dir($projectRoot . '/app') ? 'EXISTS' : 'MISSING') . "\n";
echo "Public Folder: " . (is_dir($projectRoot . '/public') ? 'EXISTS' : 'MISSING') . "\n";
echo "Routes Folder: " . (is_dir($projectRoot . '/routes') ? 'EXISTS' : 'MISSING') . "\n\n";

// 5. Check Required Files
echo "5. Required Files:\n";
$requiredFiles = [
    'routes/web.php',
    'app/Http/Controllers/BaseController.php',
    'config/database.php',
    'public/index.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = $projectRoot . '/' . $file;
    echo "$file: " . (file_exists($fullPath) ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n=== DIAGNOSTICS COMPLETE ===\n";
echo "\nNEXT STEPS:\n";
echo "1. If MySQL failed: Start XAMPP MySQL service\n";
echo "2. If files missing: Check project structure\n";
echo "3. If web server issue: Check Apache/Nginx\n";
echo "4. Try accessing: http://localhost/apsdreamhome\n";
