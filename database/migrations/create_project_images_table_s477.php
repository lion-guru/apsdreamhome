<?php
/**
 * Migration S477: Create/Ensure project_images table and columns
 * 
 * Creates or updates the project_images table for admin project image management.
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S477: Ensure project_images table & columns ===\n";

try {
    $tableExists = (bool)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'project_images'"
    )->fetchColumn();

    if (!$tableExists) {
        $pdo->exec("
        CREATE TABLE `project_images` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `project_id` INT UNSIGNED NOT NULL,
            `image_path` VARCHAR(500) NOT NULL,
            `image_name` VARCHAR(255) NULL,
            `alt_text` VARCHAR(255) NULL,
            `display_order` INT NOT NULL DEFAULT 0,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `file_size` BIGINT UNSIGNED NULL,
            `mime_type` VARCHAR(100) NULL,
            `uploaded_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_project` (`project_id`),
            INDEX `idx_tenant` (`tenant_id`),
            INDEX `idx_display_order` (`display_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        echo "  [+] Table project_images created successfully\n";
    } else {
        // Table exists - ensure all columns exist
        $cols = [
            'project_id'    => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `project_id` INT UNSIGNED NOT NULL AFTER `tenant_id`",
            'image_path'    => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `image_path` VARCHAR(500) NOT NULL AFTER `project_id`",
            'image_name'    => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `image_name` VARCHAR(255) NULL AFTER `image_path`",
            'alt_text'      => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `alt_text` VARCHAR(255) NULL AFTER `image_name`",
            'display_order' => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `display_order` INT NOT NULL DEFAULT 0 AFTER `alt_text`",
            'is_primary'    => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `is_primary` TINYINT(1) NOT NULL DEFAULT 0 AFTER `display_order`",
            'file_size'     => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `file_size` BIGINT UNSIGNED NULL AFTER `is_primary`",
            'mime_type'     => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `mime_type` VARCHAR(100) NULL AFTER `file_size`",
            'uploaded_by'   => "ALTER TABLE `project_images` ADD COLUMN IF NOT EXISTS `uploaded_by` INT UNSIGNED NULL AFTER `mime_type`",
        ];

        foreach ($cols as $colName => $alterSql) {
            $hasCol = (bool)$pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'project_images' 
                 AND COLUMN_NAME = '$colName'"
            )->fetchColumn();

            if (!$hasCol) {
                $pdo->exec($alterSql);
                echo "  [+] Added missing column `project_images`.`$colName`\n";
            } else {
                echo "  [*] Column `project_images`.`$colName` already exists (skipped)\n";
            }
        }
    }

    echo "=== MIGRATION S477 COMPLETE: Success ===\n";

} catch (\Throwable $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

