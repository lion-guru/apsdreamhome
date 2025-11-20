<?php
require_once 'app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    $result = $db->query('DESCRIBE users');
    echo '=== USERS TABLE ===' . PHP_EOL;
    foreach ($result as $row) {
        echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}