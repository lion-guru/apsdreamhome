<?php

// Simple test to check if whereStatic works
require_once 'config/bootstrap.php';

try {
    echo "Testing static call to whereStatic()...\n";
    $result = App\Models\ConsolidatedUser::whereStatic('email', 'test@example.com');
    echo "Static call successful!\n";
    var_dump($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}