<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$tables = [
    'inter_company_entities' => "
        CREATE TABLE inter_company_entities (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            entity_code VARCHAR(50) NOT NULL,
            entity_name VARCHAR(255) NOT NULL,
            entity_type ENUM('parent','subsidiary','sister','branch') DEFAULT 'subsidiary',
            country VARCHAR(100),
            currency VARCHAR(10) DEFAULT 'INR',
            tax_id VARCHAR(100),
            contact_person VARCHAR(255),
            contact_email VARCHAR(255),
            contact_phone VARCHAR(50),
            address TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_entity_code (tenant_id, entity_code),
            INDEX idx_tenant (tenant_id),
            INDEX idx_type (entity_type),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'inter_company_transactions' => "
        CREATE TABLE inter_company_transactions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            transaction_number VARCHAR(50) NOT NULL,
            transaction_date DATE NOT NULL,
            from_entity_id BIGINT UNSIGNED NOT NULL,
            to_entity_id BIGINT UNSIGNED NOT NULL,
            transaction_type ENUM('sale','purchase','service','loan','expense_allocation','fixed_asset_transfer','other') DEFAULT 'sale',
            description TEXT,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            currency VARCHAR(10) DEFAULT 'INR',
            exchange_rate DECIMAL(10,4) DEFAULT 1,
            amount_inr DECIMAL(15,2) NOT NULL DEFAULT 0,
            reference_document VARCHAR(255),
            status ENUM('draft','posted','reconciled','cancelled') DEFAULT 'draft',
            posted_by INT,
            posted_at DATETIME,
            reconciled_by INT,
            reconciled_at DATETIME,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_txn_number (tenant_id, transaction_number),
            INDEX idx_tenant (tenant_id),
            INDEX idx_from_entity (from_entity_id),
            INDEX idx_to_entity (to_entity_id),
            INDEX idx_date (transaction_date),
            INDEX idx_type (transaction_type),
            INDEX idx_status (status),
            FOREIGN KEY (from_entity_id) REFERENCES inter_company_entities(id),
            FOREIGN KEY (to_entity_id) REFERENCES inter_company_entities(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'inter_company_reconciliations' => "
        CREATE TABLE inter_company_reconciliations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            reconciliation_number VARCHAR(50) NOT NULL,
            reconciliation_date DATE NOT NULL,
            from_entity_id BIGINT UNSIGNED NOT NULL,
            to_entity_id BIGINT UNSIGNED NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            from_entity_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
            to_entity_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
            difference DECIMAL(15,2) NOT NULL DEFAULT 0,
            status ENUM('open','reconciled','partial') DEFAULT 'open',
            reconciled_by INT,
            reconciled_at DATETIME,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_rec_number (tenant_id, reconciliation_number),
            INDEX idx_tenant (tenant_id),
            INDEX idx_from_entity (from_entity_id),
            INDEX idx_to_entity (to_entity_id),
            INDEX idx_period (period_start, period_end),
            INDEX idx_status (status),
            FOREIGN KEY (from_entity_id) REFERENCES inter_company_entities(id),
            FOREIGN KEY (to_entity_id) REFERENCES inter_company_entities(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables as $table => $sql) {
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetchColumn();
    if ($exists) {
        echo "SKIP $table\n";
    } else {
        $pdo->exec($sql);
        echo "CREATED $table\n";
    }
}

// Seed default entities
$exists = $pdo->query("SELECT id FROM inter_company_entities WHERE entity_code = 'PARENT' LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO inter_company_entities (tenant_id, entity_code, entity_name, entity_type, country, currency, contact_person, contact_email) VALUES
        (1, 'PARENT', 'APS Dream Home - Head Office', 'parent', 'India', 'INR', 'Admin', 'admin@apsdreamhome.com'),
        (1, 'SUB001', 'APS Dream Home - Gorakhpur Branch', 'subsidiary', 'India', 'INR', 'Branch Manager', 'gorakhpur@apsdreamhome.com'),
        (1, 'SUB002', 'APS Dream Home - Lucknow Branch', 'subsidiary', 'India', 'INR', 'Branch Manager', 'lucknow@apsdreamhome.com')");
    echo "SEEDED default entities\n";
} else {
    echo "SKIP default entities\n";
}

// Menu
$exists = $pdo->query("SELECT id FROM admin_menu_items WHERE url = '/admin/inter-company' LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_menu_items (tenant_id, name, url, icon, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES
        (1, 'Inter-Company Transactions', '/admin/inter-company', 'fas fa-exchange-alt', NULL, 'finance', 30, 'financial.view', 1, NOW(), NOW())");
    $menuId = (int)$pdo->lastInsertId();
    echo "SEEDED menu id=$menuId\n";
    $roles = ['admin','super_admin','manager','ceo','cfo','coo','finance_director','accountant'];
    foreach ($roles as $role) {
        $ex = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE tenant_id = 1 AND role = '$role' AND menu_item_id = $menuId LIMIT 1")->fetchColumn();
        if (!$ex) {
            $canEdit = in_array($role, ['admin','super_admin','manager','ceo','cfo','coo','finance_director']) ? 1 : 0;
            $canDel = in_array($role, ['admin','super_admin']) ? 1 : 0;
            $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, '$role', $menuId, 1, $canEdit, $canEdit, $canDel, NOW(), NOW())");
        }
    }
    echo "SEEDED role permissions\n";
} else {
    echo "SKIP menu\n";
}
echo "Done.\n";
