<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

define('APP_ROOT', dirname(__DIR__));
$appRoot = APP_ROOT;
if (file_exists($appRoot . '/.env')) {
    $dotenv = Dotenv::createImmutable($appRoot);
    $dotenv->safeLoad();
}
$config = require $appRoot . '/config/database.php';
$db = $config['database'];

try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['database']}", $db['username'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- Bookings Table ---\n";
    $stmt = $pdo->query("DESCRIBE bookings");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "{$row['Field']} ({$row['Type']})\n";
    }

    echo "\n--- Customers Table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE customers");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo "{$row['Field']} ({$row['Type']})\n";
        }
        echo "\n--- Properties Table ---\n";
        $stmt = $pdo->query("DESCRIBE properties");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo "{$row['Field']} ({$row['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Customers table does not exist.\n";
    }

    echo "\n--- Users Table ---\n";
    $stmt = $pdo->query("DESCRIBE users");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "{$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
