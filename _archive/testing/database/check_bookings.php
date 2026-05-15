<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1:3307;dbname=apsdreamhome', 'root', '');
    
    // Check bookings table
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() == 0) {
        echo "bookings table does not exist\n";
        
        // Create bookings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_number VARCHAR(50) UNIQUE,
                property_id INT,
                customer_id INT,
                agent_id INT,
                booking_date DATE,
                amount DECIMAL(15,2),
                status VARCHAR(50) DEFAULT 'pending',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "Created bookings table\n";
    } else {
        echo "bookings table exists\n";
    }
    
    // Check the columns
    $stmt = $pdo->query("DESCRIBE bookings");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns:\n";
    foreach ($cols as $col) {
        echo "  - " . $col['Field'] . "\n";
    }
    
} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
