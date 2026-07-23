<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Get all admin menu items with their URLs
$stmt = $pdo->query("SELECT id, name, url, parent_id, section, is_active, sort_order FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, sort_order");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total active menu items: " . count($items) . "\n\n";

// Check if routes exist for each URL
$routesFile = file_get_contents('routes/web.php');
$apiRoutesFile = file_get_contents('routes/api.php');

$broken = 0;
$ok = 0;

foreach ($items as $item) {
    $url = $item['url'];
    // Normalize URL - remove leading slash if present
    $normalizedUrl = ltrim($url, '/');
    
    // Check if route exists in web.php or api.php
    $found = false;
    
    // Check for exact match or pattern match
    if (strpos($routesFile, "'$url'") !== false || 
        strpos($routesFile, "\"$url\"") !== false ||
        strpos($apiRoutesFile, "'$url'") !== false ||
        strpos($apiRoutesFile, "\"$url\"") !== false) {
        $found = true;
    } else {
        // Check for pattern with parameters
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', preg_quote($url, '/'));
        if (preg_match("/$pattern/", $routesFile) || preg_match("/$pattern/", $apiRoutesFile)) {
            $found = true;
        }
    }
    
    if (!$found) {
        // Check if it's a parent menu (no URL expected)
        $children = $pdo->prepare("SELECT COUNT(*) FROM admin_menu_items WHERE parent_id = ? AND is_active = 1");
        $children->execute([$item['id']]);
        $childCount = $children->fetchColumn();
        
        if ($childCount > 0) {
            echo "⚠️  PARENT MENU (no route needed): ID {$item['id']} | {$item['name']} | URL: {$url} | Section: {$item['section']} | Children: $childCount\n";
        } else {
            echo "❌ BROKEN URL: ID {$item['id']} | {$item['name']} | URL: {$url} | Section: {$item['section']}\n";
            $broken++;
        }
    } else {
        $ok++;
    }
}

echo "\n=== Summary ===\n";
echo "OK URLs: $ok\n";
echo "Broken URLs: $broken\n";
echo "Total: " . count($items) . "\n";