<?php

// Debug test for whereStatic method
require_once 'config/bootstrap.php';

try {
    echo "Testing whereStatic method...\n";
    
    // Test 1: Basic whereStatic call
    $query = App\Models\ConsolidatedUser::whereStatic('email', 'test@example.com');
    echo "Query object created successfully\n";
    echo "Wheres: " . json_encode($query->wheres) . "\n";
    
    // Test 2: Try to get first result
    echo "\nTesting first() method...\n";
    $user = $query->first();
    echo "First method completed\n";
    if ($user) {
        echo "User found: " . json_encode($user->attributes) . "\n";
    } else {
        echo "No user found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}