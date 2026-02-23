<?php

// Simple test to check if whereStatic works
require_once 'config/bootstrap.php';

try {
    echo "Testing static call to whereStatic()...\n";
    $result = App\Models\ConsolidatedUser::whereStatic('email', 'test@example.com');
    echo "Static call successful!\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:19 CODE REMOVED: 2026-02-22 19:56:19
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}