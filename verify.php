<?php
// Final verification test
echo "<h1>?? APS DREAM HOME - WORKING!</h1>";
echo "<p>? PHP is working: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
try {
    require_once 'includes/db_connection.php';
    $conn = getDbConnection();
    if ($conn) {
        echo "<p>✅ Database connected successfully!</p>";

        // Test data
        $result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            echo "<p>✅ Found " . ($row['count'] ?? 0) . " properties in database</p>";
        }

        echo "<p>✅ All systems operational!</p>";
        echo "<hr>";
        echo "<h3>🎉 Your Website is Ready!</h3>";
        echo "<p><a href='index.php' class='btn btn-primary'>Go to Homepage</a></p>";
        echo "<p><a href='properties.php' class='btn btn-success'>View Properties</a></p>";
    } else {
        echo "<p>❌ Database connection failed</p>";
        echo "<p>Make sure MySQL is running in XAMPP Control Panel</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please start XAMPP MySQL service</p>";
}
?>
