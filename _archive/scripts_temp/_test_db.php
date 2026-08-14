<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "start\n";
require 'config/bootstrap.php';
echo "loaded bootstrap\n";
try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();
    echo "db connected\n";
    $r = $db->query("SELECT COUNT(*) as c FROM user_activity_log");
    $row = $r->fetch();
    echo "count: " . $row['c'] . "\n";
} catch (\Exception $e) {
    echo "error: " . $e->getMessage() . "\n";
}?>