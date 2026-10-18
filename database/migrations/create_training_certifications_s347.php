<?php
/**
 * Migration: Training Certifications & Skill Categories (Phase 4 HR)
 * Idempotent: CREATE TABLE IF NOT EXISTS + INSERT IGNORE seeds
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$pdo->exec("
CREATE TABLE IF NOT EXISTS `training_certifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NOT NULL,
    `certificate_number` VARCHAR(64) NOT NULL,
    `issued_at` DATETIME NOT NULL,
    `expires_at` DATETIME NULL,
    `status` ENUM('active','expired','revoked') NOT NULL DEFAULT 'active',
    `verification_hash` VARCHAR(64) NOT NULL,
    `pdf_path` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cert_number` (`certificate_number`),
    UNIQUE KEY `uq_course_employee` (`course_id`, `employee_id`),
    KEY `idx_employee` (`employee_id`),
    KEY `idx_status` (`status`),
    KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `skill_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(50) NULL,
    `color` VARCHAR(7) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Seed skill categories
$cats = [
    ['Technical Skills', 'Programming, databases, cloud, DevOps', 'code', '#3b82f6', 1],
    ['Sales & CRM', 'Lead management, negotiation, pipeline', 'handshake', '#10b981', 2],
    ['Communication', 'Verbal, written, presentation, negotiation', 'message-square', '#8b5cf6', 3],
    ['Leadership', 'Team management, coaching, decision making', 'users', '#f59e0b', 4],
    ['Compliance & Legal', 'RERA, labor law, contracts, documentation', 'shield', '#ef4444', 5],
    ['Project Management', 'Planning, execution, agile, risk management', 'clipboard', '#06b6d4', 6],
    ['Financial', 'Budgeting, forecasting, accounting, taxation', 'dollar-sign', '#84cc16', 7],
    ['Customer Service', 'Support, ticketing, escalation, satisfaction', 'headphones', '#ec4899', 8],
];

$stmt = $pdo->prepare("
INSERT IGNORE INTO `skill_categories` (`name`, `description`, `icon`, `color`, `sort_order`, `is_active`)
VALUES (?, ?, ?, ?, ?, 1)
");
foreach ($cats as $c) {
    $stmt->execute($c);
}

echo "DONE: training_certifications + skill_categories tables ready.\n";