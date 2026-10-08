<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "Creating marketing_toolkit_usage table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS `marketing_toolkit_usage` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `tool_type` ENUM('watermark','banner','ai_writer','qr_flyer','catalog') NOT NULL,
    `output_path` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_tool` (`tool_type`),
    KEY `idx_date` (`created_at`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Marketing toolkit usage tracking';
";

try {
    $db->exec($sql);
    echo "✅ marketing_toolkit_usage created\n";
} catch (\Exception $e) {
    echo "❌ " . $e->getMessage() . "\n";
}

echo "Done!\n";