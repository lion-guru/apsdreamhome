<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check gallery table for the specific image
    echo "Checking gallery table for gallery_1775737368_5845b931.JPG...\n";
    $stmt = $conn->query("SELECT * FROM gallery WHERE image_path LIKE '%gallery_1775737368%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        foreach ($results as $row) {
            echo "Found: ID={$row['id']}, Path={$row['image_path']}\n";
        }
    } else {
        echo "Not found in gallery table\n";
    }
    
    // Check project_gallery table
    echo "\nChecking project_gallery table for gallery_1775737368_5845b931.JPG...\n";
    $stmt = $conn->query("SELECT * FROM project_gallery WHERE image_path LIKE '%gallery_1775737368%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        foreach ($results as $row) {
            echo "Found: ID={$row['id']}, Path={$row['image_path']}\n";
        }
    } else {
        echo "Not found in project_gallery table\n";
    }
    
    // Check all tables that might have image_path
    echo "\nSearching all tables for 'gallery_1775737368'...\n";
    $stmt = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='apsdreamhome'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$table' AND COLUMN_NAME LIKE '%path%'");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if ($columns) {
            foreach ($columns as $col) {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM `$table` WHERE `$col` LIKE '%gallery_1775737368%'");
                $count = $stmt->fetch()['count'];
                if ($count > 0) {
                    echo "Found in $table.$col: $count records\n";
                    $stmt = $conn->query("SELECT * FROM `$table` WHERE `$col` LIKE '%gallery_1775737368%' LIMIT 1");
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    print_r($row);
                }
            }
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
