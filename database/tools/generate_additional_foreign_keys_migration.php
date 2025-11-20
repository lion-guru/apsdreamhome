<?php
// Generate SQL to add additional foreign keys beyond core, safely
// Usage: php database/tools/generate_additional_foreign_keys_migration.php --out=database/migrations/005_add_additional_foreign_keys.sql

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

function primaryKeyFor(mysqli $con, string $db, string $table): ?string {
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

function referencedColumnType(mysqli $con, string $db, string $table, string $column): string {
    $col = columnInfo($con, $db, $table, $column);
    return $col && !empty($col['COLUMN_TYPE']) ? $col['COLUMN_TYPE'] : 'bigint(20) unsigned';
}

function alignColumnType(array &$sqlOut, mysqli $con, string $db, string $table, string $column, string $refTable, string $refColumn): void {
    $col = columnInfo($con, $db, $table, $column);
    $refType = referencedColumnType($con, $db, $refTable, $refColumn);
    if ($col && isset($col['COLUMN_TYPE']) && strtolower((string)$col['COLUMN_TYPE']) !== strtolower((string)$refType)) {
        $nullSpec = isNullable($col) ? 'NULL' : 'NOT NULL';
        $sqlOut[] = "ALTER TABLE `{$table}` MODIFY `{$column}` {$refType} {$nullSpec};";
    }
}

// Candidate additional relationships discovered in codebase; generator will validate presence
$relations = [
    // plots
    ['table' => 'plots', 'column' => 'customer_id', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_plots_customer_id'],
    ['table' => 'plots', 'column' => 'associate_id', 'ref_table' => 'associates', 'ref_column' => 'id', 'name' => 'fk_plots_associate_id'],

    // property_visits
    ['table' => 'property_visits', 'column' => 'property_id', 'ref_table' => 'properties', 'ref_column' => 'id', 'name' => 'fk_property_visits_property_id'],
    ['table' => 'property_visits', 'column' => 'customer_id', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_property_visits_customer_id'],
    ['table' => 'property_visits', 'column' => 'created_by', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_property_visits_created_by'],

    // mlm_commissions
    ['table' => 'mlm_commissions', 'column' => 'associate_id', 'ref_table' => 'associates', 'ref_column' => 'id', 'name' => 'fk_mlm_commissions_associate_id'],
    ['table' => 'mlm_commissions', 'column' => 'booking_id', 'ref_table' => 'bookings', 'ref_column' => 'id', 'name' => 'fk_mlm_commissions_booking_id'],

    // leads assignments
    ['table' => 'leads', 'column' => 'assigned_to', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_leads_assigned_to'],

    // properties type
    ['table' => 'properties', 'column' => 'type_id', 'ref_table' => 'property_types', 'ref_column' => 'id', 'name' => 'fk_properties_type_id'],

    // associates owner
    ['table' => 'associates', 'column' => 'user_id', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_associates_user_id'],

    // documents owner
    ['table' => 'documents', 'column' => 'owner_user_id', 'ref_table' => 'users', 'ref_column' => 'id', 'name' => 'fk_documents_owner_user_id'],

    // projects land purchase, if present
    ['table' => 'projects', 'column' => 'land_purchase_id', 'ref_table' => 'land_purchases', 'ref_column' => 'id', 'name' => 'fk_projects_land_purchase_id'],
];

$sqlOut = [];
$sqlOut[] = "-- 005_add_additional_foreign_keys.sql";
$sqlOut[] = "-- Generated: " . date('Y-m-d H:i:s');
$sqlOut[] = "-- Add safe additional foreign keys discovered across the schema";
$sqlOut[] = "";

foreach ($relations as $rel) {
    $t = $rel['table']; $c = $rel['column']; $rt = $rel['ref_table']; $rc = $rel['ref_column']; $name = $rel['name'];
    if (!tableExists($con, $dbName, $t) || !tableExists($con, $dbName, $rt)) { $sqlOut[] = "-- SKIP: `$t` or `$rt` missing"; continue; }
    $col = columnInfo($con, $dbName, $t, $c);
    $refPk = primaryKeyFor($con, $dbName, $rt);
    $refCol = columnInfo($con, $dbName, $rt, $rc);
    if (!$col || !$refPk || !$refCol) { $sqlOut[] = "-- SKIP: `$t`.`$c` or `$rt` PK missing"; continue; }
    if ($refPk !== $rc) { $sqlOut[] = "-- SKIP: `$rt` PK is `$refPk`, expected `$rc`"; continue; }
    if (!isIntegerType($col)) { $sqlOut[] = "-- SKIP: `$t`.`$c` not integer type"; continue; }
    if (strtolower((string)$col['COLUMN_TYPE']) !== strtolower((string)$refCol['COLUMN_TYPE'])) {
        $nullSpec = isNullable($col) ? 'NULL' : 'NOT NULL';
        $sqlOut[] = "ALTER TABLE `$t` MODIFY `$c` {$refCol['COLUMN_TYPE']} $nullSpec;";
    }
    $sqlOut[] = "-- $t.$c -> $rt.$rc";
    $ixName = "ix_{$t}_{$c}";
    if (!indexExists($con, $dbName, $t, $ixName)) {
        $sqlOut[] = "ALTER TABLE `$t` ADD INDEX `$ixName` (`$c`);";
    } else {
        $sqlOut[] = "-- SKIP INDEX: `$ixName` already exists";
    }
    $onDelete = isNullable($col) ? 'SET NULL' : 'RESTRICT';
    if (!foreignKeyExists($con, $dbName, $t, $name)) {
        $sqlOut[] = "ALTER TABLE `$t` ADD CONSTRAINT `$name` FOREIGN KEY (`$c`) REFERENCES `$rt`(`$rc`) ON DELETE $onDelete ON UPDATE CASCADE;";
    } else {
        $sqlOut[] = "-- SKIP FK: `$name` already exists";
    }
    $sqlOut[] = "";
}

function parseOutArg(array $argv): string {
    $default = dirname(__DIR__) . '/migrations/005_add_additional_foreign_keys.sql';
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--out=')) {
            $val = substr($arg, 6);
            // Resolve relative path against project root
            if (!preg_match('/^([a-zA-Z]:\\\\|\\\\|\/)/', $val)) {
                $val = dirname(__DIR__, 2) . '/' . ltrim($val, '/');
            }
            return $val;
        }
    }
    return $default;
}

$outPath = parseOutArg($_SERVER['argv'] ?? []);
$dir = dirname($outPath);
if (!is_dir($dir)) { mkdir($dir, 0777, true); }
file_put_contents($outPath, implode("\n", $sqlOut));
echo "Additional foreign keys migration written to: $outPath\n";

?>
