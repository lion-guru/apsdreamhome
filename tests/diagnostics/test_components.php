<?php
echo "Testing PHP execution...\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP version: " . phpversion() . "\n";

// Test if we can include the config file
try {
    require_once 'includes/config.php';
    echo "✅ Config file loaded successfully\n";
    echo "Database: " . DB_NAME . "\n";
} catch (Exception $e) {
    echo "❌ Error loading config: " . $e->getMessage() . "\n";
}

// Test if we can include the template file
try {
    require_once 'includes/enhanced_universal_template.php';
    echo "✅ Template file loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading template: " . $e->getMessage() . "\n";
}

// Test database connection
try {
    require_once 'includes/db_connection.php';
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n";
}
?>
