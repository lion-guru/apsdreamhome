<?php
/**
 * Create SMS tables
 */

$host = '127.0.0.1';
$port = '3307';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SMS OTP Logs
    $sql1 = "CREATE TABLE IF NOT EXISTS sms_otp_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mobile VARCHAR(20) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        status ENUM('pending', 'verified', 'expired', 'failed') DEFAULT 'pending',
        verified_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX idx_mobile (mobile),
        INDEX idx_otp (otp),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql1);
    echo "✅ sms_otp_logs table created\n";
    
    // SMS Logs
    $sql2 = "CREATE TABLE IF NOT EXISTS sms_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mobile VARCHAR(20) NOT NULL,
        type VARCHAR(50) NOT NULL,
        message TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        message_id VARCHAR(100),
        error_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mobile (mobile),
        INDEX idx_type (type),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql2);
    echo "✅ sms_logs table created\n";
    
    echo "\n✅ SMS tables setup complete!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
