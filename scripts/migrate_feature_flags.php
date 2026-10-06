<?php
/**
 * Migration: Feature Flags Table
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

echo "Creating feature_flags table...\n";
$sql = "
    CREATE TABLE IF NOT EXISTS feature_flags (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        flag_key VARCHAR(100) NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        flag_group VARCHAR(50) NOT NULL DEFAULT 'general',
        enabled TINYINT(1) NOT NULL DEFAULT 0,
        rollout_percentage INT UNSIGNED NOT NULL DEFAULT 100,
        targeting_rules JSON NULL,
        start_date DATETIME NULL,
        end_date DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        , UNIQUE KEY uk_flag_key (flag_key)
        , INDEX idx_group (flag_group)
        , INDEX idx_enabled (enabled)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";
$pdo->exec($sql);
echo "Created feature_flags table\n";

echo "Migration completed successfully!\n";