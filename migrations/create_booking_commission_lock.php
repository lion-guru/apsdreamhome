<?php
/**
 * Migration: Create booking_commission_lock table
 * 
 * This table stores the agent's locked rank at plot sale time.
 * Commission is always calculated using the locked rate, not the current rank.
 * This ensures commission consistency regardless of future rank promotions.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getPdo();

echo "=== Creating booking_commission_lock table ===\n\n";

try {
    // Create booking_commission_lock table
    $sql = "CREATE TABLE IF NOT EXISTS booking_commission_lock (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        agent_id INT NOT NULL,
        locked_rank VARCHAR(50) NOT NULL,
        locked_rate DECIMAL(5,2) NOT NULL,
        locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        tenant_id INT NOT NULL DEFAULT 1,
        UNIQUE KEY unique_booking_lock (booking_id),
        KEY idx_agent_id (agent_id),
        KEY idx_tenant_id (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ booking_commission_lock table created\n\n";

    // Verify table structure
    $columns = $pdo->query("SHOW COLUMNS FROM booking_commission_lock")->fetchAll(PDO::FETCH_ASSOC);
    echo "Table structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }
    echo "\n✓ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
