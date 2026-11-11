<?php
/**
 * Migration: Create voice_recordings table for S3 storage references
 */

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$sql = "
CREATE TABLE IF NOT EXISTS voice_recordings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    call_sid VARCHAR(100) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    s3_key VARCHAR(500) NOT NULL,
    s3_url TEXT,
    file_size BIGINT UNSIGNED DEFAULT 0,
    duration INT UNSIGNED DEFAULT 0,
    status ENUM('pending','uploaded','failed','deleted') DEFAULT 'pending',
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_call_provider (call_sid, provider),
    INDEX idx_call_sid (call_sid),
    INDEX idx_provider (provider),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "Table 'voice_recordings' created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Create AWS S3 config entry
$stmt = $pdo->prepare("SELECT id FROM service_configs WHERE group_name = 'aws' AND service_name = 's3' LIMIT 1");
$stmt->execute();
$exists = $stmt->fetch();

if (!$exists) {
    $stmt = $pdo->prepare("
        INSERT INTO service_configs 
        (group_name, service_name, config_key, config_value, config_type, description, is_secret, sort_order, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        'aws',
        's3',
        's3',
        json_encode([
            'access_key' => '',
            'secret_key' => '',
            'region' => 'ap-south-1',
            'bucket' => '',
            'key_prefix' => 'voice-recordings/',
        ]),
        'json',
        'AWS S3 storage configuration for voice recordings',
        1,
        1,
    ]);
    echo "AWS S3 config created successfully.\n";
} else {
    echo "AWS S3 config already exists.\n";
}

echo "Migration completed successfully.\n";