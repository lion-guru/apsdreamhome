<?php
// Idempotent: nominee columns on plot_bookings + GPS check-in columns on
// site_visits + colony_price_revisions table (P3).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();

function ensureColumn($db, $table, $col, $ddl) {
    $names = array_column($db->fetchAll("DESCRIBE $table"), 'Field');
    if (in_array($col, $names)) {
        echo "skip $table.$col (exists)\n";
        return;
    }
    $db->execute("ALTER TABLE $table ADD COLUMN $col $ddl");
    echo "added $table.$col\n";
}

try {
    ensureColumn($db, 'plot_bookings', 'nominee_name', "VARCHAR(150) NULL");
    ensureColumn($db, 'plot_bookings', 'nominee_relation', "VARCHAR(50) NULL");
    ensureColumn($db, 'plot_bookings', 'nominee_dob', "DATE NULL");
    ensureColumn($db, 'plot_bookings', 'nominee_aadhaar', "VARCHAR(20) NULL");
    ensureColumn($db, 'plot_bookings', 'nominee_phone', "VARCHAR(20) NULL");
    ensureColumn($db, 'plot_bookings', 'guardian_name', "VARCHAR(150) NULL");
    ensureColumn($db, 'site_visits', 'checkin_lat', "DECIMAL(10,7) NULL");
    ensureColumn($db, 'site_visits', 'checkin_lng', "DECIMAL(10,7) NULL");
    ensureColumn($db, 'site_visits', 'checkin_time', "DATETIME NULL");
    ensureColumn($db, 'site_visits', 'checkin_photo', "VARCHAR(255) NULL");
    $db->execute("
        CREATE TABLE IF NOT EXISTS colony_price_revisions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            colony_id INT NOT NULL,
            current_price DECIMAL(10,2) NOT NULL,
            new_price DECIMAL(10,2) NOT NULL,
            effective_date DATE NOT NULL,
            announcement_title VARCHAR(150) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_colony (colony_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "colony_price_revisions table created/verified\n";
} catch (\Throwable $e) {
    echo 'MIGRATE-FAILED: ' . substr($e->getMessage(), 0, 200) . PHP_EOL;
    exit(1);
}
