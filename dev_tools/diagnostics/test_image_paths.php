<?php
// Test script to verify image path changes
require_once __DIR__ . '/../../app/core/App.php';
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../app/helpers.php';

echo "Testing image path changes...\n";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'Not defined') . "\n";

// Test property-detail.php changes
echo "\n1. Testing property-detail.php image paths:\n";
echo "   Hero image path: " . BASE_URL . 'public/uploads/property/test.jpg' . "\n";

// Test similar properties
echo "   Similar property image path: " . BASE_URL . 'public/uploads/property/similar.jpg' . "\n";

// Test submit-property.php changes
echo "\n2. Testing submit-property.php upload directory:\n";
$upload_dir = __DIR__ . "/public/uploads/property/";
echo "   Upload directory: " . $upload_dir . "\n";
echo "   Directory exists: " . (is_dir($upload_dir) ? "Yes" : "No") . "\n";

// Test asset helper function
echo "\n3. Testing asset helper function:\n";
echo "   Default image: " . get_asset_url('property-banner.jpg', 'images') . "\n";

// Test database connection
echo "\n4. Testing database connection:\n";
$db = \App\Core\App::database();
try {
    $query = "SELECT id, title, main_image FROM property LIMIT 3";
    $result = $db->fetchAll($query);
    if ($result) {
        echo "   Found " . count($result) . " properties:\n";
        foreach ($result as $property) {
            echo "   - Property: " . h($property['title']) . "\n";
            echo "     Image path: " . (empty($property['main_image']) ? "No image" : (defined('BASE_URL') ? BASE_URL : '') . 'public/uploads/property/' . $property['main_image']) . "\n";
        }
    } else {
        echo "   No properties found in database.\n";
    }
} catch (Exception $e) {
    echo "   Database error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";