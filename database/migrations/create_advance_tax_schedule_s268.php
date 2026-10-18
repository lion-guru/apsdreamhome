<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();

echo "=== Migration: create_advance_tax_schedule_s268 ===\n";

$exists = $db->fetchAll("SHOW TABLES LIKE 'advance_tax_schedule'");
if (empty($exists)) {
    $db->execute("CREATE TABLE advance_tax_schedule (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED DEFAULT 1,
        financial_year VARCHAR(9) NOT NULL,
        quarter TINYINT NOT NULL,
        due_date DATE NOT NULL,
        cumulative_pct DECIMAL(5,2) NOT NULL,
        projected_profit DECIMAL(15,2) NOT NULL,
        projected_tax DECIMAL(15,2) NOT NULL,
        due_amount DECIMAL(15,2) NOT NULL,
        paid_amount DECIMAL(15,2) DEFAULT 0,
        challan_ref VARCHAR(100) DEFAULT NULL,
        status ENUM('pending','paid','overdue') DEFAULT 'pending',
        paid_date DATE DEFAULT NULL,
        notes VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_fy_quarter (tenant_id, financial_year, quarter)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED advance_tax_schedule\n";
} else {
    echo "SKIPPED advance_tax_schedule (exists)\n";
}

$menuExists = $db->fetchAll("SELECT id FROM admin_menu_items WHERE url = '/admin/tax-planning'");
if (empty($menuExists)) {
    $db->insert('admin_menu_items', [
        'tenant_id' => 1,
        'name' => 'Tax Planning',
        'url' => '/admin/tax-planning',
        'icon' => 'fas fa-calculator',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 7,
        'permission_key' => 'financial.view',
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    $menuId = (int)$db->lastInsertId();
    echo "SEEDED menu item #{$menuId}\n";
    foreach (['admin','super_admin','finance_director','accountant'] as $role) {
        $db->execute("INSERT IGNORE INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_edit) VALUES (?, ?, 1, 1)", [$role, $menuId]);
    }
    echo "SEEDED role permissions (4 roles)\n";
} else {
    echo "SKIPPED menu (exists #{$menuExists[0]['id']})\n";
}
echo "DONE\n";