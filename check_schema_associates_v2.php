<?php
try {
    $dsn = "mysql:host=localhost;dbname=apsdreamhome;charset=utf8";
    $username = "root";
    $password = "";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "Connected successfully\n";
    
    $stmt = $pdo->query("DESCRIBE associates");
    $columns = $stmt->fetchAll();
    
    print_r($columns);
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
