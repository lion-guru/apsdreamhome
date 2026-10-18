<?php
/**
 * Migration: Create reminders table
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Creating reminders table...\n";

try {
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

    echo "✅ reminders table created\n";

    echo "\n✅ Migration completed successfully!\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}