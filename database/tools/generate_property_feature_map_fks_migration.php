<?php
// Generate SQL to add foreign keys for property_feature_map
// Usage: php database/tools/generate_property_feature_map_fks_migration.php

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

function foreignKeyExists(mysqli $con, string $db, string $table, string $constraintName): bool {
    $row = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME=? AND CONSTRAINT_TYPE='FOREIGN KEY' LIMIT 1", [$db, $table, $constraintName]);
    return (bool)$row;
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

$sqlOut = [];
$sqlOut[] = "-- 006_add_property_feature_map_fks.sql";
$sqlOut[] = "-- Generated: " . date('Y-m-d H:i:s');
$sqlOut[] = "-- Add foreign keys for property_feature_map join table";
$sqlOut[] = "";

if (!tableExists($con, $dbName, 'property_feature_map')) {
    $sqlOut[] = "-- SKIP: `property_feature_map` missing";
} else {
    // property_id -> properties(id)
    if (tableExists($con, $dbName, 'properties') && columnInfo($con, $dbName, 'property_feature_map', 'property_id')) {
        alignColumnType($sqlOut, $con, $dbName, 'property_feature_map', 'property_id', 'properties', 'id');
        if (!foreignKeyExists($con, $dbName, 'property_feature_map', 'fk_property_feature_map_property')) {
            $sqlOut[] = "ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
        } else {
            $sqlOut[] = "-- SKIP FK: `fk_property_feature_map_property` already exists";
        }
    } else {
        $sqlOut[] = "-- SKIP: `properties` or `property_feature_map.property_id` missing";
    }

    // feature_id -> property_features(id)
    if (tableExists($con, $dbName, 'property_features') && columnInfo($con, $dbName, 'property_feature_map', 'feature_id')) {
        alignColumnType($sqlOut, $con, $dbName, 'property_feature_map', 'feature_id', 'property_features', 'id');
        if (!foreignKeyExists($con, $dbName, 'property_feature_map', 'fk_property_feature_map_feature')) {
            $sqlOut[] = "ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_feature` FOREIGN KEY (`feature_id`) REFERENCES `property_features`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
        } else {
            $sqlOut[] = "-- SKIP FK: `fk_property_feature_map_feature` already exists";
        }
    } else {
        $sqlOut[] = "-- SKIP: `property_features` or `property_feature_map.feature_id` missing";
    }
}

$migrationsDir = dirname(__DIR__) . '/migrations';
if (!is_dir($migrationsDir)) { mkdir($migrationsDir, 0777, true); }
$targetFile = $migrationsDir . '/006_add_property_feature_map_fks.sql';
file_put_contents($targetFile, implode("\n", $sqlOut));
echo "Property feature map FKs migration written to: $targetFile\n";

?>

