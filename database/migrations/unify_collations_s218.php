<?php
/**
 * Collation Unification (Session 218, re-applied post-restore).
 * Idempotent: ALTERs run ONLY when current collation mismatches.
 * Safe to re-run after any DB restore.
 *
 * Background: cross-table string `=` across mixed collations throws
 * MySQL 1267 (killed /milestones-list once). These business keys must
 * share utf8mb4_general_ci. latin1 must stay extinct.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$targets = [
    // [table, column, full column definition suffix]
    ['rera_projects', 'rera_number', "varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL"],
    ['land_brokers', 'rera_number', "varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL"],
    ['gata_master', 'gata_no', "varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL"],
];

foreach ($targets as [$table, $col, $def]) {
    $row = $pdo->query(
        "SELECT COLLATION_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$col'"
    )->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo "SKIP $table.$col (column absent)\n"; continue; }
    if (($row['COLLATION_NAME'] ?? '') === 'utf8mb4_general_ci') { echo "SKIP $table.$col (already unified)\n"; continue; }
    $pdo->exec("ALTER TABLE `$table` MODIFY `$col` $def");
    echo "FIXED $table.$col ({$row['COLLATION_NAME']} → utf8mb4_general_ci)\n";
}

$lat = $pdo->query(
    "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND COLLATION_NAME LIKE 'latin1%'"
)->fetch(PDO::FETCH_ASSOC);
echo "latin1 columns remaining: " . (int)($lat['c'] ?? -1) . "\n";
echo "Done.\n";
