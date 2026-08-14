<?php
/**
 * Phase 49: Property Comparison Lists
 * Customers can save properties to comparison lists and view side-by-side
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec("DROP TABLE IF EXISTS property_comparisons");
$db->exec("
    CREATE TABLE property_comparisons (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NULL,
        session_id VARCHAR(64) NULL,
        name VARCHAR(100) NULL,
        property_ids TEXT NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        share_token VARCHAR(64) NULL,
        view_count INT(11) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_session (session_id),
        INDEX idx_token (share_token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "OK property_comparisons table created\n";?>