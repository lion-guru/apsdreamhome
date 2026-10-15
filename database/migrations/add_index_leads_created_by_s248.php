<?php
/**
 * Add index on leads.created_by (Session 248).
 * Idempotent: CREATE INDEX runs ONLY when the index is absent.
 *
 * Background: every associate CRM query scopes (assigned_to=? OR created_by=?).
 * assigned_to is indexed 4× over but created_by has NO index, so EXPLAIN shows
 * type=ALL over ~38k rows with key=NULL (the unindexed OR leg poisons the plan).
 * Enables index_merge on the whole CRM family (list/counters/delete/note/status/assign).
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query(
    "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'created_by'"
)->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "SKIP leads.created_by (already indexed)\nDone.\n";
    return;
}

$pdo->exec("CREATE INDEX idx_leads_created_by ON `leads` (`created_by`)");
echo "FIXED leads.created_by (index created)\nDone.\n";
