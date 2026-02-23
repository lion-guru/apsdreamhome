<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/App.php';
require_once __DIR__ . '/app/config/env.php';

use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($columns);
