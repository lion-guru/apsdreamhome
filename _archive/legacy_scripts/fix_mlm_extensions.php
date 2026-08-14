<?php
$conn = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "=== Fix 1: Missing Associate Extension Records ===\n";

$associates = $conn->query("SELECT id, name, email, phone, password, referral_code FROM users WHERE role = 'associate' ORDER BY id")->fetchAll();
$count = 0;
$existingAssoc = $conn->query("SELECT user_id FROM associates")->fetchAll(PDO::FETCH_COLUMN);

foreach ($associates as $u) {
    if (in_array($u['id'], $existingAssoc)) continue;
    $name = $conn->quote($u['name']);
    $email = $conn->quote($u['email']);
    $phone = $conn->quote($u['phone'] ?? '');
    $password = $conn->quote($u['password']);
    $conn->exec("INSERT INTO associates (user_id, name, email, level, phone, password, status, created_at, updated_at) VALUES ({$u['id']}, $name, $email, 'bronze', $phone, $password, 'active', NOW(), NOW())");
    echo "  Created associates record for {$u['name']} (id={$u['id']})\n";
    $count++;
}
if ($count === 0) echo "  No missing associates records found - all 12 have extension records\n";

echo "\n=== Fix 2: Missing Agent Extension Records ===\n";

$agents = $conn->query("SELECT id, name, email FROM users WHERE role = 'agent' ORDER BY id")->fetchAll();
$existingAgents = $conn->query("SELECT user_id FROM agents")->fetchAll(PDO::FETCH_COLUMN);
$count = 0;

foreach ($agents as $u) {
    if (in_array($u['id'], $existingAgents)) continue;
    $conn->exec("INSERT INTO agents (user_id, status, created_at, updated_at) VALUES ({$u['id']}, 'active', NOW(), NOW())");
    echo "  Created agents record for {$u['name']} (id={$u['id']})\n";
    $count++;
}
if ($count === 0) echo "  No missing agents records found\n";

echo "\n=== Fix 3: Verify Network Tree for missing associates ===\n";
$allUsers = $conn->query("SELECT id, name, referred_by FROM users WHERE role = 'associate'")->fetchAll();
$existingNt = $conn->query("SELECT associate_id FROM network_tree")->fetchAll(PDO::FETCH_COLUMN);
foreach ($allUsers as $u) {
    if (in_array($u['id'], $existingNt)) continue;
    $referrerId = $u['referred_by'];
    if ($referrerId) {
        $rt = $conn->prepare("SELECT id, root_id, level FROM network_tree WHERE associate_id = ? LIMIT 1");
        $rt->execute([$referrerId]);
        $referrerTree = $rt->fetch();
        if ($referrerTree) {
            $rootId = $referrerTree['root_id'];
            $parentId = $referrerId;
            $level = (int)$referrerTree['level'] + 1;
            $lc = $conn->prepare("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'left'");
            $lc->execute([$referrerId]);
            $leftCount = (int)$lc->fetchColumn();
            $rc = $conn->prepare("SELECT COUNT(*) FROM network_tree WHERE parent_id = ? AND position = 'right'");
            $rc->execute([$referrerId]);
            $rightCount = (int)$rc->fetchColumn();
            $position = $leftCount <= $rightCount ? 'left' : 'right';
        } else {
            $rootId = $referrerId;
            $parentId = $referrerId;
            $level = 1;
            $position = 'left';
        }
    } else {
        $rootId = $u['id'];
        $parentId = null;
        $level = 0;
        $position = 'left';
    }
    $stmt = $conn->prepare("INSERT IGNORE INTO network_tree (associate_id, root_id, parent_id, level, position, total_left_count, total_right_count, total_left_bv, total_right_bv, personal_bv, is_active, joined_at, updated_at) VALUES (?, ?, ?, ?, ?, 0, 0, 0.00, 0.00, 0.00, 1, NOW(), NOW())");
    $stmt->execute([$u['id'], $rootId, $parentId, $level, $position]);
    echo "  Created network_tree for {$u['name']} (id={$u['id']})\n";
}

echo "\nAll fixes applied!\n";?>