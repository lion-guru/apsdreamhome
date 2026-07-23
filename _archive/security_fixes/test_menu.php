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
    
    $stmt = $pdo->query("SELECT id, label, url, parent_id, icon, sort_order FROM admin_menu_items WHERE is_active = 1 ORDER BY parent_id, sort_order");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total rows: " . count($results) . "\n";
    foreach ($results as $r) {
        echo "ID: " . $r['id'] . " | " . $r['label'] . " | URL: " . $r['url'] . " | Parent: " . $r['parent_id'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}