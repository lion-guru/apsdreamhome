<?php

/**
 * Create Missing network_tree Table
 * For MLM Network Structure
 */

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS network_tree (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT NOT NULL,
        parent_id INT DEFAULT NULL,
        level INT DEFAULT 0,
        position ENUM('left', 'right') DEFAULT NULL,
        total_left_count INT DEFAULT 0,
        total_right_count INT DEFAULT 0,
        total_left_bv DECIMAL(15,2) DEFAULT 0.00,
        total_right_bv DECIMAL(15,2) DEFAULT 0.00,
        personal_bv DECIMAL(15,2) DEFAULT 0.00,
        rank_id INT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_associate (associate_id),
        INDEX idx_parent (parent_id),
        INDEX idx_level (level),
        INDEX idx_rank (rank_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✅ network_tree table created successfully!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
