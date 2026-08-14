<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Property images table (if not exists)
$pdo->exec("
CREATE TABLE IF NOT EXISTS property_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    image_type ENUM('exterior', 'interior', 'floor_plan', 'elevation', 'amenity', 'document', 'other') DEFAULT 'other',
    uploaded_by BIGINT UNSIGNED DEFAULT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    caption VARCHAR(255) DEFAULT '',
    file_size INT DEFAULT 0,
    mime_type VARCHAR(100) DEFAULT '',
    width INT DEFAULT 0,
    height INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_property (property_id),
    KEY idx_primary (is_primary),
    KEY idx_type (image_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "property_images table created/verified\n";

// Property image tags table
$pdo->exec("
CREATE TABLE IF NOT EXISTS property_image_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_id BIGINT UNSIGNED NOT NULL,
    tag_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) DEFAULT 'other',
    confidence DECIMAL(3,2) DEFAULT 0.00,
    source ENUM('ai', 'manual', 'filename') DEFAULT 'ai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES property_images(id) ON DELETE CASCADE,
    UNIQUE KEY uk_image_tag (image_id, tag_name),
    KEY idx_tag (tag_name),
    KEY idx_category (category),
    KEY idx_confidence (confidence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "property_image_tags table created/verified\n";

// Add foreign key if not exists
try {
    $pdo->exec("
        ALTER TABLE property_images 
        ADD CONSTRAINT fk_property_images_property 
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    ");
    echo "Foreign key added to property_images\n";
} catch (Exception $e) {
    // Ignore if already exists
    echo "Foreign key may already exist\n";
}

echo "\n=== Property Image Tagging tables created successfully ===\n";?>