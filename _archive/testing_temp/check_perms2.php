<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getPdo();
$stmt = $db->query("SELECT role, COUNT(*) as c FROM admin_role_menu_permissions GROUP BY role");
$results = $stmt->fetchAll();
if (empty($results)) {
    echo "TABLE IS EMPTY!\n";
} else {
    foreach ($results as $row) {
        echo "Role: {$row['role']}, Count: {$row['c']}\n";
    }
}
