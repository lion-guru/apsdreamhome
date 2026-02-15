<?php

// Test the base Model class with detailed debugging
require_once 'config/bootstrap.php';

// Create a simple test model
class TestModel extends App\Core\Model {
    protected static $table = 'test_table';
}

try {
    echo "=== Detailed debugging ===\n";
    
    // Test 1: Create instance and check properties
    echo "Test 1: Creating instance...\n";
    $instance = new TestModel();
    
    echo "Instance created\n";
    echo "wheres property exists: " . (property_exists($instance, 'wheres') ? 'YES' : 'NO') . "\n";
    echo "wheres value: " . var_export($instance->wheres, true) . "\n";
    
    // Test 2: Check if we can set wheres manually
    echo "\nTest 2: Setting wheres manually...\n";
    $instance->wheres = [];
    echo "After manual set, wheres: " . json_encode($instance->wheres) . "\n";
    
    // Test 3: Call where on instance after manual set
    echo "\nTest 3: Calling where after manual set...\n";
    $result = $instance->where('email', 'test@example.com');
    echo "After where call, wheres: " . json_encode($result->wheres) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}