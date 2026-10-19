<?php
/**
 * Migration: Seed Admin Menu for Phase 5 HR Services
 * Session: 355
 * Idempotent: Safe to re-run, checks before inserting
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();

    // Get or create parent menu sections using permission_key
    $hrSection = $db->fetch("SELECT id FROM admin_menu_items WHERE permission_key = 'hr_management' AND parent_id IS NULL LIMIT 1");
    if (!$hrSection) {
        $pdo->execute("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at) VALUES (1, 'HR Management', 'fas fa-users-cog', '#', NULL, 'hr', 10, 'hr_management', 1, NOW())");
        $hrSectionId = (int)$pdo->lastInsertId();
        echo "Created HR Management section\n";
    } else {
        $hrSectionId = $hrSection['id'];
        echo "HR Management section exists (id: $hrSectionId)\n";
    }

    // Section: Finance (for payroll)
    $financeSection = $db->fetch("SELECT id FROM admin_menu_items WHERE permission_key = 'finance' AND parent_id IS NULL LIMIT 1");
    if (!$financeSection) {
        $pdo->execute("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at) VALUES (1, 'Finance', 'fas fa-rupee-sign', '#', NULL, 'finance', 20, 'finance', 1, NOW())");
        $financeSectionId = (int)$pdo->lastInsertId();
    } else {
        $financeSectionId = $financeSection['id'];
    }

    // Section: Recruitment
    $recruitSection = $db->fetch("SELECT id FROM admin_menu_items WHERE permission_key = 'recruitment' AND parent_id IS NULL LIMIT 1");
    if (!$recruitSection) {
        $pdo->execute("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at) VALUES (1, 'Recruitment', 'fas fa-briefcase', '#', NULL, 'hr', 15, 'recruitment', 1, NOW())");
        $recruitSectionId = (int)$pdo->lastInsertId();
    } else {
        $recruitSectionId = $recruitSection['id'];
    }

    // Define menu items to seed
    $menuItems = [
        // HR Management Section
        [
            'permission_key' => 'expense_reimbursement',
            'title' => 'Expense Reimbursement',
            'icon' => 'fas fa-receipt',
            'parent_id' => $hrSectionId,
            'section' => 'hr',
            'route' => '/admin/expense-reimbursement',
            'sort_order' => 1,
        ],
        [
            'permission_key' => 'performance_goals',
            'title' => 'Performance & Goals',
            'icon' => 'fas fa-bullseye',
            'parent_id' => $hrSectionId,
            'section' => 'hr',
            'route' => '/admin/performance',
            'sort_order' => 2,
        ],
        [
            'permission_key' => 'payroll',
            'title' => 'Payroll Management',
            'icon' => 'fas fa-money-bill-wave',
            'parent_id' => $financeSectionId,
            'section' => 'finance',
            'route' => '/admin/payroll',
            'sort_order' => 1,
        ],
        [
            'permission_key' => 'recruitment_ats',
            'title' => 'Recruitment / ATS',
            'icon' => 'fas fa-user-plus',
            'parent_id' => $recruitSectionId,
            'section' => 'hr',
            'route' => '/admin/recruitment',
            'sort_order' => 1,
        ],
        [
            'permission_key' => 'document_vault',
            'title' => 'Document Vault',
            'icon' => 'fas fa-folder-open',
            'parent_id' => $hrSectionId,
            'section' => 'hr',
            'route' => '/admin/document-vault',
            'sort_order' => 3,
        ],
    ];

    foreach ($menuItems as $item) {
        $existing = $db->fetch("SELECT id FROM admin_menu_items WHERE permission_key = ? LIMIT 1", [$item['permission_key']]);
        if (!$existing) {
            $columns = ['tenant_id', 'name', 'icon', 'url', 'parent_id', 'section', 'order_index', 'permission_key', 'is_active', 'created_at'];
            $values = [
                1, $item['title'], $item['icon'], $item['route'], $item['parent_id'],
                $item['section'], $item['sort_order'], $item['permission_key'], 1, date('Y-m-d H:i:s')
            ];
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            $sql = "INSERT INTO admin_menu_items (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            $pdo->execute($sql, $values);
            $menuId = (int)$pdo->lastInsertId();
            echo "Created menu: {$item['title']} (id: $menuId)\n";

            // Grant permissions to admin roles
            $roles = ['super_admin', 'admin', 'director', 'ceo', 'coo', 'hr_director', 'hr_manager', 'manager'];
            foreach ($roles as $role) {
                $pdo->execute("
                    INSERT IGNORE INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at)
                    VALUES (?, ?, 1, 1, 1, 1, NOW())
                ", [$role, $menuId]);
            }
            echo "  Granted permissions for roles\n";
        } else {
            echo "Menu exists: {$item['title']}\n";
        }
    }

    // Employee Self-Service menu items (under parent_id for employee_self_service)
    $empSection = $db->fetch("SELECT id FROM admin_menu_items WHERE permission_key = 'employee_self_service' AND parent_id IS NULL LIMIT 1");
    if ($empSection) {
        $empMenuItems = [
            ['permission_key' => 'emp_expense_claims', 'title' => 'My Expense Claims', 'icon' => 'fas fa-receipt', 'parent_id' => $empSection['id'], 'section' => 'employee', 'route' => '/employee/self-service/expenses', 'sort_order' => 1],
            ['permission_key' => 'emp_performance', 'title' => 'My Performance & Goals', 'icon' => 'fas fa-bullseye', 'parent_id' => $empSection['id'], 'section' => 'employee', 'route' => '/employee/self-service/performance', 'sort_order' => 2],
            ['permission_key' => 'emp_payslips', 'title' => 'My Payslips', 'icon' => 'fas fa-file-invoice-dollar', 'parent_id' => $empSection['id'], 'section' => 'employee', 'route' => '/employee/self-service/payslips', 'sort_order' => 3],
            ['permission_key' => 'emp_documents', 'title' => 'My Documents', 'icon' => 'fas fa-folder', 'parent_id' => $empSection['id'], 'section' => 'employee', 'route' => '/employee/self-service/documents', 'sort_order' => 4],
            ['permission_key' => 'emp_leave_calendar', 'title' => 'Leave Calendar', 'icon' => 'fas fa-calendar-alt', 'parent_id' => $empSection['id'], 'section' => 'employee', 'route' => '/employee/self-service/leave-calendar', 'sort_order' => 5],
        ];

        foreach ($empMenuItems as $item) {
            $existing = $db->fetch("SELECT id FROM admin_menu_items WHERE permission_key = ? LIMIT 1", [$item['permission_key']]);
            if (!$existing) {
                $columns = ['tenant_id', 'name', 'icon', 'url', 'parent_id', 'section', 'order_index', 'permission_key', 'is_active', 'created_at'];
                $values = [
                    1, $item['title'], $item['icon'], $item['route'], $item['parent_id'],
                    $item['section'], $item['sort_order'], $item['permission_key'], 1, date('Y-m-d H:i:s')
                ];
                $placeholders = str_repeat('?,', count($columns) - 1) . '?';
                $sql = "INSERT INTO admin_menu_items (" . implode(', ', $columns) . ") VALUES ($placeholders)";
                $pdo->execute($sql, $values);
                echo "Created employee menu: {$item['title']}\n";
            } else {
                echo "Employee menu exists: {$item['title']}\n";
            }
        }
    }

    echo "\n=== HR MENU SEEDING COMPLETED SUCCESSFULLY ===\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}