<?php
/**
 * Add 'site_visit' to leads.status ENUM (Session 241).
 * Idempotent: ALTER runs ONLY when site_visit is absent from the enum.
 * Safe to re-run after any DB restore.
 *
 * Background: associate lead_detail view offers a Site Visit pipeline step
 * and CrmController@updateLeadStatus whitelists it, but the DB enum never
 * had the value — every Site-Visit update died (invalid-status or 1267).
 * Appended at END so existing values keep their internal index numbers.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'status'"
)->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "SKIP leads.status (column absent)\nDone.\n";
    return;
}

if (strpos($row['COLUMN_TYPE'], "'site_visit'") !== false) {
    echo "SKIP leads.status (site_visit already present)\nDone.\n";
    return;
}

$pdo->exec(
    "ALTER TABLE `leads` MODIFY `status`
      ENUM('new','contacted','qualified','proposal','negotiation','closed_won','closed_lost','nurture','pending','follow_up','converted','not_interested','site_visit')
      NULL DEFAULT 'new'"
);
echo "FIXED leads.status (site_visit appended)\nDone.\n";
