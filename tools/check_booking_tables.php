<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Listing tables with 'booking' in name...\n";
try {
    $stmt = $conn->query("SHOW TABLES LIKE '%booking%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $t) {
        echo "- $t\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
