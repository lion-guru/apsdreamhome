<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$app = \App\Core\App::getInstance();
$app->setBasePath(dirname(__DIR__));

// Initialize Database (via App)
// The App constructor calls initializeDatabase()
// But let's make sure it's available
$db = $app->db();

echo "Database connection: " . ($db ? "OK" : "Failed") . "\n";

// Test SeoMetadata instantiation
try {
    echo "Instantiating SeoMetadata...\n";
    $seo = new \App\Models\SeoMetadata();
    echo "SeoMetadata instantiated successfully.\n";

    // Test getByPage (query builder)
    echo "Testing getByPage('home')...\n";
    try {
        $result = $seo->getByPage('home');
        echo "getByPage executed successfully.\n";
        echo "Result: " . ($result ? "Found" : "Not Found") . "\n";
    } catch (\Throwable $e) {
        echo "Error in getByPage: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
} catch (\Throwable $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
