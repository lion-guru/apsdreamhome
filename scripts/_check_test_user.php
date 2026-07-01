<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, name, email, role, password FROM users WHERE email = 'testuser@example.com'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    echo "User found: id={$user['id']}, name={$user['name']}, role={$user['role']}\n";
    echo "Password hash: {$user['password']}\n";
    echo "Verify Test@123: " . (password_verify('Test@123', $user['password']) ? 'YES' : 'NO') . "\n";
} else {
    echo "User NOT found\n";
    // Find first customer
    $stmt2 = $db->query("SELECT id, name, email, role, password FROM users WHERE role = 'customer' LIMIT 1");
    $u2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($u2) {
        echo "First customer: {$u2['email']} / {$u2['name']}\n";
        echo "Hash: {$u2['password']}\n";
    } else {
        echo "No customers exist\n";
    }
}