<?php
/**
 * Create user_contacts table (Session 256, user mini-CRM).
 * Idempotent: CREATE runs ONLY when the table is absent.
 *
 * Why a separate table (zero-dup audit): property-posting customers get
 * inquiries on THEIR listings — those are THEIR leads. Storing them in
 * `leads` would pollute the company pipeline (associate scopes, admin
 * lists, scoring). Owner = users.id, visible only to the owner.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_contacts'"
)->fetch(PDO::FETCH_ASSOC);

if ($exists) {
    echo "SKIP user_contacts (exists)\nDone.\n";
    return;
}

$pdo->exec(
    "CREATE TABLE `user_contacts` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
      `user_id` BIGINT(20) UNSIGNED NOT NULL,
      `name` VARCHAR(150) NOT NULL,
      `phone` VARCHAR(20) NOT NULL,
      `email` VARCHAR(150) NULL DEFAULT NULL,
      `property_ref` VARCHAR(150) NULL DEFAULT NULL,
      `stage` ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
      `notes` TEXT NULL DEFAULT NULL,
      `next_followup_date` DATE NULL DEFAULT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
      `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_uc_owner` (`user_id`),
      KEY `idx_uc_stage` (`stage`),
      CONSTRAINT `fk_uc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
echo "FIXED user_contacts (created)\nDone.\n";
