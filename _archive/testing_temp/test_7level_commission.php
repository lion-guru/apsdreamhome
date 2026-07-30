<?php
/**
 * 7-Level Differential Commission Test Setup
 * 
 * Creates a deep upline chain:
 *   L0 (Site Manager-20%) → L1 (President-18%) → L2 (VP-15%) → L3 (Sr BDM-12%) → L4 (BDM-10%) → L5 (Sr Associate-7%) → L6 (Associate-5%)
 * 
 * Then creates a booking for L6 and calculates commission to verify differential distribution.
 * Expected: L6=5%, L5=2%, L4=3%, L3=2%, L2=3%, L1=3%, L0=2% = 20% total (exactly at cap)
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

require_once __DIR__ . '/../app/Services/MLM/MLMCommissionEngine.php';

echo "=== 7-LEVEL DIFFERENTIAL COMMISSION TEST ===\n\n";

$rankOrder = ['associate', 'senior_associate', 'bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];

// ── STEP 1: Create 7 users with proper upline chain ──
echo "STEP 1: Creating 7 users with deep upline chain...\n";

$chain = [
    ['name' => 'Deep SM - SiteManager',   'rank' => 'site_manager',     'ref' => null],    // L0 - top
    ['name' => 'Deep SM - President',     'rank' => 'president',        'ref' => null],    // L1
    ['name' => 'Deep SM - VP',            'rank' => 'vice_president',   'ref' => null],    // L2
    ['name' => 'Deep SM - SrBDM',         'rank' => 'sr_bdm',           'ref' => null],    // L3
    ['name' => 'Deep SM - BDM',           'rank' => 'bdm',              'ref' => null],    // L4
    ['name' => 'Deep SM - SrAssociate',   'rank' => 'senior_associate', 'ref' => null],    // L5
    ['name' => 'Deep SM - Associate',     'rank' => 'associate',        'ref' => null],    // L6 - seller
];

$createdUserIds = [];
$associateIds = [];

// Check if already created
$check = $pdo->query("SELECT id, name FROM users WHERE name LIKE 'Deep SM%' ORDER BY id");
$existing = $check->fetchAll();

if (count($existing) >= 7) {
    echo "  Found " . count($existing) . " existing test users. Reusing...\n";
    // Build chain by walking referred_by from bottom (has most recent referred_by=null chain)
    // Find the user with no referred_by = top of chain
    foreach ($existing as $e) {
        $uidMap[(int)$e['id']] = $e['name'];
    }
    // Walk chain from bottom: find the user whose name ends with 'Associate' (L6, seller)
    $bottomId = null;
    foreach ($existing as $e) {
        if ($e['name'] === 'Deep SM - Associate') {
            $bottomId = (int)$e['id'];
            break;
        }
    }
    // Walk up from bottom to top
    $chainUsers = [];
    $cur = $bottomId;
    for ($i = 6; $i >= 0; $i--) {
        $chainUsers[$i] = $cur;
        $stmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->execute([$cur]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $cur = $r ? (int)$r['referred_by'] : null;
    }
    $createdUserIds = $chainUsers; // [0]=top, [6]=bottom
} else {
    // Clean old test data first
    $pdo->exec("DELETE FROM users WHERE name LIKE 'Deep SM%'");
    
    // Create chain bottom-up so referred_by is set correctly
    $nameOrder = ['Deep SM - Associate', 'Deep SM - SrAssociate', 'Deep SM - BDM', 'Deep SM - SrBDM', 'Deep SM - VP', 'Deep SM - President', 'Deep SM - SiteManager'];
    
    $prevUserId = null;
    for ($i = 6; $i >= 0; $i--) {
        $rb = $prevUserId ? $prevUserId : null;
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status, referred_by, created_at) VALUES (?, ?, ?, ?, 'associate', 'active', ?, NOW())");
        $email = 'deep_sm_' . strtolower(str_replace(' ', '_', $nameOrder[$i])) . '@test.com';
        $phone = '900000000' . (70 + $i);
        $hash = password_hash('Test123', PASSWORD_DEFAULT);
        $stmt->execute([$nameOrder[$i], $email, $phone, $hash, $rb]);
        $uid = (int)$pdo->lastInsertId();
        $createdUserIds[$i] = $uid;
        
        if ($i < 6) {
            echo "  Created {$nameOrder[$i]} (user=$uid, rank={$rankOrder[$i]}, referred_by=$prevUserId)\n";
        } else {
            echo "  Created {$nameOrder[$i]} (user=$uid, rank={$rankOrder[$i]}, referred_by=null) - TOP\n";
        }
        
        $prevUserId = $uid;
    }
}

echo "\n  Upline chain (top→bottom): ";
for ($i = 0; $i < 7; $i++) {
    $uid = $createdUserIds[$i];
    echo "L$i (user=$uid)";
    if ($i < 6) echo " ← ";
}
echo "\n";
echo "  Top (L0, no upline): user={$createdUserIds[0]}\n";
echo "  Bottom (L6, seller): user={$createdUserIds[6]}\n";

// ── STEP 2: Create/Update associate records with correct rank levels ──
echo "\nSTEP 2: Setting associate levels...\n";

// createdUserIds[0] = TOP of chain (Site Manager)
// createdUserIds[6] = BOTTOM of chain (Associate = seller)
// So position 0 maps to rankOrder[6] (site_manager), position 6 maps to rankOrder[0] (associate)
for ($i = 0; $i < 7; $i++) {
    $uid = $createdUserIds[$i];
    $rank = $rankOrder[6 - $i]; // Inverted: top=L0=site_manager, bottom=L6=associate
    
    // Check if associate record exists
    $stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id = ?");
    $stmt->execute([$uid]);
    $assoc = $stmt->fetch();
    
    if ($assoc) {
        // Update level
        $stmt = $pdo->prepare("UPDATE associates SET level = ? WHERE user_id = ?");
        $stmt->execute([$rank, $uid]);
        echo "  Updated assoc user=$uid level=$rank\n";
    } else {
        // Create new associate record
        $stmt = $pdo->prepare("INSERT INTO associates (user_id, level, status, created_at) VALUES (?, ?, 'active', NOW())");
        $stmt->execute([$uid, $rank]);
        $assocId = (int)$pdo->lastInsertId();
        echo "  Created assoc id=$assocId user=$uid level=$rank\n";
    }
    
    // Also update mlm_profiles
    $stmt = $pdo->prepare("SELECT id FROM mlm_profiles WHERE user_id = ?");
    $stmt->execute([$uid]);
    $prof = $stmt->fetch();
    if ($prof) {
        $stmt = $pdo->prepare("UPDATE mlm_profiles SET current_level = ? WHERE user_id = ?");
        $stmt->execute([$rank, $uid]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO mlm_profiles (user_id, current_level, lifetime_sales, total_commission, created_at) VALUES (?, ?, 0, 0, NOW())");
        $stmt->execute([$uid, $rank]);
    }
}

// ── STEP 3: Create a booking for the L6 (Associate) user ──
echo "\nSTEP 3: Creating test booking...\n";

$bottomUserId = $createdUserIds[6]; // L6 = Associate (5%)
// Find or create associate record for bottom user
$stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id = ?");
$stmt->execute([$bottomUserId]);
$bottomAssoc = $stmt->fetch();
$bottomAssocId = (int)$bottomAssoc['id'];

// Find a plot that's not already booked
$plot = $pdo->query("SELECT id FROM plots WHERE id NOT IN (SELECT plot_id FROM plot_bookings WHERE plot_id IS NOT NULL) ORDER BY RAND() LIMIT 1")->fetch();
if (!$plot) {
    echo "  ERROR: No available plots!\n";
    exit(1);
}
$plotId = (int)$plot['id'];

// Create a plot booking with ₹25,00,000 agreement value
$bookingAmount = 2500000;
$bookingNumber = 'APS-BK-DEEP-' . date('YmdHis');
$stmt = $pdo->prepare("INSERT INTO plot_bookings (plot_id, customer_id, booking_number, booking_date, total_plot_value, booking_amount, agreement_value, status, associate_id, channel, created_at) VALUES (?, 3, ?, CURDATE(), ?, ?, ?, 'emi_active', ?, 'associate', NOW())");
$stmt->execute([$plotId, $bookingNumber, $bookingAmount, $bookingAmount, $bookingAmount, $bottomAssocId]);
$bookingId = (int)$pdo->lastInsertId();
echo "  Created booking #$bookingId: plot=$plotId, value=₹$bookingAmount, assoc=$bottomAssocId (user=$bottomUserId)\n";

// ── STEP 4: Clear old commissions for this booking (if any) ──
$pdo->prepare("DELETE FROM mlm_commission_ledger WHERE booking_id = ?")->execute([$bookingId]);
echo "  Cleared old ledger entries for booking #$bookingId\n";

// ── STEP 5: Run commission calculation ──
echo "\nSTEP 4: Running commission calculation...\n";

$engine = new \App\Services\MLM\MLMCommissionEngine($pdo);
$result = $engine->calculateBookingCommission($bookingId);

echo "\n═══════════════════════════════════════════════════════════\n";
echo "COMMISSION RESULT FOR BOOKING #$bookingId (₹" . number_format($bookingAmount) . ")\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$totalPct = 0;
$totalAmt = 0;
$expectedPct = [5.0, 2.0, 3.0, 2.0, 3.0, 3.0, 2.0]; // Expected differential breakdown

echo sprintf("%-4s %-25s %-18s %-8s %-8s %-12s\n", "Lvl", "User", "Rank", "Rate%", "Diff%", "Amount");
echo str_repeat("─", 75) . "\n";

foreach ($result['entries'] as $entry) {
    $lvl = $entry['level'];
    $uid = $entry['beneficiary_user_id'];
    
    // Get user name and rank
    $uStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $uStmt->execute([$uid]);
    $uRow = $uStmt->fetch();
    $name = $uRow ? $uRow['name'] : "user-$uid";
    
    $rank = $rankOrder[$lvl] ?? 'unknown';
    $pct = $entry['pct'];
    $amt = $entry['amount'];
    
    $totalPct += $pct;
    $totalAmt += $amt;
    
    $status = '';
    if (isset($expectedPct[$lvl]) && abs($pct - $expectedPct[$lvl]) < 0.01) {
        $status = ' ✓';
    } else {
        $status = ' ✗ EXPECTED ' . ($expectedPct[$lvl] ?? '?');
    }
    
    echo sprintf("L%d   %-25s %-18s %6.1f%%  %5.1f%%  ₹%s%s\n",
        $lvl,
        substr($name, 0, 25),
        $rank,
        $pct,
        $pct,
        number_format($amt, 0),
        $status
    );
}

echo str_repeat("─", 75) . "\n";
echo sprintf("%-4s %-25s %-18s %6.1f%%  %5.1f%%  ₹%s\n",
    '', '', 'TOTAL', $totalPct, $totalPct, number_format($totalAmt, 0));

echo "\n═══════════════════════════════════════════════════════════\n";
echo "VERIFICATION\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  Total %: " . number_format($totalPct, 2) . "% (cap: 20%)\n";
echo "  Total ₹: " . number_format($totalAmt, 0) . "\n";
echo "  Expected ₹: " . number_format($bookingAmount * 0.20, 0) . " (20% of ₹" . number_format($bookingAmount) . ")\n";
echo "  Entries created: " . count($result['created_ids']) . "\n";
echo "  Cap enforced: " . ($totalPct <= 20.0 ? 'YES ✓' : 'NO ✗') . "\n";

// ── STEP 6: Verify each level's differential ──
echo "\n═══════════════════════════════════════════════════════════\n";
echo "DIFFERENTIAL MODEL VERIFICATION\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  Level 6 (Associate)     → gets 5.0% direct = ₹" . number_format($bookingAmount * 0.05) . "\n";
echo "  Level 5 (Sr Associate)  → gets 7%-5% = 2.0% = ₹" . number_format($bookingAmount * 0.02) . "\n";
echo "  Level 4 (BDM)           → gets 10%-7% = 3.0% = ₹" . number_format($bookingAmount * 0.03) . "\n";
echo "  Level 3 (Sr BDM)        → gets 12%-10% = 2.0% = ₹" . number_format($bookingAmount * 0.02) . "\n";
echo "  Level 2 (VP)            → gets 15%-12% = 3.0% = ₹" . number_format($bookingAmount * 0.03) . "\n";
echo "  Level 1 (President)     → gets 18%-15% = 3.0% = ₹" . number_format($bookingAmount * 0.03) . "\n";
echo "  Level 0 (Site Manager)  → gets 20%-18% = 2.0% = ₹" . number_format($bookingAmount * 0.02) . "\n";
echo "  ──────────────────────────────────────────────────\n";
echo "  TOTAL                   → 20.0% = ₹" . number_format($bookingAmount * 0.20) . "\n";

$allPass = true;
foreach ($result['entries'] as $entry) {
    $lvl = $entry['level'];
    if (isset($expectedPct[$lvl]) && abs($entry['pct'] - $expectedPct[$lvl]) >= 0.01) {
        $allPass = false;
        echo "\n  MISMATCH at L$lvl: got {$entry['pct']}%, expected {$expectedPct[$lvl]}%\n";
    }
}

echo "\n" . ($allPass ? "ALL LEVELS MATCH EXPECTED DIFFERENTIALS ✓" : "SOME LEVELS DO NOT MATCH ✗") . "\n";
echo "RESULT: " . ($result['total'] > 0 ? "COMMISSION GENERATED ✓" : "NO COMMISSION ✗") . "\n";
