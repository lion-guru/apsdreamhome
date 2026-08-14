<?php
require 'app/Core/Autoloader.php';
$spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

// Test just the price query
require 'app/Core/Database.php';
try {
    $db = App\Core\Database::getInstance()->getConnection();
    $priceStmt = $db->prepare("SELECT MIN(price_per_sqft) as min_price FROM plots WHERE colony_id = ? AND status != 'sold'");
    $priceStmt->execute([1]);
    $priceRow = $priceStmt->fetch(\PDO::FETCH_ASSOC);
    echo "Price row for colony_id=1: " . print_r($priceRow, true) . "\n";
    if ($priceRow && !empty($priceRow['min_price'])) {
        $minPrice = (float)$priceRow['min_price'];
        echo "Price text: Starting from " . "\xE2\x82\xB9" . number_format($minPrice) . "/sqft\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}?>