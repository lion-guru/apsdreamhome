<?php
/**
 * Daily Maintenance Dues Generation Cron
 * Runs daily at 03:00
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\MaintenanceBillingService;

echo "=== Daily Maintenance Dues Generation ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $service = new MaintenanceBillingService();
    
    // Generate bills for current month
    $month = date('n');
    $year = date('Y');
    
    echo "Generating maintenance dues for $month/$year\n";
    
    $result = $service->generateMonthlyBills($month, $year);
    
    echo "Result: " . json_encode($result) . "\n";
    echo "✅ Maintenance dues generation completed\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Maintenance Dues Generation Completed ===\n";