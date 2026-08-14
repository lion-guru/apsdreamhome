<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance()->getPdo();
$stmt = $db->query("SELECT id, name, role FROM users LIMIT 5");
while ($row = $stmt->fetch()) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Role: {$row['role']}\n";
}?>