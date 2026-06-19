<?php
header('Content-Type: text/plain');
try {
    $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
    $db = new PDO($dsn, 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking user customer1@apsdreamhome.com:\n";
    $stmt = $db->prepare("SELECT id, email, name, role, status, two_factor_enabled, password FROM users WHERE email = ?");
    $stmt->execute(['customer1@apsdreamhome.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        print_r($user);
        echo "Verifying password 'Aps@2026': " . (password_verify('Aps@2026', $user['password']) ? "MATCH" : "MISMATCH") . "\n";
        echo "Verifying password 'Admin@2026': " . (password_verify('Admin@2026', $user['password']) ? "MATCH" : "MISMATCH") . "\n";
        echo "Verifying password 'Test1234': " . (password_verify('Test1234', $user['password']) ? "MATCH" : "MISMATCH") . "\n";
    } else {
        echo "User customer1@apsdreamhome.com NOT found!\n";
        
        echo "\nListing some users:\n";
        $stmt = $db->query("SELECT id, email, name, role, status FROM users LIMIT 10");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }

} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
