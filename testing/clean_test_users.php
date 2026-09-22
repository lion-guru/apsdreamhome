<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stmt = $pdo->query("SELECT id FROM users WHERE email LIKE '%@apsdreamhome.test'");
$userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($userIds)) {
    $in = implode(',', array_map('intval', $userIds));
    $pdo->exec("DELETE FROM mlm_commission_ledger WHERE source_user_id IN ($in) OR beneficiary_user_id IN ($in)");
    $pdo->exec("DELETE FROM wallet_transactions WHERE user_id IN ($in) OR related_user_id IN ($in)");
    $pdo->exec("DELETE FROM user_wallets WHERE user_id IN ($in)");
    $pdo->exec("DELETE FROM mlm_profiles WHERE user_id IN ($in)");
    $pdo->exec("DELETE FROM mlm_network_tree WHERE associate_id IN ($in)");
    $pdo->exec("DELETE FROM network_tree WHERE associate_id IN ($in)");
    $pdo->exec("DELETE FROM associates WHERE user_id IN ($in)");
    $pdo->exec("DELETE FROM users WHERE id IN ($in)");
    echo "Successfully cleaned " . count($userIds) . " test users.\n";
} else {
    echo "No test users found to clean.\n";
}
