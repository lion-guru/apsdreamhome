<?php

require_once 'app/Core/App.php';
require_once 'app/Core/Autoloader.php';
require_once 'app/Core/Database.php';
require_once 'app/Services/DifferentialCommissionCalculator.php';

use App\Services\DifferentialCommissionCalculator;

// Mock setup for testing
define('APP_ROOT', __DIR__);
define('BASE_URL', 'http://localhost/apsdreamhome/');

try {
    $calculator = new DifferentialCommissionCalculator();
    
    // Test Case: Associate (5%) has Sr. Associate (7%) above them
    // Sale: 1,000,000
    // Associate should get 5% = 50,000
    // Sr. Associate should get (7-5) = 2% = 20,000
    
    echo "Testing Differential Commission Calculation...\n";
    
    // We would need real user IDs and tree structure in DB to test fully
    // But we can check if the rank mapping works
    print_r(($calculator->calculate(1000000, 1, 1))); // Assuming buyer 1, but we need sponsor tree

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
