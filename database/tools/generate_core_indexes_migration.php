<?php
declare(strict_types=1);

// Core Indexes Generator
// Scans live schema and generates ADD INDEX statements for common columns.
// - Targets *_id columns and frequently queried fields (status, timestamps, email, phone, etc.)
// - Skips columns already indexed (by any index name)
// - Writes to database/migrations/001_add_core_indexes.sql

error_reporting(E_ALL);
ini_set('display_errors', '1');

function projectRoot(): string {
    return dirname(__DIR__, 2);
}

function getDbConnection(): mysqli {
    $root = projectRoot();
    $configPath = $root . '/includes/config/config.php';
    if (!file_exists($configPath)) {
        fwrite(STDERR, "Config not found: {$configPath}\n");
        exit(1);
    }
    require $configPath; // expects $con (mysqli) and DB constants
    if (!isset($con) || !($con instanceof mysqli)) {
        fwrite(STDERR, "Database connection \$con not available after requiring config.\n");
        exit(1);
    }
    return $con;
}

function getDatabaseName(mysqli $con): string {
    $res = $con->query('SELECT DATABASE() as db');
    if (!$res) {
        throw new RuntimeException('Failed to query current database: ' . $con->error);
    }
    $row = $res->fetch_assoc();
    return $row['db'] ?? '';
}

function getTables(mysqli $con, string $db): array {
    $stmt = $con->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE"');
    $stmt->bind_param('s', $db);
    $stmt->execute();
    $res = $stmt->get_result();
    $tables = [];
    while ($row = $res->fetch_assoc()) {
        $tables[] = $row['TABLE_NAME'];
    }
    $stmt->close();
    sort($tables);
    return $tables;
}

function getColumns(mysqli $con, string $db, string $table): array {
    $stmt = $con->prepare('SELECT COLUMN_NAME, COLUMN_KEY, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->bind_param('ss', $db, $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row;
    }
    $stmt->close();
    return $cols;
}

function columnHasAnyIndex(mysqli $con, string $db, string $table, string $column): bool {
    $stmt = $con->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = (bool)$res->fetch_row();
    $stmt->close();
    return $exists;
}

function makeIndexName(string $table, string $column): string {
    $name = 'ix_' . $table . '_' . $column;
    // MySQL index name length limit is 64 bytes
    if (strlen($name) > 64) {
        $name = substr($name, 0, 64);
    }
    return $name;
}

function shouldIndexColumn(array $col): bool {
    $name = $col['COLUMN_NAME'];
    $key = $col['COLUMN_KEY'] ?? '';
    $dataType = strtolower($col['DATA_TYPE'] ?? '');

    // Skip primary key
    if ($key === 'PRI') {
        return false;
    }
    // Skip extremely large text/blob types
    if (in_array($dataType, ['blob','longblob','mediumblob','tinyblob','text','longtext','mediumtext','tinytext'], true)) {
        return false;
    }
    // Common foreign-key-like columns
    if (str_ends_with($name, '_id')) {
        return true;
    }
    // Frequently queried simple fields
    $common = [
        'status', 'created_at', 'updated_at', 'email', 'phone', 'mobile',
        'booking_date', 'payment_date', 'transaction_date', 'visit_date'
    ];
    if (in_array($name, $common, true)) {
        return true;
    }
    // Heuristic: dates/times
    if (in_array($dataType, ['date','datetime','timestamp'], true) && preg_match('/(_at|_date)$/', $name)) {
        return true;
    }
    return false;
}

function generateCoreIndexMigration(mysqli $con, string $db): string {
    $tables = getTables($con, $db);
    $lines = [];
    $lines[] = '-- Auto-generated core index migration';
    $lines[] = '-- This file was generated based on current live schema.';
    $lines[] = '-- It adds indexes for common *_id and frequently queried fields, skipping existing ones.';
    $lines[] = '';

    foreach ($tables as $table) {
        $cols = getColumns($con, $db, $table);
        foreach ($cols as $col) {
            $colName = $col['COLUMN_NAME'];
            if (!shouldIndexColumn($col)) {
                continue;
            }
            if (columnHasAnyIndex($con, $db, $table, $colName)) {
                $lines[] = sprintf('-- SKIP: `%s`.`%s` already indexed', $table, $colName);
                continue;
            }
            $idx = makeIndexName($table, $colName);
            $lines[] = sprintf('ALTER TABLE `%s` ADD INDEX `%s` (`%s`);', $table, $idx, $colName);
        }
        $lines[] = '';
    }

    // If we generated no ALTERs, keep a note so the migration is harmless
    $hasAlter = false;
    foreach ($lines as $ln) {
        if (str_starts_with($ln, 'ALTER TABLE')) {
            $hasAlter = true;
            break;
        }
    }
    if (!$hasAlter) {
        $lines[] = '-- No new indexes required based on current schema. Safe no-op migration.';
    }

    return implode("\n", $lines) . "\n";
}

function writeMigration(string $content, string $outPath): void {
    $path = $outPath;
    if (!is_dir(dirname($path))) {
        throw new RuntimeException('Migrations directory missing: ' . dirname($path));
    }
    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException('Failed to write migration: ' . $path);
    }
    echo "Wrote core indexes migration: {$path}\n";
}

function parseOutArg(array $argv): string {
    $default = projectRoot() . '/database/migrations/001_add_core_indexes.sql';
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--out=')) {
            $val = substr($arg, 6);
            // If a relative path is provided, resolve against project root
            if (!preg_match('/^([a-zA-Z]:\\\\|\\\\|\/)\S*/', $val)) { // not absolute
                $val = projectRoot() . '/' . ltrim($val, '/');
            }
            return $val;
        }
    }
    return $default;
}

// Main
try {
    $con = getDbConnection();
    $db = getDatabaseName($con);
    if ($db === '') {
        throw new RuntimeException('Current database name could not be determined.');
    }
    $content = generateCoreIndexMigration($con, $db);
    $outPath = parseOutArg($_SERVER['argv'] ?? []);
    writeMigration($content, $outPath);
    echo "Done.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
