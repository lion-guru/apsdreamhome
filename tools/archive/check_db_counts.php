<?php
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/App.php';
require_once __DIR__ . '/app/config/env.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $userCount = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo "Legacy 'user' table count: $userCount\n";
    echo "Modern 'users' table count: $usersCount\n";

    echo "\n--- Table: api_keys ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE api_keys");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['Field'] . " | " . $col['Type'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error describing api_keys: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
