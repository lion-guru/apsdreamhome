<?php
/**
 * Test script to verify autoloader and class loading
 */

// Define project root
$projectRoot = dirname(__DIR__, 2);

// Load autoloader
require_once $projectRoot . '/app/core/App.php';

echo "Autoloader loaded.\n";

// Test loading CommissionService
try {
    if (class_exists('App\Services\CommissionService')) {
        echo "SUCCESS: App\Services\CommissionService loaded.\n";
        $service = new App\Services\CommissionService();
        echo "SUCCESS: CommissionService instantiated.\n";
    } else {
        echo "FAILURE: App\Services\CommissionService NOT found.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test loading a Controller
try {
    if (class_exists('App\Http\Controllers\Analytics\AdminReportsController')) {
        echo "SUCCESS: App\Http\Controllers\Analytics\AdminReportsController loaded.\n";
    } else {
        echo "FAILURE: App\Http\Controllers\Analytics\AdminReportsController NOT found.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
