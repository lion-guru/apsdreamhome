<?php
/**
 * Monthly Salary Grants Processing Cron
 * Runs on 1st of each month at 02:00
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\Payroll\SalaryGrantService;

echo "=== Monthly Salary Grants Cron Started ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $db = Database::getInstance()->getConnection();
    $service = new SalaryGrantService();
    
    $month = date('Y-m');
    echo "Processing month: $month\n";
    
    $result = $service->processMonthlyGrants($month);
    
    echo "Result: " . json_encode($result) . "\n";
    echo "✅ Salary grants completed successfully\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Monthly Salary Grants Cron Completed ===\n";