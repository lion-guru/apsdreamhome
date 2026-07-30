<?php
header('Content-Type: text/plain');
try {
    $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
    $db = new PDO($dsn, 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $password = 'Aps@2026';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    echo "Updating users table to password '$password'...\n";
    $stmt = $db->prepare("UPDATE users SET password = ?");
    $stmt->execute([$hash]);
    echo "Updated " . $stmt->rowCount() . " users.\n";

    echo "Checking user customer1@apsdreamhome.com again:\n";
    $stmt = $db->prepare("SELECT id, email, name, password FROM users WHERE email = ?");
    $stmt->execute(['customer1@apsdreamhome.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        print_r($user);
        echo "Verifying password '$password': " . (password_verify($password, $user['password']) ? "MATCH" : "MISMATCH") . "\n";
    }

} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
