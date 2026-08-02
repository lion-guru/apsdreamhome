<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Database\Database;
$db = Database::getInstance();
try {
    $cols = $db->fetchAll('SHOW COLUMNS FROM user_activity_log');
    foreach ($cols as $c) echo $c['Field'] . "\n";
} catch (Exception $e) {
    echo "Table does not exist: " . $e->getMessage() . "\n";
}
