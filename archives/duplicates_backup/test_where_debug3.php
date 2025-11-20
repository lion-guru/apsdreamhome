<?php

// Debug test for whereStatic method - step by step
require_once 'config/bootstrap.php';

try {
    echo "=== Testing whereStatic method step by step ===\n";
    
    // Step 1: Create instance directly
    echo "Step 1: Creating instance...\n";
    $instance = new App\Models\ConsolidatedUser();
    echo "Instance created, wheres: " . json_encode($instance->wheres) . "\n";
    
    // Step 2: Call where on instance
    echo "Step 2: Calling where('email', 'test@example.com')...\n";
    $result = $instance->where('email', 'test@example.com');
    echo "After where call, wheres: " . json_encode($result->wheres) . "\n";
    echo "Same instance? " . ($result === $instance ? 'YES' : 'NO') . "\n";
    
    // Step 3: Test whereStatic manually
    echo "Step 3: Testing whereStatic manually...\n";
    $instance2 = new App\Models\ConsolidatedUser();
    echo "New instance created, wheres: " . json_encode($instance2->wheres) . "\n";
    
    $result2 = $instance2->where('email', 'test@example.com');
    echo "After where call on new instance, wheres: " . json_encode($result2->wheres) . "\n";
    
    // Step 4: Test the actual whereStatic method
    echo "Step 4: Testing whereStatic method...\n";
    $query = App\Models\ConsolidatedUser::whereStatic('email', 'test@example.com');
    echo "After whereStatic call, wheres: " . json_encode($query->wheres) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}