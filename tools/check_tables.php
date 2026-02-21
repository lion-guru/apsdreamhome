<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting table check...\n";

// Define APP_ROOT manually if needed
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

echo "APP_ROOT: " . APP_ROOT . "\n";

$bootstrap = APP_ROOT . '/config/bootstrap.php';
if (file_exists($bootstrap)) {
    echo "Loading bootstrap from $bootstrap\n";
    require_once $bootstrap;
} else {
    die("Bootstrap file not found at $bootstrap\n");
}

echo "Bootstrap loaded.\n";

use App\Core\Database;

try {
    echo "Getting Database instance...\n";
    $db = Database::getInstance();
    echo "Getting connection...\n";
    $conn = $db->getConnection();

    echo "Querying tables...\n";
    $tables = [];
    $stmt = $conn->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    echo "Found " . count($tables) . " tables.\n";
    echo "----------------------------------------\n";

    echo "\nTable List:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }

    $target_tables = ['projects', 'user', 'users', 'agent', 'agents', 'associates', 'mlm_profiles', 'banking_details', 'kyc_details'];

    foreach ($target_tables as $table) {
        if (in_array($table, $tables)) {
            echo "\nTable: $table\n";
            $stmt = $conn->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Columns: " . implode(', ', $columns) . "\n";

            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            echo "Rows: " . $stmt->fetchColumn() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
