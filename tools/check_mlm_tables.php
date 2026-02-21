<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Listing MLM tables...\n";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'mlm_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $t) {
        echo "- $t\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
