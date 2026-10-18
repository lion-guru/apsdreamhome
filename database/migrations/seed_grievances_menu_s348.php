<?php
/**
 * Migration: Seed Employee Grievances Menu Items (Admin HRM + Employee Self-Service)
 * Session: 348
 * Idempotent: Safe to re-run, checks url existence before inserting.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running seed_grievances_menu_s348...\n";

// 1. Admin HRM: Employee Grievances
$stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
$stmt->execute(['/admin/grievances']);
$adminGrievanceId = $stmt->fetchColumn();

if ($adminGrievanceId) {
    echo "SKIP admin_menu_items (/admin/grievances) - already exists with ID: {$adminGrievanceId}\n";
} else {
    $maxOrderRow = $pdo->query("SELECT COALESCE(MAX(order_index), 0) AS max_order FROM admin_menu_items WHERE section = 'hrm'")->fetch();
    $nextOrder = (int)($maxOrderRow['max_order'] ?? 0) + 1;

    $insertStmt = $pdo->prepare("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, 'Employee Grievances', 'fas fa-balance-scale', '/admin/grievances', NULL, 'hrm', ?, 'hrm.view', 1, NOW(), NOW())");
    $insertStmt->execute([$nextOrder]);
    $adminGrievanceId = $pdo->lastInsertId();
    echo "SEEDED admin_menu_items#{$adminGrievanceId} (Employee Grievances -> /admin/grievances)\n";
}

if ($adminGrievanceId) {
    $adminRoles = ['super_admin', 'admin', 'director', 'ceo', 'coo', 'hr_director', 'hr_manager'];
    foreach ($adminRoles as $role) {
        $chk = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE role = ? AND menu_item_id = ? LIMIT 1");
        $chk->execute([$role, $adminGrievanceId]);
        if (!$chk->fetchColumn()) {
            $ins = $pdo->prepare("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, ?, ?, 1, 1, 1, 1, NOW(), NOW())");
            $ins->execute([$role, $adminGrievanceId]);
            echo "SEEDED perm for {$role} on menu#{$adminGrievanceId}\n";
        }
    }
}

// 2. Employee Self-Service: My Grievances
$stmt->execute(['/employee/self-service/grievances']);
$empGrievanceId = $stmt->fetchColumn();

if ($empGrievanceId) {
    echo "SKIP admin_menu_items (/employee/self-service/grievances) - already exists with ID: {$empGrievanceId}\n";
} else {
    $parentId = $pdo->query("SELECT id FROM admin_menu_items WHERE url = '/employee/self-service' AND section = 'employee' LIMIT 1")->fetchColumn();
    if (!$parentId) {
        $parentId = $pdo->query("SELECT id FROM admin_menu_items WHERE (name = 'Self Service' OR name = 'Quality Control') AND section = 'employee' LIMIT 1")->fetchColumn();
    }

    $maxOrderRow = $pdo->query("SELECT COALESCE(MAX(order_index), 0) AS max_order FROM admin_menu_items WHERE section = 'employee'")->fetch();
    $nextOrder = (int)($maxOrderRow['max_order'] ?? 0) + 1;

    $insertStmt = $pdo->prepare("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, 'My Grievances', 'fas fa-balance-scale', '/employee/self-service/grievances', ?, 'employee', ?, 'employee', 1, NOW(), NOW())");
    $insertStmt->execute([$parentId ?: null, $nextOrder]);
    $empGrievanceId = $pdo->lastInsertId();
    echo "SEEDED admin_menu_items#{$empGrievanceId} (My Grievances -> /employee/self-service/grievances)\n";
}

if ($empGrievanceId) {
    $empRoles = ['employee', 'agent', 'telecaller', 'manager', 'admin', 'super_admin'];
    foreach ($empRoles as $role) {
        $chk = $pdo->prepare("SELECT id FROM admin_role_menu_permissions WHERE role = ? AND menu_item_id = ? LIMIT 1");
        $chk->execute([$role, $empGrievanceId]);
        if (!$chk->fetchColumn()) {
            $ins = $pdo->prepare("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, ?, ?, 1, 1, 1, 0, NOW(), NOW())");
            $ins->execute([$role, $empGrievanceId]);
            echo "SEEDED perm for {$role} on menu#{$empGrievanceId}\n";
        }
    }
}

echo "Migration seed_grievances_menu_s348 completed.\n";
