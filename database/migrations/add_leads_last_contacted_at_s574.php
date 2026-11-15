<?php
/**
 * Add leads.last_contacted_at (Session 573).
 * Idempotent: ADD runs ONLY when the column is absent. Safe to re-run after restore.
 *
 * Background: ManualCallingDeskController (Employee telecaller "Log Call") has always
 * written leads.last_contacted_at = NOW() after every call, and CRMService / the
 * lead_kanban view read it — but the column was never migrated. Every such UPDATE died
 * with 1054 inside the catch. This migration adds the missing domain field; no backfill.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT COUNT(*) AS n FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'last_contacted_at'"
)->fetch(PDO::FETCH_ASSOC);

if ((int)($row['n'] ?? 0) > 0) {
    echo "SKIP leads.last_contacted_at (already present)\nDone.\n";
    return;
}

echo "Safety note: nullable DATETIME, no backfill, no enum change. Taking no data risk.\n";
$pdo->exec("ALTER TABLE `leads` ADD COLUMN `last_contacted_at` DATETIME NULL DEFAULT NULL AFTER `first_response_at`");
echo "FIXED leads.last_contacted_at\nDone.\n";
