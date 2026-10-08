<?php
// Idempotent: associate/agent offer campaigns (festival/season sale boosts).
// Admin creates + activates; associates/agents see live offers with progress.
require_once __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getPdo();
$exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='associate_offers'")->fetchColumn();
if (!$exists) {
    $pdo->exec("CREATE TABLE associate_offers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(150) NOT NULL,
        description TEXT NULL,
        reward_type ENUM('bonus_amount','commission_boost_pct','gift') NOT NULL DEFAULT 'bonus_amount',
        reward_value DECIMAL(12,2) NOT NULL DEFAULT 0,
        criteria_type ENUM('sale_volume','booking_count') NOT NULL DEFAULT 'sale_volume',
        criteria_value DECIMAL(15,2) NOT NULL DEFAULT 0,
        colony_id INT UNSIGNED NULL,
        starts_at DATE NOT NULL,
        ends_at DATE NOT NULL,
        status ENUM('draft','active','expired','closed') NOT NULL DEFAULT 'draft',
        created_by INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status_dates (status, starts_at, ends_at),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "created associate_offers\n";
} else {
    echo "associate_offers already present\n";
}
