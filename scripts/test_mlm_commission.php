<?php
/**
 * Test MLM hierarchy and commission distribution
 */
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== MLM HIERARCHY CHECK ===\n\n";

// 1. Check users with associate role
$associates = $db->fetchAll("SELECT id, name, email, referral_code, referred_by, role FROM users WHERE role = 'associate' ORDER BY id");
echo "Associates in users table: " . count($associates) . "\n";
foreach ($associates as $a) {
    echo "  ID: {$a['id']}, Name: {$a['name']}, Code: {$a['referral_code']}, Referred by: {$a['referred_by']}\n";
}

// 2. Check mlm_profiles
echo "\n=== MLM PROFILES ===\n";
$profiles = $db->fetchAll("SELECT user_id, referral_code, sponsor_user_id, current_level, total_team_size, direct_referrals, lifetime_sales, total_commission, pending_commission FROM mlm_profiles");
echo "MLM Profiles: " . count($profiles) . "\n";
foreach ($profiles as $p) {
    echo "  User: {$p['user_id']}, Code: {$p['referral_code']}, Sponsor: {$p['sponsor_user_id']}, Level: {$p['current_level']}, Team: {$p['total_team_size']}, Direct: {$p['direct_referrals']}, Sales: {$p['lifetime_sales']}, Total Comm: {$p['total_commission']}, Pending: {$p['pending_commission']}\n";
}

// 3. Check mlm_network_tree
echo "\n=== MLM NETWORK TREE ===\n";
$tree = $db->fetchAll("SELECT associate_id, sponsor_id, parent_id, level FROM mlm_network_tree ORDER BY level, associate_id");
echo "Network Tree entries: " . count($tree) . "\n";
foreach ($tree as $t) {
    echo "  Assoc: {$t['associate_id']}, Sponsor: {$t['sponsor_id']}, Parent: {$t['parent_id']}, Level: {$t['level']}\n";
}

// 4. Check associates table
echo "\n=== ASSOCIATES TABLE ===\n";
$assocExt = $db->fetchAll("SELECT id, user_id, referral_code, level, sponsor_id FROM associates");
echo "Associates extension: " . count($assocExt) . "\n";
foreach ($assocExt as $a) {
    echo "  ID: {$a['id']}, User: {$a['user_id']}, Code: {$a['referral_code']}, Level: {$a['level']}, Sponsor: {$a['sponsor_id']}\n";
}

// 5. Check commission ledger
echo "\n=== COMMISSION LEDGER ===\n";
$ledger = $db->fetchAll("SELECT * FROM mlm_commission_ledger ORDER BY created_at DESC LIMIT 20");
echo "Commission Ledger entries: " . count($ledger) . "\n";
foreach ($ledger as $l) {
    echo "  ID: {$l['id']}, Beneficiary: {$l['beneficiary_user_id']}, Source: {$l['source_user_id']}, Type: {$l['commission_type']}, Amount: {$l['amount']}, Status: {$l['status']}\n";
}

// 6. Check bookings
echo "\n=== BOOKINGS ===\n";
$bookings = $db->fetchAll("SELECT id, booking_number, customer_id, plot_id, total_plot_value, status FROM plot_bookings ORDER BY id DESC LIMIT 10");
echo "Recent bookings: " . count($bookings) . "\n";
foreach ($bookings as $b) {
    echo "  ID: {$b['id']}, Number: {$b['booking_number']}, Customer: {$b['customer_id']}, Plot: {$b['plot_id']}, Amount: {$b['total_plot_value']}, Status: {$b['status']}\n";
}

// 7. Check booking_commissions
echo "\n=== BOOKING COMMISSIONS ===\n";
$bcomm = $db->fetchAll("SELECT * FROM booking_commissions ORDER BY created_at DESC LIMIT 20");
echo "Booking Commissions: " . count($bcomm) . "\n";
foreach ($bcomm as $c) {
    echo "  ID: {$c['id']}, Booking: {$c['booking_id']}, Type: {$c['commission_type']}, User: {$c['beneficiary_user_id']}, Amount: {$c['amount']}, Rate: {$c['percent']}%\n";
}

// 8. Test upline walk for a booking
if (!empty($bookings)) {
    $testBooking = $bookings[0];
    echo "\n=== TEST UPLINE WALK FOR BOOKING {$testBooking['id']} ===\n";
    
    // Get the customer who booked
    $customerId = $testBooking['customer_id'];
    echo "Customer ID: $customerId\n";
    
    // Find their referrer (sponsor)
    $ref = $db->fetch("SELECT referred_by FROM users WHERE id = ?", [$customerId]);
    if ($ref && $ref['referred_by']) {
        echo "Direct referrer: {$ref['referred_by']}\n";
        
        // Walk upline
        $current = $ref['referred_by'];
        for ($level = 1; $level <= 3; $level++) {
            $parent = $db->fetch("SELECT id, name, role, referred_by FROM users WHERE id = ?", [$current]);
            if (!$parent || empty($parent['referred_by'])) break;
            echo "  Level $level: User {$parent['id']} ({$parent['name']}, {$parent['role']})\n";
            $current = $parent['referred_by'];
        }
    } else {
        echo "No referrer found\n";
    }
}

// 9. Check wallet balances
echo "\n=== USER WALLETS ===\n";
$wallets = $db->fetchAll("SELECT user_id, balance, total_credited, total_debited FROM user_wallets WHERE user_type = 'associate'");
echo "Associate wallets: " . count($wallets) . "\n";
foreach ($wallets as $w) {
    echo "  User: {$w['user_id']}, Balance: {$w['balance']}, Credited: {$w['total_credited']}, Debited: {$w['total_debited']}\n";
}

// 10. Check rank benefits
echo "\n=== RANK BENEFITS ===\n";
$ranks = $db->fetchAll("SELECT * FROM mlm_rank_benefits WHERE is_active = 1 ORDER BY rank_order");
echo "Active ranks: " . count($ranks) . "\n";
foreach ($ranks as $r) {
    echo "  {$r['rank_name']}: Direct={$r['direct_sale_pct']}%, L1={$r['l1_pct']}%, L2={$r['l2_pct']}%, L3={$r['l3_pct']}%, MinLegs={$r['min_leg_count']}, MinVol={$r['min_qualifying_volume']}\n";
}

echo "\n=== DONE ===\n";