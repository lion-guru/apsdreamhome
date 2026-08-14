<?php
$root = dirname(__DIR__);
define('APP_ROOT', $root);
require_once APP_ROOT . '/app/Core/Autoloader.php';
\App\Core\Autoloader::getInstance()->register();

$config = require APP_ROOT . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Clean first
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id IN (1001,1002,1003,1004,1005,1006) OR source_user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM plot_bookings WHERE id IN (99901, 99902, 99903, 99904)");
$pdo->exec("DELETE FROM leads WHERE email IN ('test_cust1@example.com', 'test_cust2@example.com', 'test_cust3@example.com')");
$pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN (SELECT id FROM associates WHERE user_id IN (1001,1002,1003,1004,1005,1006))");
$pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM associates WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM users WHERE id IN (1001,1002,1003,1004,1005,1006,2001,2002,2003)");

// Seed users
foreach ([[1001,'telecaller'],[1002,'telecaller'],[1003,'telecaller'],[1004,'associate'],[1005,'associate'],[1006,'associate'],[2001,'customer'],[2002,'customer'],[2003,'customer']] as [$id,$role]) {
    $pdo->prepare("INSERT INTO users (id, name, email, phone, password, role, onboarding_track, status, created_at) VALUES (?, ?, ?, '9876543210', 'hashed', ?, ?, 'active', NOW())")->execute([$id, "User $id", "user{$id}@example.com", $role, $role]);
}

// Seed associates
foreach ([[1001,'telecaller',1002],[1002,'telecaller',1003],[1003,'telecaller',null],[1004,'mlm',null],[1005,'mlm',null],[1006,'mlm',null]] as [$uid,$track,$parent]) {
    $pdo->prepare("INSERT INTO associates (user_id, status, agent_track, telecaller_salary, telecaller_incentive_rate, telecaller_sqft_rate, telecaller_parent_id, created_at) VALUES (?, 'active', ?, 0, 0, 0, ?, NOW())")->execute([$uid, $track, $parent]);
}

// Seed mlm_profiles
foreach ([[1004,1005,'Associate'],[1005,1006,'Associate'],[1006,1,'Associate']] as [$uid,$spon,$level]) {
    $pdo->prepare("INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, lifetime_sales, status) VALUES (?, ?, ?, 'associate', ?, 100000, 'active')")->execute([$uid, "ref$uid", $spon, $level]);
}

// Network tree
$pdo->exec("INSERT INTO mlm_network_tree (associate_id, parent_id, sponsor_id, level, position) VALUES (1004, 1005, 1005, 1, 'left')");
$pdo->exec("INSERT INTO mlm_network_tree (associate_id, parent_id, sponsor_id, level, position) VALUES (1005, 1006, 1006, 2, 'left')");

// Seed leads
$pdo->prepare("INSERT INTO leads (name, email, phone, assigned_to, status, created_at) VALUES (?, ?, ?, ?, 'converted', NOW())")->execute(['Cust1', 'test_cust1@example.com', '9876543210', 1001]);

// Seed plot
$pdo->exec("INSERT INTO plots (id, colony_id, plot_number, area_sqft, price_per_sqft, total_price, status) VALUES (1, 2, 'T-01', 1200, 1000, 1200000, 'booked')");

// Lookup associate IDs
$assocIdMap = [];
foreach ($pdo->query("SELECT id, user_id FROM associates WHERE user_id IN (1001,1004,1005,1006)")->fetchAll(PDO::FETCH_ASSOC) as $ar) {
    $assocIdMap[$ar['user_id']] = $ar['id'];
}
$a4 = $assocIdMap[1004];
$a5 = $assocIdMap[1005];
echo "User 1004 -> associates.id = $a4\n";
echo "User 1005 -> associates.id = $a5\n\n";

// Seed booking 99901
$pdo->prepare("INSERT INTO plot_bookings (id, plot_id, associate_id, customer_id, booking_number, booking_date, booking_amount, total_plot_value, agreement_value, status, created_at) VALUES (99901, 1, ?, 2001, 'TC-DEBUG-001', CURDATE(), 50000, 1000000, 1000000, 'token_paid', NOW())")->execute([$a4]);

echo "=== Simulating verifyUplineSideVolume(1005, '2026-06') ===\n\n";

// Step 1: associateId for user 1005
$stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id = ? LIMIT 1");
$stmt->execute([1005]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$associateId = $row ? (int)$row['id'] : 1005;
echo "Step 1: associateId for user 1005 = $associateId\n";

// Step 2: personal sales
$stmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS personal_sales FROM plot_bookings pb WHERE pb.associate_id = ? AND DATE_FORMAT(pb.created_at, '%Y-%m') = ? AND pb.status NOT IN ('cancelled', 'refunded')");
$stmt->execute([$associateId, '2026-06']);
$personalSales = (float)$stmt->fetchColumn();
echo "Step 2: personalSales (user 1005's associate_id) = $personalSales\n";

// Step 3: direct children
$stmt = $pdo->prepare("SELECT associate_id AS user_id FROM mlm_network_tree WHERE parent_id = ?");
$stmt->execute([1005]);
$directChildren = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Step 3: directChildren of user 1005 = " . json_encode($directChildren) . "\n";

// Step 4: for each child, get downline and leg volume
foreach ($directChildren as $childUserId) {
    echo "\n--- Child $childUserId ---\n";
    
    // getDownlineUserIds
    $downline = [];
    $toProcess = [$childUserId];
    while (!empty($toProcess)) {
        $batch = implode(',', array_fill(0, count($toProcess), '?'));
        $stmt = $pdo->prepare("SELECT associate_id FROM mlm_network_tree WHERE parent_id IN ($batch)");
        $stmt->execute($toProcess);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $toProcess = [];
        foreach ($children as $cId) {
            $cId = (int)$cId;
            if (!in_array($cId, $downline) && $cId !== $childUserId) {
                $downline[] = $cId;
                $toProcess[] = $cId;
            }
        }
    }
    echo "  downline of $childUserId = " . json_encode($downline) . "\n";
    
    $descendents = $downline;
    $descendents[] = $childUserId;
    echo "  descendents (with child) = " . json_encode($descendents) . "\n";
    
    $inClause = implode(',', array_fill(0, count($descendents), '?'));
    $stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id IN ($inClause)");
    $stmt->execute($descendents);
    $assocIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  assocIds = " . json_encode($assocIds) . "\n";
    
    if (!empty($assocIds)) {
        $inAssocClause = implode(',', array_fill(0, count($assocIds), '?'));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS volume FROM plot_bookings pb WHERE pb.associate_id IN ($inAssocClause) AND DATE_FORMAT(pb.created_at, '%Y-%m') = ? AND pb.status NOT IN ('cancelled', 'refunded')");
        $params = array_merge($assocIds, ['2026-06']);
        $stmt->execute($params);
        $volume = (float)$stmt->fetchColumn();
        echo "  leg volume = $volume\n";
    } else {
        echo "  no assocIds â†’ leg volume = 0\n";
    }
}

// Step 5: final calculation
echo "\n=== Final side-volume calculation ===\n";
// Re-run the full logic
$legVolumes = [];
foreach ($directChildren as $childUserId) {
    $downline = [];
    $toProcess = [$childUserId];
    while (!empty($toProcess)) {
        $batch = implode(',', array_fill(0, count($toProcess), '?'));
        $stmt = $pdo->prepare("SELECT associate_id FROM mlm_network_tree WHERE parent_id IN ($batch)");
        $stmt->execute($toProcess);
        $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $toProcess = [];
        foreach ($children as $cId) {
            $cId = (int)$cId;
            if (!in_array($cId, $downline) && $cId !== $childUserId) {
                $downline[] = $cId;
                $toProcess[] = $cId;
            }
        }
    }
    $descendents = $downline;
    $descendents[] = $childUserId;
    
    $inClause = implode(',', array_fill(0, count($descendents), '?'));
    $stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id IN ($inClause)");
    $stmt->execute($descendents);
    $assocIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($assocIds)) {
        $legVolumes[] = 0.0;
    } else {
        $inAssocClause = implode(',', array_fill(0, count($assocIds), '?'));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(pb.agreement_value, pb.total_plot_value, 0)), 0) AS volume FROM plot_bookings pb WHERE pb.associate_id IN ($inAssocClause) AND DATE_FORMAT(pb.created_at, '%Y-%m') = ? AND pb.status NOT IN ('cancelled', 'refunded')");
        $params = array_merge($assocIds, ['2026-06']);
        $stmt->execute($params);
        $legVolumes[] = (float)$stmt->fetchColumn();
    }
}
echo "legVolumes = " . json_encode($legVolumes) . "\n";
echo "personalSales = $personalSales\n";

if (empty($legVolumes)) {
    $sideVolume = $personalSales;
} else {
    rsort($legVolumes);
    echo "After rsort: " . json_encode($legVolumes) . "\n";
    array_shift($legVolumes);
    echo "After array_shift: " . json_encode($legVolumes) . "\n";
    $sideVolume = $personalSales + array_sum($legVolumes);
}
echo "sideVolume = $sideVolume\n";
echo "return: " . ($sideVolume >= 50000.0 ? 'TRUE (override PAID)' : 'FALSE (override NOT paid)') . "\n";

// Cleanup
$pdo->exec("DELETE FROM mlm_commission_ledger WHERE beneficiary_user_id IN (1001,1002,1003,1004,1005,1006) OR source_user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM plot_bookings WHERE id IN (99901, 99902, 99903, 99904)");
$pdo->exec("DELETE FROM plots WHERE id = 1");
$pdo->exec("DELETE FROM leads WHERE email IN ('test_cust1@example.com', 'test_cust2@example.com', 'test_cust3@example.com')");
$pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN (SELECT id FROM associates WHERE user_id IN (1001,1002,1003,1004,1005,1006))");
$pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM associates WHERE user_id IN (1001,1002,1003,1004,1005,1006)");
$pdo->exec("DELETE FROM users WHERE id IN (1001,1002,1003,1004,1005,1006,2001,2002,2003)");?>