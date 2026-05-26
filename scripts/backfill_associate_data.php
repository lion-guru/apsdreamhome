<?php
$db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$associates = $db->query("SELECT id, name, email, referral_code, referred_by, user_type, role FROM users WHERE user_type = 'associate' OR role = 'associate' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($associates) . " associates\n";

// 1. Fix role
$db->exec("UPDATE users SET role = 'associate' WHERE user_type = 'associate' AND role = 'user'");
echo "Fixed role for user-type associates\n";

// 2. Generate referral_codes
$stmt = $db->prepare("UPDATE users SET referral_code = ? WHERE id = ? AND (referral_code IS NULL OR referral_code = '')");
foreach ($associates as $a) {
    if (empty($a['referral_code'])) {
        $code = strtoupper(substr($a['name'], 0, 3)) . date('ymd') . rand(100, 999);
        $stmt->execute([$code, $a['id']]);
        echo "  Set referral_code for {$a['name']} (id={$a['id']}) -> $code\n";
    }
}

// 3. Create mlm_profiles
$pstmt = $db->prepare("INSERT IGNORE INTO mlm_profiles (user_id, referral_code, sponsor_user_id, user_type, current_level, total_team_size, direct_referrals, status, created_at, updated_at) VALUES (?, ?, ?, 'associate', 'Associate', 0, 0, 'active', NOW(), NOW())");
foreach ($associates as $a) {
    $check = $db->prepare("SELECT id FROM mlm_profiles WHERE user_id = ?");
    $check->execute([$a['id']]);
    if (!$check->fetch()) {
        $sponsor = $a['referred_by'] ?? null;
        $rc = $a['referral_code'] ?? strtoupper(substr($a['name'], 0, 3)) . date('ymd') . rand(100, 999);
        $pstmt->execute([$a['id'], $rc, $sponsor]);
        echo "  Created mlm_profile for {$a['name']} (id={$a['id']})\n";
    }
}

// 4. Create wallet_points
$wstmt = $db->prepare("INSERT IGNORE INTO wallet_points (user_id, points_balance, total_earned, total_used, referral_earnings, commission_earnings, bonus_earnings, status, created_at, updated_at) VALUES (?, 0, 0, 0, 0, 0, 0, 'active', NOW(), NOW())");
foreach ($associates as $a) {
    $check = $db->prepare("SELECT id FROM wallet_points WHERE user_id = ?");
    $check->execute([$a['id']]);
    if (!$check->fetch()) {
        $wstmt->execute([$a['id']]);
        echo "  Created wallet_points for {$a['name']} (id={$a['id']})\n";
    }
}

// 5. Create network_tree entries
$ntstmt = $db->prepare("INSERT IGNORE INTO network_tree (associate_id, root_id, parent_id, level, position) VALUES (?, ?, ?, ?, ?)");
foreach ($associates as $a) {
    $check = $db->prepare("SELECT id FROM network_tree WHERE associate_id = ?");
    $check->execute([$a['id']]);
    if ($check->fetch()) continue;
    
    $referrerId = $a['referred_by'];
    if ($referrerId) {
        $rt = $db->prepare("SELECT id, root_id, level FROM network_tree WHERE associate_id = ? LIMIT 1");
        $rt->execute([$referrerId]);
        $referrerTree = $rt->fetch(PDO::FETCH_ASSOC);
        if ($referrerTree) {
            $rootId = $referrerTree['root_id'];
            $parentId = $referrerId;
            $level = $referrerTree['level'] + 1;
            $lc = $db->prepare("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'left'");
            $lc->execute([$referrerId]);
            $leftCount = (int)$lc->fetchColumn();
            $rc = $db->prepare("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'right'");
            $rc->execute([$referrerId]);
            $rightCount = (int)$rc->fetchColumn();
            $position = $leftCount <= $rightCount ? 'left' : 'right';
        } else {
            $rootId = $a['id'];
            $parentId = null;
            $level = 0;
            $position = 'left';
        }
    } else {
        $rootId = $a['id'];
        $parentId = null;
        $level = 0;
        $position = 'left';
    }
    $ntstmt->execute([$a['id'], $rootId, $parentId, $level, $position]);
    echo "  Created network_tree for {$a['name']} (id={$a['id']})\n";
}

echo "\nAll done!\n";
