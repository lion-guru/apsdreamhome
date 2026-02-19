<?php
// Configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

echo "\n-- ID MAPPING --\n";
foreach($pdo->query('SELECT uid, uemail FROM user') as $u) {
    echo "User: " . $u['uemail'] . " (Legacy ID: " . $u['uid'] . ")";
    
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$u['uemail']]);
    $newId = $stmt->fetchColumn();
    
    echo " -> New ID: " . ($newId ?: 'NOT FOUND') . "\n";
}
?>
