<?php
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

use App\Services\InvestmentService;
use App\Services\MLM\MatchingBonusService;

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    echo "--- E2E TESTING: MLM PLAN UPGRADES ---\n\n";

    // 1. Test Matching Rates
    echo "1. Testing MatchingBonusService Match Rates:\n";
    $matchService = new MatchingBonusService($pdo);
    $rates = $matchService->calculateMonthlyMatching(date('Y-m-01'), date('Y-m-t')); // run helper to get rates internally
    
    // We can directly call the protected/public helper getMatchRates via reflection or just instantiate and test
    $reflector = new ReflectionClass(MatchingBonusService::class);
    $method = $reflector->getMethod('getMatchRates');
    $method->setAccessible(true);
    $ratesResult = $method->invoke($matchService);
    
    echo "  Gen 1 Match Rate: {$ratesResult[1]}% (Expected: 10%)\n";
    echo "  Gen 2 Match Rate: {$ratesResult[2]}% (Expected: 5%)\n";
    echo "  Gen 3 Match Rate: {$ratesResult[3]}% (Expected: 2%)\n";
    
    if ($ratesResult[1] == 10 && $ratesResult[2] == 5 && $ratesResult[3] == 2) {
        echo "  ✅ PASS: Match rates correctly resolved from settings!\n\n";
    } else {
        echo "  ❌ FAIL: Match rates resolution mismatch.\n\n";
    }

    // 2. Test Investment Commission & 45-Day Hold
    echo "2. Testing Investment Commission & 45-Day Hold:\n";
    
    // Ensure Block C plan exists
    $planStmt = $pdo->query("SELECT * FROM investment_plans WHERE plan_code = 'BLOCK-C-5L' LIMIT 1");
    $plan = $planStmt->fetch(PDO::FETCH_ASSOC);
    if (!$plan) {
        throw new Exception("BLOCK-C-5L plan not found.");
    }

    $investmentService = new InvestmentService($pdo);
    $testUserId = 9; // Investor
    $referrerUserId = 2; // Referrer

    // We'll create a test investment of ₹5,00,000
    $investData = [
        'amount' => 500000,
        'monthly_amount' => null,
        'sip_date' => null,
        'referrer_user_id' => $referrerUserId
    ];

    $result = $investmentService->invest($testUserId, (int)$plan['id'], $investData);

    if (!$result['success']) {
        throw new Exception("Investment failed: " . ($result['error'] ?? 'Unknown error'));
    }

    $investmentId = $result['investment_id'];
    echo "  ✅ Test investment #$investmentId created.\n";

    // Query ledger entries for this investment
    // Investment commissions are stored with receipt_id = investmentId
    $ledgerStmt = $pdo->prepare("SELECT * FROM mlm_commission_ledger WHERE receipt_id = ? AND commission_type = 'investment_sale'");
    $ledgerStmt->execute([$investmentId]);
    $ledgerRows = $ledgerStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Found " . count($ledgerRows) . " commission ledger entries for this investment:\n";
    
    $expectedRates = [0 => 3.5, 1 => 1.0, 2 => 0.5]; // 0=direct (3.5%), 1=L1 (1.0%), 2=L2 (0.5%)
    $allOk = true;

    foreach ($ledgerRows as $row) {
        $level = (int)$row['level'];
        $amount = (float)$row['amount'];
        $pct = (float)$row['commission_percentage'];
        $holdUntil = $row['hold_until'];

        // Verify hold_until is exactly 45 days from today
        $today = new DateTime('today');
        $holdDate = new DateTime($holdUntil);
        $diffDays = (int)$today->diff($holdDate)->days;

        echo "    - Beneficiary #{$row['beneficiary_user_id']} | Level $level | Pct $pct% | Amount ₹" . number_format($amount) . " | Hold: $holdUntil ($diffDays days)\n";

        // Check rates
        if ($level === 0 && $pct != 3.5) {
            echo "      ❌ Error: Level 0 should be 3.5%\n";
            $allOk = false;
        }
        if ($level === 1 && $pct != 1.0) {
            echo "      ❌ Error: Level 1 should be 1.0%\n";
            $allOk = false;
        }
        if ($level === 2 && $pct != 0.5) {
            echo "      ❌ Error: Level 2 should be 0.5%\n";
            $allOk = false;
        }

        // Check hold days (allow 1 day diff for edge cases at midnight, but should be exactly 45)
        if ($diffDays !== 45) {
            echo "      ❌ Error: Hold period is $diffDays days, expected 45 days.\n";
            $allOk = false;
        }
    }

    if ($allOk) {
        echo "  ✅ PASS: Commission percentages (3.5% / 1% / 0.5%) and 45-day hold validated successfully!\n\n";
    } else {
        echo "  ❌ FAIL: Commission validation failed.\n\n";
    }

    // Cleanup
    $pdo->prepare("DELETE FROM investments WHERE id = ?")->execute([$investmentId]);
    $pdo->prepare("DELETE FROM mlm_commission_ledger WHERE receipt_id = ? AND commission_type = 'investment_sale'")->execute([$investmentId]);
    echo "🧹 Cleanup complete. Test investment and ledger entries deleted.\n";

} catch (Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
}
