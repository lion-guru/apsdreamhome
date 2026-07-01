<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

use App\Services\InvestmentService;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    $service = new InvestmentService($pdo);

    // Fetch the ₹5 Lakh plan
    $planStmt = $pdo->query("SELECT * FROM investment_plans WHERE plan_code = 'BLOCK-C-5L' LIMIT 1");
    $plan = $planStmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        throw new Exception("BLOCK-C-5L plan not found in database.");
    }

    echo "--- PLAN DETAIL ---\n";
    echo "Plan Name: {$plan['plan_name']}\n";
    echo "Promised SqFt: {$plan['plot_promised_sqft']}\n";
    echo "Promised Value: ₹" . number_format($plan['plot_promised_value']) . "\n\n";

    // Create a test investment
    $testUserId = 9; // Let's use user #9
    $investData = [
        'amount' => 500000,
        'monthly_amount' => null,
        'sip_date' => null,
    ];

    $result = $service->invest($testUserId, (int)$plan['id'], $investData);

    if (!$result['success']) {
        throw new Exception("Investment failed: " . ($result['error'] ?? 'Unknown error'));
    }

    $investmentId = $result['investment_id'];
    echo "✅ Test investment created successfully! ID: $investmentId\n\n";

    // Fetch and check the new investment row
    $invStmt = $pdo->prepare("SELECT * FROM investments WHERE id = ?");
    $invStmt->execute([$investmentId]);
    $inv = $invStmt->fetch(PDO::FETCH_ASSOC);

    echo "--- NEW INVESTMENT ROW IN DATABASE ---\n";
    echo "ID: {$inv['id']}\n";
    echo "User ID: {$inv['user_id']}\n";
    echo "Principal: ₹" . number_format($inv['principal_amount']) . "\n";
    echo "Company Contribution Liability: ₹" . number_format($inv['company_contribution']) . " (Expected: ₹499,000)\n";
    echo "Promised SqFt: {$inv['plot_promised_sqft']} SqFt (Expected: 1,000)\n";
    echo "Promised Value: ₹" . number_format($inv['plot_promised_value']) . " (Expected: ₹999,000)\n";
    echo "Maturity Status: {$inv['maturity_status']} (Expected: pending)\n\n";

    // Cleanup
    $pdo->prepare("DELETE FROM investments WHERE id = ?")->execute([$investmentId]);
    echo "🧹 Cleanup complete. Test investment deleted.\n";

} catch (Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
}
