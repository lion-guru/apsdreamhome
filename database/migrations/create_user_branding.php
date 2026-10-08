<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "Creating user_branding table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS `user_branding` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `display_name` VARCHAR(100) NULL,
    `phone` VARCHAR(20) NULL,
    `photo_path` VARCHAR(500) NULL,
    `tagline` VARCHAR(200) NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Personal branding for marketing toolkit (name/phone/photo auto-applied)';
";

try {
    $db->exec($sql);
    echo "✅ user_branding created\n";
} catch (\Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
}

echo "Done!\n";