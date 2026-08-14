<?php
// Fix user 69 role from 'customer' to 'telecaller'
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 1. Fix user 69 role
echo "=== FIXING USER 69 ROLE ===\n";
$stmt = $pdo->prepare("UPDATE users SET role = 'telecaller' WHERE id = 69 AND role = 'customer'");
$stmt->execute();
echo "Rows affected: " . $stmt->rowCount() . "\n";

// Verify
$stmt = $pdo->query("SELECT id, email, role FROM users WHERE id = 69");
$u = $stmt->fetch(PDO::FETCH_ASSOC);
echo "After fix: ID={$u['id']} email={$u['email']} role={$u['role']}\n";

// 2. Also clear any stale cache files
echo "\n=== CLEARING STALE CACHE ===\n";
$cacheDir = $root . '/storage/cache';
$files = glob($cacheDir . '/*.cache');
$cleared = 0;
foreach ($files as $f) {
    if (unlink($f)) $cleared++;
}
echo "Cleared {$cleared} cache files\n";

// 3. Verify telecaller now gets 16 items from the INNER JOIN query
echo "\n=== VERIFYING INNER JOIN QUERY ===\n";
$stmt = $pdo->prepare("
    SELECT mi.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete
    FROM admin_menu_items mi
    INNER JOIN admin_role_menu_permissions rp ON mi.id = rp.menu_item_id AND rp.role = ?
    WHERE mi.is_active = 1 AND rp.can_view = 1
    ORDER BY mi.order_index ASC
");
$stmt->execute(['telecaller']);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Telecaller menu items: " . count($items) . "\n";

echo "\nDONE\n";?>