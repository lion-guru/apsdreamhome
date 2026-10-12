<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/../../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. township_lighting_logs
$pdo->exec("
CREATE TABLE IF NOT EXISTS township_lighting_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colony_id INT NOT NULL,
    audit_date DATE NOT NULL,
    feeder_pillar_code VARCHAR(100) NOT NULL,
    total_poles_connected INT NOT NULL DEFAULT 0,
    poles_functional INT NOT NULL DEFAULT 0,
    poles_faulty INT NOT NULL DEFAULT 0,
    uptime_percent DECIMAL(5, 2) NOT NULL DEFAULT 100.00,
    timer_mode ENUM('astronomical_timer', 'photocell_sensor', 'manual_mcb_switch', 'smart_iot') NOT NULL DEFAULT 'astronomical_timer',
    on_time TIME NULL,
    off_time TIME NULL,
    phase_balance_status ENUM('balanced_ryb', 'phase_r_trip', 'phase_y_trip', 'phase_b_trip', 'voltage_fluctuation') NOT NULL DEFAULT 'balanced_ryb',
    cable_health ENUM('healthy_underground', 'minor_joint_fault', 'cut_repair_needed') NOT NULL DEFAULT 'healthy_underground',
    faulty_pole_numbers VARCHAR(255) NULL,
    electrician_name VARCHAR(150) NULL,
    electrician_phone VARCHAR(20) NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_date (colony_id, audit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Table township_lighting_logs created/verified.\n";

// 2. township_rwh_logs
$pdo->exec("
CREATE TABLE IF NOT EXISTS township_rwh_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colony_id INT NOT NULL,
    audit_date DATE NOT NULL,
    pit_identifier VARCHAR(150) NOT NULL,
    recharge_type ENUM('borewell_recharge_shaft', 'percolation_tank', 'injection_well', 'catchment_swale') NOT NULL DEFAULT 'borewell_recharge_shaft',
    catchment_area_sqft DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    filtration_media_status ENUM('clean_gravel_sand', 'needs_flushing', 'replaced_charcoal', 'clogged') NOT NULL DEFAULT 'clean_gravel_sand',
    desilting_chambers_cleared TINYINT(1) NOT NULL DEFAULT 1,
    recharge_capacity_kl DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    water_table_depth_ft DECIMAL(6, 2) NULL,
    monsoon_readiness_score ENUM('optimal_ready', 'needs_cleaning', 'urgent_civil_repair', 'decommissioned') NOT NULL DEFAULT 'optimal_ready',
    up_groundwater_noc_status ENUM('compliant_valid', 'inspection_due', 'renewal_applied', 'exempt') NOT NULL DEFAULT 'compliant_valid',
    environmental_officer_name VARCHAR(150) NULL,
    officer_contact VARCHAR(20) NULL,
    maintenance_actions_done TEXT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_colony_rwh (colony_id, audit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Table township_rwh_logs created/verified.\n";

// Seed menu items into admin_menu_items
$menuItems = [
    [
        'name' => 'Street Lighting & Poles',
        'url' => '/admin/township-lighting',
        'icon' => 'fas fa-lightbulb',
        'section' => 'township_ops',
        'order_index' => 11,
        'permission_key' => 'township.lighting'
    ],
    [
        'name' => 'Rainwater Recharge (RWH)',
        'url' => '/admin/township-rwh',
        'icon' => 'fas fa-cloud-showers-heavy',
        'section' => 'township_ops',
        'order_index' => 12,
        'permission_key' => 'township.rwh'
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
echo "Menu items and permissions added for lighting and RWH.\n";
