<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "Connected OK\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'legal%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Legal tables:\n";
    foreach ($tables as $t) {
        echo "  $t\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}