<?php
// generate_missing_fk_columns_migration.php
// Safely add missing foreign key columns for plots and transactions based on live DB types.
// This script inspects referenced PK types to add columns with aligned types and intended nullability.

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection \$con is not available (mysqli).\n");
    exit(1);
}

$dbName = null;
$resDb = $con->query('SELECT DATABASE() AS db');
if ($resDb && ($rowDb = $resDb->fetch_assoc())) {
    $dbName = $rowDb['db'] ?? null;
}
if (!$dbName) {
    fwrite(STDERR, "[ERROR] Unable to determine current database name.\n");
    exit(1);
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $sql = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function columnExists(mysqli $con, string $db, string $table, string $column): bool {
    $sql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function columnType(mysqli $con, string $db, string $table, string $column): ?string {
    $sql = "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['COLUMN_TYPE'] ?? null;
}

function addColumnSQL(string $table, string $column, string $type, bool $nullable, ?string $after = null): string {
    $nullSql = $nullable ? 'NULL' : 'NOT NULL';
    $afterSql = $after ? " AFTER `{$after}`" : '';
    return sprintf("ALTER TABLE `%s` ADD COLUMN `%s` %s %s%s;", $table, $column, $type, $nullSql, $afterSql);
}

// Desired columns with references and intended nullability
$targets = [
    ['table' => 'plots', 'column' => 'project_id',    'ref_table' => 'projects',   'ref_column' => 'id', 'nullable' => true,  'after' => null],
    ['table' => 'plots', 'column' => 'customer_id',   'ref_table' => 'users',      'ref_column' => 'id', 'nullable' => true,  'after' => null],
    ['table' => 'plots', 'column' => 'associate_id',  'ref_table' => 'associates', 'ref_column' => 'id', 'nullable' => true,  'after' => null],
    ['table' => 'transactions', 'column' => 'customer_id',  'ref_table' => 'customers',  'ref_column' => 'id', 'nullable' => true, 'after' => null],
    ['table' => 'transactions', 'column' => 'property_id',  'ref_table' => 'properties', 'ref_column' => 'id', 'nullable' => true, 'after' => null],
];

$sqlOut = [];
$sqlOut[] = '-- 011_add_missing_fk_columns.sql';
$sqlOut[] = sprintf('-- Generated: %s', date('Y-m-d H:i:s'));
$sqlOut[] = '-- Add missing FK columns with types aligned to referenced PKs';
$sqlOut[] = '';

foreach ($targets as $t) {
    $table = $t['table'];
    $column = $t['column'];
    $refTable = $t['ref_table'];
    $refColumn = $t['ref_column'];
    $nullable = $t['nullable'];
    $after = $t['after'];

    if (!tableExists($con, $dbName, $table)) {
        $sqlOut[] = sprintf('-- SKIP: `%s` table missing', $table);
        $sqlOut[] = '';
        continue;
    }
    if (columnExists($con, $dbName, $table, $column)) {
        $sqlOut[] = sprintf('-- SKIP: `%s`.`%s` already exists', $table, $column);
        $sqlOut[] = '';
        continue;
    }
    if (!tableExists($con, $dbName, $refTable)) {
        $sqlOut[] = sprintf('-- SKIP: `%s` table missing for reference', $refTable);
        $sqlOut[] = '';
        continue;
    }
    $type = columnType($con, $dbName, $refTable, $refColumn);
    if (!$type) {
        $sqlOut[] = sprintf('-- SKIP: `%s`.`%s` type not found', $refTable, $refColumn);
        $sqlOut[] = '';
        continue;
    }
    $sqlOut[] = addColumnSQL($table, $column, $type, $nullable, $after);
    $sqlOut[] = '';
}

$migrationDir = __DIR__ . '/../migrations';
if (!is_dir($migrationDir)) {
    mkdir($migrationDir, 0777, true);
}
$fileName = $migrationDir . '/011_add_missing_fk_columns.sql';
$content = implode("\n", $sqlOut) . "\n";
if (file_put_contents($fileName, $content) === false) {
    fwrite(STDERR, "[ERROR] Failed to write migration file: {$fileName}\n");
    exit(1);
}

echo "Written migration: {$fileName}\n";
?>

