<?php
require_once __DIR__ . '/client/ApsDreamClient.php';

/**
 * Test Script for APS Dream Home PHP Client
 */

// Configuration
$baseUrl = 'http://localhost/apsdreamhomefinal/api/v1';
$testEmail = 'admin@example.com';
$testPassword = 'admin123';

// Initialize client
$client = new ApsDreamClient($baseUrl, null, true);

echo "=== Starting APS Dream Home API Tests ===\n\n";

try {
    // 1. Test Login
    echo "1. Testing login...\n";
    $login = $client->login($testEmail, $testPassword);
    echo "✅ Logged in as: " . $login['user']['email'] . "\n";
    
    // 2. Test Get Profile
    echo "\n2. Testing get profile...\n";
    $profile = $client->getProfile();
    echo "✅ Profile retrieved. Welcome, " . $profile['first_name'] . "!\n";
    
    // 3. Test Get Properties
    echo "\n3. Testing get properties...\n";
    $properties = $client->getProperties(['status' => 'available']);
    echo "✅ Found " . count($properties) . " available properties\n";
    
    if (!empty($properties)) {
        $firstProperty = $properties[0];
        echo "   - First property: " . $firstProperty['title'] . " ($'" . number_format($firstProperty['price']) . ")\n";
        
        // 4. Test Get Single Property
        echo "\n4. Testing get single property...\n";
        $property = $client->getProperty($firstProperty['id']);
        echo "✅ Retrieved property: " . $property['title'] . "\n";
    }
    
    // 5. Test Logout
    echo "\n5. Testing logout...\n";
    $logout = $client->logout();
    echo "✅ Logged out successfully\n";
    
    echo "\n=== All tests completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
        echo "   - Check database connection and credentials\n";
        echo "   - Make sure the database is running and accessible\n";
        echo "   - Verify the database user has proper permissions\n";
    }
    exit(1);
}
