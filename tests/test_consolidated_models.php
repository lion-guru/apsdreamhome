<?php

/**
 * Test file for consolidated models
 * This file tests the functionality of the new consolidated User and Property models
 */

// Bootstrap the application
require_once __DIR__ . '/../config/bootstrap.php';

// Register the autoloader for consolidated models
$autoloader = App\Core\Autoloader::getInstance();
$autoloader->addNamespace('App\Models', APP_ROOT . '/app/Models');
$autoloader->addNamespace('App\Core', APP_ROOT . '/app/Core');

use App\Models\ConsolidatedUser;
use App\Models\ConsolidatedProperty;

echo "=== Testing Consolidated Models ===\n\n";

// Test 1: User Authentication
echo "Test 1: User Authentication\n";
echo "-------------------------\n";
try {
    // Test with known credentials (you may need to adjust these)
    $user = ConsolidatedUser::authenticate('test@example.com', 'password123');
    
    if ($user) {
        echo "✓ Authentication successful\n";
        echo "  User ID: " . $user->id . "\n";
        echo "  Email: " . $user->email . "\n";
        echo "  Role: " . $user->role . "\n";
        echo "  Full Name: " . $user->getFullName() . "\n";
    } else {
        echo "✗ Authentication failed (expected for test credentials)\n";
    }
} catch (Exception $e) {
    echo "✗ Authentication error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: User Registration
echo "Test 2: User Registration\n";
echo "-------------------------\n";
try {
    $testData = [
        'username' => 'testuser_' . time(),
        'email' => 'test_' . time() . '@example.com',
        'password' => 'password123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'role' => 'customer',
        'status' => 'active'
    ];
    
    $newUser = ConsolidatedUser::register($testData);
    
    if ($newUser) {
        echo "✓ Registration successful\n";
        echo "  User ID: " . $newUser->id . "\n";
        echo "  Username: " . $newUser->username . "\n";
        echo "  Email: " . $newUser->email . "\n";
        
        // Test password verification
        if ($newUser->verifyPassword('password123')) {
            echo "  ✓ Password verification successful\n";
        } else {
            echo "  ✗ Password verification failed\n";
        }
        
        // Clean up
        $newUser->delete();
        echo "  ✓ Test user cleaned up\n";
    } else {
        echo "✗ Registration failed\n";
    }
} catch (Exception $e) {
    echo "✗ Registration error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Property Search
echo "Test 3: Property Search\n";
echo "-----------------------\n";
try {
    $filters = [
        'location' => 'Dubai',
        'property_type' => 'apartment',
        'min_price' => 100000,
        'max_price' => 5000000,
        'bedrooms' => 2
    ];
    
    $properties = ConsolidatedProperty::searchProperties($filters, 5);
    
    echo "✓ Search completed\n";
    echo "  Found " . count($properties) . " properties\n";
    
    if (count($properties) > 0) {
        $property = $properties[0];
        echo "  Sample property:\n";
        echo "    ID: " . $property->id . "\n";
        echo "    Title: " . $property->title . "\n";
        echo "    Price: " . $property->getFormattedPrice() . "\n";
        echo "    Location: " . $property->location . "\n";
        echo "    Type: " . $property->property_type . "\n";
    }
} catch (Exception $e) {
    echo "✗ Search error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Featured Properties
echo "Test 4: Featured Properties\n";
echo "---------------------------\n";
try {
    $featuredProperties = ConsolidatedProperty::getFeaturedProperties(3);
    
    echo "✓ Featured properties retrieved\n";
    echo "  Found " . count($featuredProperties) . " featured properties\n";
    
    foreach ($featuredProperties as $index => $property) {
        echo "  Property " . ($index + 1) . ":\n";
        echo "    ID: " . $property->id . "\n";
        echo "    Title: " . $property->title . "\n";
        echo "    Price: " . $property->getFormattedPrice() . "\n";
        echo "    Featured: " . ($property->isFeatured() ? 'Yes' : 'No') . "\n";
    }
} catch (Exception $e) {
    echo "✗ Featured properties error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Property Types
echo "Test 5: Property Types\n";
echo "----------------------\n";
try {
    $propertyTypes = ConsolidatedProperty::getPropertyTypes();
    
    echo "✓ Property types retrieved\n";
    echo "  Available types: " . implode(', ', $propertyTypes) . "\n";
} catch (Exception $e) {
    echo "✗ Property types error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Property Statistics
echo "Test 6: Property Statistics\n";
echo "---------------------------\n";
try {
    $stats = ConsolidatedProperty::getPropertyStats();
    
    echo "✓ Property statistics retrieved\n";
    echo "  Total properties: " . ($stats['total'] ?? 'N/A') . "\n";
    echo "  Active properties: " . ($stats['active'] ?? 'N/A') . "\n";
    echo "  Featured properties: " . ($stats['featured'] ?? 'N/A') . "\n";
    echo "  Sold properties: " . ($stats['sold'] ?? 'N/A') . "\n";
    
    if (isset($stats['by_type']) && is_array($stats['by_type'])) {
        echo "  Properties by type:\n";
        foreach ($stats['by_type'] as $type => $count) {
            echo "    $type: $count\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Property statistics error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 7: Legacy Mode Toggle
echo "Test 7: Legacy Mode Testing\n";
echo "-------------------------\n";
try {
    // Test modern mode (default)
    ConsolidatedUser::setLegacyMode(false);
    echo "✓ Modern mode enabled\n";
    
    // Test legacy mode
    ConsolidatedUser::setLegacyMode(true);
    echo "✓ Legacy mode enabled\n";
    
    // Test with legacy mode
    $user = ConsolidatedUser::findUnified(1);
    if ($user) {
        echo "✓ Legacy mode find successful\n";
        echo "  User ID: " . $user->id . "\n";
    } else {
        echo "✗ Legacy mode find failed\n";
    }
    
    // Reset to modern mode
    ConsolidatedUser::setLegacyMode(false);
    echo "✓ Reset to modern mode\n";
} catch (Exception $e) {
    echo "✗ Legacy mode error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Test Summary ===\n";
echo "All consolidated model tests completed.\n";
echo "Check the output above for any errors or failures.\n";
echo "\nNote: Some tests may fail if the database doesn't have the expected data.\n";
echo "This is normal for a test environment.\n";