<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$cols = [];
$r = $pdo->query("DESCRIBE purchase_orders");
foreach ($r as $row) { $cols[$row['Field']] = $row; }

$addCols = [
    'order_date' => "ADD COLUMN order_date DATE DEFAULT NULL",
    'expected_delivery' => "ADD COLUMN expected_delivery DATE DEFAULT NULL",
    'vendor_name' => "ADD COLUMN vendor_name VARCHAR(255) DEFAULT NULL",
    'vendor_contact' => "ADD COLUMN vendor_contact VARCHAR(100) DEFAULT NULL",
    'vendor_email' => "ADD COLUMN vendor_email VARCHAR(255) DEFAULT NULL",
    'vendor_address' => "ADD COLUMN vendor_address TEXT DEFAULT NULL",
    'subtotal' => "ADD COLUMN subtotal DECIMAL(15,2) DEFAULT 0.00",
    'tax_amount' => "ADD COLUMN tax_amount DECIMAL(15,2) DEFAULT 0.00",
    'discount_amount' => "ADD COLUMN discount_amount DECIMAL(15,2) DEFAULT 0.00",
    'total_amount' => "ADD COLUMN total_amount DECIMAL(15,2) DEFAULT 0.00",
    'notes' => "ADD COLUMN notes TEXT DEFAULT NULL",
    'terms_conditions' => "ADD COLUMN terms_conditions TEXT DEFAULT NULL",
    'delivery_location' => "ADD COLUMN delivery_location VARCHAR(255) DEFAULT NULL",
    'requested_by' => "ADD COLUMN requested_by INT DEFAULT NULL",
    'approved_by' => "ADD COLUMN approved_by INT DEFAULT NULL",
    'approved_at' => "ADD COLUMN approved_at DATETIME DEFAULT NULL",
    'received_by' => "ADD COLUMN received_by INT DEFAULT NULL",
    'received_at' => "ADD COLUMN received_at DATETIME DEFAULT NULL",
    'grn_number' => "ADD COLUMN grn_number VARCHAR(50) DEFAULT NULL",
    'department_id' => "ADD COLUMN department_id INT DEFAULT NULL",
    'project_id' => "ADD COLUMN project_id INT DEFAULT NULL",
    'colony_id' => "ADD COLUMN colony_id INT DEFAULT NULL",
    'payment_terms' => "ADD COLUMN payment_terms VARCHAR(100) DEFAULT NULL",
    'payment_status' => "ADD COLUMN payment_status ENUM('unpaid','partial','paid') DEFAULT 'unpaid'",
    'paid_amount' => "ADD COLUMN paid_amount DECIMAL(15,2) DEFAULT 0.00",
];

foreach ($addCols as $name => $ddl) {
    if (isset($cols[$name])) {
        echo "SKIP purchase_orders.$name\n";
        continue;
    }
    $pdo->exec("ALTER TABLE purchase_orders $ddl");
    echo "ADDED purchase_orders.$name\n";
}

$exists = $pdo->query("SHOW TABLES LIKE 'purchase_order_items'")->fetchColumn();
if ($exists) {
    echo "SKIP purchase_order_items\n";
} else {
    $pdo->exec("CREATE TABLE purchase_order_items (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        purchase_order_id INT UNSIGNED NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        item_description TEXT,
        unit VARCHAR(50) DEFAULT 'Nos',
        quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
        unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        discount_percent DECIMAL(5,2) DEFAULT 0.00,
        tax_percent DECIMAL(5,2) DEFAULT 0.00,
        line_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        received_qty DECIMAL(10,2) DEFAULT 0.00,
        accepted_qty DECIMAL(10,2) DEFAULT 0.00,
        rejected_qty DECIMAL(10,2) DEFAULT 0.00,
        rejection_reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_po (purchase_order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED purchase_order_items\n";
}

$exists = $pdo->query("SHOW TABLES LIKE 'grn_records'")->fetchColumn();
if ($exists) {
    echo "SKIP grn_records\n";
} else {
    $pdo->exec("CREATE TABLE grn_records (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        grn_number VARCHAR(50) NOT NULL,
        purchase_order_id INT UNSIGNED NOT NULL,
        received_by INT DEFAULT NULL,
        received_at DATETIME DEFAULT NULL,
        vendor_id INT DEFAULT NULL,
        vendor_name VARCHAR(255),
        invoice_number VARCHAR(100),
        invoice_date DATE,
        transport_name VARCHAR(255),
        vehicle_number VARCHAR(50),
        driver_name VARCHAR(255),
        driver_phone VARCHAR(50),
        challan_number VARCHAR(100),
        challan_date DATE,
        total_items INT DEFAULT 0,
        total_quantity DECIMAL(12,2) DEFAULT 0,
        total_amount DECIMAL(15,2) DEFAULT 0,
        remarks TEXT,
        status ENUM('draft','verified','accepted','partial','rejected') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_po (purchase_order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED grn_records\n";
}

$pdo->exec("INSERT IGNORE INTO admin_menu_items (id, tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active) VALUES
    (349, 1, 'Purchase Orders', 'fas fa-shopping-cart', '/admin/purchase-orders', NULL, 'finance', 28, 'financial.view', 1)");
$exists = $pdo->query("SELECT id FROM admin_role_menu_permissions WHERE role = 'admin' AND menu_item_id = 349 LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'admin', 349, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'super_admin', 349, 1, 1, 1, 1)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'finance_director', 349, 1, 1, 1, 0)");
    $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, 'accountant', 349, 1, 0, 0, 0)");
}
echo "Menu + permissions seeded\n";
echo "Done.\n";