<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$passwordPlain = 'Aps@2026';
$hashArgon2id  = password_hash($passwordPlain, PASSWORD_ARGON2ID);
$hashBcrypt    = password_hash($passwordPlain, PASSWORD_BCRYPT);

echo "=====================================================\n";
echo "UPDATING ALL PASSWORDS TO '{$passwordPlain}'\n";
echo "=====================================================\n";

// 1. Update `users` table
$stmt = $pdo->prepare("UPDATE users SET password = ?");
$stmt->execute([$hashArgon2id]);
$usersUpdated = $stmt->rowCount();
echo "Updated {$usersUpdated} users in 'users' table.\n";

// 2. Update `admin` table if it exists
try {
    $stmtAdmin = $pdo->prepare("UPDATE admin SET password = ?, apass = ?");
    $stmtAdmin->execute([$hashBcrypt, $hashArgon2id]);
    $adminUpdated = $stmtAdmin->rowCount();
    echo "Updated {$adminUpdated} records in 'admin' table.\n";
} catch (\Exception $e) {
    echo "Notice: Admin table update: " . $e->getMessage() . "\n";
}

// 3. Clear failed login attempts to remove any lockouts
try {
    $pdo->query("DELETE FROM login_attempts");
    echo "Cleared 'login_attempts' table.\n";
} catch (\Exception $e) {
    echo "Notice: login_attempts error: " . $e->getMessage() . "\n";
}

// 4. Verify password_verify on all users
$allUsers = $pdo->query("SELECT id, email, phone, role, status, registration_status, password FROM users")->fetchAll(PDO::FETCH_ASSOC);
$verifiedCount = 0;
$failedCount = 0;

foreach ($allUsers as $u) {
    if (password_verify($passwordPlain, $u['password'])) {
        $verifiedCount++;
    } else {
        $failedCount++;
        echo "FAILED verification for User ID {$u['id']} ({$u['email']})\n";
    }
}

echo "\nVerification Results for 'users':\n";
echo "  Total Users: " . count($allUsers) . "\n";
echo "  Verified Success: {$verifiedCount}\n";
echo "  Failed: {$failedCount}\n";

// 5. Verify admin table
try {
    $allAdmins = $pdo->query("SELECT id, email, username, password, apass FROM admin")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nVerification Results for 'admin':\n";
    foreach ($allAdmins as $a) {
        $v1 = !empty($a['password']) ? password_verify($passwordPlain, $a['password']) : 'empty';
        $v2 = !empty($a['apass']) ? password_verify($passwordPlain, $a['apass']) : 'empty';
        echo "  Admin ID {$a['id']} (user: " . ($a['username'] ?: $a['email']) . "): password=" . var_export($v1, true) . ", apass=" . var_export($v2, true) . "\n";
    }
} catch (\Exception $e) {
    echo "Admin verification notice: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
