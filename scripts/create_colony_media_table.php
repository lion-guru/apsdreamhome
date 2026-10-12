<?php
// Idempotent: colony media hub (drone video, site photos, 3D layout, embeds).
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS colony_media (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            colony_id INT NOT NULL,
            title VARCHAR(150) NOT NULL,
            media_type ENUM('drone_video','site_photo','layout_3d','youtube_embed') NOT NULL,
            media_url VARCHAR(255) NOT NULL,
            thumbnail_url VARCHAR(255) NULL,
            tags VARCHAR(100) NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_colony (colony_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "colony_media table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
