<?php
define('APP_ROOT', dirname(__DIR__));
$config = require APP_ROOT . '/config/database.php';
$db_config = $config['database'];

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $username = $db_config['username'];
    $password = $db_config['password'];

    echo "Connecting to DSN: $dsn with user: $username\n";

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully.\n";

    $stmt = $pdo->query("DESCRIBE properties");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Table 'properties' columns:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }

    // Check for featured properties
    echo "\nChecking for featured properties...\n";
    $stmt = $pdo->query("SELECT count(*) as count FROM properties WHERE featured = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Featured properties count: " . $row['count'] . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
