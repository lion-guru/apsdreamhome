<?php
// scripts/db_fix_add_pk.php
// Connects to DB and ensures PRIMARY KEY (AUTO_INCREMENT id) exists
// on selected tables. Safe to run multiple times.

header('Content-Type: text/plain; charset=utf-8');

$projectRoot = dirname(__DIR__);
$configPhp = $projectRoot . DIRECTORY_SEPARATOR . 'config.php';

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'apsdreamhome';

// Soft-load .env if present
$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $k = trim($k);
        $v = trim(trim($v), "\"'\"");
        if ($k !== '') putenv("$k=$v");
    }
    $DB_HOST = getenv('DB_HOST') ?: $DB_HOST;
    $DB_USER = getenv('DB_USER') ?: $DB_USER;
    $DB_PASS = getenv('DB_PASS') ?: $DB_PASS;
    $DB_NAME = getenv('DB_NAME') ?: $DB_NAME;
}

// Prefer existing config.php connection if available
if (file_exists($configPhp)) {
    require_once $configPhp; // may define $con (mysqli)
}

if (!isset($con) || !($con instanceof mysqli)) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $con = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($con->connect_error) {
        echo "Connection error: {$con->connect_error}\n";
        exit(1);
    }
    $con->set_charset('utf8mb4');
}

function table_exists(mysqli $con, string $db, string $table): bool {
    $sql = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?";
    $st = $con->prepare($sql);
    $st->bind_param('ss', $db, $table);
    $st->execute();
    $res = $st->get_result();
    $exists = $res && $res->num_rows > 0;
    $st->close();
    return $exists;
}

function has_primary_key(mysqli $con, string $db, string $table): bool {
    $sql = "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_TYPE='PRIMARY KEY'";
    $st = $con->prepare($sql);
    $st->bind_param('ss', $db, $table);
    $st->execute();
    $res = $st->get_result();
    $has = $res && $res->num_rows > 0;
    $st->close();
    return $has;
}

function has_column(mysqli $con, string $db, string $table, string $column): bool {
    $sql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?";
    $st = $con->prepare($sql);
    $st->bind_param('sss', $db, $table, $column);
    $st->execute();
    $res = $st->get_result();
    $has = $res && $res->num_rows > 0;
    $st->close();
    return $has;
}

function ensure_pk(mysqli $con, string $db, string $table): void {
    echo "\n==> Checking {$table}\n";
    if (!table_exists($con, $db, $table)) {
        echo "Table not found, skipping.\n";
        return;
    }

    $hasPk = has_primary_key($con, $db, $table);
    $hasId = has_column($con, $db, $table, 'id');

    if ($hasPk && $hasId) {
        echo "Already has PRIMARY KEY on column 'id' (or some PK). Skipping.\n";
        return;
    }

    if (!$hasId) {
        // Add id column as first column
        $sql = "ALTER TABLE `{$table}` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
        if ($con->query($sql) === true) {
            echo "Added 'id' AUTO_INCREMENT PRIMARY KEY.\n";
        } else {
            echo "Failed adding id PK: " . $con->error . "\n";
        }
        return;
    }

    // id exists but no PK or not AI, try to modify
    // Drop existing PK if any (rare case where PK is on another column)
    $sqlDrop = "ALTER TABLE `{$table}` DROP PRIMARY KEY";
    // Try dropping PK safely; ignore errors if none
    $con->query($sqlDrop);

    $sqlMod = "ALTER TABLE `{$table}` MODIFY `id` INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)";
    if ($con->query($sqlMod) === true) {
        echo "Set 'id' as AUTO_INCREMENT PRIMARY KEY.\n";
    } else {
        echo "Failed setting 'id' as PK: " . $con->error . "\n";
    }
}

$targets = [
    'mlm_commission_levels_backup',
    'mlm_commission_plans',
];

foreach ($targets as $t) {
    ensure_pk($con, $DB_NAME, $t);
}

echo "\nDone. Now refresh /scripts/db_live_scan.php to verify.\n";
