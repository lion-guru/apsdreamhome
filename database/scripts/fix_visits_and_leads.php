<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Rename 'visits' to 'associate_field_visits'
    $tables = $pdo->query("SHOW TABLES LIKE 'visits'")->fetchAll();
    if (count($tables) > 0) {
        // Check if associate_field_visits already exists
        $target = $pdo->query("SHOW TABLES LIKE 'associate_field_visits'")->fetchAll();
        if (count($target) == 0) {
            $pdo->exec("RENAME TABLE visits TO associate_field_visits");
            echo "Renamed 'visits' to 'associate_field_visits'.\n";
        } else {
            echo "'associate_field_visits' already exists. Cannot rename 'visits'.\n";
        }
    } else {
        echo "'visits' table not found.\n";
    }

    // 2. Add 'lead_id' to 'property_visits'
    $columns = $pdo->query("SHOW COLUMNS FROM property_visits LIKE 'lead_id'")->fetchAll();
    if (count($columns) == 0) {
        $pdo->exec("ALTER TABLE property_visits ADD COLUMN lead_id INT(11) NULL AFTER customer_id");
        echo "Added 'lead_id' column to 'property_visits'.\n";
    } else {
        echo "'lead_id' column already exists in 'property_visits'.\n";
    }
    
    // 3. Make customer_id nullable in property_visits
    // First drop NOT NULL constraint
    $pdo->exec("ALTER TABLE property_visits MODIFY COLUMN customer_id BIGINT(20) UNSIGNED NULL");
    echo "Modified 'customer_id' in 'property_visits' to be NULLABLE.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
