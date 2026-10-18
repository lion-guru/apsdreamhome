<?php
/**
 * Add lost_reason + first_response_at to leads (Session 256, CRM best-build).
 * Idempotent: each ALTER runs ONLY when its column is absent.
 *
 * - lost_reason: mandatory-on-close dropdown (price/location/loan/competitor/
 *   no_response/other) — powers lost-reason analytics (BUILD #4).
 * - first_response_at: stamped on the associate's FIRST action (note/status/
 *   assign) — powers speed-to-lead SLA (BUILD #1). Never overwritten.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$have = [];
foreach ($pdo->query(
    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads'
      AND COLUMN_NAME IN ('lost_reason','first_response_at')"
)->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $have[$r['COLUMN_NAME']] = true;
}

if (empty($have['lost_reason'])) {
    $pdo->exec("ALTER TABLE `leads` ADD COLUMN `lost_reason` VARCHAR(50) NULL DEFAULT NULL AFTER `status`");
    echo "FIXED leads.lost_reason (added)\n";
} else {
    echo "SKIP leads.lost_reason (present)\n";
}

if (empty($have['first_response_at'])) {
    $pdo->exec("ALTER TABLE `leads` ADD COLUMN `first_response_at` DATETIME NULL DEFAULT NULL AFTER `lost_reason`");
    echo "FIXED leads.first_response_at (added)\n";
} else {
    echo "SKIP leads.first_response_at (present)\n";
}
echo "Done.\n";
