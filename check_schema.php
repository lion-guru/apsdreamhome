<?php
require_once 'app/core/Database.php';
require_once 'app/core/App.php';
require_once 'config/config.php';

use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query("DESCRIBE associates");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($columns);

$stmt = $db->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($columns);
