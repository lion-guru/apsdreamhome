<?php
/**
 * Debug: Telecaller Sidebar — why 145 items instead of ~18?
 * Checks: 1) DB query result, 2) Cache state, 3) Role detection, 4) Redis availability
 */

$root = dirname(__DIR__);
require_once $root . '/config/database.php';

// Bootstrap DB
$dbConfig = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
    $dbConfig['username'],
    $dbConfig['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== TELECALLER SIDEBAR DEBUG ===\n\n";

// 1. Count active menu items
$stmt = $pdo->query("SELECT COUNT(*) as c FROM admin_menu_items WHERE is_active = 1");
$total = $stmt->fetch()['c'];
echo "Total active menu items: {$total}\n";

// 2. Count telecaller permissions
$stmt = $pdo->query("SELECT COUNT(*) as c FROM admin_role_menu_permissions WHERE role = 'telecaller'");
$tcPerms = $stmt->fetch()['c'];
echo "Telecaller permissions (all): {$tcPerms}\n";

$stmt = $pdo->query("SELECT COUNT(*) as c FROM admin_role_menu_permissions WHERE role = 'telecaller' AND can_view = 1");
$tcViewPerms = $stmt->fetch()['c'];
echo "Telecaller can_view=1 permissions: {$tcViewPerms}\n";

// 3. Run the INNER JOIN query that AdminMenuService::getMenuItemsByRole uses
$query = "
    SELECT mi.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete
    FROM admin_menu_items mi
    INNER JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = ?
    WHERE mi.is_active = 1 AND rp.can_view = 1
    ORDER BY order_index ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['telecaller']);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nINNER JOIN query result count: " . count($items) . "\n";

// 4. Also check LEFT JOIN (old broken query that leaked everything)
$query2 = "
    SELECT mi.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete
    FROM admin_menu_items mi
    LEFT JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = 'telecaller'
    WHERE mi.is_active = 1 AND rp.can_view = 1
    ORDER BY order_index ASC
";
$stmt2 = $pdo->query($query2);
$items2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "LEFT JOIN (old broken) query result count: " . count($items2) . "\n";

// 5. Check if there's a LEFT JOIN with WHERE NULL leak
$query3 = "
    SELECT mi.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete
    FROM admin_menu_items mi
    LEFT JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = 'telecaller'
    WHERE mi.is_active = 1 AND (rp.role IS NULL OR rp.can_view = 1)
    ORDER BY order_index ASC
";
$stmt3 = $pdo->query($query3);
$items3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
echo "LEFT JOIN + NULL leak query result count: " . count($items3) . " (this is the BAD pattern)\n";

// 6. Check ALL roles and their permission counts
echo "\n=== ALL ROLES ===\n";
$stmt4 = $pdo->query("
    SELECT role, COUNT(*) as total, SUM(CASE WHEN can_view = 1 THEN 1 ELSE 0 END) as can_view_count
    FROM admin_role_menu_permissions
    GROUP BY role
    ORDER BY role
");
while ($row = $stmt4->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['role']}: {$row['can_view_count']}/{$row['total']} can_view\n";
}

// 7. Check specific telecaller permission URLs (first 20)
echo "\n=== TELECALLER CAN_VIEW URLs ===\n";
$stmt5 = $pdo->prepare("
    SELECT mi.url, mi.name, mi.section
    FROM admin_menu_items mi
    INNER JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = 'telecaller'
    WHERE mi.is_active = 1 AND rp.can_view = 1
    ORDER BY mi.order_index ASC
");
$stmt5->execute(['telecaller']);
while ($row = $stmt5->fetch(PDO::FETCH_ASSOC)) {
    echo "  [{$row['section']}] {$row['url']} — {$row['name']}\n";
}

// 8. Check cache state
echo "\n=== CACHE STATE ===\n";
$cacheDir = $root . '/storage/cache';
$cacheKey = 'admin_sidebar_role_' . md5('telecaller');
$cacheFile = $cacheDir . '/' . $cacheKey . '.cache';
echo "Cache file path: {$cacheFile}\n";
echo "Cache file exists: " . (file_exists($cacheFile) ? 'YES' : 'NO') . "\n";
if (file_exists($cacheFile)) {
    $data = unserialize(file_get_contents($cacheFile));
    echo "Cached item count: " . (is_array($data) ? count($data) : 'NOT ARRAY') . "\n";
    echo "First item: " . ($data[0]['name'] ?? 'N/A') . "\n";
}

// 9. Check Redis availability
echo "\n=== REDIS STATE ===\n";
try {
    $redis = new Redis();
    $connected = $redis->connect('127.0.0.1', 6379, 2);
    if ($connected) {
        echo "Redis: CONNECTED\n";
        $redisKeys = $redis->keys('*admin_sidebar*');
        echo "Redis admin_sidebar* keys: " . count($redisKeys) . "\n";
        foreach ($redisKeys as $key) {
            $val = $redis->get($key);
            $data = @unserialize($val);
            echo "  {$key}: " . (is_array($data) ? count($data) . ' items' : 'non-array') . "\n";
        }
    } else {
        echo "Redis: NOT CONNECTED\n";
    }
} catch (Exception $e) {
    echo "Redis: UNAVAILABLE ({$e->getMessage()})\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
