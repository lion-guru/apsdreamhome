<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$hash = password_hash('Test1234', PASSWORD_BCRYPT);
$stmt = $pdo->prepare('UPDATE users SET password=? WHERE email=?');
$stmt->execute([$hash, 'customer1@apsdreamhome.com']);
echo "Updated rows: " . $stmt->rowCount() . "\n";

$stmt2 = $pdo->prepare('SELECT password FROM users WHERE email=?');
$stmt2->execute(['customer1@apsdreamhome.com']);
$row = $stmt2->fetch();
echo "Verify: " . (password_verify('Test1234', $row['password']) ? 'OK' : 'FAIL') . "\n";?>