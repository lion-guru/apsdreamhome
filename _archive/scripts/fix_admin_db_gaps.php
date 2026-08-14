<?php
/**
 * Fix Admin DB Gaps - Recreate admin_user_menu_permissions & Ensure gateway_logs columns
 * 
 * Run via: php scripts/fix_admin_db_gaps.php
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

try {
    $pdo = \App\Core\Database::getInstance()->getConnection();

    echo "=== FIXING ADMIN DATABASE GAPS ===\n\n";

    // 1. Recreate admin_user_menu_permissions table
    echo "Checking 'admin_user_menu_permissions' table...\n";
    $tableExists = $pdo->query("SHOW TABLES LIKE 'admin_user_menu_permissions'")->rowCount() > 0;
    if (!$tableExists) {
        $pdo->exec("CREATE TABLE `admin_user_menu_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `menu_item_id` INT NOT NULL,
            `can_view` TINYINT(1) DEFAULT 1,
            `can_create` TINYINT(1) DEFAULT 0,
            `can_edit` TINYINT(1) DEFAULT 0,
            `can_delete` TINYINT(1) DEFAULT 0,
            `granted_by` INT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_menu` (`user_id`, `menu_item_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_menu_item_id` (`menu_item_id`),
            FOREIGN KEY (`menu_item_id`) REFERENCES `admin_menu_items`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "âœ“ Successfully created 'admin_user_menu_permissions' table!\n";
    } else {
        echo "âœ“ 'admin_user_menu_permissions' table already exists.\n";
    }

    // 2. Ensure gateway_logs table columns exist
    echo "\nChecking 'gateway_logs' table columns...\n";
    $tableExists = $pdo->query("SHOW TABLES LIKE 'gateway_logs'")->rowCount() > 0;
    if (!$tableExists) {
        // Create gateway_logs if missing
        $pdo->exec("CREATE TABLE gateway_logs (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            gateway VARCHAR(50) NOT NULL,
            action VARCHAR(100) NULL,
            method VARCHAR(10) NULL,
            endpoint VARCHAR(255) NULL,
            recipient VARCHAR(100) NULL,
            request_payload LONGTEXT NULL,
            response_payload LONGTEXT NULL,
            http_code INT NULL,
            response_code SMALLINT(5) UNSIGNED NULL,
            status VARCHAR(20) DEFAULT 'pending',
            amount_paise BIGINT(20) UNSIGNED NULL,
            cost DECIMAL(10,4) DEFAULT 0,
            transaction_id VARCHAR(80) NULL,
            duration_ms INT(10) UNSIGNED DEFAULT 0,
            retry_count TINYINT(3) UNSIGNED DEFAULT 0,
            error_message TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_gateway (gateway),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "âœ“ Successfully created 'gateway_logs' table!\n";
    } else {
        // Verify columns
        $existing = [];
        foreach ($pdo->query('DESCRIBE gateway_logs') as $r) {
            $existing[strtolower($r['Field'])] = true;
        }

        $required = [
            'action'    => "VARCHAR(100) NULL AFTER gateway",
            'recipient' => "VARCHAR(100) NULL AFTER endpoint",
            'http_code' => "INT NULL AFTER response_payload",
            'cost'      => "DECIMAL(10,4) DEFAULT 0 AFTER amount_paise",
        ];

        $added = 0;
        foreach ($required as $col => $def) {
            if (!isset($existing[strtolower($col)])) {
                $pdo->exec("ALTER TABLE gateway_logs ADD COLUMN {$col} {$def}");
                echo "  + Added column '{$col}' to 'gateway_logs'\n";
                $added++;
            }
        }
        echo "âœ“ 'gateway_logs' columns verification complete ($added added).\n";
    }

    echo "\n=== ALL DB GAPS FIXED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>