<?php
/**
 * Create campaign_delivery_schedule table for scheduled campaign deliveries
 * Run once: php scripts/create_campaign_delivery_schedule.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS campaign_delivery_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    scheduled_time DATETIME NOT NULL,
    delivery_types JSON NOT NULL,
    status ENUM('scheduled','completed','failed') DEFAULT 'scheduled',
    processed_at TIMESTAMP NULL,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign (campaign_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_time (scheduled_time),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->query($sql);
    echo "campaign_delivery_schedule table created OK\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>