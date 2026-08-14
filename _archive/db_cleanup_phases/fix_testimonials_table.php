<?php
/**
 * Fix Testimonials Table Structure
 * Updates the testimonials table to match controller expectations
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "ðŸ”� Checking testimonials table structure...\n";
    
    // Check current structure
    $result = $conn->query("SHOW COLUMNS FROM testimonials");
    $existingColumns = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
        echo "   - {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\nðŸ”§ Adding missing columns...\n";
    
    // Add missing columns
    if (!in_array('customer_name', $existingColumns)) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN customer_name VARCHAR(255) AFTER id");
        echo "âœ… Added customer_name column\n";
    }
    
    if (!in_array('customer_email', $existingColumns)) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN customer_email VARCHAR(255) AFTER customer_name");
        echo "âœ… Added customer_email column\n";
    }
    
    if (!in_array('customer_phone', $existingColumns)) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN customer_phone VARCHAR(50) AFTER customer_email");
        echo "âœ… Added customer_phone column\n";
    }
    
    if (!in_array('content', $existingColumns)) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN content TEXT AFTER rating");
        echo "âœ… Added content column\n";
    }
    
    if (!in_array('is_featured', $existingColumns)) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER status");
        echo "âœ… Added is_featured column\n";
    }
    
    if (!in_array('property_id', $existingColumns)) {
        $conn->query("ALTER TABLE testimonials ADD COLUMN property_id INT NULL AFTER is_featured");
        echo "âœ… Added property_id column\n";
    }
    
    echo "\nâœ… Testimonials table structure fixed!\n";
    
    // Verify final structure
    echo "\nðŸ”� Final table structure:\n";
    $result = $conn->query("SHOW COLUMNS FROM testimonials");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "â�Œ Error: " . $e->getMessage() . "\n";
}?>