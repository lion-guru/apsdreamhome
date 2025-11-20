<?php
// Apply robust FK adjustment: transactions.customer_id -> customers.id with ON DELETE SET NULL
// Usage: php database/tools/apply_transactions_fk_set_null.php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

function getConfig(): array {
    $cfgPath = __DIR__ . '/../../includes/config/config.php';
    if (file_exists($cfgPath)) {
        $config = include $cfgPath;
        if (is_array($config) && isset($config['db'])) {
            return [
                'host' => $config['db']['host'] ?? 'localhost',
                'user' => $config['db']['user'] ?? 'root',
                'pass' => $config['db']['password'] ?? '',
                'name' => $config['db']['name'] ?? 'apsdreamhome'
            ];
        }
    }
    // Fallback simple config
    return [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'apsdreamhome'
    ];
}

function getMysqli(): mysqli {
    $cfg = getConfig();
    $mysqli = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
    if ($mysqli->connect_error) {
        throw new RuntimeException('DB connect error: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function fetchOne(mysqli $con, string $sql, array $params = []): ?array {
    $stmt = $con->prepare($sql);
    if (!$stmt) { throw new RuntimeException('Prepare failed: ' . $con->error); }
    if (!empty($params)) {
        // bind dynamically
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) { throw new RuntimeException('Execute failed: ' . $stmt->error); }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function columnType(mysqli $con, string $db, string $table, string $column): ?string {
    $row = fetchOne($con, "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?", [$db, $table, $column]);
    return $row ? $row['COLUMN_TYPE'] : null;
}

function existingFkName(mysqli $con, string $db, string $table, string $column, string $refTable): ?string {
    $row = fetchOne($con, "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? AND REFERENCED_TABLE_NAME=? LIMIT 1", [$db, $table, $column, $refTable]);
    return $row ? $row['CONSTRAINT_NAME'] : null;
}

function indexExistsOnColumn(mysqli $con, string $db, string $table, string $column): bool {
    $row = fetchOne($con, "SELECT 1 AS ok FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1", [$db, $table, $column]);
    return (bool)$row;
}

function run(mysqli $con, string $sql): void {
    if (!$con->query($sql)) {
        throw new RuntimeException('Query failed: ' . $con->error . "\nSQL: " . $sql);
    }
}

function main(): void {
    $cfg = getConfig();
    $db = $cfg['name'];
    $con = getMysqli();

    echo "Applying transactions.customer_id SET NULL FK adjustment...\n";

    // Get referenced type from customers.id to ensure exact match
    $refType = columnType($con, $db, 'customers', 'id');
    if (!$refType) {
        throw new RuntimeException('Could not determine type of customers.id');
    }
    echo "- customers.id type: {$refType}\n";

    // Find existing FK name if present
    $fkName = existingFkName($con, $db, 'transactions', 'customer_id', 'customers');
    if ($fkName) {
        echo "- Dropping existing FK: {$fkName}\n";
        run($con, "SET FOREIGN_KEY_CHECKS=0");
        run($con, "ALTER TABLE `transactions` DROP FOREIGN KEY `{$con->real_escape_string($fkName)}`");
        run($con, "SET FOREIGN_KEY_CHECKS=1");
    } else {
        echo "- No existing FK found; proceeding to create\n";
    }

    // Align type and nullability
    echo "- Modifying transactions.customer_id to {$refType} NULL\n";
    run($con, "ALTER TABLE `transactions` MODIFY COLUMN `customer_id` {$refType} NULL");

    // Ensure index exists on child column (MySQL requires/indexes it; be explicit)
    if (!indexExistsOnColumn($con, $db, 'transactions', 'customer_id')) {
        echo "- Adding index on transactions.customer_id\n";
        run($con, "ALTER TABLE `transactions` ADD INDEX `idx_transactions_customer_id` (`customer_id`)");
    } else {
        echo "- Index on transactions.customer_id already exists\n";
    }

    // Add FK with SET NULL
    echo "- Adding FK fk_transactions_customer_id with ON DELETE SET NULL\n";
    run($con, "SET FOREIGN_KEY_CHECKS=0");
    run($con, "ALTER TABLE `transactions` ADD CONSTRAINT `fk_transactions_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
    run($con, "SET FOREIGN_KEY_CHECKS=1");

    echo "✅ Completed adjustment for transactions.customer_id\n";
}

main();
?>

