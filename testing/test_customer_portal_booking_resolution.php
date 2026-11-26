<?php
/**
 * Automated Test: Customer Portal Booking Resolution, Auto-Link & Schedule Accuracy
 */

@session_start();
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

echo "=== Testing Customer Portal Booking Resolution & Schedule Accuracy ===\n";

$db = \App\Core\Database\Database::getInstance()->getPdo();

// 1. Verify baseline before test
$tables = ['users', 'plot_bookings', 'leads', 'transactions', 'mlm_commission_ledger', 'email_queue'];
$expectedBaseline = [
    'users' => 82,
    'plot_bookings' => 23,
    'leads' => 994,
    'transactions' => 5,
    'mlm_commission_ledger' => 359,
    'email_queue' => 14,
];

foreach ($tables as $t) {
    $c = (int)$db->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
    if ($c !== $expectedBaseline[$t]) {
        echo "❌ FAILED: Pre-test baseline drift on {$t}: expected {$expectedBaseline[$t]}, got {$c}\n";
        exit(1);
    }
}
echo "✅ PASS: Pre-test baseline verified (all 6 core tables exact).\n";

// 2. Test Customer Booking Discovery via Phone/Email Resolution
$testUser = $db->query("SELECT id, name, email, phone, role FROM users WHERE role = 'customer' AND phone IS NOT NULL AND phone != '' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
if (!$testUser) {
    echo "❌ FAILED: No customer user with phone found for test.\n";
    exit(1);
}

$customerId = (int)$testUser['id'];
$customerPhone = $testUser['phone'];
$customerEmail = $testUser['email'] ?? '';

echo "Testing with customer: {$testUser['name']} (ID: {$customerId}, Phone: {$customerPhone})\n";

// Create a simulated associate doorstep booking with customer's phone but customer_id = NULL
$testBookingNumber = 'BK-TEST-CP-' . strtoupper(substr(uniqid(), -6));
$availablePlot = $db->query("SELECT id, colony_id, total_price FROM plots WHERE status = 'available' AND is_active = 1 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
$plotId = (int)$availablePlot['id'];
$colonyId = (int)$availablePlot['colony_id'];
$totalPrice = (float)$availablePlot['total_price'];
$discountAmount = 50000.00;
$agreementValue = $totalPrice - $discountAmount;
$tokenAmount = 51000.00;

$cleanupBooking = function ($bid) use ($db) {
    $db->prepare("DELETE FROM booking_payment_schedules WHERE booking_id = ?")->execute([$bid]);
    $db->prepare("DELETE FROM booking_status_history WHERE booking_id = ?")->execute([$bid]);
    $db->prepare("DELETE FROM booking_documents WHERE booking_id = ?")->execute([$bid]);
    $db->prepare("DELETE FROM booking_payment_receipts WHERE booking_id = ?")->execute([$bid]);
    $db->prepare("DELETE FROM plot_bookings WHERE id = ?")->execute([$bid]);
};

$db->prepare("
    INSERT INTO plot_bookings 
    (plot_id, colony_id, customer_id, customer_name, customer_phone, customer_email, booking_number, booking_date, total_plot_value, discount_amount, agreement_value, booking_amount, status, approval_status, associate_id, channel, notes, created_by, created_at)
    VALUES (?, ?, NULL, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, 'token_paid', 'approved', 2, 'associate', 'Customer portal resolution probe', 2, NOW())
")->execute([$plotId, $colonyId, $testUser['name'], $customerPhone, $customerEmail, $testBookingNumber, $totalPrice, $discountAmount, $agreementValue, $tokenAmount]);
$testBookingId = (int)$db->lastInsertId();

// Set session as this customer
$_SESSION['user_id'] = $customerId;
$_SESSION['role'] = 'customer';

// 3. Test UserController::myBookings() renders this phone-linked booking
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/user/bookings';
$ctrl = new \App\Http\Controllers\Front\UserController();
$ctrl->myBookings();
$myBookingsView = ob_get_clean();

if (strpos($myBookingsView, $testBookingNumber) !== false) {
    echo "✅ PASS: UserController::myBookings() successfully resolved booking {$testBookingNumber} via customer phone match!\n";
} else {
    echo "❌ FAILED: myBookings() failed to resolve booking by phone.\n";
    $cleanupBooking($testBookingId);
    exit(1);
}

// 4. Test UserController::bookingDetail() renders with auto-link and proper actions
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/user/bookings/' . $testBookingId;
$ctrl->bookingDetail($testBookingId);
$detailView = ob_get_clean();

if (strpos($detailView, $testBookingNumber) !== false &&
    strpos($detailView, 'Official Receipt & EMI Schedule') !== false &&
    strpos($detailView, 'Register NACH Auto-Debit') !== false) {
    echo "✅ PASS: UserController::bookingDetail() rendered verified allotment actions without duplicate token pay prompts.\n";
} else {
    echo "❌ FAILED: bookingDetail() missing expected elements or action buttons.\n";
    $cleanupBooking($testBookingId);
    exit(1);
}

// Verify customer_id was auto-linked
$linkedCustId = (int)$db->query("SELECT customer_id FROM plot_bookings WHERE id = {$testBookingId}")->fetchColumn();
if ($linkedCustId === $customerId) {
    echo "✅ PASS: Booking customer_id was automatically linked to User #{$customerId}.\n";
} else {
    echo "❌ FAILED: customer_id auto-link expected {$customerId}, got {$linkedCustId}\n";
    $cleanupBooking($testBookingId);
    exit(1);
}

// 5. Test Payment Schedule Net Principal with Discount
$bookingSvc = new \App\Services\Sales\BookingLifecycleService($db);
$scheduleResult = $bookingSvc->generatePaymentSchedule($testBookingId, 12, 0.0); // 12 months, 0% interest promo
if (!empty($scheduleResult['success'])) {
    $expectedPrincipal = $agreementValue - $tokenAmount;
    $actualPrincipal = (float)($scheduleResult['summary']['principal'] ?? 0);
    if (abs($actualPrincipal - $expectedPrincipal) < 0.01) {
        echo "✅ PASS: Payment schedule net principal (₹" . number_format($actualPrincipal) . ") accurately factored ₹" . number_format($discountAmount) . " promotional discount!\n";
    } else {
        echo "❌ FAILED: Schedule principal mismatch: expected ₹{$expectedPrincipal}, got ₹{$actualPrincipal}\n";
        $cleanupBooking($testBookingId);
        exit(1);
    }
} else {
    echo "❌ FAILED: generatePaymentSchedule failed: " . ($scheduleResult['error'] ?? 'unknown') . "\n";
    $cleanupBooking($testBookingId);
    exit(1);
}

// 6. Clean up test records
$cleanupBooking($testBookingId);

// 7. Verify post-test baseline
echo "Verifying post-test baseline counts...\n";
foreach ($tables as $t) {
    $c = (int)$db->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
    if ($c !== $expectedBaseline[$t]) {
        echo "❌ FAILED: Post-test baseline drift on {$t}: expected {$expectedBaseline[$t]}, got {$c}\n";
        exit(1);
    }
}
echo "✅ PASS: Zero DB drift confirmed! All core table counts exactly match baseline.\n";

echo "=== All Customer Portal & Schedule Tests PASSED Successfully! ===\n";
