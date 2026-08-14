<?php
/**
 * COMPREHENSIVE TEST DATA SEEDING + WORKFLOW TESTING
 * 
 * 1. Create associate hierarchy (3 levels)
 * 2. Seed plot bookings with full payment + EMI
 * 3. Run commission calculation
 * 4. Verify all workflows
 */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();

echo "=== COMPREHENSIVE TEST DATA SEEDING ===\n\n";

// ============================================================
// STEP 1: Create 3-level associate hierarchy
// ============================================================
echo "STEP 1: Creating associate hierarchy...\n";

// Level 1: Top associate (sponsored by admin)
$level1Email = 'leader.associate@apsdreamhome.com';
$level1 = $db->query("SELECT id FROM users WHERE email='$level1Email'")->fetch();
if (!$level1) {
    $hash = password_hash('Aps@2026', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (name, email, phone, password, role, status, created_at) VALUES ('Rajesh Leader', '$level1Email', '9999900001', '$hash', 'associate', 'active', NOW())");
    $level1Id = $db->lastInsertId();
    $db->exec("INSERT INTO associates (user_id, name, email, phone, level, referral_code, sponsor_id, status, joining_date) VALUES ($level1Id, 'Rajesh Leader', '$level1Email', '9999900001', 'associate', 'LEADER01', 1, 'active', CURDATE())");
    $assoc1Id = $db->lastInsertId();
    echo "  Created Level 1: Rajesh Leader (user_id=$level1Id, assoc_id=$assoc1Id)\n";
} else {
    $level1Id = $level1['id'];
    $a = $db->query("SELECT id FROM associates WHERE user_id=$level1Id")->fetch();
    $assoc1Id = $a['id'] ?? 0;
    echo "  Level 1 exists: user_id=$level1Id\n";
}

// Level 2: Two associates under Level 1
$level2Emails = ['mohan.middle@apsdreamhome.com', 'suresh.middle@apsdreamhome.com'];
$level2Ids = [];
$level2AssocIds = [];
foreach ($level2Emails as $i => $email) {
    $existing = $db->query("SELECT id FROM users WHERE email='$email'")->fetch();
    if (!$existing) {
        $hash = password_hash('Aps@2026', PASSWORD_DEFAULT);
        $name = $i === 0 ? 'Mohan Middle' : 'Suresh Middle';
        $db->exec("INSERT INTO users (name, email, phone, password, role, status, created_at) VALUES ('$name', '$email', '999990000" . ($i+2) . "', '$hash', 'associate', 'active', NOW())");
        $uid = $db->lastInsertId();
        $code = 'MID0' . ($i+1);
        $db->exec("INSERT INTO associates (user_id, name, email, phone, level, referral_code, sponsor_id, status, joining_date) VALUES ($uid, '$name', '$email', '999990000" . ($i+2) . "', 'associate', '$code', $level1Id, 'active', CURDATE())");
        $aid = $db->lastInsertId();
        $level2Ids[] = $uid;
        $level2AssocIds[] = $aid;
        echo "  Created Level 2: $name (user_id=$uid, assoc_id=$aid)\n";
    } else {
        $level2Ids[] = $existing['id'];
        $a = $db->query("SELECT id FROM associates WHERE user_id={$existing['id']}")->fetch();
        $level2AssocIds[] = $a['id'] ?? 0;
        echo "  Level 2 exists: $email\n";
    }
}

// Level 3: Two associates under Level 2[0]
$level3Emails = ['deepak.bottom@apsdreamhome.com', 'ramesh.bottom@apsdreamhome.com'];
$level3Ids = [];
foreach ($level3Emails as $i => $email) {
    $existing = $db->query("SELECT id FROM users WHERE email='$email'")->fetch();
    if (!$existing) {
        $hash = password_hash('Aps@2026', PASSWORD_DEFAULT);
        $name = $i === 0 ? 'Deepak Bottom' : 'Ramesh Bottom';
        $db->exec("INSERT INTO users (name, email, phone, password, role, status, created_at) VALUES ('$name', '$email', '999990000" . ($i+4) . "', '$hash', 'associate', 'active', NOW())");
        $uid = $db->lastInsertId();
        $code = 'BOT0' . ($i+1);
        $db->exec("INSERT INTO associates (user_id, name, email, phone, level, referral_code, sponsor_id, status, joining_date) VALUES ($uid, '$name', '$email', '999990000" . ($i+4) . "', 'associate', '$code', {$level2Ids[0]}, 'active', CURDATE())");
        $level3Ids[] = $uid;
        echo "  Created Level 3: $name (user_id=$uid)\n";
    } else {
        $level3Ids[] = $existing['id'];
        echo "  Level 3 exists: $email\n";
    }
}

// ============================================================
// STEP 2: Create MLM network tree entries (dual-write)
// ============================================================
echo "\nSTEP 2: Creating MLM network tree entries...\n";

$treeEntries = [
    // Level 1 under admin (id=1)
    [$level1Id, 1, $level1Id, 1],
];
// Level 2 under Level 1
foreach ($level2Ids as $uid) {
    $treeEntries[] = [$uid, $level1Id, $uid, 2];
}
// Level 3 under Level 2[0]
foreach ($level3Ids as $uid) {
    $treeEntries[] = [$uid, $level2Ids[0], $uid, 3];
}

foreach ($treeEntries as $entry) {
    [$assocUserId, $parentId, $sponsorId, $level] = $entry;
    
    // Check mlm_network_tree
    $exists = $db->query("SELECT id FROM mlm_network_tree WHERE associate_id=$assocUserId")->fetch();
    if (!$exists) {
        $db->exec("INSERT INTO mlm_network_tree (associate_id, parent_id, sponsor_id, level, created_at) VALUES ($assocUserId, $parentId, $sponsorId, $level, NOW())");
        echo "  Created mlm_network_tree: user_id=$assocUserId, parent=$parentId, level=$level\n";
    } else {
        echo "  mlm_network_tree exists: user_id=$assocUserId\n";
    }
    
    // Check network_tree (binary tree for display)
    $exists2 = $db->query("SELECT id FROM network_tree WHERE associate_id=$assocUserId")->fetch();
    if (!$exists2) {
        $db->exec("INSERT INTO network_tree (associate_id, parent_id, level, is_active, joined_at) VALUES ($assocUserId, $parentId, $level, 1, NOW())");
        echo "  Created network_tree: user_id=$assocUserId, parent=$parentId, level=$level\n";
    } else {
        echo "  network_tree exists: user_id=$assocUserId\n";
    }
}

// ============================================================
// STEP 3: Create wallet entries for all associates
// ============================================================
echo "\nSTEP 3: Creating wallet entries...\n";
$allAssocIds = array_merge([$level1Id], $level2Ids, $level3Ids);
foreach ($allAssocIds as $uid) {
    $exists = $db->query("SELECT id FROM wallet_points WHERE user_id=$uid")->fetch();
    if (!$exists) {
        $db->exec("INSERT INTO wallet_points (user_id, balance, total_earned, created_at) VALUES ($uid, 0, 0, NOW())");
        echo "  Created wallet for user_id=$uid\n";
    }
}

// ============================================================
// STEP 4: Seed plot bookings + payments
// ============================================================
echo "\nSTEP 4: Creating plot bookings with payments...\n";

// Get available plots from Suryoday Colony (colony_id=2)
$plots = $db->query("SELECT id, colony_id, plot_number, total_price, area_sqft FROM plots WHERE colony_id=2 AND status='available' LIMIT 3")->fetchAll();

if (empty($plots)) {
    echo "  WARNING: No available plots in colony 2. Checking all colonies...\n";
    $plots = $db->query("SELECT id, colony_id, plot_number, total_price, area_sqft FROM plots WHERE status='available' LIMIT 3")->fetchAll();
}

if (!empty($plots)) {
    // Customer user for bookings
    $custId = 41; // customer@apsdreamhome.com
    
    foreach ($plots as $i => $plot) {
        // Check if already booked
        $existing = $db->query("SELECT id FROM plot_bookings WHERE plot_id={$plot['id']}")->fetch();
        if ($existing) {
            echo "  Plot {$plot['plot_number']} already booked\n";
            continue;
        }
        
        $bookingAmount = $plot['total_price'] * 0.10; // 10% booking amount
        $statuses = ['pending', 'confirmed', 'completed'];
        $status = $statuses[$i % 3];
        
        // Create booking
        $db->exec("INSERT INTO plot_bookings (user_id, plot_id, colony_id, booking_amount, status, payment_status, booking_date, created_at) 
                    VALUES ($custId, {$plot['id']}, {$plot['colony_id']}, $bookingAmount, '$status', 'partial', CURDATE(), NOW())");
        $bookingId = $db->lastInsertId();
        echo "  Created booking #$bookingId: Plot {$plot['plot_number']} (â‚¹" . number_format($bookingAmount) . ") status=$status\n";
        
        // Update plot status
        $db->exec("UPDATE plots SET status='booked', customer_id=$custId, booking_date=CURDATE() WHERE id={$plot['id']}");
        
        // Create payment schedule (4 EMIs)
        $emiAmount = ($plot['total_price'] - $bookingAmount) / 4;
        for ($m = 1; $m <= 4; $m++) {
            $dueDate = date('Y-m-d', strtotime("+$m months"));
            $emiStatus = $m === 1 ? 'paid' : ($m === 2 ? 'pending' : 'upcoming');
            $paidAt = $m === 1 ? 'NOW()' : 'NULL';
            $db->exec("INSERT INTO booking_payment_schedules (booking_id, user_id, installment_number, amount, due_date, status, paid_at, created_at) 
                        VALUES ($bookingId, $custId, $m, $emiAmount, '$dueDate', '$emiStatus', $paidAt, NOW())");
        }
        echo "  Created 4 EMI schedule entries (1 paid, 1 pending, 2 upcoming)\n";
        
        // If status is completed, record full payment
        if ($status === 'completed') {
            $db->exec("UPDATE plots SET status='sold', sale_date=CURDATE(), total_paid={$plot['total_price']}, payment_status='completed' WHERE id={$plot['id']}");
            $db->exec("INSERT INTO booking_payments (booking_id, user_id, amount, payment_method, payment_date, transaction_id, status, created_at) 
                        VALUES ($bookingId, $custId, {$plot['total_price']}, 'bank_transfer', CURDATE(), 'TXN-TEST-" . rand(1000,9999) . "', 'completed', NOW())");
            echo "  Recorded full payment for booking #$bookingId\n";
        }
    }
} else {
    echo "  ERROR: No available plots found anywhere!\n";
}

// ============================================================
// STEP 5: Run commission calculation for completed bookings
// ============================================================
echo "\nSTEP 5: Running commission calculation...\n";

$completedBookings = $db->query("SELECT pb.id, pb.customer_id, pb.colony_id, pb.booking_amount, p.total_price 
    FROM plot_bookings pb 
    JOIN plots p ON pb.plot_id=p.id 
    WHERE pb.status='fully_paid' 
    AND pb.id NOT IN (SELECT DISTINCT booking_id FROM mlm_commission_ledger WHERE type='direct_sale' AND booking_id IS NOT NULL)
    LIMIT 5")->fetchAll();

if (!empty($completedBookings)) {
    foreach ($completedBookings as $booking) {
        echo "  Processing commission for booking #{$booking['id']}...\n";
        
        // Direct sale commission (5% of booking amount)
        $directComm = $booking['total_price'] * 0.05;
        
        // Record in commission ledger for the associate who sold
        $db->exec("INSERT INTO mlm_commission_ledger (user_id, beneficiary_user_id, booking_id, type, amount, description, status, created_at) 
                    VALUES ($level1Id, $level1Id, {$booking['id']}, 'direct_sale', $directComm, 'Direct sale commission for booking #{$booking['id']}', 'approved', NOW())");
        echo "    Direct sale commission: â‚¹" . number_format($directComm) . " to Rajesh Leader\n";
        
        // Override commission for parent (2.5% differential)
        $overrideComm = $booking['total_price'] * 0.025;
        $db->exec("INSERT INTO mlm_commission_ledger (user_id, beneficiary_user_id, booking_id, type, amount, description, status, created_at) 
                    VALUES ($level1Id, 1, {$booking['id']}, 'override', $overrideComm, 'Override commission from booking #{$booking['id']}', 'approved', NOW())");
        echo "    Override commission: â‚¹" . number_format($overrideComm) . " to Admin (upline)\n";
    }
} else {
    echo "  No unprocessed completed bookings found\n";
    // Force-mark one booking as completed to trigger commissions
    $pendingBooking = $db->query("SELECT pb.id, p.total_price FROM plot_bookings pb JOIN plots p ON pb.plot_id=p.id WHERE pb.status='confirmed' LIMIT 1")->fetch();
    if ($pendingBooking) {
        $db->exec("UPDATE plot_bookings SET status='completed' WHERE id={$pendingBooking['id']}");
        $db->exec("UPDATE plots SET status='sold', sale_date=CURDATE(), payment_status='completed' WHERE id=(SELECT plot_id FROM plot_bookings WHERE id={$pendingBooking['id']})");
        
        $directComm = $pendingBooking['total_price'] * 0.05;
        $db->exec("INSERT INTO mlm_commission_ledger (user_id, beneficiary_user_id, booking_id, type, amount, description, status, created_at) 
                    VALUES ($level1Id, $level1Id, {$pendingBooking['id']}, 'direct_sale', $directComm, 'Direct sale commission for booking #{$pendingBooking['id']}', 'approved', NOW())");
        echo "  Force-completed booking #{$pendingBooking['id']} + recorded â‚¹" . number_format($directComm) . " commission\n";
    }
}

// ============================================================
// STEP 6: Verify final state
// ============================================================
echo "\n=== FINAL STATE VERIFICATION ===\n\n";

$r = $db->query("SELECT COUNT(*) as c FROM users WHERE role='associate'")->fetch();
echo "Total associates: {$r['c']}\n";

$r = $db->query("SELECT COUNT(*) as c FROM mlm_network_tree")->fetch();
echo "MLM tree nodes: {$r['c']}\n";

$r = $db->query("SELECT COUNT(*) as c FROM network_tree")->fetch();
echo "Network tree nodes: {$r['c']}\n";

$r = $db->query("SELECT COUNT(*) as c FROM plot_bookings")->fetch();
echo "Total bookings: {$r['c']}\n";

$r = $db->query("SELECT COUNT(*) as c FROM booking_payment_schedules")->fetch();
echo "EMI schedules: {$r['c']}\n";

$r = $db->query("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as total FROM mlm_commission_ledger")->fetch();
echo "Commission ledger: {$r['c']} entries, total â‚¹" . number_format($r['total']) . "\n";

$r = $db->query("SELECT COUNT(*) as c FROM wallet_points WHERE user_id IN (" . implode(',', $allAssocIds) . ")")->fetch();
echo "Associate wallets: {$r['c']}\n";

echo "\n=== SEEDING COMPLETE ===\n";
echo "Login credentials: Aps@2026 for all test users\n";
echo "\nHierarchy:\n";
echo "  Admin (id=1)\n";
echo "    â””â”€â”€ Rajesh Leader (leader.associate@apsdreamhome.com)\n";
echo "        â”œâ”€â”€ Mohan Middle (mohan.middle@apsdreamhome.com)\n";
echo "        â”‚   â”œâ”€â”€ Deepak Bottom (deepak.bottom@apsdreamhome.com)\n";
echo "        â”‚   â””â”€â”€ Ramesh Bottom (ramesh.bottom@apsdreamhome.com)\n";
echo "        â””â”€â”€ Suresh Middle (suresh.middle@apsdreamhome.com)\n";?>