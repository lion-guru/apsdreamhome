<?php
/**
 * Employee Sub-Role System Migration
 * 
 * Maps employee designations/departments to RBAC sub-roles.
 * When role=employee, AdminMenuService looks up designation → sub-role → menu permissions.
 * 
 * Run: php scripts/migrate_employee_sub_roles.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== EMPLOYEE SUB-ROLE SYSTEM MIGRATION ===\n\n";

    // 1. Create employee_designation_roles mapping table
    echo "[1] Creating employee_designation_roles table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `employee_designation_roles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `designation` VARCHAR(100) NOT NULL,
        `department` VARCHAR(100) DEFAULT NULL,
        `sub_role` VARCHAR(50) NOT NULL,
        `dashboard_view` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_designation_dept` (`designation`, `department`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  OK\n";

    // 2. Seed designation -> sub-role mapping
    echo "\n[2] Seeding designation->sub-role mappings...\n";

    $mappings = [
        // HR Department
        ['Manager',          'HR',        'employee_hr_manager'],
        ['Executive',        'HR',        'employee_hr_executive'],
        ['Senior Executive', 'HR',        'employee_hr_executive'],
        ['Officer',          'HR',        'employee_hr_executive'],
        // Land Management Department
        ['Manager',          'Land',      'employee_land_manager'],
        ['Executive',        'Land',      'employee_land_executive'],
        ['Senior Executive', 'Land',      'employee_land_executive'],
        ['Officer',          'Land',      'employee_land_executive'],
        // Legal Department
        ['Manager',          'Legal',     'employee_legal_manager'],
        ['Executive',        'Legal',     'employee_legal_executive'],
        ['Senior Executive', 'Legal',     'employee_legal_executive'],
        ['Officer',          'Legal',     'employee_legal_executive'],
        ['Analyst',          'Legal',     'employee_legal_executive'],
        // Finance/Accounts Department
        ['Manager',          'Finance',   'employee_finance_manager'],
        ['Executive',        'Finance',   'employee_finance_executive'],
        ['Senior Executive', 'Finance',   'employee_finance_executive'],
        ['Officer',          'Finance',   'employee_finance_executive'],
        ['Analyst',          'Finance',   'employee_finance_executive'],
        ['CA',               'Finance',   'employee_finance_manager'],
        // Marketing Department
        ['Manager',          'Marketing', 'employee_marketing_manager'],
        ['Executive',        'Marketing', 'employee_marketing_executive'],
        ['Senior Executive', 'Marketing', 'employee_marketing_executive'],
        ['Officer',          'Marketing', 'employee_marketing_executive'],
        // IT Department
        ['Manager',          'IT',        'employee_it_manager'],
        ['Executive',        'IT',        'employee_it_executive'],
        ['Senior Executive', 'IT',        'employee_it_executive'],
        ['Officer',          'IT',        'employee_it_executive'],
        // Operations Department
        ['Manager',          'Operations','employee_operations_manager'],
        ['Executive',        'Operations','employee_operations_executive'],
        ['Senior Executive', 'Operations','employee_operations_executive'],
        ['Officer',          'Operations','employee_operations_executive'],
        ['Analyst',          'Operations','employee_operations_executive'],
        // Sales Department
        ['Manager',          'Sales',     'employee_sales_manager'],
        ['Executive',        'Sales',     'employee_sales_executive'],
        ['Senior Executive', 'Sales',     'employee_sales_executive'],
        ['Officer',          'Sales',     'employee_sales_executive'],
        ['Analyst',          'Sales',     'employee_sales_executive'],
        // Telecalling (cross-department)
        ['Telecaller',       null,        'employee_telecaller'],
        ['Sr. Telecaller',   null,        'employee_telecaller'],
        ['Telecalling Executive', null,   'employee_telecaller'],
        // Default fallback
        ['*',                null,        'employee_general'],
    ];

    $ins = $pdo->prepare("INSERT INTO employee_designation_roles (designation, department, sub_role, dashboard_view) VALUES (?, ?, ?, NULL)
                          ON DUPLICATE KEY UPDATE sub_role = VALUES(sub_role)");

    $inserted = 0;
    foreach ($mappings as $m) {
        $ins->execute([$m[0], $m[1], $m[2]]);
        $inserted++;
    }
    echo "  OK: $inserted mappings\n";

    // 3. Create sub-role menu permissions
    echo "\n[3] Seeding sub-role menu permissions...\n";

    $menuItems = $pdo->query("SELECT id, section FROM admin_menu_items WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
    $sectionMap = [];
    foreach ($menuItems as $item) {
        $sectionMap[$item['section']][] = $item['id'];
    }

    $subRoleSections = [
        'employee_hr_manager'        => ['dashboards', 'hrm', 'users', 'settings'],
        'employee_hr_executive'      => ['dashboards', 'hrm'],
        'employee_land_manager'      => ['dashboards', 'properties', 'locations', 'bookings'],
        'employee_land_executive'    => ['dashboards', 'properties'],
        'employee_legal_manager'     => ['dashboards', 'legal', 'settings'],
        'employee_legal_executive'   => ['dashboards', 'legal'],
        'employee_finance_manager'   => ['dashboards', 'finance', 'reports', 'settings'],
        'employee_finance_executive' => ['dashboards', 'finance', 'reports'],
        'employee_marketing_manager'   => ['dashboards', 'marketing', 'cms', 'settings'],
        'employee_marketing_executive' => ['dashboards', 'marketing', 'cms'],
        'employee_it_manager'        => ['dashboards', 'settings', 'system', 'reports'],
        'employee_it_executive'      => ['dashboards', 'settings'],
        'employee_operations_manager'   => ['dashboards', 'bookings', 'properties', 'reports', 'settings'],
        'employee_operations_executive' => ['dashboards', 'bookings', 'properties'],
        'employee_sales_manager'     => ['dashboards', 'bookings', 'properties', 'crm', 'reports', 'mlm'],
        'employee_sales_executive'   => ['dashboards', 'bookings', 'properties', 'crm'],
        'employee_telecaller'        => ['dashboards', 'crm', 'properties'],
        'employee_general'           => ['dashboards'],
    ];

    $totalPerms = 0;
    foreach ($subRoleSections as $subRole => $sections) {
        $pdo->prepare("DELETE FROM admin_role_menu_permissions WHERE role = ?")->execute([$subRole]);

        $count = 0;
        foreach ($sections as $section) {
            if (isset($sectionMap[$section])) {
                $ins2 = $pdo->prepare("INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete)
                                       VALUES (?, ?, 1, 1, 1, 0) ON DUPLICATE KEY UPDATE can_view = 1");
                foreach ($sectionMap[$section] as $menuItemId) {
                    $ins2->execute([$subRole, $menuItemId]);
                    $count++;
                }
            }
        }
        $totalPerms += $count;
        echo "  $subRole: $count items\n";
    }
    echo "  Total: $totalPerms permissions\n";

    // 4. Verify current employees
    echo "\n[4] Current employee designation mapping:\n";
    $stmt = $pdo->query("
        SELECT u.id, u.name, e.designation, e.department, edr.sub_role
        FROM users u
        LEFT JOIN employees e ON e.user_id = u.id
        LEFT JOIN employee_designation_roles edr ON edr.designation = e.designation AND (edr.department = e.department OR edr.department IS NULL)
        WHERE u.role = 'employee'
        ORDER BY u.id
    ");
    while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $subRole = $emp['sub_role'] ?? '(no mapping)';
        echo "  ID:{$emp['id']} | {$emp['name']} | {$emp['designation']}/{$emp['department']} -> $subRole\n";
    }

    echo "\n=== MIGRATION COMPLETE ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
