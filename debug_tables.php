<?php
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);