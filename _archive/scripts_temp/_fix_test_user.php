<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$hash = password_hash('Test@123', PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'testuser@example.com'");
$stmt->execute([$hash]);
echo "Updated password for testuser@example.com\n";
echo "New hash: $hash\n";
echo "Verify: " . (password_verify('Test@123', $hash) ? 'YES' : 'NO') . "\n";