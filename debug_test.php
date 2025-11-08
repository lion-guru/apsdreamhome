<?php
echo "Testing basic PHP functionality...<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "PHP version: " . phpversion() . "<br>";
echo "Date/Time: " . date('Y-m-d H:i:s') . "<br>";

// Test database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    echo "✅ Database connection successful!<br>";
    echo "Database: apsdreamhome<br>";
    echo "Tables: " . $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = "apsdreamhome"')->fetchColumn() . " tables<br>";
} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Check if files exist
$files_to_check = [
    'includes/config.php',
    'includes/enhanced_universal_template.php',
    'includes/db_connection.php'
];

foreach ($files_to_check as $file) {
    echo ($file . ": " . (file_exists($file) ? "✅ EXISTS" : "❌ MISSING") . "<br>");
}
?>
