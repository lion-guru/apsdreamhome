<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
echo 'Database instance created: ' . get_class($db) . "\n";
$conn = $db->getConnection();
echo 'Connection: ' . get_class($conn) . "\n";