<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$u = $db->fetchOne("SELECT * FROM users WHERE email = 'rajesh.associate@apsdreamhome.test'");
echo "USER ID: {$u['id']} | ROLE: {$u['role']} | STATUS: {$u['status']}\n";
echo "PW CHECK: " . (password_verify('Password@123', $u['password']) ? "MATCH" : "MISMATCH") . "\n";
// Re-hash to make 100% sure
$newHash = password_hash('Password@123', PASSWORD_DEFAULT);
$db->execute("UPDATE users SET password = ? WHERE id = ?", [$newHash, $u['id']]);
echo "PASSWORD RE-HASHED TO Password@123.\n";
