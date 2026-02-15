<?php
/**
 * Property Search Functionality Test
 * Tests the property search with various filters
 */

require_once 'config/bootstrap.php';
require_once 'app/models/ConsolidatedProperty.php';

try {
    echo "=== Property Search Functionality Test ===\n\n";
    
    $propertyModel = new \App\Models\ConsolidatedProperty();
    
    // Test 1: Basic search without filters
    echo "Test 1: Basic search (no filters)\n";
    $results = $propertyModel->searchProperties([]);
    echo "✓ Found " . count($results) . " properties\n\n";
    
    // Test 2: Search by property type
    echo "Test 2: Search by property type 'Apartment'\n";
    $results = $propertyModel->searchProperties(['type' => 'Apartment']);
    echo "✓ Found " . count($results) . " apartments\n\n";
    
    // Test 3: Search by price range
    echo "Test 3: Search by price range (50000 - 200000)\n";
    $results = $propertyModel->searchProperties([
        'min_price' => 50000,
        'max_price' => 200000
    ]);
    echo "✓ Found " . count($results) . " properties in price range\n\n";
    
    // Test 4: Search by location (city)
    echo "Test 4: Search by location 'Dubai'\n";
    $results = $propertyModel->searchProperties(['location' => 'Dubai']);
    echo "✓ Found " . count($results) . " properties in Dubai\n\n";
    
    // Test 5: Combined search
    echo "Test 5: Combined search (Apartment in Dubai, 2+ bedrooms)\n";
    $results = $propertyModel->searchProperties([
        'type' => 'Apartment',
        'location' => 'Dubai',
        'bedrooms' => 2
    ]);
    echo "✓ Found " . count($results) . " matching properties\n\n";
    
    // Test 6: Search with bedrooms and bathrooms
    echo "Test 6: Search with specific bedrooms (3) and bathrooms (2)\n";
    $results = $propertyModel->searchProperties([
        'bedrooms' => 3,
        'bathrooms' => 2
    ]);
    echo "✓ Found " . count($results) . " properties with 3BR/2BA\n\n";
    
    // Test 7: Get property types
    echo "Test 7: Available property types\n";
    $types = $propertyModel->getPropertyTypes();
    echo "✓ Found " . count($types) . " property types:\n";
    foreach ($types as $type) {
        echo "  - {$type}\n";
    }
    echo "\n";
    
    // Test 8: Get featured properties
    echo "Test 8: Featured properties\n";
    $featured = $propertyModel->getFeaturedProperties();
    echo "✓ Found " . count($featured) . " featured properties\n\n";
    
    // Test 9: Get property statistics
    echo "Test 9: Property statistics\n";
    $stats = $propertyModel->getPropertyStats();
    echo "✓ Statistics retrieved:\n";
    foreach ($stats as $key => $value) {
        echo "  - $key: $value\n";
    }
    echo "\n";
    
    // Test 10: Sample property details
    echo "Test 10: Sample property details\n";
    $allProperties = $propertyModel->searchProperties([]);
    if (!empty($allProperties)) {
        $sample = $allProperties[0];
        echo "✓ Sample property:\n";
        echo "  - ID: {$sample->id}\n";
        echo "  - Title: {$sample->title}\n";
        echo "  - Type: " . ($sample->property_type_name ?? 'N/A') . "\n";
        echo "  - Price: {$sample->price}\n";
        echo "  - Location: " . ($sample->address ?? 'N/A') . "\n";
        echo "  - Bedrooms: " . ($sample->bedrooms ?? 'N/A') . "\n";
        echo "  - Bathrooms: " . ($sample->bathrooms ?? 'N/A') . "\n";
        echo "  - Area: " . ($sample->area_sqft ?? 'N/A') . " sqft\n";
    } else {
        echo "⚠ No properties found in database\n";
    }
    
    echo "\n=== All Tests Completed Successfully! ===\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}