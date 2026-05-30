<?php
$conn = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo "=== Associate Registration Integrity Check ===\n\n";

$associates = $conn->query("SELECT id, name, email, referred_by FROM users WHERE role = 'associate' ORDER BY id")->fetchAll();
echo "Total associate users: " . count($associates) . "\n\n";

$passed = 0;
$failed = 0;

foreach ($associates as $u) {
    $uid = $u['id'];
    $name = $u['name'];
    echo "--- {$name} (ID: {$uid}) ---\n";

    $assocCheck = $conn->prepare("SELECT id FROM associates WHERE user_id = ?");
    $assocCheck->execute([$uid]);
    $hasAssoc = (bool)$assocCheck->fetch();
    echo "  associates record: " . ($hasAssoc ? "PASS" : "FAIL") . "\n";
    if ($hasAssoc) $passed++; else $failed++;

    $profileCheck = $conn->prepare("SELECT id, sponsor_user_id FROM mlm_profiles WHERE user_id = ?");
    $profileCheck->execute([$uid]);
    $profile = $profileCheck->fetch();
    $hasProfile = (bool)$profile;
    echo "  mlm_profiles record: " . ($hasProfile ? "PASS" : "FAIL") . "\n";
    if ($hasProfile) $passed++; else $failed++;
    if ($profile && $u['referred_by']) {
        echo "    sponsor_user_id: {$profile['sponsor_user_id']} (expected {$u['referred_by']}) - " . ($profile['sponsor_user_id'] == $u['referred_by'] ? "MATCH" : "MISMATCH") . "\n";
    }

    $walletCheck = $conn->prepare("SELECT id, points_balance FROM wallet_points WHERE user_id = ?");
    $walletCheck->execute([$uid]);
    $hasWallet = (bool)$walletCheck->fetch();
    echo "  wallet_points record: " . ($hasWallet ? "PASS" : "FAIL") . "\n";
    if ($hasWallet) $passed++; else $failed++;

    $treeCheck = $conn->prepare("SELECT id, level FROM network_tree WHERE associate_id = ?");
    $treeCheck->execute([$uid]);
    $hasTree = (bool)$treeCheck->fetch();
    echo "  network_tree record: " . ($hasTree ? "PASS" : "FAIL") . "\n";
    if ($hasTree) $passed++; else $failed++;

    echo "\n";
}

echo "=== Summary ===\n";
echo "Passed: {$passed}/" . ($passed + $failed) . "\n";
echo "Failed: {$failed}/" . ($passed + $failed) . "\n";
echo ($failed === 0 ? "\nAll checks passed!" : "\nSome checks failed - run scripts/fix_mlm_extensions.php to repair.") . "\n";
