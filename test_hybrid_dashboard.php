<?php
echo "🧪 Testing Hybrid Commission Dashboard...\n";
try {
    require_once 'includes/config.php';
    $config = AppConfig::getInstance();
    $conn = $config->getDatabaseConnection();

    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    echo "✅ Database connection successful\n";

    // Test hybrid commission dashboard
    require_once 'hybrid_commission_dashboard.php';
    echo "✅ Hybrid commission dashboard loaded successfully\n";

    echo "\n🎉 Hybrid Commission Dashboard is now working!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
