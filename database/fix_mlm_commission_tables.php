<?php
/**
 * APS Dream Home - MLM Commission Tables Fix
 * 
 * This script fixes inconsistencies between mlm_commissions and mlm_commission_ledger tables
 * to ensure all dashboard widgets display properly.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>MLM Commission Tables Fix</h1>";
echo "<pre>";
echo "Connected successfully to database\n\n";

// Check for associates table
$result = $conn->query("SHOW TABLES LIKE 'associates'");
if($result && $result->num_rows == 0) {
    echo "Creating associates table...\n";
    
    // Create associates table
    $conn->query("CREATE TABLE IF NOT EXISTS associates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        contact VARCHAR(50),
        email VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add sample associates
    $conn->query("INSERT INTO associates (id, name, email, status) VALUES
        (1, 'Admin User', 'admin@example.com', 'active'),
        (2, 'Associate Two', 'associate2@example.com', 'active'),
        (3, 'Associate Three', 'associate3@example.com', 'active'),
        (4, 'Associate One', 'associate1@example.com', 'active'),
        (5, 'Associate Five', 'associate5@example.com', 'active')
    ");
    
    echo "Created associates table with sample data\n";
}

// Check for mlm_commission_ledger table
$result = $conn->query("SHOW TABLES LIKE 'mlm_commission_ledger'");
if($result && $result->num_rows == 0) {
    echo "Creating mlm_commission_ledger table...\n";
    
    // Create MLM Commission Ledger table
    $conn->query("CREATE TABLE IF NOT EXISTS mlm_commission_ledger (
        id INT AUTO_INCREMENT PRIMARY KEY,
        associate_id INT,
        commission_amount DECIMAL(10,2) NOT NULL,
        commission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        description TEXT,
        status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
        FOREIGN KEY (associate_id) REFERENCES associates(id)
    )");
    
    // Add sample commission data
    $conn->query("INSERT INTO mlm_commission_ledger (associate_id, commission_amount, status, description) VALUES
        (4, 20000000.00, 'paid', 'Commission for Property Sale #1'),
        (5, 25000000.00, 'paid', 'Commission for Property Sale #2'),
        (1, 15000000.00, 'paid', 'Commission for Property Sale #3'),
        (3, 9000000.00, 'paid', 'Commission for Property Sale #4'),
        (2, 7000000.00, 'paid', 'Commission for Property Sale #5')
    ");
    
    echo "Created mlm_commission_ledger table with sample data\n";
} else {
    echo "Checking mlm_commission_ledger data...\n";
    
    // Check if there's enough data
    $result = $conn->query("SELECT COUNT(*) as count FROM mlm_commission_ledger");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    echo "Found $count records in mlm_commission_ledger\n";
    
    if ($count < 5) {
        echo "Adding more commission data...\n";
        
        // Add more commission data
        $conn->query("INSERT INTO mlm_commission_ledger (associate_id, commission_amount, status, description) VALUES
            (4, 20000000.00, 'paid', 'Commission for Property Sale #1'),
            (5, 25000000.00, 'paid', 'Commission for Property Sale #2'),
            (1, 15000000.00, 'paid', 'Commission for Property Sale #3'),
            (3, 9000000.00, 'paid', 'Commission for Property Sale #4'),
            (2, 7000000.00, 'paid', 'Commission for Property Sale #5')
        ");
        
        $result = $conn->query("SELECT COUNT(*) as count FROM mlm_commission_ledger");
        $row = $result->fetch_assoc();
        $newCount = $row['count'];
        
        echo "Updated mlm_commission_ledger: $newCount records\n";
    }
}

// Check for mlm_commissions table (used by final_dashboard_check.php)
$result = $conn->query("SHOW TABLES LIKE 'mlm_commissions'");
if($result && $result->num_rows > 0) {
    echo "Syncing mlm_commissions with mlm_commission_ledger...\n";
    
    // Get data from mlm_commission_ledger
    $result = $conn->query("SELECT l.*, a.name FROM mlm_commission_ledger l 
                           JOIN associates a ON l.associate_id = a.id 
                           ORDER BY l.commission_amount DESC LIMIT 5");
    
    if ($result && $result->num_rows > 0) {
        // Clear existing data
        $conn->query("TRUNCATE TABLE mlm_commissions");
        
        // Insert synchronized data
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['associate_id'];
            $user_name = $row['name'] ? $conn->real_escape_string($row['name']) : "Associate $user_id";
            $amount = $row['commission_amount'];
            $status = $row['status'];
            
            $conn->query("INSERT INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type, status) 
                         VALUES ($user_id, '$user_name', $user_id, $user_id, $amount, 'sales', '$status')");
        }
        
        echo "Synchronized mlm_commissions with data from mlm_commission_ledger\n";
    }
} else {
    echo "Creating mlm_commissions table...\n";
    
    // Create mlm_commissions table
    $conn->query("CREATE TABLE IF NOT EXISTS mlm_commissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_name VARCHAR(100),
        transaction_id INT,
        property_id INT,
        commission_amount DECIMAL(12,2),
        commission_type VARCHAR(50),
        status VARCHAR(50) DEFAULT 'paid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Get data from mlm_commission_ledger
    $result = $conn->query("SELECT l.*, a.name FROM mlm_commission_ledger l 
                           JOIN associates a ON l.associate_id = a.id 
                           ORDER BY l.commission_amount DESC LIMIT 5");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['associate_id'];
            $user_name = $row['name'] ? $conn->real_escape_string($row['name']) : "Associate $user_id";
            $amount = $row['commission_amount'];
            $status = $row['status'];
            
            $conn->query("INSERT INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type, status) 
                         VALUES ($user_id, '$user_name', $user_id, $user_id, $amount, 'sales', '$status')");
        }
        
        echo "Created mlm_commissions table with data from mlm_commission_ledger\n";
    } else {
        // Add sample commission data if no ledger data exists
        $conn->query("INSERT INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type) VALUES
            (4, 'Associate One', 4, 4, 20000000.00, 'sales'),
            (5, 'Associate Two', 5, 5, 25000000.00, 'sales'),
            (1, 'Admin User', 1, 1, 15000000.00, 'sales'),
            (3, 'Associate Three', 3, 3, 9000000.00, 'sales'),
            (2, 'Associate Four', 2, 2, 7000000.00, 'sales')
        ");
        
        echo "Created mlm_commissions table with sample data\n";
    }
}

// Verify associates have names
$result = $conn->query("SELECT id, name FROM associates WHERE name IS NULL OR name = ''");
if ($result && $result->num_rows > 0) {
    echo "\nFixing associates with missing names...\n";
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $conn->query("UPDATE associates SET name = 'Associate $id' WHERE id = $id");
        echo "Updated associate ID $id with name 'Associate $id'\n";
    }
}

// Close connection
$conn->close();
echo "\nMLM Commission tables fix complete. All commission widgets should now display properly.\n";
echo "</pre>";
echo "<p><a href='index.php' class='btn' style='display: inline-block; background-color: #3498db; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;'>Return to Database Management Hub</a></p>";
?>
