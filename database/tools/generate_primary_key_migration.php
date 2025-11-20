<?php
// Generate SQL to add missing primary keys to critical tables safely
// Usage: php database/tools/generate_primary_key_migration.php

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

// Target tables (high-impact) to normalize with primary keys
$targetTables = [
    'users',
    'properties',
    'bookings',
    'leads',
    'payments',
    'projects',
    'plots',
    'property_images',
    'property_types',
    'customers',
];

function fetchOneAssoc(mysqli $con, string $sql, array $params = []): ?array {
    $stmt = $con->prepare($sql);
    if (!$stmt) return null;
    if ($params) {
        // Bind dynamically
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) return null;
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function fetchAllAssoc(mysqli $con, string $sql, array $params = []): array {
    $stmt = $con->prepare($sql);
    if (!$stmt) return [];
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) return [];
    $res = $stmt->get_result();
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    $stmt->close();
    return $rows;
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $r = fetchOneAssoc($con, 'SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?', [$db, $table]);
    return (bool)$r;
}

function hasPrimaryKey(mysqli $con, string $db, string $table): bool {
    $r = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_TYPE='PRIMARY KEY'", [$db, $table]);
    return (bool)$r;
}

function getColumn(mysqli $con, string $db, string $table, string $column): ?array {
    return fetchOneAssoc($con, 'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?', [$db, $table, $column]);
}

function hasDuplicates(mysqli $con, string $table, string $column): bool {
    // Limit scan to 1 duplicate example
    $sql = "SELECT `$column`, COUNT(*) AS c FROM `$table` GROUP BY `$column` HAVING c > 1 LIMIT 1";
    $res = $con->query($sql);
    if (!$res) return false; // on error, assume no duplicates to avoid blocking
    $row = $res->fetch_assoc();
    return (bool)$row;
}

function isIntegerType(?array $col): bool {
    if (!$col) return false;
    $type = strtolower((string)$col['DATA_TYPE']);
    return in_array($type, ['tinyint','smallint','mediumint','int','bigint'], true);
}

function isAutoIncrement(?array $col): bool {
    if (!$col) return false;
    return stripos((string)$col['EXTRA'], 'auto_increment') !== false;
}

$sqlOut = [];
$sqlOut[] = "-- 002_add_missing_primary_keys.sql";
$sqlOut[] = "-- Generated: " . date('Y-m-d H:i:s');
$sqlOut[] = "-- Safely add primary keys to critical tables";
$sqlOut[] = "";

foreach ($targetTables as $t) {
    if (!tableExists($con, $dbName, $t)) {
        $sqlOut[] = "-- SKIP: `$t` does not exist";
        continue;
    }

    if (hasPrimaryKey($con, $dbName, $t)) {
        $sqlOut[] = "-- SKIP: `$t` already has PRIMARY KEY";
        continue;
    }

    $idCol = getColumn($con, $dbName, $t, 'id');
    $engineRow = fetchOneAssoc($con, 'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?', [$dbName, $t]);
    $engine = strtoupper((string)($engineRow['ENGINE'] ?? ''));

    // Ensure InnoDB for safety
    if ($engine && $engine !== 'INNODB') {
        $sqlOut[] = "ALTER TABLE `$t` ENGINE=InnoDB;";
    }

    if ($idCol && isIntegerType($idCol) && !hasDuplicates($con, $t, 'id')) {
        // Check if table already has any AUTO_INCREMENT column
        $autoInc = fetchOneAssoc($con, 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND EXTRA LIKE "%auto_increment%" LIMIT 1', [$dbName, $t]);
        // Promote `id` to PRIMARY KEY; only add AUTO_INCREMENT when no other auto column exists
        $sqlOut[] = "ALTER TABLE `$t` ADD PRIMARY KEY (`id`);";
        if (!$autoInc && !isAutoIncrement($idCol)) {
            $sqlOut[] = "ALTER TABLE `$t` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;";
        } else {
            // Ensure integer type consistency even if not auto
            $sqlOut[] = "ALTER TABLE `$t` MODIFY `id` BIGINT UNSIGNED NOT NULL;";
        }
        $sqlOut[] = "-- Applied PK on `$t`.`id`";
    } else {
        // Add synthetic primary key column to avoid disruption
        $sqlOut[] = "ALTER TABLE `$t` ADD COLUMN `pk_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;";
        $sqlOut[] = "-- Added synthetic PK `$t`.`pk_id`";
    }

    $sqlOut[] = ""; // spacer per table
}

$migrationsDir = dirname(__DIR__) . '/migrations';
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0777, true);
}

$targetFile = $migrationsDir . '/002_add_missing_primary_keys.sql';
file_put_contents($targetFile, implode("\n", $sqlOut));

echo "Primary key migration written to: $targetFile\n";

?>
