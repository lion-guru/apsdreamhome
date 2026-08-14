<?php
/**
 * Create whatsapp_lead_shares table for tracking WhatsApp lead shares
 * Run once: php scripts/create_whatsapp_lead_shares.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS whatsapp_lead_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lead_id INT NOT NULL,
    shared_to VARCHAR(255) NOT NULL,
    channel VARCHAR(50) NOT NULL DEFAULT 'whatsapp',
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_lead (lead_id),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->query($sql);
    echo "whatsapp_lead_shares table created OK\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>