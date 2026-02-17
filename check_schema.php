<?php
require_once __DIR__ . '/app/Core/Database/Database.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

// Define DB constants if not defined
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'apsdreamhome');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

$db = Database::getInstance();

echo "Checking 'careers' table columns:\n";
try {
    $stmt = $db->query("DESCRIBE careers");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nChecking 'news' table columns:\n";
try {
    $stmt = $db->query("DESCRIBE news");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
