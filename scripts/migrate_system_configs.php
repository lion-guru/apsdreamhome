<?php
/**
 * Migration: System Configs Table
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

$tid = 1;

echo "Creating system_configs table...\n";
$sql = "
    CREATE TABLE IF NOT EXISTS system_configs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(100) NOT NULL,
        config_value TEXT,
        config_type VARCHAR(20) NOT NULL DEFAULT 'string',
        config_group VARCHAR(50) NOT NULL DEFAULT 'general',
        description TEXT,
        is_public TINYINT(1) NOT NULL DEFAULT 0,
        is_editable TINYINT(1) NOT NULL DEFAULT 1,
        validation_rules JSON NULL,
        options JSON NULL,
        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        , UNIQUE KEY uk_config_key (config_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
$pdo->exec($sql);
echo "Created system_configs table\n";

echo "Creating system_config_audit table...\n";
$sql = "
    CREATE TABLE IF NOT EXISTS system_config_audit (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(100) NOT NULL,
        old_value TEXT,
        new_value TEXT,
        changed_by BIGINT UNSIGNED,
        changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        , INDEX idx_config_key (config_key)
        , INDEX idx_changed_by (changed_by)
        , INDEX idx_changed_at (changed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
$pdo->exec($sql);
echo "Created system_config_audit table\n";

echo "Migration completed successfully!\n";