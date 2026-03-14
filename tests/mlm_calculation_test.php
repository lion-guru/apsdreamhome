<?php

// Bootstrap the environment
define('APP_ROOT', dirname(__DIR__));

// Use the autoloader as designed
require_once APP_ROOT . '/app/Core/Autoloader.php';

// Mock environment variables
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_DATABASE'] = 'apsdreamhome';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = '';

// Correct the Database class resolution in the autoloader if necessary
// The autoloader has: $autoloader->addClassMap('Database', APP_ROOT . '/app/core/Database.php');
// But the file is in app/Core/Database/Database.php and namespaced App\Core\Database

use App\Services\DifferentialCommissionCalculator;

echo "Testing Differential Commission Calculation...\n";

// Test scenario: Sale of 5M, Buyer ID 10, Property ID 5
$saleAmount = 5000000;
$buyerId = 10;
$propertyId = 5;

try {
    $calculator = new DifferentialCommissionCalculator();
    $result = $calculator->calculate($saleAmount, $buyerId, $propertyId);

    if ($result['success']) {
        echo "SUCCESS: Distributed " . $result['total_distributed'] . "%\n";
        foreach ($result['commissions'] as $comm) {
            echo "Agent ID: " . $comm['user_id'] . " | Rank: " . $comm['rank'] . " | Amount: ₹" . number_format($comm['amount']) . " (" . $comm['percent'] . "%)\n";
        }
    } else {
        echo "FAILED: " . ($result['message'] ?? $result['error']) . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack Trace: " . $e->getTraceAsString() . "\n";
}
