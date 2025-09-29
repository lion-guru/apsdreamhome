<?php
echo "🧪 Testing Fixed System...\n";
try {
    require_once 'includes/config.php';
    $config = AppConfig::getInstance();
    $conn = $config->getDatabaseConnection();

    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    echo "✅ Database connection successful\n";

    // Test hybrid system
    require_once 'includes/hybrid_commission_system.php';
    $hybrid_system = new HybridRealEstateCommission($conn);
    echo "✅ Hybrid commission system initialized\n";

    // Test property management
    require_once 'property_management.php';
    echo "✅ Property management system loaded\n";

    echo "\n🎉 All systems working properly!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
