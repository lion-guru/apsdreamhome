<?php
require_once __DIR__ . '/../includes/security/security_functions.php';
require_once __DIR__ . '/../includes/Database.php';

function migratePasswords() {
    $pdo = Database::getInstance();

    $stmt = $pdo->query("SELECT id, password FROM admin");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        // Check if the password is not already hashed with Argon2id
        if (strpos($user['password'], '$argon2id$') !== 0) {
            $hashedPassword = hashPassword($user['password']);
            $updateStmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $user['id']]);
        }
    }
}

migratePasswords();
echo "Passwords migrated successfully.\n";