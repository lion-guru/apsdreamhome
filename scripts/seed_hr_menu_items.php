<?php
/**
 * Seed admin menu items for new HR modules (idempotent).
 * Mirrors role permissions from Payroll Management (id 72).
 * Run: php scripts/seed_hr_menu_items.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

$items = [
    ['Salary Advances', 'fas fa-hand-holding-usd', '/admin/salary/advances', 12, 'payroll.view'],
    ['Reimbursements', 'fas fa-receipt', '/admin/salary/reimbursements', 13, 'payroll.view'],
    ['Salary Arrears', 'fas fa-history', '/admin/salary/arrears/history', 14, 'payroll.view'],
    ['F&F Settlements', 'fas fa-file-invoice-dollar', '/admin/fnf/history', 15, 'payroll.view'],
    ['F&F Calculator', 'fas fa-calculator', '/admin/fnf/calculator', 16, 'payroll.view'],
    ['Employee Assets', 'fas fa-laptop', '/admin/fnf/assets', 17, 'payroll.view'],
    ['Gratuity', 'fas fa-award', '/admin/gratuity/report', 18, 'payroll.view'],
    ['Shift Roster', 'fas fa-calendar-alt', '/admin/shift-roster/roster', 19, 'hrm.view'],
    ['Overtime Requests', 'fas fa-clock', '/admin/shift-roster/overtime-requests', 20, 'hrm.view'],
];

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();

    $findStmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? AND tenant_id = ? LIMIT 1");
    $insStmt = $pdo->prepare("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, ?, ?, ?, NULL, 'hrm', ?, ?, 1, NOW(), NOW())");

    // Roles/grants mirrored from Payroll Management (id 72)
    $grants = $pdo->query("SELECT role, can_view, can_create, can_edit, can_delete FROM admin_role_menu_permissions WHERE menu_item_id = 72")->fetchAll(PDO::FETCH_ASSOC);
    $permStmt = $pdo->prepare("INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    foreach ($items as [$name, $icon, $url, $order, $permKey]) {
        $findStmt->execute([$url, 1]);
        $row = $findStmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "SKIP (exists): $url (id {$row['id']})\n";
            $menuId = (int)$row['id'];
        } else {
            $insStmt->execute([$name, $icon, $url, $order, $permKey]);
            $menuId = (int)$pdo->lastInsertId();
            echo "INSERTED: $name -> $url (id $menuId)\n";
        }
        $mirrored = 0;
        foreach ($grants as $g) {
            $permStmt->execute([$g['role'], $menuId, $g['can_view'], $g['can_create'], $g['can_edit'], $g['can_delete']]);
            $mirrored += $permStmt->rowCount();
        }
        echo "  permissions mirrored for $mirrored role(s)\n";
    }
    echo "\nDone.\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
