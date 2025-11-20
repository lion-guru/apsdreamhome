<?php
// generate_bookings_customer_fk_migration.php
// Purpose: Safely add FK for bookings.customer_id -> customers.id with orphan cleanup

date_default_timezone_set('UTC');

function getConfig(): array {
    $configPath = __DIR__ . '/../config/database.php';
    if (file_exists($configPath)) {
        $cfg = include $configPath;
        if (is_array($cfg)) return $cfg;
    }
    return [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'name' => 'apsdreamhome',
        'port' => 3306,
        'charset' => 'utf8mb4',
    ];
}

function getMysqliConnection(): ?mysqli {
    $cfg = getConfig();
    $con = @new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name'], $cfg['port']);
    if ($con->connect_errno) {
        fwrite(STDERR, "DB connect error: {$con->connect_error}\n");
        return null;
    }
    $con->set_charset($cfg['charset'] ?? 'utf8mb4');
    return $con;
}

function fetchOneAssoc(mysqli $con, string $sql, array $params = []): ?array {
    if ($params) {
        $stmt = $con->prepare($sql);
        if (!$stmt) return null;
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) return null;
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    } else {
        $res = $con->query($sql);
        return $res ? $res->fetch_assoc() : null;
    }
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $row = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=? LIMIT 1", [$db, $table]);
    return (bool)$row;
}

function columnInfo(mysqli $con, string $db, string $table, string $column): ?array {
    return fetchOneAssoc($con, "SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?", [$db, $table, $column]);
}

function referencedColumnType(mysqli $con, string $db, string $table, string $column): ?string {
    $row = fetchOneAssoc($con, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?", [$db, $table, $column]);
    return $row['COLUMN_TYPE'] ?? null;
}

function indexExists(mysqli $con, string $db, string $table, string $index): bool {
    $row = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1", [$db, $table, $index]);
    return (bool)$row;
}

function foreignKeyExists(mysqli $con, string $db, string $table, string $constraintName): bool {
    $row = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME=? AND CONSTRAINT_TYPE='FOREIGN KEY' LIMIT 1", [$db, $table, $constraintName]);
    return (bool)$row;
}

function countOrphans(mysqli $con, string $db, string $table, string $column, string $refTable, string $refColumn): int {
    $sql = "SELECT COUNT(*) AS cnt FROM `{$table}` t LEFT JOIN `{$refTable}` r ON t.`{$column}` = r.`{$refColumn}` WHERE t.`{$column}` IS NOT NULL AND r.`{$refColumn}` IS NULL";
    $row = fetchOneAssoc($con, $sql);
    return (int)($row['cnt'] ?? 0);
}

function isNullable(?array $col): bool { return ($col && strtoupper((string)$col['IS_NULLABLE']) === 'YES'); }

function generate(): array {
    $out = [];
    $db = getConfig()['name'];
    $con = getMysqliConnection();
    if (!$con) {
        $out[] = "-- ERROR: Unable to connect to database";
        return $out;
    }

    $out[] = "-- " . basename(__FILE__);
    $out[] = "-- Generated: " . date('Y-m-d H:i:s');
    $out[] = "-- Add FK for bookings.customer_id -> customers.id with SET NULL and cleanup";

    // Preconditions
    if (!tableExists($con, $db, 'bookings')) { $out[] = "-- SKIP: bookings table missing"; return $out; }
    if (!tableExists($con, $db, 'customers')) { $out[] = "-- SKIP: customers table missing"; return $out; }

    $col = columnInfo($con, $db, 'bookings', 'customer_id');
    if (!$col) { $out[] = "-- SKIP: bookings.customer_id column missing"; return $out; }

    $refType = referencedColumnType($con, $db, 'customers', 'id');
    if (!$refType) { $out[] = "-- SKIP: customers.id column missing"; return $out; }

    // Align type and nullability if needed
    $currType = strtolower($col['COLUMN_TYPE']);
    $refTypeLower = strtolower($refType);
    if ($currType !== $refTypeLower || !isNullable($col)) {
        $out[] = "ALTER TABLE `bookings` MODIFY `customer_id` {$refType} NULL;";
    }

    // Cleanup orphans for SET NULL rule
    $orphans = countOrphans($con, $db, 'bookings', 'customer_id', 'customers', 'id');
    if ($orphans > 0) {
        $out[] = "UPDATE `bookings` b LEFT JOIN `customers` c ON b.`customer_id` = c.`id` SET b.`customer_id` = NULL WHERE c.`id` IS NULL;";
    }

    // Index
    if (!indexExists($con, $db, 'bookings', 'ix_bookings_customer_id')) {
        $out[] = "ALTER TABLE `bookings` ADD INDEX `ix_bookings_customer_id` (`customer_id`);";
    } else {
        $out[] = "-- SKIP IDX: ix_bookings_customer_id exists";
    }

    // FK
    if (!foreignKeyExists($con, $db, 'bookings', 'fk_bookings_customer_id')) {
        $out[] = "ALTER TABLE `bookings` ADD CONSTRAINT `fk_bookings_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
    } else {
        $out[] = "-- SKIP FK: fk_bookings_customer_id exists";
    }

    return $out;
}

function writeMigration(array $sql): ?string {
    $dir = __DIR__ . '/../migrations';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $path = $dir . '/008_add_bookings_customer_fk.sql';
    $content = implode("\n", $sql) . "\n";
    if (file_put_contents($path, $content) === false) return null;
    return $path;
}

$sqlOut = generate();
$file = writeMigration($sqlOut);
if ($file) {
    echo "Bookings FK migration written to: {$file}\n";
} else {
    fwrite(STDERR, "Failed to write migration file\n");
    exit(1);
}

?>
