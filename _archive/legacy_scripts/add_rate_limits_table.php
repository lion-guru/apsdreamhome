<?php
/**
 * Add rate_limits table for Mobile API JWT rate limiting
 * Also creates api_tokens and push_tokens tables required by JWTAuthService
 *
 * Run: php scripts/add_rate_limits_table.php
 */

require_once __DIR__ . '/../app/Core/Database/Database.php';
require_once __DIR__ . '/../app/Core/ConfigService.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();

    echo "Creating rate_limits table...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rate_limits (
          id INT AUTO_INCREMENT PRIMARY KEY,
          rate_key VARCHAR(100),
          request_count INT DEFAULT 1,
          window_start DATETIME,
          UNIQUE KEY uk_key (rate_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  - rate_limits table created (or already exists)\n";

    echo "Creating api_tokens table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_tokens (
          id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          user_id BIGINT(20) UNSIGNED NOT NULL,
          user_type VARCHAR(20) NOT NULL DEFAULT 'customer',
          token TEXT NOT NULL,
          refresh_token TEXT,
          device_info VARCHAR(255),
          ip_address VARCHAR(45),
          expires_at DATETIME,
          last_used_at DATETIME,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          INDEX idx_user (user_id),
          INDEX idx_token (token(191)),
          INDEX idx_refresh (refresh_token(191)),
          INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  - api_tokens table created (or already exists)\n";

    echo "Creating push_tokens table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS push_tokens (
          id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          user_id BIGINT(20) UNSIGNED NOT NULL,
          user_type VARCHAR(20) NOT NULL DEFAULT 'customer',
          device_token TEXT NOT NULL,
          platform VARCHAR(20) DEFAULT 'android',
          device_id VARCHAR(255),
          is_active TINYINT(1) DEFAULT 1,
          last_used_at DATETIME,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY uk_user_device (user_id, device_token(191)),
          INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  - push_tokens table created (or already exists)\n";

    echo "\nAll tables created successfully.\n";
    echo "Tables now ready for Mobile API V2 (JWT auth).\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>