<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Create whatsapp_config table
$pdo->exec("
CREATE TABLE IF NOT EXISTS whatsapp_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "whatsapp_config table created/verified\n";

// Create whatsapp_message_logs table
$pdo->exec("
CREATE TABLE IF NOT EXISTS whatsapp_message_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    to_number VARCHAR(20) NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    request_payload JSON DEFAULT NULL,
    response_payload JSON DEFAULT NULL,
    http_status INT DEFAULT 0,
    status ENUM('sent', 'failed', 'delivered', 'read') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL DEFAULT NULL,
    read_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_to_number (to_number),
    KEY idx_template (template_name),
    KEY idx_status (status),
    KEY idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "whatsapp_message_logs table created/verified\n";

// Create digilocker_sessions table
$pdo->exec("
CREATE TABLE IF NOT EXISTS digilocker_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    state VARCHAR(100) NOT NULL,
    scopes JSON DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    access_token TEXT DEFAULT NULL,
    refresh_token TEXT DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    user_data JSON DEFAULT NULL,
    status ENUM('pending', 'authorized', 'expired', 'revoked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_state (state),
    KEY idx_user (user_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "digilocker_sessions table created/verified\n";

// Insert default WhatsApp config (to be filled by admin)
$configs = [
    ['phone_number_id', '', 'WhatsApp Business Phone Number ID'],
    ['access_token', '', 'WhatsApp Business Access Token'],
    ['api_url', 'https://graph.facebook.com/v18.0', 'WhatsApp API Base URL'],
];

$stmt = $pdo->prepare("
    INSERT INTO whatsapp_config (config_key, config_value, description, is_active)
    VALUES (?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE description = VALUES(description)
");

$inserted = 0;
foreach ($configs as $config) {
    try {
        $stmt->execute($config);
        $inserted++;
    } catch (Exception $e) {
        // Ignore
    }
}

echo "Inserted/Updated $inserted WhatsApp config entries\n";

echo "\n=== All communication tables created successfully ===\n";?>