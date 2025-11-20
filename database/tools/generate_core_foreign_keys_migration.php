<?php
// Generate SQL to add core foreign keys where safe
// Usage: php database/tools/generate_core_foreign_keys_migration.php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';
require_once $projectRoot . '/includes/config/constants.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection $con (mysqli) not available.\n");
    exit(1);
}

$dbName = DB_NAME ?? null;
if (!$dbName) {
    fwrite(STDERR, "[ERROR] DB_NAME not defined.\n");
    exit(1);
}

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

function primaryKeyFor(mysqli $con, string $db, string $table): ?string {
    // Try `id` first, else `pk_id`
    $pk = fetchOneAssoc($con, "SELECT k.COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS tc JOIN information_schema.KEY_COLUMN_USAGE k ON tc.CONSTRAINT_NAME=k.CONSTRAINT_NAME AND tc.TABLE_SCHEMA=k.TABLE_SCHEMA AND tc.TABLE_NAME=k.TABLE_NAME WHERE tc.TABLE_SCHEMA=? AND tc.TABLE_NAME=? AND tc.CONSTRAINT_TYPE='PRIMARY KEY' LIMIT 1", [$db, $table]);
    return $pk['COLUMN_NAME'] ?? null;
}

function isIntegerType(?array $col): bool {
    if (!$col) return false;
    $type = strtolower((string)$col['DATA_TYPE']);
    return in_array($type, ['tinyint','smallint','mediumint','int','bigint'], true);
}

function isNullable(?array $col): bool { return ($col && strtoupper((string)$col['IS_NULLABLE']) === 'YES'); }

function indexExists(mysqli $con, string $db, string $table, string $indexName): bool {
    $row = fetchOneAssoc($con, 'SELECT 1 AS ok FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1', [$db, $table, $indexName]);
    return (bool)$row;
}

function foreignKeyExists(mysqli $con, string $db, string $table, string $constraintName): bool {
    $row = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME=? AND CONSTRAINT_TYPE='FOREIGN KEY' LIMIT 1", [$db, $table, $constraintName]);
    return (bool)$row;
}

// Define core relationships; generator will check presence before emitting
$relations = [
    ['table' => 'bookings', 'column' => 'property_id', 'ref_table' => 'properties', 'ref_column' => 'id', 'name' => 'fk_bookings_property_id'],
    ['table' => 'bookings', 'column' => 'user_id',     'ref_table' => 'users',      'ref_column' => 'id', 'name' => 'fk_bookings_user_id'],
    ['table' => 'bookings', 'column' => 'customer_id', 'ref_table' => 'users',      'ref_column' => 'id', 'name' => 'fk_bookings_customer_id'],
    ['table' => 'property_images', 'column' => 'property_id', 'ref_table' => 'properties', 'ref_column' => 'id', 'name' => 'fk_property_images_property_id'],
    ['table' => 'property_inquiries', 'column' => 'property_id', 'ref_table' => 'properties', 'ref_column' => 'id', 'name' => 'fk_property_inquiries_property_id'],
    ['table' => 'leads', 'column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_leads_user_id'],
    ['table' => 'payments', 'column' => 'booking_id', 'ref_table' => 'bookings', 'ref_column' => 'id', 'name' => 'fk_payments_booking_id'],
    ['table' => 'plots', 'column' => 'project_id', 'ref_table' => 'projects', 'ref_column' => 'id', 'name' => 'fk_plots_project_id'],
    ['table' => 'plot_bookings', 'column' => 'plot_id', 'ref_table' => 'plots', 'ref_column' => 'id', 'name' => 'fk_plot_bookings_plot_id'],
    ['table' => 'plot_bookings', 'column' => 'booking_id', 'ref_table' => 'bookings', 'ref_column' => 'id', 'name' => 'fk_plot_bookings_booking_id'],
];

$sqlOut = [];
$sqlOut[] = "-- 003_add_core_foreign_keys.sql";
$sqlOut[] = "-- Generated: " . date('Y-m-d H:i:s');
$sqlOut[] = "-- Add safe core foreign keys for critical integrity";
$sqlOut[] = "";

foreach ($relations as $rel) {
    $t = $rel['table']; $c = $rel['column']; $rt = $rel['ref_table']; $rc = $rel['ref_column']; $name = $rel['name'];
    // Validate tables and columns
    if (!tableExists($con, $dbName, $t) || !tableExists($con, $dbName, $rt)) { $sqlOut[] = "-- SKIP: `$t` or `$rt` missing"; continue; }
    $col = columnInfo($con, $dbName, $t, $c);
    $refPk = primaryKeyFor($con, $dbName, $rt);
    $refCol = columnInfo($con, $dbName, $rt, $rc);
    if (!$col || !$refPk || !$refCol) { $sqlOut[] = "-- SKIP: `$t`.`$c` or `$rt` PK missing"; continue; }
    // Require the referenced PK to be `id` to align with code expectations
    if ($refPk !== $rc) { $sqlOut[] = "-- SKIP: `$rt` PK is `$refPk`, expected `$rc`"; continue; }
    // Only create FK if referencing column is integer
    if (!isIntegerType($col)) { $sqlOut[] = "-- SKIP: `$t`.`$c` not integer type"; continue; }
    // If type mismatch, align referencing column to referenced COLUMN_TYPE
    if (strtolower((string)$col['COLUMN_TYPE']) !== strtolower((string)$refCol['COLUMN_TYPE'])) {
        $nullSpec = isNullable($col) ? 'NULL' : 'NOT NULL';
        $sqlOut[] = "ALTER TABLE `$t` MODIFY `$c` {$refCol['COLUMN_TYPE']} $nullSpec;";
    }

    // Add index if not present
    $sqlOut[] = "-- $t.$c -> $rt.$rc";
    $ixName = "ix_{$t}_{$c}";
    if (!indexExists($con, $dbName, $t, $ixName)) {
        $sqlOut[] = "ALTER TABLE `$t` ADD INDEX `$ixName` (`$c`);";
    } else {
        $sqlOut[] = "-- SKIP INDEX: `$ixName` already exists";
    }
    // Set ON DELETE based on nullability
    $onDelete = isNullable($col) ? 'SET NULL' : 'RESTRICT';
    if (!foreignKeyExists($con, $dbName, $t, $name)) {
        $sqlOut[] = "ALTER TABLE `$t` ADD CONSTRAINT `$name` FOREIGN KEY (`$c`) REFERENCES `$rt`(`$rc`) ON DELETE $onDelete ON UPDATE CASCADE;";
    } else {
        $sqlOut[] = "-- SKIP FK: `$name` already exists";
    }
    $sqlOut[] = "";
}

$migrationsDir = dirname(__DIR__) . '/migrations';
if (!is_dir($migrationsDir)) { mkdir($migrationsDir, 0777, true); }
$targetFile = $migrationsDir . '/003_add_core_foreign_keys.sql';
file_put_contents($targetFile, implode("\n", $sqlOut));
echo "Core foreign keys migration written to: $targetFile\n";

?>
