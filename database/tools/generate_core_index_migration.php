<?php
// Generates a core index migration SQL file based on live DB schema.
// Usage: php database/tools/generate_core_index_migration.php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';
require_once $projectRoot . '/includes/config/constants.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection \$con is not available (mysqli).\n");
    exit(1);
}

$dbName = DB_NAME ?? null;
if (!$dbName) {
    fwrite(STDERR, "[ERROR] DB_NAME not defined in constants.php.\n");
    exit(1);
}

function tableHasColumn(mysqli $con, string $db, string $table, string $column): bool {
    $stmt = $con->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res && $res->num_rows > 0;
    $stmt->close();
    return $exists;
}

function columnIndexed(mysqli $con, string $db, string $table, string $column): bool {
    $stmt = $con->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res && $res->num_rows > 0;
    $stmt->close();
    return $exists;
}

$targets = [
    'bookings' => ['property_id', 'user_id', 'status', 'created_at', 'visit_date'],
    'properties' => ['status', 'is_active', 'property_type_id', 'city', 'location_id', 'agent_id'],
    'users' => ['email', 'type', 'status'],
];

$sqlLines = [];
$sqlLines[] = '-- Auto-generated core index migration';
$sqlLines[] = 'SET NAMES utf8mb4;';
$sqlLines[] = 'SET @@session.sql_mode = REPLACE(@@session.sql_mode, "ONLY_FULL_GROUP_BY", "");';

foreach ($targets as $table => $columns) {
    // Verify table exists
    $stmt = $con->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->bind_param('ss', $dbName, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res && $res->num_rows > 0;
    $stmt->close();
    if (!$exists) {
        $sqlLines[] = "-- Skipping {$table}: table not found";
        continue;
    }

    $sqlLines[] = "-- Table: {$table}";
    foreach ($columns as $col) {
        if (!tableHasColumn($con, $dbName, $table, $col)) {
            $sqlLines[] = "-- Skipping index on {$table}.{$col}: column not found";
            continue;
        }
        if (columnIndexed($con, $dbName, $table, $col)) {
            $sqlLines[] = "-- Index already exists on {$table}.{$col}";
            continue;
        }
        $idxName = sprintf('idx_%s_%s', $table, $col);
        // For users.email, create a normal index first to avoid duplicate issues
        $sqlLines[] = sprintf('ALTER TABLE `%s` ADD INDEX `%s` (`%s`);', $table, $idxName, $col);
    }
}

$outDir = dirname(__DIR__) . '/migrations';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$outFile = $outDir . '/001_add_core_indexes.sql';
file_put_contents($outFile, implode("\n", $sqlLines) . "\n");

echo "Generated migration: {$outFile}\n";

