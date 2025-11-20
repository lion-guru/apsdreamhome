<?php

// Debug test for whereStatic method
require_once 'config/bootstrap.php';

try {
    echo "Testing whereStatic method...\n";
    
    // Test 1: Create instance directly
    $instance = new App\Models\ConsolidatedUser();
    echo "Instance created, wheres: " . json_encode($instance->wheres) . "\n";
    
    // Test 2: Call where on instance
    $result = $instance->where('email', 'test@example.com');
    echo "After where call, wheres: " . json_encode($result->wheres) . "\n";
    
    // Test 3: Test whereStatic
    $query = App\Models\ConsolidatedUser::whereStatic('email', 'test@example.com');
    echo "After whereStatic call, wheres: " . json_encode($query->wheres) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}