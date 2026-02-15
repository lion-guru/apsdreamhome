<?php
require_once 'config/bootstrap.php';

// Use a concrete model to get connection
class TestModel extends App\Core\Model {
    protected static $table = 'users';
}

try {
    $pdo = TestModel::getConnection();
    $stmt = $pdo->query('DESCRIBE users');
    echo "Users table structure:\n";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\nProperties table structure:\n";
    $stmt = $pdo->query('DESCRIBE properties');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}