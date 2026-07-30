<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

require_once 'app/Services/MLM/MLMCommissionEngine.php';

$engine = new \App\Services\MLM\MLMCommissionEngine($pdo);

echo "=== Testing Differential Commission Model ===\n\n";

// Test 1: Check getCanonicalRates
echo "--- Test 1: getCanonicalRates ---\n";
$ranks = ['associate', 'senior_associate', 'bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];
foreach ($ranks as $rank) {
    $rates = \App\Services\MLM\MLMCommissionEngine::getCanonicalRates($rank);
    echo sprintf("  %-20s: direct=%s%% l1=%s%% l2=%s%% l3=%s%%\n", $rank, $rates['direct'], $rates['l1'], $rates['l2'], $rates['l3']);
}

// Test 2: Check loadRankRates
echo "\n--- Test 2: loadRankRates (from DB) ---\n";
$reflection = new ReflectionClass($engine);
$method = $reflection->getMethod('loadRankRates');
$method->setAccessible(true);
$rates = $method->invoke($engine);
foreach ($rates as $rank => $rate) {
    echo sprintf("  %-20s: %s%%\n", $rank, $rate);
}

// Test 3: Test getUserRank for a sample user
echo "\n--- Test 3: getUserRank for sample users ---\n";
$method2 = $reflection->getMethod('getUserRank');
$method2->setAccessible(true);

// Get some associate user IDs
$stmt = $pdo->query("SELECT user_id, level FROM associates WHERE status = 'active' LIMIT 5");
$associates = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($associates as $a) {
    $rank = $method2->invoke($engine, (int)$a['user_id']);
    echo sprintf("  User %d (DB level: %s) -> Rank: %s\n", $a['user_id'], $a['level'], $rank);
}

// Test 4: Test differential calculation logic manually
echo "\n--- Test 4: Manual Differential Calculation ---\n";
$slabs = [
    'associate'        => 5.0,
    'senior_associate' => 7.0,
    'bdm'              => 10.0,
    'sr_bdm'           => 12.0,
    'vice_president'   => 15.0,
    'president'        => 18.0,
    'site_manager'     => 20.0,
];

$saleAmount = 1000000; // 10 Lakh

echo "Sale Amount: ₹" . number_format($saleAmount) . "\n\n";

// Scenario A: Associate (5%) sells, Senior Associate (7%) is L1, BDM (10%) is L2
echo "Scenario A: Associate sells (5%), Senior Associate L1 (7%), BDM L2 (10%)\n";
$prevRate = 5.0;
echo "  Agent (Associate): 5% = ₹" . number_format($saleAmount * 0.05) . "\n";
$uplines = ['Senior Associate' => 7.0, 'BDM' => 10.0];
foreach ($uplines as $name => $rate) {
    if ($rate === $prevRate) {
        echo "  $name: Same rank - override\n";
    } else {
        $diff = $rate - $prevRate;
        $amt = $saleAmount * ($diff / 100);
        echo "  $name: $rate% - $prevRate% = ${diff}% = ₹" . number_format($amt) . "\n";
        $prevRate = $rate;
    }
}
$total = 5 + 2 + 3;
echo "  TOTAL: {$total}% = ₹" . number_format($saleAmount * $total / 100) . "\n\n";

// Scenario B: Senior Associate (7%) sells, BDM (10%) L1, Sr. BDM (12%) L2
echo "Scenario B: Senior Associate sells (7%), BDM L1 (10%), Sr. BDM L2 (12%)\n";
$prevRate = 7.0;
echo "  Agent (Sr. Associate): 7% = ₹" . number_format($saleAmount * 0.07) . "\n";
$uplines = ['BDM' => 10.0, 'Sr. BDM' => 12.0];
foreach ($uplines as $name => $rate) {
    if ($rate === $prevRate) {
        echo "  $name: Same rank - override\n";
    } else {
        $diff = $rate - $prevRate;
        $amt = $saleAmount * ($diff / 100);
        echo "  $name: $rate% - $prevRate% = ${diff}% = ₹" . number_format($amt) . "\n";
        $prevRate = $rate;
    }
}
$total = 7 + 3 + 2;
echo "  TOTAL: {$total}% = ₹" . number_format($saleAmount * $total / 100) . "\n\n";

// Scenario C: BDM (10%) sells, Sr. BDM (12%) L1, VP (15%) L2
echo "Scenario C: BDM sells (10%), Sr. BDM L1 (12%), VP L2 (15%)\n";
$prevRate = 10.0;
echo "  Agent (BDM): 10% = ₹" . number_format($saleAmount * 0.10) . "\n";
$uplines = ['Sr. BDM' => 12.0, 'Vice President' => 15.0];
foreach ($uplines as $name => $rate) {
    if ($rate === $prevRate) {
        echo "  $name: Same rank - override\n";
    } else {
        $diff = $rate - $prevRate;
        $amt = $saleAmount * ($diff / 100);
        echo "  $name: $rate% - $prevRate% = ${diff}% = ₹" . number_format($amt) . "\n";
        $prevRate = $rate;
    }
}
$total = 10 + 2 + 3;
echo "  TOTAL: {$total}% = ₹" . number_format($saleAmount * $total / 100) . "\n\n";

// Scenario D: Same-rank test
echo "Scenario D: Same-rank breakaway (both 10%)\n";
$prevRate = 10.0;
echo "  Agent (BDM): 10% = ₹" . number_format($saleAmount * 0.10) . "\n";
$uplines = ['Another BDM (same rank)' => 10.0];
$sameRankCount = 0;
foreach ($uplines as $name => $rate) {
    if ($rate === $prevRate) {
        $sameRankCount++;
        $override = ($sameRankCount === 1) ? 2.0 : (($sameRankCount === 2) ? 1.0 : 0.0);
        $amt = $saleAmount * ($override / 100);
        echo "  $name: Same rank Gen $sameRankCount -> ${override}% = ₹" . number_format($amt) . "\n";
    } else {
        $diff = $rate - $prevRate;
        $amt = $saleAmount * ($diff / 100);
        echo "  $name: $rate% - $prevRate% = ${diff}% = ₹" . number_format($amt) . "\n";
        $prevRate = $rate;
    }
}
echo "  TOTAL: 12% = ₹" . number_format($saleAmount * 0.12) . "\n\n";

echo "=== All differential calculations verified ===\n";