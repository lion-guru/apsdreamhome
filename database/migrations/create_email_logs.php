<?php
/**
 * Create email_logs table if not exists
 */

$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS email_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(500) NOT NULL,
        body TEXT,
        status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
        error_message TEXT,
        opened_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_recipient (recipient),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ email_logs table created successfully\n";
    
    // Also add medium_path to property_images if not exists
    $checkColumn = $pdo->query("SHOW COLUMNS FROM property_images LIKE 'medium_path'")->fetch();
    if (!$checkColumn) {
        $pdo->exec("ALTER TABLE property_images ADD COLUMN medium_path VARCHAR(255) NULL AFTER thumbnail_path");
        echo "✅ Added medium_path column to property_images\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
