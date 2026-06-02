<?php
/**
 * Create MLM Tables
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "Creating MLM Tables...\n\n";

$tables = [
    'mlm_transactions' => "CREATE TABLE IF NOT EXISTS mlm_transactions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        from_user_id INT UNSIGNED,
        transaction_type VARCHAR(50) NOT NULL,
        points INT DEFAULT 0,
        amount DECIMAL(12,2) DEFAULT 0,
        description TEXT,
        reference_id VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_type (transaction_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'mlm_earnings' => "CREATE TABLE IF NOT EXISTS mlm_earnings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        earning_type VARCHAR(50) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        level INT DEFAULT 1,
        from_user_id INT UNSIGNED,
        status ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        paid_at TIMESTAMP NULL,
        INDEX idx_user_status (user_id, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'mlm_points' => "CREATE TABLE IF NOT EXISTS mlm_points (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        points INT NOT NULL,
        points_type VARCHAR(50) DEFAULT 'referral',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $name => $sql) {
    try {
        $db->execute($sql);
        echo "✅ Created: $name\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⏭️  Already exists: $name\n";
        } else {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Done!\n";
