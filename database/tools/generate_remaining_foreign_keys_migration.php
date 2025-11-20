<?php
// Generate SQL to add remaining foreign keys across core domain tables
// Usage: php database/tools/generate_remaining_foreign_keys_migration.php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';
require_once $projectRoot . '/includes/config/constants.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection (mysqli) not available.\n");
    exit(1);
}

$dbName = DB_NAME ?? null;
if (!$dbName) { fwrite(STDERR, "[ERROR] DB_NAME not defined.\n"); exit(1); }

function fetchOneAssoc(mysqli $con, string $sql, array $params = []): ?array {
    $stmt = $con->prepare($sql);
    if (!$stmt) return null;
    if ($params) { $types = str_repeat('s', count($params)); $stmt->bind_param($types, ...$params); }
    if (!$stmt->execute()) return null;
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $r = fetchOneAssoc($con, 'SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?', [$db, $table]);
    return (bool)$r;
}

function columnInfo(mysqli $con, string $db, string $table, string $column): ?array {
    return fetchOneAssoc($con, 'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?', [$db, $table, $column]);
}

function indexExists(mysqli $con, string $db, string $table, string $indexName): bool {
    $row = fetchOneAssoc($con, 'SELECT 1 AS ok FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=?', [$db, $table, $indexName]);
    return (bool)$row;
}

function foreignKeyExists(mysqli $con, string $db, string $table, string $constraintName): bool {
    $row = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME=? AND CONSTRAINT_TYPE='FOREIGN KEY' LIMIT 1", [$db, $table, $constraintName]);
    return (bool)$row;
}

function primaryKeyFor(mysqli $con, string $db, string $table): ?string {
    $pk = fetchOneAssoc($con, "SELECT k.COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS tc JOIN information_schema.KEY_COLUMN_USAGE k ON tc.CONSTRAINT_NAME=k.CONSTRAINT_NAME AND tc.TABLE_SCHEMA=k.TABLE_SCHEMA AND tc.TABLE_NAME=k.TABLE_NAME WHERE tc.TABLE_SCHEMA=? AND tc.TABLE_NAME=? AND tc.CONSTRAINT_TYPE='PRIMARY KEY' LIMIT 1", [$db, $table]);
    return $pk['COLUMN_NAME'] ?? null;
}

function referencedColumnType(mysqli $con, string $db, string $table, string $column): string {
    $col = columnInfo($con, $db, $table, $column);
    return $col && !empty($col['COLUMN_TYPE']) ? $col['COLUMN_TYPE'] : 'bigint(20) unsigned';
}

function isNullable(?array $col): bool { return ($col && strtoupper((string)$col['IS_NULLABLE']) === 'YES'); }

function alignColumnType(array &$sqlOut, mysqli $con, string $db, string $table, string $column, string $refTable, string $refColumn): void {
    $col = columnInfo($con, $db, $table, $column);
    $refType = referencedColumnType($con, $db, $refTable, $refColumn);
    if ($col && isset($col['COLUMN_TYPE']) && strtolower((string)$col['COLUMN_TYPE']) !== strtolower((string)$refType)) {
        $nullSpec = isNullable($col) ? 'NULL' : 'NOT NULL';
        $sqlOut[] = "ALTER TABLE `{$table}` MODIFY `{$column}` {$refType} {$nullSpec};";
    }
}

function countOrphans(mysqli $con, string $db, string $table, string $column, string $refTable, string $refColumn): int {
    $sql = "SELECT COUNT(*) AS cnt FROM `{$table}` t LEFT JOIN `{$refTable}` r ON t.`{$column}` = r.`{$refColumn}` WHERE t.`{$column}` IS NOT NULL AND r.`{$refColumn}` IS NULL";
    $row = fetchOneAssoc($con, $sql);
    return (int)($row['cnt'] ?? 0);
}

$sqlOut = [];
$sqlOut[] = "-- 007_add_remaining_foreign_keys.sql";
$sqlOut[] = "-- Generated: " . date('Y-m-d H:i:s');
$sqlOut[] = "-- Add safe remaining foreign keys across bookings, payments, plots, and transactions";
$sqlOut[] = "";

// Helper to add FK with index and type alignment
function addFk(array &$sqlOut, mysqli $con, string $db, array $cfg): void {
    $t = $cfg['table']; $c = $cfg['column']; $rt = $cfg['ref_table']; $rc = $cfg['ref_column'];
    $fkName = $cfg['name']; $onDelete = $cfg['on_delete'] ?? 'RESTRICT'; $onUpdate = $cfg['on_update'] ?? 'CASCADE';
    $indexName = $cfg['index'] ?? ('ix_' . $t . '_' . $c);

    if (!tableExists($con, $db, $t) || !tableExists($con, $db, $rt)) { $sqlOut[] = "-- SKIP: `$t` or `$rt` missing"; return; }
    $col = columnInfo($con, $db, $t, $c);
    $refPk = primaryKeyFor($con, $db, $rt);
    $refCol = columnInfo($con, $db, $rt, $rc);
    if (!$col || !$refPk || !$refCol) { $sqlOut[] = "-- SKIP: `$t`.`$c` or `$rt` PK missing"; return; }

    alignColumnType($sqlOut, $con, $db, $t, $c, $rt, $rc);

    // Data clean-up if orphans exist and ON DELETE SET NULL is intended
    $orphans = countOrphans($con, $db, $t, $c, $rt, $rc);
    if ($orphans > 0) {
        if (strtoupper($onDelete) === 'SET NULL') {
            // Ensure column is nullable before cleanup
            $colInfo = columnInfo($con, $db, $t, $c);
            if ($colInfo && !isNullable($colInfo)) {
                $refType = referencedColumnType($con, $db, $rt, $rc);
                $sqlOut[] = "ALTER TABLE `{$t}` MODIFY `{$c}` {$refType} NULL;";
            }
            $sqlOut[] = "UPDATE `{$t}` t LEFT JOIN `{$rt}` r ON t.`{$c}` = r.`{$rc}` SET t.`{$c}` = NULL WHERE r.`{$rc}` IS NULL;";
        } else {
            $sqlOut[] = "-- SKIP FK: `{$fkName}` has {$orphans} orphan rows and delete rule is {$onDelete}";
            return;
        }
    }
    if (!indexExists($con, $db, $t, $indexName)) {
        $sqlOut[] = "ALTER TABLE `{$t}` ADD INDEX `{$indexName}` (`{$c}`);";
    } else {
        $sqlOut[] = "-- SKIP IDX: `{$indexName}` exists";
    }
    if (!foreignKeyExists($con, $db, $t, $fkName)) {
        $sqlOut[] = "ALTER TABLE `{$t}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$c}`) REFERENCES `{$rt}`(`{$rc}`) ON DELETE {$onDelete} ON UPDATE {$onUpdate};";
    } else {
        $sqlOut[] = "-- SKIP FK: `{$fkName}` exists";
    }
}

// Target relationships inferred from codebase
$targets = [
    // bookings -> customers/properties
    ['table' => 'bookings', 'column' => 'customer_id', 'ref_table' => 'customers', 'ref_column' => 'id', 'name' => 'fk_bookings_customer_id', 'on_delete' => 'CASCADE'],
    ['table' => 'bookings', 'column' => 'property_id', 'ref_table' => 'properties', 'ref_column' => 'id', 'name' => 'fk_bookings_property_id', 'on_delete' => 'SET NULL'],

    // payments -> bookings (and optionally customers)
    ['table' => 'payments', 'column' => 'booking_id', 'ref_table' => 'bookings', 'ref_column' => 'id', 'name' => 'fk_payments_booking_id', 'on_delete' => 'CASCADE'],
    ['table' => 'payments', 'column' => 'customer_id', 'ref_table' => 'customers', 'ref_column' => 'id', 'name' => 'fk_payments_customer_id', 'on_delete' => 'SET NULL'],

    // plots -> projects/users/associates
    ['table' => 'plots', 'column' => 'project_id', 'ref_table' => 'projects', 'ref_column' => 'id', 'name' => 'fk_plots_project_id', 'on_delete' => 'SET NULL'],
    ['table' => 'plots', 'column' => 'customer_id', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_plots_customer_id', 'on_delete' => 'SET NULL'],
    ['table' => 'plots', 'column' => 'associate_id', 'ref_table' => 'associates', 'ref_column' => 'id', 'name' => 'fk_plots_associate_id', 'on_delete' => 'SET NULL'],

    // transactions -> customers/properties
    ['table' => 'transactions', 'column' => 'customer_id', 'ref_table' => 'customers', 'ref_column' => 'id', 'name' => 'fk_transactions_customer_id', 'on_delete' => 'CASCADE'],
    ['table' => 'transactions', 'column' => 'property_id', 'ref_table' => 'properties', 'ref_column' => 'id', 'name' => 'fk_transactions_property_id', 'on_delete' => 'SET NULL'],
];

foreach ($targets as $cfg) {
    addFk($sqlOut, $con, $dbName, $cfg);
}

$migrationsDir = dirname(__DIR__) . '/migrations';
if (!is_dir($migrationsDir)) { mkdir($migrationsDir, 0777, true); }
$targetFile = $migrationsDir . '/007_add_remaining_foreign_keys.sql';
file_put_contents($targetFile, implode("\n", $sqlOut));
echo "Remaining foreign keys migration written to: $targetFile\n";

?>
