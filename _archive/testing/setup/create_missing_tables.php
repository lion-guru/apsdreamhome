<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1:3307;dbname=apsdreamhome', 'root', '');
    
    // Create land_records table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS land_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            land_title VARCHAR(255),
            location VARCHAR(255),
            area DECIMAL(10,2),
            owner_name VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Created land_records table\n";
    
    // Create property_categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS property_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Created property_categories table\n";
    
    // Add some default categories
    $categories = ['Residential', 'Commercial', 'Industrial', 'Agricultural', 'Mixed Use'];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO property_categories (name) VALUES (?)");
        $stmt->execute([$cat]);
    }
    echo "Added default categories\n";
    
    // Add column to properties if missing
    $stmt = $pdo->query("DESCRIBE properties");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    
    if (!in_array('category_id', $colNames)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN category_id INT AFTER type");
        echo "Added category_id to properties\n";
    }
    
    if (!in_array('project_id', $colNames)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN project_id INT AFTER category_id");
        echo "Added project_id to properties\n";
    }
    
    if (!in_array('plot_id', $colNames)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN plot_id INT AFTER project_id");
        echo "Added plot_id to properties\n";
    }
    
    if (!in_array('land_id', $colNames)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN land_id INT AFTER plot_id");
        echo "Added land_id to properties\n";
    }
    
    echo "\nDone!\n";
    
} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
