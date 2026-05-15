<?php
// Database configuration
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully!\n\n";
    
    // Check if associates table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'associates'");
    if ($stmt->rowCount() > 0) {
        echo "Associates table exists.\n\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE associates");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
        echo "\n";
        
        // Create a test associate
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO associates (name, email, phone, password, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        
        $name = 'Test Associate';
        $email = 'associate@apsdreamhome.com';
        $phone = '9876543210';
        $status = 'active';
        
        $stmt->execute([$name, $email, $phone, $hashedPassword, $status]);
        
        echo "Test associate created successfully!\n\n";
        echo "Login credentials:\n";
        echo "Email: associate@apsdreamhome.com\n";
        echo "Password: password123\n\n";
        
        // Verify the associate was created
        $stmt = $pdo->query("SELECT id, name, email, status FROM associates WHERE email = 'associate@apsdreamhome.com'");
        $associate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($associate) {
            echo "Associate verified:\n";
            echo "ID: " . $associate['id'] . "\n";
            echo "Name: " . $associate['name'] . "\n";
            echo "Email: " . $associate['email'] . "\n";
            echo "Status: " . $associate['status'] . "\n";
        }
        
    } else {
        echo "Associates table does not exist. Creating it...\n";
        
        // Create associates table
        $sql = "CREATE TABLE associates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "Associates table created successfully!\n\n";
        
        // Create a test associate
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO associates (name, email, phone, password, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $name = 'Test Associate';
        $email = 'associate@apsdreamhome.com';
        $phone = '9876543210';
        $status = 'active';
        
        $stmt->execute([$name, $email, $phone, $hashedPassword, $status]);
        
        echo "Test associate created successfully!\n\n";
        echo "Login credentials:\n";
        echo "Email: associate@apsdreamhome.com\n";
        echo "Password: password123\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
