<?php

// Test the base Model class directly
require_once 'config/bootstrap.php';

// Create a simple test model
class TestModel extends App\Core\Model {
    protected static $table = 'test_table';
}

try {
    echo "=== Testing base Model class ===\n";
    
    // Test 1: Create instance directly
    echo "Test 1: Creating instance...\n";
    $instance = new TestModel();
    echo "Instance created, wheres: " . json_encode($instance->wheres) . "\n";
    
    // Test 2: Call where on instance
    echo "Test 2: Calling where('email', 'test@example.com')...\n";
    $result = $instance->where('email', 'test@example.com');
    echo "After where call, wheres: " . json_encode($result->wheres) . "\n";
    
    // Test 3: Test whereStatic
    echo "Test 3: Testing whereStatic method...\n";
    $query = TestModel::whereStatic('email', 'test@example.com');
    echo "After whereStatic call, wheres: " . json_encode($query->wheres) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}