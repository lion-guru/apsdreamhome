<?php
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if bookings table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'bookings'");
    $bookingsExists = $stmt->fetch();
    
    if ($bookingsExists) {
        echo "✓ bookings table exists\n";
        
        // Check structure
        $stmt = $conn->query("DESCRIBE bookings");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Columns in bookings table:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']}: {$col['Type']}\n";
        }
        
        // Check if there are any bookings
        $stmt = $conn->query("SELECT COUNT(*) as count FROM bookings");
        $count = $stmt->fetch()['count'];
        echo "Total bookings: $count\n";
    } else {
        echo "✗ bookings table does NOT exist\n";
    }
    
    // Check related tables
    $tables = ['booking_payments', 'booking_logs', 'payment_receipts', 'mlm_commission_ledger'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch();
        echo ($exists ? "✓" : "✗") . " $table table " . ($exists ? "exists" : "does NOT exist") . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
