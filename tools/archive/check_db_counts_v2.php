<?php
require_once __DIR__ . '/app/Core/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $userCount = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo "Legacy 'user' table count: $userCount\n";
    echo "Modern 'users' table count: $usersCount\n";
    
    // Check roles
    if ($userCount > 0) {
        $stmt = $pdo->query("SELECT DISTINCT utype FROM user");
        echo "Legacy 'utype' values: " . implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN)) . "\n";
    }
    
    if ($usersCount > 0) {
        $stmt = $pdo->query("SELECT DISTINCT role FROM users");
        echo "Modern 'role' values: " . implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN)) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
