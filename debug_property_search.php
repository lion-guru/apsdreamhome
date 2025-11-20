<?php

// Bootstrap the application
require_once __DIR__ . '/config/bootstrap.php';

// Register the autoloader for consolidated models
$autoloader = App\Core\Autoloader::getInstance();
$autoloader->addNamespace('App\Models', APP_ROOT . '/app/Models');
$autoloader->addNamespace('App\Core', APP_ROOT . '/app/Core');

use App\Models\ConsolidatedProperty;

echo "=== Debug Property Search ===\n\n";

// First, let's check what properties exist
echo "Checking existing properties...\n";
try {
    $allProperties = ConsolidatedProperty::all();
    echo "Total properties found: " . count($allProperties) . "\n";
    
    if (count($allProperties) > 0) {
        echo "First property structure:\n";
        $firstProperty = $allProperties[0];
        echo "ID: " . $firstProperty->id . "\n";
        echo "Title: " . $firstProperty->title . "\n";
        echo "Type: " . ($firstProperty->type ?? 'null') . "\n";
        echo "Status: " . ($firstProperty->status ?? 'null') . "\n";
        echo "City: " . ($firstProperty->city ?? 'null') . "\n";
        echo "Price: " . ($firstProperty->price ?? 'null') . "\n";
    }
} catch (Exception $e) {
    echo "Error checking properties: " . $e->getMessage() . "\n";
}

echo "\nNow testing search with filters...\n";
try {
    $filters = [
        'property_type' => 'apartment',
        'min_price' => 100000,
        'max_price' => 5000000,
        'bedrooms' => 2
    ];
    
    echo "Search filters: " . json_encode($filters) . "\n";
    
    // Let's try to see the actual SQL query
    $query = ConsolidatedProperty::query();
    
    if (!empty($filters['property_type'])) {
        echo "Adding property_type filter for: " . $filters['property_type'] . "\n";
        $query->where('type', '=', $filters['property_type']);
    }
    if (!empty($filters['min_price'])) {
        $query->where('price', '>=', $filters['min_price']);
    }
    if (!empty($filters['max_price'])) {
        $query->where('price', '<=', $filters['max_price']);
    }
    if (!empty($filters['bedrooms'])) {
        $query->where('bedrooms', '>=', $filters['bedrooms']);
    }
    $query->where('status', '=', 'active');
    
    echo "Executing query...\n";
    $results = $query->get();
    echo "Search results: " . count($results) . " properties found\n";
    
} catch (Exception $e) {
    echo "Search error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";