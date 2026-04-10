<?php
// Check Wallet System Tables
$pdo = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = [
    'wallet_points',
    'wallet_transactions',
    'referral_rewards',
    'wallet_emi_transfers',
    'wallet_configuration',
    'withdrawal_requests',
    'user_bank_accounts',
    'emi_schedules',
    'emi_payments'
];

echo "Checking Wallet System Tables...\n\n";

foreach ($tables as $table) {
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
    echo $exists ? "✅ $table exists\n" : "❌ $table missing\n";
}

echo "\nDone.\n";
