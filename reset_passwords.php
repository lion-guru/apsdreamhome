<?php
require_once 'vendor/autoload.php';

use App\Core\Database\Database;
use App\Core\Security;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Update admin user password to "admin123"
$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time' => 4, 'threads' => 1]);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@apsdreamhome.com'");
$stmt->execute([$hash]);

echo "Password updated for admin@apsdreamhome.com\n";

// Also update the other admin users
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email IN ('admin@apsdreamhomes.com', 'apdreamhomes44@gmail.com')");
$stmt->execute([$hash]);

echo "Passwords updated for all admin users\n";

// Verify
$stmt = $pdo->query("SELECT id, name, email, role, password FROM users WHERE role IN ('admin', 'super_admin')");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}\n";
    echo "Password hash: {$row['password']}\n\n";
}