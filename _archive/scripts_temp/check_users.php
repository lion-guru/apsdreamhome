<?php
require __DIR__ . '/../vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

$roles = ['super_admin', 'admin', 'manager', 'cto', 'cfo', 'director', 'agent', 'associate', 'telecaller', 'employee', 'customer'];

foreach ($roles as $role) {
    $user = $db->fetchOne('SELECT * FROM users WHERE role = ? AND status = "active" ORDER BY id DESC LIMIT 1', [$role]);
    if ($user) {
        echo "$role: {$user['id']} - {$user['email']} - {$user['name']}\n";
    } else {
        echo "$role: NOT FOUND\n";
    }
}?>