<?php
require_once 'config.php';
require_once 'includes/db_connection.php';

$pdo = getMysqliConnection();
if ($pdo) {
    echo 'Connected successfully!' . PHP_EOL;
    
    // Check if projects table exists
    $result = $pdo->query("SHOW TABLES LIKE 'projects'");
    if ($result && $result->rowCount() > 0) {
        echo 'Projects table exists!' . PHP_EOL;
        
        // Get table structure
        $columns = $pdo->query("DESCRIBE projects");
        echo 'Projects table columns:' . PHP_EOL;
        while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo '- ' . $col['Field'] . ' (' . $col['Type'] . ')' . PHP_EOL;
        }
    } else {
        echo 'Projects table does not exist!' . PHP_EOL;
    }
} else {
    echo 'Connection failed!' . PHP_EOL;
}