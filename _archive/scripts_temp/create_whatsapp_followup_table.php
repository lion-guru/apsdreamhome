<?php
/**
 * Create whatsapp_followup_log table for WhatsApp follow-up tracking
 * Run once: php scripts/create_whatsapp_followup_table.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    
    $sql = "CREATE TABLE IF NOT EXISTS whatsapp_followup_log (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id INT UNSIGNED NULL,
        installment_id INT UNSIGNED NULL,
        user_id INT UNSIGNED NULL,
        phone VARCHAR(20) NOT NULL,
        followup_type ENUM('post_call', 'emi_reminder', 'welcome', 'nurture', 'manual') NOT NULL,
        message TEXT NOT NULL,
        status ENUM('sent', 'failed', 'pending') NOT NULL DEFAULT 'pending',
        error_message VARCHAR(500) NULL,
        sent_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_phone (phone),
        INDEX idx_type_date (followup_type, created_at),
        INDEX idx_status_date (status, created_at),
        INDEX idx_session (session_id),
        INDEX idx_installment (installment_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->execute($sql);
    echo "OK: whatsapp_followup_log table created\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>