<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SHOW TABLES LIKE 'pf_esi_config'")->fetchColumn();
if ($exists) {
    echo "SKIP pf_esi_config\n";
} else {
    $pdo->exec("CREATE TABLE pf_esi_config (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        config_key VARCHAR(100) NOT NULL,
        config_value VARCHAR(255) NOT NULL,
        description TEXT,
        effective_from DATE,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_config_key (config_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED pf_esi_config\n";
}

$exists = $pdo->query("SHOW TABLES LIKE 'pf_esi_deductions'")->fetchColumn();
if ($exists) {
    echo "SKIP pf_esi_deductions\n";
} else {
    $pdo->exec("CREATE TABLE pf_esi_deductions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        employee_id INT UNSIGNED NOT NULL,
        employee_name VARCHAR(255),
        department VARCHAR(100),
        designation VARCHAR(150),
        month_year VARCHAR(7) NOT NULL,
        basic_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
        gross_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
        pf_wage DECIMAL(15,2) NOT NULL DEFAULT 0,
        esi_wage DECIMAL(15,2) NOT NULL DEFAULT 0,
        pf_employee DECIMAL(15,2) NOT NULL DEFAULT 0,
        pf_employer DECIMAL(15,2) NOT NULL DEFAULT 0,
        pf_admin_charges DECIMAL(15,2) NOT NULL DEFAULT 0,
        esi_employee DECIMAL(15,2) NOT NULL DEFAULT 0,
        esi_employer DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_deduction DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_employer DECIMAL(15,2) NOT NULL DEFAULT 0,
        status ENUM('draft','processed','paid') DEFAULT 'draft',
        processed_by INT,
        processed_at DATETIME,
        paid_at DATETIME,
        payment_ref VARCHAR(100),
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_emp_month (employee_id, month_year),
        INDEX idx_month (month_year),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED pf_esi_deductions\n";
}

$defaults = [
    ['pf_employee_rate', '12', 'Employee PF contribution rate (% of PF wage)'],
    ['pf_employer_rate', '12', 'Employer PF contribution rate (% of PF wage)'],
    ['pf_admin_rate', '1.61', 'PF admin charges rate (% of PF wage)'],
    ['pf_wage_limit', '15000', 'Monthly PF wage ceiling (basic+DA)'],
    ['esi_employee_rate', '0.75', 'Employee ESI contribution rate (% of gross)'],
    ['esi_employer_rate', '3.25', 'Employer ESI contribution rate (% of gross)'],
    ['esi_wage_limit', '21000', 'Monthly ESI wage ceiling (gross)'],
    ['esi_disablement_limit', '21000', 'ESI disablement benefit wage limit'],
];
foreach ($defaults as $d) {
    $exists = $pdo->query("SELECT id FROM pf_esi_config WHERE config_key = '{$d[0]}' LIMIT 1")->fetchColumn();
    if (!$exists) {
        $pdo->exec("INSERT INTO pf_esi_config (tenant_id, config_key, config_value, description, effective_from, is_active) VALUES (1, '{$d[0]}', '{$d[1]}', '{$d[2]}', CURDATE(), 1)");
        echo "SEEDED pf_esi_config.{$d[0]} = {$d[1]}\n";
    } else {
        echo "SKIP pf_esi_config.{$d[0]}\n";
    }
}

$pdo->exec("INSERT IGNORE INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active) VALUES
    (350, 1, 'PF/ESI Compliance', 'fas fa-shield-alt', '/admin/pf-esi', NULL, 'finance', 29, 'financial.view', 1)");
$exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = 'admin' AND menu_item_id = 350 LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'admin', 350, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'super_admin', 350, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'finance_director', 350, 1, 1, 1, 0)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'accountant', 350, 1, 1, 0, 0)");
}
echo "Menu + permissions seeded\n";
echo "Done.\n";