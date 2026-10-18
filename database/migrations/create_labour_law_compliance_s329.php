<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$table = 'labour_law_compliances';
$exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetchColumn();
if (!$exists) {
    $pdo->exec("CREATE TABLE labour_law_compliances (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        law_name VARCHAR(255) NOT NULL,
        compliance_type VARCHAR(100) NOT NULL,
        registration_no VARCHAR(100),
        authority VARCHAR(255),
        applicable_unit VARCHAR(255),
        frequency VARCHAR(50) DEFAULT 'annual',
        due_date DATE,
        last_filed_date DATE,
        next_due_date DATE,
        status ENUM('pending','filed','overdue','not_applicable') DEFAULT 'pending',
        document_path VARCHAR(500),
        penalty_amount DECIMAL(12,2) DEFAULT 0,
        assigned_to INT UNSIGNED,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_status (status),
        INDEX idx_due_date (due_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED $table\n";
} else {
    echo "SKIP $table\n";
}

$exists = $pdo->query("SELECT id FROM admin_menu_items WHERE url = '/admin/labour-law-compliance' LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_menu_items (tenant_id, name, url, icon, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES
        (1, 'Labour Law Compliance', '/admin/labour-law-compliance', 'fas fa-gavel', 71, 'hrm', 50, 'hrm.view', 1, NOW(), NOW())");
    $menuId = (int)$pdo->lastInsertId();
    echo "SEEDED menu id=$menuId\n";
    foreach (['admin','super_admin','manager','accountant'] as $role) {
        $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES
            (1, '$role', $menuId, 1, 1, 1, 1, NOW(), NOW())");
    }
    echo "SEEDED role permissions\n";
} else {
    echo "SKIP menu\n";
}
