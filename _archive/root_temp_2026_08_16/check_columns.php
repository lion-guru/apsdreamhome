<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();
$cols = $db->query('SHOW COLUMNS FROM legal_documents')->fetchAll(\PDO::FETCH_COLUMN, 0);
print_r($cols);