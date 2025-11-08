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
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>MLM Commission Tables Fix</h1>";
echo "<pre>";
echo "Connected successfully to database\n\n";

// Check for associates table using prepared statement
$result = $conn->prepare("SHOW TABLES LIKE 'associates'");
$result->execute();
$table_result = $result->get_result();
if($table_result && $table_result->num_rows == 0) {
    echo "Creating associates table...\n";
    $result->close();

    // Create associates table using prepared statement
    $create_stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS associates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        contact VARCHAR(50),
        email VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $create_stmt->execute();
    $create_stmt->close();

    // Add sample associates using prepared statement
    $insert_stmt = $conn->prepare("INSERT INTO associates (id, name, email, status) VALUES (?, ?, ?, 'active')");
    $associates = [
        [1, 'Admin User', 'admin@example.com'],
        [2, 'Associate Two', 'associate2@example.com'],
        [3, 'Associate Three', 'associate3@example.com'],
        [4, 'Associate One', 'associate1@example.com']
    ];

    foreach ($associates as $associate) {
        $insert_stmt->bind_param("iss", $associate[0], $associate[1], $associate[2]);
        $insert_stmt->execute();
    }
    $insert_stmt->close();
    
    echo "Created associates table with sample data\n";
}

// Check for mlm_commission_ledger table using prepared statement
$result = $conn->prepare("SHOW TABLES LIKE 'mlm_commission_ledger'");
$result->execute();
$table_result = $result->get_result();
if($table_result && $table_result->num_rows == 0) {
    echo "Creating mlm_commission_ledger table...\n";
    $result->close();

    // Create mlm_commission_ledger table using prepared statement
    $create_stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS mlm_commission_ledger (
        id INT AUTO_INCREMENT PRIMARY KEY,
        commission_id INT,
        user_id INT,
        transaction_type VARCHAR(50),
        amount DECIMAL(12,2),
        balance_before DECIMAL(12,2),
        balance_after DECIMAL(12,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $create_stmt->execute();
    $create_stmt->close();

    echo "Created mlm_commission_ledger table\n";
    
    // Add sample data using prepared statement
    $sampleData = [
        [1, 1, 'paid', 5000000.00, 0.00, 5000000.00],
        [2, 2, 'paid', 3000000.00, 0.00, 3000000.00],
        [3, 3, 'pending', 2000000.00, 0.00, 2000000.00],
        [4, 4, 'paid', 1500000.00, 0.00, 1500000.00],
        [5, 5, 'paid', 2500000.00, 0.00, 2500000.00]
    ];
    
    $insert_stmt = $conn->prepare("INSERT INTO mlm_commission_ledger (commission_id, user_id, transaction_type, amount, balance_before, balance_after) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($sampleData as $data) {
        $insert_stmt->bind_param("iisdss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
        $insert_stmt->execute();
    }
    $insert_stmt->close();
    
    echo "Created mlm_commission_ledger table with sample data\n";
} else {
    echo "Checking mlm_commission_ledger data...\n";
    
    // Check if there's enough data
    $result = $conn->prepare("SELECT COUNT(*) as count FROM mlm_commission_ledger");
    $result->execute();
    $row = $result->get_result()->fetch_assoc();
    $count = $row['count'];
    
    echo "Found $count records in mlm_commission_ledger\n";
    
    if ($count < 5) {
        echo "Adding more commission data...\n";
        
        // Add more commission data
        $sampleData = [
            [6, 1, 'paid', 2000000.00, 0.00, 2000000.00],
            [7, 2, 'paid', 1500000.00, 0.00, 1500000.00],
            [8, 3, 'pending', 1000000.00, 0.00, 1000000.00],
            [9, 4, 'paid', 500000.00, 0.00, 500000.00],
            [10, 5, 'paid', 250000.00, 0.00, 250000.00]
        ];
        
        $insert_stmt = $conn->prepare("INSERT INTO mlm_commission_ledger (commission_id, user_id, transaction_type, amount, balance_before, balance_after) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($sampleData as $data) {
            $insert_stmt->bind_param("iisdss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
            $insert_stmt->execute();
        }
        $insert_stmt->close();
        
        $result = $conn->prepare("SELECT COUNT(*) as count FROM mlm_commission_ledger");
        $result->execute();
        $row = $result->get_result()->fetch_assoc();
        $newCount = $row['count'];
        
        echo "Updated mlm_commission_ledger: $newCount records\n";
    }
}

// Check for mlm_commissions table using prepared statement
$result = $conn->prepare("SHOW TABLES LIKE 'mlm_commissions'");
$result->execute();
$table_result = $result->get_result();
if($table_result && $table_result->num_rows == 0) {
    echo "Creating mlm_commissions table...\n";
    $result->close();

    // Create mlm_commissions table using prepared statement
    $create_stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS mlm_commissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        amount DECIMAL(12,2),
        status VARCHAR(50) DEFAULT 'pending',
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $create_stmt->execute();
    $create_stmt->close();
    
    // Add sample data using prepared statement
    $sampleData = [
        [1, 5000000.00, 'paid', 'Initial commission'],
        [2, 3000000.00, 'paid', 'Referral bonus'],
        [3, 2000000.00, 'pending', 'Monthly commission'],
        [4, 1500000.00, 'paid', 'Performance bonus']
    ];
    
    $insert_stmt = $conn->prepare("INSERT INTO mlm_commissions (user_id, amount, status, description) VALUES (?, ?, ?, ?)");
    foreach ($sampleData as $data) {
        $insert_stmt->bind_param("idss", $data[0], $data[1], $data[2], $data[3]);
        $insert_stmt->execute();
    }
    $insert_stmt->close();
    
    echo "Created mlm_commissions table with sample data\n";
} else {
    echo "mlm_commissions table already exists.\n";
}

// Get data from mlm_commission_ledger using prepared statement
$stmt = $conn->prepare("SELECT l.*, a.name FROM mlm_commission_ledger l 
                      LEFT JOIN associates a ON l.user_id = a.id 
                      ORDER BY l.created_at");
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $check_stmt = $conn->prepare("SELECT id FROM mlm_commissions WHERE id = ?");
    $insert_stmt = $conn->prepare("INSERT INTO mlm_commissions (id, user_id, amount, status, description, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?)");
    
    while ($row = $result->fetch_assoc()) {
        // Check if this commission exists in mlm_commissions
        $check_stmt->bind_param("i", $row['commission_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if (!$check_result || $check_result->num_rows == 0) {
            // Insert missing commission
            $insert_stmt->bind_param("iidsss", 
                $row['commission_id'], 
                $row['user_id'], 
                $row['amount'],
                $row['transaction_type'],
                $row['description'],
                $row['created_at']
            );
            $insert_stmt->execute();
            echo "- Added missing commission #" . htmlspecialchars($row['commission_id']) . " for " . htmlspecialchars($row['name']) . "\n";
        }
    }
    $check_stmt->close();
    $insert_stmt->close();
}

// Verify associates have names
$result = $conn->prepare("SELECT id, name FROM associates WHERE name IS NULL OR name = ''");
$result->execute();
$result = $result->get_result();

if ($result && $result->num_rows > 0) {
    echo "\nFixing associates with missing names...\n";
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $stmt = $conn->prepare("UPDATE associates SET name = 'Associate $id' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo "Updated associate ID $id with name 'Associate $id'\n";
    }
}

// Close connection
$conn->close();
echo "\nMLM Commission tables fix complete. All commission widgets should now display properly.\n";
echo "</pre>";
echo "<p><a href='index.php' class='btn' style='display: inline-block; background-color: #3498db; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;'>Return to Database Management Hub</a></p>";
?>
