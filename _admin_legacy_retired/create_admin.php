<?php
require_once __DIR__ . '/../includes/security/security_functions.php';
require_once __DIR__ . '/../includes/Database.php';

function createAdminUser($username, $password) {
    $pdo = Database::getInstance();
    $hashedPassword = hashPassword($password);

    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashedPassword]);
}

// Usage: php create_admin.php <username> <password>
if ($argc < 3) {
    echo "Usage: php create_admin.php <username> <password>\n";
    exit(1);
}

createAdminUser($argv[1], $argv[2]);
echo "Admin user created successfully.\n";