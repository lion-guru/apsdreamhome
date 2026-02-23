<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    echo "\n--- Data Counts ---\n";
    $userCount = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Legacy 'user' count: $userCount\n";
    echo "Modern 'users' count: $usersCount\n";

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
