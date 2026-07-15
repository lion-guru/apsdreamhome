<?php
/**
 * Standardize User Passwords - Sets all passwords to 'Admin@2026' for testing
 * 
 * Run via: php scripts/standardize_passwords.php
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

try {
    $pdo = \App\Core\Database::getInstance()->getConnection();

    echo "=== STANDARDIZING TEST PASSWORDS ===\n\n";

    $newPassword = 'Admin@2026';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 1. Update users table
    echo "Updating 'users' table passwords...\n";
    $stmt1 = $pdo->prepare("UPDATE users SET password = ?");
    $stmt1->execute([$hash]);
    $usersUpdated = $stmt1->rowCount();
    echo "✓ Updated {$usersUpdated} users to password '{$newPassword}'.\n";

    // 2. Update associates table (if exists and has password column)
    $hasAssociates = $pdo->query("SHOW TABLES LIKE 'associates'")->rowCount() > 0;
    if ($hasAssociates) {
        $cols = $pdo->query("SHOW COLUMNS FROM associates")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('password', $cols, true)) {
            echo "\nUpdating 'associates' table passwords...\n";
            $stmt2 = $pdo->prepare("UPDATE associates SET password = ?");
            $stmt2->execute([$hash]);
            $associatesUpdated = $stmt2->rowCount();
            echo "✓ Updated {$associatesUpdated} associates to password '{$newPassword}'.\n";
        }
    }

    echo "\n=== PASSWORDS STANDARDIZED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
