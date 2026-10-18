<?php
/**
 * Create workflow_rules table (Session 256, CRM automation v1).
 * Idempotent: CREATE runs ONLY when the table is absent.
 *
 * Rule = WHEN trigger (+ optional conditions) THEN action, owned per user.
 * v1 triggers: lead_created, status_changed, note_added.
 * v1 actions: add_note, set_status, create_followup.
 * (auto_assign / whatsapp_send deliberately phase-2: permission + provider side-effects.)
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'workflow_rules'"
)->fetch(PDO::FETCH_ASSOC);

if ($exists) {
    echo "SKIP workflow_rules (exists)\nDone.\n";
    return;
}

$pdo->exec(
    "CREATE TABLE `workflow_rules` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
      `user_id` BIGINT(20) UNSIGNED NOT NULL,
      `name` VARCHAR(150) NOT NULL,
      `trigger` VARCHAR(30) NOT NULL DEFAULT 'lead_created',
      `conditions` TEXT NULL DEFAULT NULL,
      `action` VARCHAR(30) NOT NULL DEFAULT 'add_note',
      `action_params` TEXT NULL DEFAULT NULL,
      `is_active` TINYINT(1) NOT NULL DEFAULT 1,
      `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
      `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_wr_owner` (`user_id`),
      KEY `idx_wr_trigger` (`trigger`),
      CONSTRAINT `fk_wr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
echo "FIXED workflow_rules (created)\nDone.\n";
