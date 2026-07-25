<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== SEED BOOKINGS + PAYMENTS + COMMISSIONS ===\n";

// Get available plots
$plots = $db->query("SELECT id, colony_id, plot_number, total_price FROM plots WHERE status='available' LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
echo "Available plots: " . count($plots) . "\n";
if (empty($plots)) { echo "No plots! Exiting.\n"; exit(1); }

// Use existing customer (id=41)
$customerId = 41;
$assocId = 121161; // Rajesh Leader

foreach ($plots as $i => $plot) {
    $existing = $db->prepare("SELECT id FROM plot_bookings WHERE plot_id=?");
    $existing->execute([$plot['id']]);
    if ($existing->fetch()) { echo "Plot {$plot['plot_number']} already booked, skipping\n"; continue; }
    
    $bookingNum = 'APS-BK-' . date('Ymd') . '-' . str_pad($i+1, 4, '0', STR_PAD_LEFT);
    $bookingAmt = $plot['total_price'] * 0.10;
    $statuses = ['token_paid', 'agreement_signed', 'emi_active'];
    $status = $statuses[$i % 3];
    
    // Insert booking
    $stmt = $db->prepare("INSERT INTO plot_bookings (plot_id, colony_id, customer_id, customer_name, customer_email, booking_number, booking_date, total_plot_value, booking_amount, status, approval_status, channel, associate_id, created_at) VALUES (?, ?, ?, 'Test Customer', 'customer@apsdreamhome.com', ?, CURDATE(), ?, ?, ?, 'approved', 'associate', ?, NOW())");
    $stmt->execute([$plot['id'], $plot['colony_id'], $customerId, $bookingNum, $plot['total_price'], $bookingAmt, $status, $assocId]);
    $bookingId = $db->lastInsertId();
    echo "Booking #$bookingId: Plot {$plot['plot_number']} (₹" . number_format($plot['total_price']) . ") status=$status\n";
    
    // Update plot
    $db->prepare("UPDATE plots SET status='booked', customer_id=?, booking_date=CURDATE() WHERE id=?")->execute([$customerId, $plot['id']]);
    
    // Create 4 EMI installments
    $emiAmount = ($plot['total_price'] - $bookingAmt) / 4;
    $balance = $plot['total_price'] - $bookingAmt;
    for ($m = 1; $m <= 4; $m++) {
        $dueDate = date('Y-m-d', strtotime("+$m months"));
        if ($m === 1 && $status === 'emi_active') {
            $emiStatus = 'paid';
            $paidAmt = $emiAmount;
            $paidDate = date('Y-m-d');
            $closing = $balance - $emiAmount;
        } else {
            $emiStatus = 'pending';
            $paidAmt = 0;
            $paidDate = null;
            $closing = $balance;
        }
        $balance -= $emiAmount;
        
        $db->prepare("INSERT INTO booking_payment_schedules (booking_id, installment_no, due_date, amount, principal, interest, opening_balance, closing_balance, status, paid_date, paid_amount, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, NOW())")
           ->execute([$bookingId, $m, $dueDate, $emiAmount, $emiAmount, $balance + $emiAmount, max($closing, 0), $emiStatus, $paidDate, $paidAmt]);
    }
    echo "  4 EMI installments created\n";
    
    // Commission for completed bookings only
    if ($status === 'emi_active') {
        // Mark plot as sold
        $db->prepare("UPDATE plots SET status='sold', sale_date=CURDATE(), total_paid=?, payment_status='partial' WHERE id=?")
           ->execute([$bookingAmt + $emiAmount, $plot['id']]);
        
        // Direct sale commission (5%)
        $directComm = $plot['total_price'] * 0.05;
        $db->prepare("INSERT INTO mlm_commission_ledger (source_user_id, beneficiary_user_id, booking_id, commission_type, amount, sale_amount, commission_percentage, status, notes, created_at) VALUES (?, ?, ?, 'direct_sale', ?, ?, 5.00, 'approved', ?, NOW())")
           ->execute([$assocId, $assocId, $bookingId, $directComm, $plot['total_price'], "Direct commission for booking $bookingNum"]);
        echo "  Commission: ₹" . number_format($directComm) . " direct sale\n";
        
        // Override for admin (2.5%)
        $overrideComm = $plot['total_price'] * 0.025;
        $db->prepare("INSERT INTO mlm_commission_ledger (source_user_id, beneficiary_user_id, booking_id, commission_type, amount, sale_amount, commission_percentage, status, notes, created_at) VALUES (?, ?, ?, 'override', ?, ?, 2.50, 'approved', ?, NOW())")
           ->execute([$assocId, 1, $bookingId, $overrideComm, $plot['total_price'], "Override from $bookingNum"]);
        echo "  Commission: ₹" . number_format($overrideComm) . " override to admin\n";
        
        // Update associate earnings
        $db->prepare("UPDATE associates SET total_sales=total_sales+?, commission_earned=commission_earned+? WHERE user_id=?")
           ->execute([$plot['total_price'], $directComm, $assocId]);
    }
}

// Summary
echo "\n=== SUMMARY ===\n";
$r = $db->query("SELECT COUNT(*) as c FROM plot_bookings")->fetch();
echo "Total bookings: {$r['c']}\n";
$r = $db->query("SELECT COUNT(*) as c FROM booking_payment_schedules")->fetch();
echo "EMI schedules: {$r['c']}\n";
$r = $db->query("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as t FROM mlm_commission_ledger")->fetch();
echo "Commission: {$r['c']} entries, ₹" . number_format($r['t']) . "\n";
$r = $db->query("SELECT total_sales, commission_earned FROM associates WHERE user_id=$assocId")->fetch();
if ($r) echo "Rajesh Leader: Sales=₹" . number_format($r['total_sales']) . " Commission=₹" . number_format($r['commission_earned']) . "\n";

echo "\nDone!\n";
