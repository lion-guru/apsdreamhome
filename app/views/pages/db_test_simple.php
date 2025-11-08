<?php
require_once 'includes/config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "❌ Connection failed: " . $conn->connect_error;
    } else {
        echo "✅ Database connection successful!<br>";
        echo "📊 Database: " . DB_NAME . "<br>";
        
        $result = $conn->query("SELECT COUNT(*) as total FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "👥 Total users: " . $row['total'] . "<br>";
        }
        
        $result = $conn->query("SELECT COUNT(*) as total FROM admin");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "🔐 Total admins: " . $row['total'] . "<br>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>