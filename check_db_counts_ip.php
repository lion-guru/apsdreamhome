<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userCount = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo "Legacy 'user' table count: $userCount\n";
    echo "Modern 'users' table count: $usersCount\n";
    
    if ($userCount > 0) {
        $stmt = $pdo->query("SELECT DISTINCT utype FROM user");
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Legacy 'utype' values: " . implode(', ', $roles) . "\n";
    }
    
    if ($usersCount > 0) {
        $stmt = $pdo->query("SELECT DISTINCT role FROM users");
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Modern 'role' values: " . implode(', ', $roles) . "\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
