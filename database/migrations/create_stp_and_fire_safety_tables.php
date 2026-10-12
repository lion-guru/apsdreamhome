<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/../../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. township_stp_logs
$pdo->exec("
CREATE TABLE IF NOT EXISTS township_stp_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colony_id INT NOT NULL,
    log_date DATE NOT NULL,
    stp_plant_code VARCHAR(100) NOT NULL,
    raw_sewage_inflow_kl DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    treated_water_output_kl DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    inlet_bod_mg_l DECIMAL(6, 2) NULL,
    outlet_bod_mg_l DECIMAL(6, 2) NULL,
    inlet_cod_mg_l DECIMAL(6, 2) NULL,
    outlet_cod_mg_l DECIMAL(6, 2) NULL,
    outlet_tss_mg_l DECIMAL(6, 2) NULL,
    outlet_ph DECIMAL(4, 2) NULL,
    chlorine_dosing_ppm DECIMAL(4, 2) NULL,
    aeration_blower_hours DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    water_reuse_purpose ENUM('gardening_horticulture', 'toilet_dual_flush', 'road_washing_dust_suppression', 'groundwater_recharge', 'overspill_drain') NOT NULL DEFAULT 'gardening_horticulture',
    sludge_cake_disposed_kg DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
    uppcb_compliance_status ENUM('fully_compliant', 'minor_deviation', 'critical_violation', 'sample_sent_lab') NOT NULL DEFAULT 'fully_compliant',
    operator_name VARCHAR(150) NULL,
    operator_phone VARCHAR(20) NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_stp (colony_id, log_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Table township_stp_logs created/verified.\n";

// 2. township_fire_safety_logs
$pdo->exec("
CREATE TABLE IF NOT EXISTS township_fire_safety_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colony_id INT NOT NULL,
    inspection_date DATE NOT NULL,
    fire_zone_identifier VARCHAR(150) NOT NULL,
    static_line_pressure_bar DECIMAL(4, 2) NOT NULL DEFAULT 7.00,
    jockey_pump_status ENUM('auto_standby', 'running_pressurizing', 'manual_mode', 'fault_tripped') NOT NULL DEFAULT 'auto_standby',
    main_electric_pump_status ENUM('operational_ready', 'service_due', 'phase_trip', 'breakdown') NOT NULL DEFAULT 'operational_ready',
    diesel_engine_backup_status ENUM('operational_ready', 'battery_weak', 'fuel_low', 'cranking_failure') NOT NULL DEFAULT 'operational_ready',
    fire_water_storage_kl DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
    hydrant_valves_tested INT NOT NULL DEFAULT 0,
    hydrant_valves_healthy INT NOT NULL DEFAULT 0,
    hose_boxes_equipped INT NOT NULL DEFAULT 0,
    extinguishers_checked INT NOT NULL DEFAULT 0,
    fire_drill_conducted TINYINT(1) NOT NULL DEFAULT 0,
    up_fire_noc_status ENUM('valid_compliant', 'renewal_due_30d', 'renewal_applied', 'provisional_noc', 'expired') NOT NULL DEFAULT 'valid_compliant',
    noc_certificate_number VARCHAR(100) NULL,
    noc_expiry_date DATE NULL,
    fire_officer_name VARCHAR(150) NULL,
    fire_officer_contact VARCHAR(20) NULL,
    defects_observed TEXT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_fire (colony_id, inspection_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Table township_fire_safety_logs created/verified.\n";

// Seed menu items into admin_menu_items
$menuItems = [
    [
        'name' => 'STP Effluent & UPPCB Desk',
        'url' => '/admin/township-stp',
        'icon' => 'fas fa-vial',
        'section' => 'township_ops',
        'order_index' => 13,
        'permission_key' => 'township.stp'
    ],
    [
        'name' => 'Fire Hydrant & UP Fire NOC',
        'url' => '/admin/township-fire-safety',
        'icon' => 'fas fa-fire-extinguisher',
        'section' => 'township_ops',
        'order_index' => 14,
        'permission_key' => 'township.fire_safety'
    ]
];

$roles = ['super_admin', 'admin', 'manager', 'construction_director'];

foreach ($menuItems as $item) {
    $stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
    $stmt->execute([$item['url']]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $menuId = (int)$existingId;
        $pdo->prepare("UPDATE admin_menu_items SET name = ?, icon = ?, section = ?, order_index = ?, permission_key = ?, is_active = 1 WHERE id = ?")
            ->execute([$item['name'], $item['icon'], $item['section'], $item['order_index'], $item['permission_key'], $menuId]);
    } else {
        $pdo->prepare("INSERT INTO admin_menu_items (name, url, icon, section, order_index, permission_key, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)")
            ->execute([$item['name'], $item['url'], $item['icon'], $item['section'], $item['order_index'], $item['permission_key']]);
        $menuId = (int)$pdo->lastInsertId();
    }

    foreach ($roles as $role) {
        $pdo->prepare("
            INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete)
            VALUES (?, ?, 1, 1, 1, 1)
            ON DUPLICATE KEY UPDATE can_view = 1, can_create = 1, can_edit = 1, can_delete = 1
        ")->execute([$role, $menuId]);
    }
}
echo "Admin menu items and permissions seeded successfully for STP and Fire Safety.\n";
