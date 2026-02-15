<?php
echo "🧪 Testing AppConfig Class...\n";
try {
    require_once 'includes/config.php';
    $config = AppConfig::getInstance();
    echo "✅ AppConfig class loaded successfully\n";

    $conn = $config->getDatabaseConnection();
    if ($conn) {
        echo "✅ Database connection successful\n";
        echo "🎉 Index.php AppConfig error fixed!\n";
    } else {
        echo "❌ Database connection failed\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
