<?php
/**
 * Migration: Add Password System Tables
 * Run this to create required tables for the enhanced password system
 */

$dbHost = '127.0.0.1';
$dbPort = 3306;
$dbName = 'apsdreamhome';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
$pdo = new PDO($dsn, $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$tid = 1; // Default tenant ID

// Helper to build tenant columns
$tenantCol = $tid > 1 ? ", tenant_id INT UNSIGNED DEFAULT 1" : "";
$tenantIdx = $tid > 1 ? ", INDEX idx_tenant (tenant_id)" : "";

// 1. remember_tokens table
echo "Creating remember_tokens table...\n";
$sql = "
    CREATE TABLE IF NOT EXISTS remember_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        selector VARCHAR(32) NOT NULL,
        validator VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        user_agent TEXT
        $tenantCol
        , INDEX idx_user (user_id)
        , INDEX idx_selector (selector)
        , INDEX idx_expires (expires_at)
        $tenantIdx
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
$pdo->exec($sql);
echo "Created remember_tokens table\n";

// 2. Add 2FA columns to users table
echo "Adding 2FA columns to users table...\n";
$columns = [
    "two_factor_secret VARCHAR(255) NULL COMMENT 'Base32 encoded TOTP secret'",
    "two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether 2FA is enabled'",
    "two_factor_backup_codes JSON NULL COMMENT 'Hashed backup codes for 2FA recovery'",
    "password_changed_at DATETIME NULL COMMENT 'When password was last changed'",
    "password_history JSON NULL COMMENT 'Hashed previous passwords to prevent reuse'",
    "failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Consecutive failed login attempts'",
    "locked_until DATETIME NULL COMMENT 'Account lockout expiry'",
    "last_login_at DATETIME NULL COMMENT 'Last successful login timestamp'",
    "last_login_ip VARCHAR(45) NULL COMMENT 'IP of last successful login'",
];

foreach ($columns as $col) {
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN $col");
        echo "Added column: $col\n";
    } catch (Exception $e) {
        echo "Column may already exist: " . explode(' ', $col)[0] . "\n";
    }
}

// 3. password_strength_config table
echo "Creating password_strength_config table...\n";
$sql = "
    CREATE TABLE IF NOT EXISTS password_strength_config (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(100) NOT NULL UNIQUE,
        config_value TEXT NOT NULL,
        description TEXT,
        is_editable TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        $tenantCol
        $tenantIdx
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
$pdo->exec($sql);
echo "Created password_strength_config table\n";

// 4. Insert default password strength config
echo "Inserting default password strength config...\n";
$defaultConfigs = [
    ['min_length', '8', 'Minimum password length'],
    ['max_length', '128', 'Maximum password length'],
    ['require_uppercase', '1', 'Require uppercase letters'],
    ['require_lowercase', '1', 'Require lowercase letters'],
    ['require_numbers', '1', 'Require numbers'],
    ['require_symbols', '1', 'Require special characters'],
    ['max_consecutive_chars', '3', 'Max consecutive identical characters'],
    ['block_common_passwords', '1', 'Block commonly used passwords'],
    ['block_user_info', '1', 'Block user personal info in password'],
    ['password_history_count', '5', 'Number of previous passwords to remember'],
    ['password_expiry_days', '90', 'Days before password expires (0 = never)'],
    ['lockout_threshold', '5', 'Failed attempts before lockout'],
    ['lockout_duration_minutes', '15', 'Lockout duration in minutes'],
    ['require_2fa', '0', 'Require 2FA for all users'],
    ['remember_me_days', '30', 'Remember me token lifetime in days'],
];

$stmt = $pdo->prepare(
    "INSERT IGNORE INTO password_strength_config (config_key, config_value, description" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, ?, ?" . ($tid > 1 ? ", ?" : "") . ")"
);

foreach ($defaultConfigs as $config) {
    list($key, $value, $desc) = $config;
    try {
        $stmt->execute([$key, $value, $desc]);
        echo "Inserted config: $key\n";
    } catch (Exception $e) {
        echo "Config may already exist: $key\n";
    }
}

// 5. user_sessions table for active session tracking
echo "Creating user_sessions table...\n";
$sql = "
    CREATE TABLE IF NOT EXISTS user_sessions (
        id VARCHAR(128) NOT NULL PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        ip_address VARCHAR(45),
        user_agent TEXT,
        payload TEXT,
        last_activity INT UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        $tenantCol
        , INDEX idx_user (user_id)
        , INDEX idx_last_activity (last_activity)
        $tenantIdx
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
$pdo->exec($sql);
echo "Created user_sessions table\n";

echo "Migration completed successfully!\n";