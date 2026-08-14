<?php
require_once __DIR__ . '/../app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$user = $db->fetchOne("SELECT id, email, password, name, status FROM users WHERE email = ? LIMIT 1", ['test@aps.com']);
if ($user) {
    echo "User found: id={$user['id']}, email={$user['email']}, name={$user['name']}, status={$user['status']}\n";
    echo "Password hash: " . substr($user['password'], 0, 20) . "...\n";
    echo "Password verify: " . (password_verify('test123', $user['password']) ? 'PASS' : 'FAIL') . "\n";
} else {
    echo "User NOT found with test@aps.com\n";
    // Check all users
    $users = $db->fetchAll("SELECT id, email, status FROM users");
    echo "All users (" . count($users) . "):\n";
    foreach ($users as $u) {
        echo "  - {$u['id']}: {$u['email']} ({$u['status']})\n";
    }
}?>