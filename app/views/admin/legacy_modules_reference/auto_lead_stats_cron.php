<?php
/**
 * Auto Lead Stats Cron
 * Generates daily snapshots of lead statistics.
 */

if (PHP_SAPI === 'cli') {
    require_once __DIR__ . '/../../includes/legacy_bootstrap.php';
} else {
    require_once __DIR__ . '/core/init.php';
}

use App\Core\Database;

try {
    $db = \App\Core\App::database();

    // Ensure lead_stats_daily table exists
    $db->execute("
        CREATE TABLE IF NOT EXISTS lead_stats_daily (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_date DATE NOT NULL,
            total INT DEFAULT 0,
            new INT DEFAULT 0,
            qualified INT DEFAULT 0,
            contacted INT DEFAULT 0,
            converted INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (stat_date)
        )
    ");

    // Insert or update daily stats snapshot
    $sql = "
        INSERT INTO lead_stats_daily (stat_date, total, new, qualified, contacted, converted) 
        SELECT 
            CURDATE(), 
            COUNT(*), 
            SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END), 
            SUM(CASE WHEN status = 'Qualified' THEN 1 ELSE 0 END), 
            SUM(CASE WHEN status = 'Contacted' THEN 1 ELSE 0 END), 
            SUM(CASE WHEN status = 'Converted' THEN 1 ELSE 0 END) 
        FROM leads
        ON DUPLICATE KEY UPDATE 
            total = VALUES(total),
            new = VALUES(new),
            qualified = VALUES(qualified),
            contacted = VALUES(contacted),
            converted = VALUES(converted)
    ";

    $db->execute($sql);

    echo "[" . date('Y-m-d H:i:s') . "] Lead statistics snapshot updated.\n";

} catch (Exception $e) {
    error_log("Auto Lead Stats Cron Error: " . $e->getMessage());
    if (PHP_SAPI === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
