<?php
// Check database structure
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING USERS TABLE STRUCTURE ===\n";
    
    $stmt = $pdo->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n=== CHECKING EXISTING USERS ===\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Total users in database: {$result['count']}\n";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->prepare("SELECT id, name, email FROM users LIMIT 5");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Sample users:\n";
        foreach ($users as $user) {
            echo "- ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
