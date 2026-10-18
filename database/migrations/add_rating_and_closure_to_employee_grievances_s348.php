<?php
/**
 * Migration: Add rating and closure tracking columns to employee_grievances
 * Session: 348
 * Idempotent: Checks information_schema before adding columns
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$columnsToAdd = [
    'employee_rating' => "ALTER TABLE `employee_grievances` ADD COLUMN `employee_rating` TINYINT UNSIGNED NULL DEFAULT NULL AFTER `resolution`",
    'employee_feedback' => "ALTER TABLE `employee_grievances` ADD COLUMN `employee_feedback` TEXT NULL DEFAULT NULL AFTER `employee_rating`",
    'closed_at' => "ALTER TABLE `employee_grievances` ADD COLUMN `closed_at` TIMESTAMP NULL DEFAULT NULL AFTER `employee_feedback`",
    'closed_by' => "ALTER TABLE `employee_grievances` ADD COLUMN `closed_by` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `closed_at`"
];

foreach ($columnsToAdd as $col => $sql) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employee_grievances' AND COLUMN_NAME = ?
    ");
    $stmt->execute([$col]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec($sql);
        echo "Added column $col to employee_grievances.\n";
    } else {
        echo "Column $col already exists in employee_grievances (SKIPPED).\n";
    }
}
