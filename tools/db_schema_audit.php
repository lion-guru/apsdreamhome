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

// Get all tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Found " . count($tables) . " tables.\n";

$issues = [];

foreach ($tables as $table) {
    // Check engine
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE '$table'");
    $status = $stmt->fetch();

    // Skip views
    if ($status['Comment'] === 'VIEW' || $status['Engine'] === null) {
        // Optional: Check if view is valid by selecting 1 row
        try {
            $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
        } catch (PDOException $e) {
            $issues[] = "View '$table' is broken: " . $e->getMessage();
        }
        continue;
    }

    if ($status['Engine'] !== 'InnoDB') {
        $issues[] = "Table '$table' is using {$status['Engine']} engine (should be InnoDB).";
    }

    // Check columns for foreign keys (ending in _id)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $stmt->fetchAll();
    } catch (PDOException $e) {
        $issues[] = "Could not describe table '$table': " . $e->getMessage();
        continue;
    }

    // Get indexes
    try {
        $stmt = $pdo->query("SHOW INDEX FROM `$table`");
        $indexes = $stmt->fetchAll();
    } catch (PDOException $e) {
        $issues[] = "Could not get indexes for '$table': " . $e->getMessage();
        continue;
    }

    $indexedColumns = [];
    foreach ($indexes as $index) {
        $indexedColumns[] = $index['Column_name'];
    }

    foreach ($columns as $col) {
        $colName = $col['Field'];
        if (str_ends_with($colName, '_id') && $col['Key'] !== 'PRI') {
            // Check if indexed
            if (!in_array($colName, $indexedColumns)) {
                $issues[] = "Table '$table' column '$colName' (likely Foreign Key) is NOT indexed.";
            }
        }
    }
}

if (empty($issues)) {
    echo "No schema issues found.\n";
} else {
    echo "Found " . count($issues) . " issues:\n";
    foreach ($issues as $issue) {
        echo "- $issue\n";
    }
}
