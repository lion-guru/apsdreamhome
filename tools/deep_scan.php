<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/bootstrap.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Starting Deep Scan...\n";
echo "----------------------------------------\n";

// 1. Database Analysis
echo "\n[Database Analysis]\n";
$tables = [];
$stmt = $conn->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$legacy_tables = ['user', 'agents', 'agent', 'admin_users', 'employee'];
foreach ($legacy_tables as $table) {
    if (in_array($table, $tables)) {
        $count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "⚠️  Legacy Table Found: '$table' ($count rows)\n";
    }
}

// Check for missing foreign keys or orphans
// (Simplified check for now)
if (in_array('associates', $tables) && in_array('users', $tables)) {
    $orphans = $conn->query("SELECT COUNT(*) FROM associates WHERE user_id NOT IN (SELECT id FROM users)")->fetchColumn();
    if ($orphans > 0) {
        echo "❌ Orphaned Associates Found: $orphans (user_id not in users table)\n";
    } else {
        echo "✅ Associates Integrity: OK\n";
    }
}

// 2. Codebase Analysis (Regex Scan)
echo "\n[Codebase Analysis]\n";
$root = APP_ROOT;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$php_files = new RegexIterator($files, '/\.php$/');

$patterns = [
    '/FROM\s+user\b/i' => 'Legacy "user" table usage',
    '/JOIN\s+user\b/i' => 'Legacy "user" table usage',
    '/FROM\s+agents\b/i' => 'Legacy "agents" table usage',
    '/JOIN\s+agents\b/i' => 'Legacy "agents" table usage',
    '/extends\s+Controller\b/' => 'Legacy Controller inheritance (should be BaseController)',
    '/mysql_query\(/i' => 'Deprecated mysql_query usage',
];

$matches_found = [];

foreach ($php_files as $file) {
    if (strpos($file->getPathname(), 'vendor') !== false) continue;
    if (strpos($file->getPathname(), 'storage') !== false) continue;
    if (strpos($file->getPathname(), 'tools') !== false) continue; // Skip tools

    $content = file_get_contents($file->getPathname());

    foreach ($patterns as $pattern => $desc) {
        if (preg_match($pattern, $content, $matches)) {
            $relPath = str_replace($root, '', $file->getPathname());
            $matches_found[] = "$desc in $relPath";
        }
    }
}

if (empty($matches_found)) {
    echo "✅ No legacy code patterns found.\n";
} else {
    echo "⚠️  Found " . count($matches_found) . " potential issues:\n";
    foreach (array_slice($matches_found, 0, 20) as $match) {
        echo "- $match\n";
    }
    if (count($matches_found) > 20) echo "... and " . (count($matches_found) - 20) . " more.\n";
}

// 3. Route Analysis
echo "\n[Route Analysis]\n";
// Load routes manually to check
$routes_web = APP_ROOT . '/routes/web.php';
$routes_modern = APP_ROOT . '/routes/modern.php';

// Helper to extract controllers from route files
function scan_routes($file)
{
    $content = file_get_contents($file);
    preg_match_all("/'([a-zA-Z0-9_\\\\]+Controller)@([a-zA-Z0-9_]+)'/", $content, $matches);
    return array_combine($matches[1], $matches[2]); // Controller => Method
}

$controllers = [];
if (file_exists($routes_web)) {
    $controllers = array_merge($controllers, scan_routes($routes_web));
}
if (file_exists($routes_modern)) {
    $controllers = array_merge($controllers, scan_routes($routes_modern));
}

$missing_controllers = [];
foreach ($controllers as $controller => $method) {
    $path = APP_ROOT . '/app/Http/Controllers/' . str_replace('\\', '/', $controller) . '.php';
    if (!file_exists($path)) {
        $missing_controllers[] = $controller;
    }
}

if (empty($missing_controllers)) {
    echo "✅ All referenced controllers exist.\n";
} else {
    echo "❌ Missing Controllers referenced in routes:\n";
    foreach (array_unique($missing_controllers) as $c) {
        echo "- $c\n";
    }
}

// 4. Duplicate/Conflicting Controllers Check
echo "\n🔎 Checking for Duplicate/Conflicting Controllers...\n";
$controllers = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APP_ROOT . '/app/Http/Controllers'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $name = $file->getBasename();
        $path = $file->getPathname();
        if (isset($controllers[$name])) {
            echo "⚠️  Duplicate Controller Name Found: '$name'\n";
            echo "    - " . $controllers[$name] . "\n";
            echo "    - " . $path . "\n";
        }
        $controllers[$name] = $path;
    }
}

echo "\n✅ Deep Scan Complete.\n";
