<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SHOW TABLES LIKE 'exchange_rates'")->fetchColumn();
if ($exists) {
    echo "SKIP exchange_rates\n";
} else {
    $pdo->exec("CREATE TABLE exchange_rates (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        base_currency VARCHAR(3) NOT NULL DEFAULT 'INR',
        target_currency VARCHAR(3) NOT NULL,
        rate DECIMAL(15,6) NOT NULL,
        source ENUM('api','manual','test') NOT NULL DEFAULT 'api',
        fetched_at DATETIME NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_base_target (base_currency, target_currency),
        INDEX idx_target (target_currency),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED exchange_rates\n";
}

$rates = [
    ['INR', 'USD', 83.50], ['INR', 'EUR', 90.20], ['INR', 'GBP', 98.70],
    ['INR', 'AED', 22.70], ['INR', 'SGD', 62.10], ['INR', 'JPY', 0.56],
    ['INR', 'CAD', 61.80], ['INR', 'AUD', 54.30],
];
foreach ($rates as $r) {
    $exists = $pdo->query("SELECT id FROM exchange_rates WHERE base_currency = '{$r[0]}' AND target_currency = '{$r[1]}' LIMIT 1")->fetchColumn();
    if (!$exists) {
        $pdo->exec("INSERT INTO exchange_rates (tenant_id, base_currency, target_currency, rate, source, fetched_at, is_active) VALUES (1, '{$r[0]}', '{$r[1]}', {$r[2]}, 'test', NOW(), 1)");
        echo "SEEDED exchange_rates {$r[0]}/{$r[1]} = {$r[2]}\n";
    } else {
        echo "SKIP exchange_rates {$r[0]}/{$r[1]}\n";
    }
}

$pdo->exec("INSERT IGNORE INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active) VALUES
    (351, 1, 'Multi-Currency', 'fas fa-coins', '/admin/multi-currency', NULL, 'finance', 30, 'financial.view', 1)");
$exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = 'admin' AND menu_item_id = 351 LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'admin', 351, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'super_admin', 351, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'finance_director', 351, 1, 1, 1, 0)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'accountant', 351, 1, 0, 0, 0)");
}
echo "Menu + permissions seeded\n";
echo "Done.\n";