<?php
/**
 * Monthly Salary Grants & Diwali Dhamaka Payouts Processing Cron
 * Runs on 1st of each month at 02:00
 * Enforces Anti-Free-Rider Monthly Maintenance & 40:60 Leg Balance.
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Services\MLM\SalaryIncentiveService;
use App\Services\MLM\LeadershipSalaryService;

echo "=== Monthly Salary Grants & Diwali Dhamaka Cron Started ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    $db = Database::getInstance()->getConnection();
    $month = date('Y-m');
    echo "Processing month: $month\n";

    // 1. Process mlm_salary_grants pipeline
    $service = new SalaryIncentiveService();
    $result1 = $service->processMonthlyGrants($month);
    echo "1. mlm_salary_grants Result: " . json_encode($result1) . "\n";

    // 2. Process Leadership & Diwali Dhamaka salary_tracker pipeline
    $leaderService = new LeadershipSalaryService();
    $result2 = $leaderService->processMonthlyPayouts();
    echo "2. Leadership Salary Tracker Result: " . json_encode($result2) . "\n";

    echo "✅ All Monthly Salary Grants & Diwali Dhamaka payouts completed successfully\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Monthly Salary Grants Cron Completed ===\n";
