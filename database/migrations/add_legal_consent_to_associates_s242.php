<?php
/**
 * Add legal_consent_accepted column to associates table (Session 242).
 * Idempotent: ALTER runs ONLY when column is absent.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'associates' AND COLUMN_NAME = 'legal_consent_accepted'"
)->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "SKIP associates.legal_consent_accepted (already present)\nDone.\n";
    return;
}

$pdo->exec(
    "ALTER TABLE `associates` ADD COLUMN `legal_consent_accepted` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`"
);
echo "FIXED associates.legal_consent_accepted (added)\nDone.\n";