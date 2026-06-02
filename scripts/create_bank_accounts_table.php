<?php
/**
 * Create user_bank_accounts table for storing bank details
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "Creating user_bank_accounts table...\n\n";

$sql = "CREATE TABLE IF NOT EXISTS user_bank_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    account_holder VARCHAR(200) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    ifsc_code VARCHAR(20) NOT NULL,
    bank_name VARCHAR(200),
    branch_name VARCHAR(200),
    account_type ENUM('savings', 'current', 'od') DEFAULT 'savings',
    upi_id VARCHAR(100),
    is_primary TINYINT(1) DEFAULT 1,
    is_verified TINYINT(1) DEFAULT 0,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_primary (user_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->execute($sql);
    echo "✅ Created: user_bank_accounts\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "⏭️  Already exists: user_bank_accounts\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Done!\n";
