<?php
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$email = 'testuser@example.com';
$password = 'Aps@2026';
$name = 'Test User';
$phone = '+919999999999';
$role = 'customer';
$status = 'active';

$hash = password_hash($password, PASSWORD_DEFAULT);
$customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
$referralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);

$tid = 1;
$stmt = \App\Core\Database\Database::getInstance()->getConnection()->prepare(
    "INSERT INTO users (customer_id, name, email, phone, password, role, status, referral_code, created_at, updated_at) 
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
     ON DUPLICATE KEY UPDATE name=VALUES(name), phone=VALUES(phone), password=VALUES(password), role=VALUES(role), status=VALUES(status), updated_at=NOW()"
);
$stmt->execute([$customerId, $name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role, $status, $referralCode]);

echo "Test user created/updated successfully\n";