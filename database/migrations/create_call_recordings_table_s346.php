<?php
/**
 * Migration: Create call_recordings table
 * Idempotent - safe to run multiple times
 */

namespace App\Database\Migrations;

use App\Core\Database\Database;

class create_call_recordings_table_s346
{
    public function up(): void
    {
        $db = Database::getInstance();

        // Create table if not exists
        $db->execute("
            CREATE TABLE IF NOT EXISTS call_recordings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                associate_id BIGINT UNSIGNED NOT NULL,
                lead_id INT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                duration INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Duration in seconds',
                call_type ENUM('voice', 'video', 'conference') NOT NULL DEFAULT 'voice',
                direction ENUM('inbound', 'outbound', 'internal') NOT NULL DEFAULT 'outbound',
                caller_number VARCHAR(50) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_associate (associate_id),
                INDEX idx_lead (lead_id),
                INDEX idx_created (created_at),
                INDEX idx_duration (duration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "call_recordings table created/verified\n";
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->execute("DROP TABLE IF EXISTS call_recordings");
        echo "call_recordings table dropped\n";
    }
}