<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

foreach (['associate', 'agent', 'customer', 'employee'] as $r) {
    echo "========================================\n";
    echo "Menu permissions for role: {$r}\n";
    $rows = $pdo->query("
        SELECT mi.id, mi.name, mi.section, mi.url, rp.can_view 
        FROM admin_role_menu_permissions rp
        JOIN admin_menu_items mi ON rp.menu_item_id = mi.id
        WHERE rp.role = '{$r}' AND rp.can_view = 1
        ORDER BY mi.section, mi.order_index
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "Total: " . count($rows) . " items\n";
    foreach (array_slice($rows, 0, 15) as $row) {
        echo "  - [{$row['section']}] {$row['name']} -> {$row['url']}\n";
    }
}
