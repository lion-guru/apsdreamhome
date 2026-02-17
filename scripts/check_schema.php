<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query('DESCRIBE properties');
print_r($stmt->fetchAll(\PDO::FETCH_COLUMN));
