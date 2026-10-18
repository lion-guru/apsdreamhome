<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$tables = ['fixed_assets', 'asset_depreciation_schedules'];
foreach ($tables as $t) {
    $exists = $pdo->query("SHOW TABLES LIKE '$t'")->fetchColumn();
    if ($exists) {
        echo "SKIP $t (already exists)\n";
        continue;
    }

    if ($t === 'fixed_assets') {
        $pdo->exec("CREATE TABLE fixed_assets (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            asset_code VARCHAR(50) NOT NULL,
            asset_name VARCHAR(255) NOT NULL,
            category ENUM('building','vehicle','furniture','equipment','land','computer','other') NOT NULL DEFAULT 'equipment',
            description TEXT,
            serial_number VARCHAR(100),
            purchase_date DATE NOT NULL,
            purchase_cost DECIMAL(15,2) NOT NULL,
            salvage_value DECIMAL(15,2) DEFAULT 0.00,
            useful_life_years INT NOT NULL DEFAULT 5,
            depreciation_method ENUM('straight_line','declining_balance') NOT NULL DEFAULT 'straight_line',
            depreciation_rate DECIMAL(5,2) DEFAULT NULL,
            current_book_value DECIMAL(15,2) NOT NULL,
            location VARCHAR(255),
            department_id INT,
            assigned_to INT,
            status ENUM('active','disposed','sold','stolen','damaged','retired') NOT NULL DEFAULT 'active',
            disposal_date DATE,
            disposal_amount DECIMAL(15,2),
            invoice_number VARCHAR(100),
            vendor_name VARCHAR(255),
            warranty_expiry DATE,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_asset_code (asset_code),
            INDEX idx_category (category),
            INDEX idx_status (status),
            INDEX idx_department (department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if ($t === 'asset_depreciation_schedules') {
        $pdo->exec("CREATE TABLE asset_depreciation_schedules (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            asset_id BIGINT UNSIGNED NOT NULL,
            period_year INT NOT NULL,
            period_month INT NOT NULL,
            opening_book_value DECIMAL(15,2) NOT NULL,
            depreciation_amount DECIMAL(15,2) NOT NULL,
            closing_book_value DECIMAL(15,2) NOT NULL,
            cumulative_depreciation DECIMAL(15,2) NOT NULL,
            is_posted TINYINT(1) DEFAULT 0,
            posted_at DATETIME,
            posted_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_asset_period (asset_id, period_year, period_month),
            INDEX idx_asset (asset_id),
            INDEX idx_period (period_year, period_month)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    echo "CREATED $t\n";
}

$pdo->exec("INSERT IGNORE INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active) VALUES
    (348, 1, 'Fixed Assets', 'fas fa-building', '/admin/fixed-assets', NULL, 'finance', 27, 'financial.view', 1)");
$exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = 'admin' AND menu_item_id = 348 LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'admin', 348, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'super_admin', 348, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'finance_director', 348, 1, 1, 1, 0)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'accountant', 348, 1, 0, 0, 0)");
}
echo "Menu + permissions seeded\n";
echo "Done.\n";