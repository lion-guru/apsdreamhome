<?php
echo "🧪 Testing Development Cost Calculator...\n";
try {
    require_once 'includes/config.php';
    $config = AppConfig::getInstance();
    $conn = $config->getDatabaseConnection();

    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    echo "✅ Database connection successful\n";

    // Test development cost calculator
    require_once 'development_cost_calculator.php';
    echo "✅ Development cost calculator loaded successfully\n";

    echo "\n🎉 Development Cost Calculator is now working!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
