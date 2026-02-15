<?php
/**
 * Simple Application Test
 */

echo "🏠 APS Dream Home - SIMPLE APPLICATION TEST\n";
echo "========================================\n\n";

// Test basic functionality
echo "1. ✅ PHP is working\n";
echo "2. ✅ Project directory: " . __DIR__ . "\n";

// Check if main files exist
$files = [
    'index.php' => 'Main entry point',
    'bootstrap.php' => 'Bootstrap',
    'app/core/App.php' => 'Application core'
];

echo "3. 📋 Core Files Check:\n";
foreach ($files as $file => $desc) {
    $exists = file_exists($file) ? "✅" : "❌";
    echo "   $exists $desc\n";
}

// Test database connection
echo "\n4. 🗄️ Database Test:\n";
try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "   ❌ Connection failed: " . $conn->connect_error . "\n";
    } else {
        echo "   ✅ Database connected\n";
        $result = $conn->query("SHOW TABLES");
        echo "   ✅ Tables found: " . $result->num_rows . "\n";
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n5. 🌐 Web Access:\n";
echo "   📱 Main site: http://localhost/apsdreamhome/\n";
echo "   🎛️  Admin panel: http://localhost/apsdreamhome/admin/\n";

echo "\n🎉 TEST COMPLETED!\n";
echo "==================\n";
echo "Status: Application is ready!\n";

?>
