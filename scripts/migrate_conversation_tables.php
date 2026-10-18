<?php
/**
 * Migration: Create conversation tables for ConversationalAIService
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Creating conversation tables...\n";

try {
    // conversation_sessions table
    $db->execute("
        CREATE TABLE IF NOT EXISTS conversation_sessions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL UNIQUE,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            user_id BIGINT UNSIGNED NOT NULL,
            status ENUM('active','ended','archived') DEFAULT 'active',
            message_count INT DEFAULT 0,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ended_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_user (user_id),
            INDEX idx_tenant (tenant_id),
            INDEX idx_status (status),
            INDEX idx_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ conversation_sessions table created\n";

    // conversation_history table
    $db->execute("
        CREATE TABLE IF NOT EXISTS conversation_history (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            session_id VARCHAR(100) NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            role ENUM('user','assistant','system') NOT NULL,
            content TEXT NOT NULL,
            intent VARCHAR(100) NULL,
            actions JSON NULL,
            metadata JSON NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_user (user_id),
            INDEX idx_tenant (tenant_id),
            INDEX idx_created (created_at),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ conversation_history table created\n";

    // reminders table (if not exists)
    $db->execute("
        CREATE TABLE IF NOT EXISTS reminders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            lead_id INT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            due_date DATE NOT NULL,
            due_time TIME NOT NULL,
            type VARCHAR(50) DEFAULT 'follow_up',
            status ENUM('pending','completed','cancelled','snoozed') DEFAULT 'pending',
            created_by BIGINT UNSIGNED NULL,
            completed_at TIMESTAMP NULL,
            snoozed_until TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_lead (lead_id),
            INDEX idx_due (due_date, due_time),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ reminders table created/verified\n";

    echo "\n✅ All conversation tables created/verified successfully!\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}