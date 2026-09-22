<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '');
$user = $pdo->prepare("SELECT id, email, role, status, registration_status, password FROM users WHERE email = ?");
$user->execute(['associate@apsdreamhome.com']);
$row = $user->fetch(PDO::FETCH_ASSOC);
print_r($row);

if ($row) {
    echo "\nPassword verify: " . (password_verify('Aps@2026', $row['password']) ? 'PASS' : 'FAIL') . "\n";
    echo "Role: " . $row['role'] . "\n";
    echo "Status: " . ($row['status'] ?? 'NULL') . "\n";
    echo "Reg Status: " . ($row['registration_status'] ?? 'NULL') . "\n";
}