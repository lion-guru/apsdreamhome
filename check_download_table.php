<?php
require_once __DIR__ . '/app/Core/autoload.php';
use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'downloads'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "Table 'downloads' exists.\n";
        $stmt = $db->query("SHOW COLUMNS FROM downloads");
        print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
    } else {
        echo "Table 'downloads' does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
