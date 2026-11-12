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

    // 2. Process Peer-Rank Mentorship Royalties (1% on junior's team volume)
    $resultRoyalty = $service->processPeerRankRoyalties($month);
    echo "2. Peer-Rank Mentorship Royalty Result: " . json_encode($resultRoyalty) . "\n";

    // 3. Process Leadership Permanent Salary Tracker pipeline
    $leaderService = new LeadershipSalaryService();
    $result2 = $leaderService->processMonthlyPayouts();
    echo "3. Leadership Salary Tracker Result: " . json_encode($result2) . "\n";

    // 4. Process Fast-Start 30-Day Kickstart Bonuses
    $mlmEngine = new \App\Services\MLM\MLMCommissionEngine();
    $resultFastStart = $mlmEngine->processAllFastStartBonuses();
    echo "4. Fast-Start 30-Day Bonus Result: " . json_encode($resultFastStart) . "\n";

    echo "✅ All Monthly Salary Grants & Diwali Dhamaka payouts completed successfully\n";
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== Monthly Salary Grants Cron Completed ===\n";
