<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== Testing Failing Endpoints ===\n\n";

// Test getMlmNetworkTree (genealogy)
echo "1. Testing getMlmNetworkTree (genealogy)...\n";
$stmt = $pdo->prepare("SELECT * FROM mlm_referrals WHERE referrer_id = ? LIMIT 5");
$stmt->execute([1]);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "   mlm_referrals table: " . count($referrals) . " records\n";

// Test getMlmBusinessBreakdown
echo "2. Testing getMlmBusinessBreakdown...\n";
$stmt = $pdo->prepare("SELECT * FROM mlm_payouts WHERE user_id = ? LIMIT 5");
$stmt->execute([1]);
$payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "   mlm_payouts table: " . count($payouts) . " records\n";

// Test payouts pending
echo "3. Testing payouts/pending...\n";
$stmt = $pdo->query("SHOW TABLES LIKE 'mlm_payouts'");
$exists = $stmt->fetch() !== false;
echo "   mlm_payouts table exists: " . ($exists ? "YES" : "NO") . "\n";

// Test site-visit slots
echo "4. Testing site-visit/slots...\n";
$stmt = $pdo->query("SHOW TABLES LIKE 'site_visits'");
$exists = $stmt->fetch() !== false;
echo "   site_visits table exists: " . ($exists ? "YES" : "NO") . "\n";

echo "\nDone!\n";
