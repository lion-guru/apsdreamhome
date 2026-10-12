<?php
/**
 * One-time manifest regenerator: dumps live admin_menu_items +
 * admin_role_menu_permissions into config/admin_menu_manifest.php format.
 * Usage: php scripts/regen_menu_manifest.php [--write]
 * Default is dry-run (prints counts only). --write replaces the manifest
 * (backup saved as admin_menu_manifest.bak.php).
 */
$root = dirname(__DIR__);
$dbConfig = require $root . '/config/database.php';

$pdo = new PDO(
    "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
    $dbConfig['username'],
    $dbConfig['password'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$items = $pdo->query(
    "SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY order_index ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$byId = [];
foreach ($items as $it) $byId[(int)$it['id']] = $it;

$perms = $pdo->query(
    "SELECT role, menu_item_id, can_view, can_create, can_edit, can_delete
     FROM admin_role_menu_permissions"
)->fetchAll(PDO::FETCH_ASSOC);
$permMap = [];
foreach ($perms as $p) $permMap[(int)$p['menu_item_id']][$p['role']] = $p;

$manifest = [];
foreach ($items as $it) {
    $parentUrl = null;
    if (!empty($it['parent_id']) && isset($byId[(int)$it['parent_id']])) {
        $parentUrl = $byId[(int)$it['parent_id']]['url'];
    }
    $roles = [];
    foreach (($permMap[(int)$it['id']] ?? []) as $role => $p) {
        $roles[$role] = [
            'view' => (int)$p['can_view'],
            'create' => (int)$p['can_create'],
            'edit' => (int)$p['can_edit'],
            'delete' => (int)$p['can_delete'],
        ];
    }
    $manifest[$it['url']] = [
        'name' => $it['name'],
        'icon' => $it['icon'],
        'section' => $it['section'],
        'parent_url' => $parentUrl,
        'order' => (int)$it['order_index'],
        'perm' => $it['permission_key'],
        'roles' => $roles,
    ];
}

$count = count($manifest);
echo "Active DB menu items: $count\n";

if (($argv[1] ?? '') !== '--write') {
    echo "Dry-run. Pass --write to regenerate the manifest.\n";
    exit(0);
}

$header = "<?php\n/**\n"
    . " * Admin menu manifest — future-proof static snapshot of admin_menu_items.\n"
    . " * Generated: " . date('Y-m-d H:i:s') . " | items: {$count}\n"
    . " *\n"
    . " * Used for: (1) sidebar fallback when DB menu is unreachable/empty,\n"
    . " * (2) self-heal re-seeding of missing menu rows + role permissions.\n"
    . " * Keyed by URL (stable) — never by auto-increment id.\n"
    . " */\nreturn ";
$outFile = $root . '/config/admin_menu_manifest.php';
if (file_exists($outFile)) {
    copy($outFile, $root . '/config/admin_menu_manifest.bak.php');
    echo "Backup saved.\n";
}
file_put_contents($outFile, $header . var_export($manifest, true) . ";\n");
echo "Manifest regenerated: $count items.\n";
