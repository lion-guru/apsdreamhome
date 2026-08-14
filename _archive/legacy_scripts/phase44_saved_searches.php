<?php
/**
 * Saved Searches System
 * Allows admins to save and reuse complex filter combinations
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$db->exec("DROP TABLE IF EXISTS saved_searches");
$db->exec("
    CREATE TABLE saved_searches (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NULL,
        user_role VARCHAR(50) NOT NULL DEFAULT 'admin',
        name VARCHAR(200) NOT NULL,
        description TEXT NULL,
        entity_type VARCHAR(50) NOT NULL,
        filters JSON NOT NULL,
        is_favorite TINYINT(1) NOT NULL DEFAULT 0,
        is_public TINYINT(1) NOT NULL DEFAULT 0,
        use_count INT(11) NOT NULL DEFAULT 0,
        last_used_at TIMESTAMP NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id, user_role),
        INDEX idx_entity (entity_type),
        INDEX idx_favorite (is_favorite, entity_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK saved_searches table created\n";

$db->exec("
    CREATE TABLE IF NOT EXISTS search_history (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NULL,
        user_role VARCHAR(50) NOT NULL DEFAULT 'admin',
        entity_type VARCHAR(50) NOT NULL,
        filters JSON NOT NULL,
        results_count INT(11) NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_recent (user_id, entity_type, created_at),
        INDEX idx_entity (entity_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK search_history table created\n";?>