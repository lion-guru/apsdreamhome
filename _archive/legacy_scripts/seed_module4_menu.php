<?php
/**
 * Insert Module 4 menu items into admin_menu_items.
 * Idempotent: ON DUPLICATE KEY UPDATE so safe to re-run.
 */
$root = dirname(__DIR__);
require $root . '/config/bootstrap.php';

$items = [
    [
        'name'          => 'MLM Dashboard',
        'section'       => 'mlm',
        'icon'          => 'fa-network-wired',
        'url'           => '/admin/mlm/dashboard',
        'order_index'   => 5,
        'is_active'     => 1,
        'permission_key'=> 'admin',
    ],
    [
        'name'          => 'Payout Batches',
        'section'       => 'mlm',
        'icon'          => 'fa-money-check-alt',
        'url'           => '/admin/mlm/payouts/batches',
        'order_index'   => 15,
        'is_active'     => 1,
        'permission_key'=> 'admin',
    ],
    [
        'name'          => 'Clawback Log',
        'section'       => 'mlm',
        'icon'          => 'fa-undo',
        'url'           => '/admin/mlm/clawbacks',
        'order_index'   => 25,
        'is_active'     => 1,
        'permission_key'=> 'admin',
    ],
];

$db = \App\Core\Database\Database::getInstance();
$inserted = 0; $updated = 0;
foreach ($items as $i) {
    try {
        $existing = $db->fetchOne("SELECT id FROM admin_menu_items WHERE url = ?", [$i['url']]);
        if ($existing) {
            $db->execute(
                "UPDATE admin_menu_items SET name=?, section=?, icon=?, order_index=?, is_active=?, permission_key=?, updated_at=NOW() WHERE id=?",
                [$i['name'], $i['section'], $i['icon'], $i['order_index'], $i['is_active'], $i['permission_key'], $existing['id']]
            );
            $updated++;
            echo "UPDATED  id=" . $existing['id'] . " url=" . $i['url'] . "\n";
        } else {
            $db->execute(
                "INSERT INTO admin_menu_items (name, section, icon, url, order_index, is_active, permission_key, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$i['name'], $i['section'], $i['icon'], $i['url'], $i['order_index'], $i['is_active'], $i['permission_key']]
            );
            $inserted++;
            echo "INSERTED id=" . $db->lastInsertId() . " url=" . $i['url'] . "\n";
        }
    } catch (\Throwable $e) {
        echo "ERROR url=" . $i['url'] . " msg=" . $e->getMessage() . "\n";
    }
}
echo "DONE inserted=$inserted updated=$updated\n";?>