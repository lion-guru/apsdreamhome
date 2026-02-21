<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

define('APP_ROOT', dirname(__DIR__));
$appRoot = APP_ROOT;

// Load .env
if (file_exists($appRoot . '/.env')) {
    $dotenv = Dotenv::createImmutable($appRoot);
    $dotenv->safeLoad();
}

$config = require $appRoot . '/config/database.php';
$dbConfig = $config['database'];

$host = $dbConfig['host'];
$dbname = $dbConfig['database'];
$user = $dbConfig['username'];
$pass = $dbConfig['password'];

echo "Connecting to database '{$dbname}' at '{$host}'...\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "Connected successfully.\n";

// 1. Identify missing indexes
echo "Scanning for missing indexes...\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$missingIndexes = [];

foreach ($tables as $table) {
    // Skip views
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE '$table'");
    $status = $stmt->fetch();
    if ($status['Comment'] === 'VIEW' || $status['Engine'] === null) {
        continue;
    }

    // Get columns
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $stmt->fetchAll();
    } catch (PDOException $e) {
        continue;
    }

    // Get indexes
    try {
        $stmt = $pdo->query("SHOW INDEX FROM `$table`");
        $indexes = $stmt->fetchAll();
    } catch (PDOException $e) {
        continue;
    }

    $indexedColumns = [];
    foreach ($indexes as $index) {
        $indexedColumns[] = $index['Column_name'];
    }

    foreach ($columns as $col) {
        $colName = $col['Field'];
        if (str_ends_with($colName, '_id') && $col['Key'] !== 'PRI') {
            if (!in_array($colName, $indexedColumns)) {
                $missingIndexes[] = [
                    'table' => $table,
                    'column' => $colName
                ];
            }
        }
    }
}

echo "Found " . count($missingIndexes) . " missing indexes.\n";

// 2. Add missing indexes
if (!empty($missingIndexes)) {
    echo "Adding indexes...\n";
    foreach ($missingIndexes as $idx) {
        $table = $idx['table'];
        $column = $idx['column'];
        $indexName = "idx_{$table}_{$column}";

        // Shorten index name if too long (max 64 chars usually)
        if (strlen($indexName) > 64) {
            $indexName = substr("idx_" . md5($table . $column), 0, 64);
        }

        $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`$column`)";
        echo "Executing: $sql\n";
        try {
            $pdo->exec($sql);
            echo "  -> Success\n";
        } catch (PDOException $e) {
            echo "  -> Failed: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "No missing indexes to add.\n";
}

echo "Done.\n";
