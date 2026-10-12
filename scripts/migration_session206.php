<?php
/**
 * Idempotent migration for:
 * 1. plot_transfers (resale & transfer lifecycle columns)
 * 2. fleet_trips (dispatch tracking columns)
 * 3. fleet_trip_visits (pickup status columns)
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getConnection();

echo "Running enterprise migrations for Session 206...\n";

// 1. Ensure plot_transfers table and add missing columns
$pdo->exec("
    CREATE TABLE IF NOT EXISTS plot_transfers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        plot_id INT(11) NOT NULL,
        from_customer_id INT(11) NULL,
        to_customer_id INT(11) NULL,
        transfer_date DATE NULL,
        transfer_reason VARCHAR(255) NULL,
        transfer_amount DECIMAL(15,2) DEFAULT 0.00,
        transfer_fee DECIMAL(15,2) DEFAULT 0.00,
        old_status VARCHAR(50) NULL,
        new_status VARCHAR(50) NULL,
        document_ref VARCHAR(100) NULL,
        remarks TEXT NULL,
        processed_by INT(11) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_plot_transfers_tenant (tenant_id),
        INDEX idx_plot_transfers_plot (plot_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$plotTransferCols = [
    'booking_id' => "ALTER TABLE plot_transfers ADD COLUMN booking_id INT(11) NULL AFTER plot_id",
    'transfer_type' => "ALTER TABLE plot_transfers ADD COLUMN transfer_type ENUM('resale','family_gift','legal_heir','direct_transfer') NOT NULL DEFAULT 'resale' AFTER transfer_reason",
    'new_buyer_name' => "ALTER TABLE plot_transfers ADD COLUMN new_buyer_name VARCHAR(150) NULL AFTER to_customer_id",
    'new_buyer_phone' => "ALTER TABLE plot_transfers ADD COLUMN new_buyer_phone VARCHAR(20) NULL AFTER new_buyer_name",
    'new_buyer_aadhaar' => "ALTER TABLE plot_transfers ADD COLUMN new_buyer_aadhaar VARCHAR(20) NULL AFTER new_buyer_phone",
    'new_buyer_pan' => "ALTER TABLE plot_transfers ADD COLUMN new_buyer_pan VARCHAR(20) NULL AFTER new_buyer_aadhaar",
    'new_buyer_address' => "ALTER TABLE plot_transfers ADD COLUMN new_buyer_address TEXT NULL AFTER new_buyer_pan",
    'transfer_fee_status' => "ALTER TABLE plot_transfers ADD COLUMN transfer_fee_status ENUM('pending','paid','waived') NOT NULL DEFAULT 'pending' AFTER transfer_fee",
    'status' => "ALTER TABLE plot_transfers ADD COLUMN status ENUM('requested','under_review','approved','rejected','completed') NOT NULL DEFAULT 'requested' AFTER transfer_fee_status",
    'rejection_reason' => "ALTER TABLE plot_transfers ADD COLUMN rejection_reason TEXT NULL AFTER status",
    'noc_number' => "ALTER TABLE plot_transfers ADD COLUMN noc_number VARCHAR(50) NULL UNIQUE AFTER rejection_reason",
    'noc_issued_at' => "ALTER TABLE plot_transfers ADD COLUMN noc_issued_at DATETIME NULL AFTER noc_number",
    'documents_json' => "ALTER TABLE plot_transfers ADD COLUMN documents_json TEXT NULL AFTER noc_issued_at",
    'updated_at' => "ALTER TABLE plot_transfers ADD COLUMN updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
];

$existingPtCols = array_column($pdo->query("SHOW COLUMNS FROM plot_transfers")->fetchAll(PDO::FETCH_ASSOC), 'Field');

foreach ($plotTransferCols as $col => $sql) {
    if (!in_array($col, $existingPtCols, true)) {
        echo "Adding column {$col} to plot_transfers...\n";
        $pdo->exec($sql);
    }
}

// 2. Add dispatch tracking to fleet_trips
$tripCols = [
    'dispatch_token' => "ALTER TABLE fleet_trips ADD COLUMN dispatch_token VARCHAR(64) NULL AFTER status",
    'started_at' => "ALTER TABLE fleet_trips ADD COLUMN started_at DATETIME NULL AFTER dispatch_token",
    'arrived_at' => "ALTER TABLE fleet_trips ADD COLUMN arrived_at DATETIME NULL AFTER started_at",
    'completed_at' => "ALTER TABLE fleet_trips ADD COLUMN completed_at DATETIME NULL AFTER arrived_at",
];

$existingTripCols = array_column($pdo->query("SHOW COLUMNS FROM fleet_trips")->fetchAll(PDO::FETCH_ASSOC), 'Field');

foreach ($tripCols as $col => $sql) {
    if (!in_array($col, $existingTripCols, true)) {
        echo "Adding column {$col} to fleet_trips...\n";
        $pdo->exec($sql);
    }
}

// 3. Add pickup check-in to fleet_trip_visits
$visitCols = [
    'pickup_status' => "ALTER TABLE fleet_trip_visits ADD COLUMN pickup_status ENUM('waiting','picked_up','no_show','dropped_to_site') NOT NULL DEFAULT 'waiting' AFTER site_visit_id",
    'picked_up_at' => "ALTER TABLE fleet_trip_visits ADD COLUMN picked_up_at DATETIME NULL AFTER pickup_status",
    'notes' => "ALTER TABLE fleet_trip_visits ADD COLUMN notes VARCHAR(255) NULL AFTER picked_up_at",
];

$existingVisitCols = array_column($pdo->query("SHOW COLUMNS FROM fleet_trip_visits")->fetchAll(PDO::FETCH_ASSOC), 'Field');

foreach ($visitCols as $col => $sql) {
    if (!in_array($col, $existingVisitCols, true)) {
        echo "Adding column {$col} to fleet_trip_visits...\n";
        $pdo->exec($sql);
    }
}

echo "Migration completed successfully!\n";
