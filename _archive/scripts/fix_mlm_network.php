<?php
/**
 * Fix MLM network tree data quality
 */
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== FIXING MLM NETWORK TREE ===\n\n";

// 1. Clean empty rows
$stmt = $db->prepare("DELETE FROM mlm_network_tree WHERE associate_id = '' OR associate_id IS NULL");
$stmt->execute();
echo "Deleted " . $stmt->rowCount() . " empty rows from mlm_network_tree\n";

// 2. Rebuild network tree from users.referred_by
echo "Rebuilding network tree from users.referred_by...\n";

$users = $db->fetchAll("SELECT id, referred_by FROM users WHERE role = 'associate' AND referred_by IS NOT NULL AND referred_by != ''");
$count = 0;
foreach ($users as $u) {
    $associateId = $u['id'];
    $sponsorId = $u['referred_by'];
    
    // Get sponsor's level
    $parent = $db->fetch("SELECT level FROM mlm_network_tree WHERE associate_id = ? ORDER BY level DESC LIMIT 1", [$sponsorId]);
    $level = $parent ? (int)$parent['level'] + 1 : 1;
    
    // Insert/update
    $existing = $db->fetch("SELECT id FROM mlm_network_tree WHERE associate_id = ?", [$associateId]);
    if ($existing) {
        $db->execute("UPDATE mlm_network_tree SET sponsor_id = ?, parent_id = ?, level = ? WHERE associate_id = ?", [$sponsorId, $sponsorId, $level, $associateId]);
    } else {
        $db->execute("INSERT INTO mlm_network_tree (associate_id, sponsor_id, parent_id, level) VALUES (?, ?, ?, ?)", [$associateId, $sponsorId, $sponsorId, $level]);
    }
    $count++;
}
echo "Rebuilt $count network tree entries\n";

// 3. Fix associates table - ensure referral_code populated
echo "\nFixing associates.referral_code...\n";
$assocFix = $db->fetchAll("SELECT a.id, a.user_id, u.referral_code FROM associates a JOIN users u ON a.user_id = u.id WHERE a.referral_code = '' OR a.referral_code IS NULL");
$fixed = 0;
foreach ($assocFix as $a) {
    $db->execute("UPDATE associates SET referral_code = ? WHERE id = ?", [$a['referral_code'], $a['id']]);
    $fixed++;
}
echo "Fixed $fixed associate referral codes\n";

// 4. Create missing wallets for associates
echo "\nCreating missing wallets...\n";
$associates = $db->fetchAll("SELECT u.id FROM users u WHERE u.role = 'associate' AND NOT EXISTS (SELECT 1 FROM user_wallets w WHERE w.user_id = u.id AND w.user_type = 'associate')");
$walletCount = 0;
foreach ($associates as $a) {
    $db->execute("INSERT INTO user_wallets (user_id, user_type, balance, total_credited, total_debited, is_active) VALUES (?, 'associate', 0, 0, 0, 1)", [$a['id']]);
    $walletCount++;
}
echo "Created $walletCount wallets\n";

// 5. Create mlm_profiles for associates missing them
echo "\nCreating missing mlm_profiles...\n";
$missingProfiles = $db->fetchAll("SELECT u.id, u.referral_code, u.referred_by FROM users u WHERE u.role = 'associate' AND NOT EXISTS (SELECT 1 FROM mlm_profiles p WHERE p.user_id = u.id)");
$profileCount = 0;
foreach ($missingProfiles as $u) {
    $sponsorCode = null;
    if ($u['referred_by']) {
        $sponsor = $db->fetch("SELECT referral_code FROM users WHERE id = ?", [$u['referred_by']]);
        $sponsorCode = $sponsor['referral_code'] ?? null;
    }
    $db->execute("
        INSERT INTO mlm_profiles (user_id, referral_code, sponsor_user_id, sponsor_code, user_type, current_level, plan_mode, total_team_size, direct_referrals, lifetime_sales, total_commission, pending_commission, verification_status, status)
        VALUES (?, ?, ?, ?, 'associate', 'associate', 'rank', 0, 0, 0, 0, 0, 'pending', 'active')
    ", [$u['id'], $u['referral_code'], $u['referred_by'], $sponsorCode]);
    $profileCount++;
}
echo "Created $profileCount mlm_profiles\n";

echo "\n=== DONE ===\n";?>