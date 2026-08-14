<?php
/**
 * Create campaign_deliveries table for individual email/notification tracking
 * Run once: php scripts/create_campaign_deliveries.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS campaign_deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    user_id INT NOT NULL,
    delivery_type ENUM('notification','popup','email','sms') DEFAULT 'email',
    status ENUM('sent','opened','clicked','converted','bounced') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    converted_at TIMESTAMP NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    INDEX idx_campaign (campaign_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->query($sql);
    echo "campaign_deliveries table created OK\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>