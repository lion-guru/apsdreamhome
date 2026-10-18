<?php
/**
 * Migration: Create Recurring Journal Entries (Gap 27 - Accounting)
 * Session: 326
 * Idempotent: Safe to re-run, prints SKIP if table/columns already exist
 *
 * Creates recurring_journal_entries (templates) + recurring_journal_entry_lines
 * so the same JE can auto-post on a schedule (weekly/monthly/quarterly/yearly).
 * A cron (process-due) fires entries whose next_execution_date <= today.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// 1) recurring_journal_entries table
$exists = $pdo->query("SHOW TABLES LIKE 'recurring_journal_entries'")->fetchColumn();
if ($exists) {
    echo "SKIP recurring_journal_entries - already exists\n";
} else {
    $pdo->exec("CREATE TABLE recurring_journal_entries (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        template_name VARCHAR(100) NOT NULL,
        description TEXT,
        entry_type ENUM('manual','system','adjustment','closing') DEFAULT 'manual',
        frequency ENUM('weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
        start_date DATE NOT NULL,
        end_date DATE DEFAULT NULL,
        next_execution_date DATE NOT NULL,
        last_execution_date DATE DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        execution_count INT UNSIGNED NOT NULL DEFAULT 0,
        max_executions INT UNSIGNED DEFAULT NULL,
        created_by INT UNSIGNED DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant_active (tenant_id, is_active),
        INDEX idx_next_exec (next_execution_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED recurring_journal_entries\n";
}

// 2) recurring_journal_entry_lines table
$exists = $pdo->query("SHOW TABLES LIKE 'recurring_journal_entry_lines'")->fetchColumn();
if ($exists) {
    echo "SKIP recurring_journal_entry_lines - already exists\n";
} else {
    $pdo->exec("CREATE TABLE recurring_journal_entry_lines (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        recurring_je_id INT UNSIGNED NOT NULL,
        account_id INT UNSIGNED NOT NULL,
        debit_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        credit_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        description TEXT,
        line_order INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_recurring (recurring_je_id),
        INDEX idx_account (account_id),
        CONSTRAINT fk_rje_recurring FOREIGN KEY (recurring_je_id) REFERENCES recurring_journal_entries(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED recurring_journal_entry_lines\n";
}

// Seed menu item under finance (sibling of Purchase Orders #349 / GRN #358)
$maxIdRow = $pdo->query("SELECT MAX(id) AS max_id FROM admin_menu_items")->fetch();
$nextId = (int)($maxIdRow['max_id'] ?? 0) + 1;

$menuItems = [
    [
        'id' => $nextId,
        'tenant_id' => 1,
        'name' => 'Recurring Journal Entries',
        'icon' => 'fas fa-history',
        'url' => '/admin/recurring-journal-entries',
        'parent_id' => null,
        'section' => 'finance',
        'order_index' => 30,
        'permission_key' => 'financial.view',
        'is_active' => 1
    ]
];

foreach ($menuItems as $item) {
    $exists = $pdo->query("SELECT id FROM admin_menu_items WHERE id = {$item['id']} LIMIT 1")->fetchColumn();
    if ($exists) {
        echo "SKIP admin_menu_items#{$item['id']} ({$item['name']}) - already exists\n";
        continue;
    }
    $stmt = $pdo->prepare("
        INSERT INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $item['id'], $item['tenant_id'], $item['name'], $item['icon'], $item['url'],
        $item['parent_id'], $item['section'], $item['order_index'], $item['permission_key'], $item['is_active']
    ]);
    echo "SEEDED admin_menu_items#{$item['id']} ({$item['name']})\n";
}

// Role permissions
$rolesFull = ['admin', 'super_admin'];
$rolesLimited = ['manager', 'ceo', 'cfo', 'coo', 'finance_director', 'finance_manager', 'senior_accountant', 'chartered_accountant'];
$rolesViewOnly = ['accountant'];

$permStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete)
    VALUES (1, ?, ?, 1, 1, 1, 1)
");

foreach ($menuItems as $item) {
    $menuId = $item['id'];
    foreach ($rolesFull as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if ($exists) { echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n"; }
        else { $permStmt->execute([$role, $menuId]); echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - full\n"; }
    }
    foreach ($rolesLimited as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) { $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 1, 1, 0)"); echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - limited\n"; }
        else { echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n"; }
    }
    foreach ($rolesViewOnly as $role) {
        $exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$exists) { $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, '$role', $menuId, 1, 0, 0, 0)"); echo "SEEDED admin_role_menu_permissions ($role, menu#$menuId) - view only\n"; }
        else { echo "SKIP admin_role_menu_permissions ($role, menu#$menuId)\n"; }
    }
}

echo "Done.\n";