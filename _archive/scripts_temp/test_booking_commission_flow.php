<?php
/**
 * Test complete booking → commission flow
 */
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== TESTING BOOKING → COMMISSION FLOW ===\n\n";

// 1. Create a test customer with a referrer
echo "1. Creating test customer with referrer...\n";

// Get an associate to be the referrer
$referrer = $db->fetch("SELECT id, referral_code FROM users WHERE role = 'associate' AND id = 2");
echo "Referrer: User {$referrer['id']}, Code: {$referrer['referral_code']}\n";

// Create test customer
$customerData = [
    'name' => 'Test Customer ' . time(),
    'email' => 'testcust' . time() . '@example.com',
    'phone' => '9876543210',
    'password' => password_hash('Test1234', PASSWORD_DEFAULT),
    'referral_code' => 'CUST' . time(),
    'referred_by' => $referrer['id'],
    'role' => 'customer',
    'status' => 'active'
];
$db->insert('users', $customerData);
$customerId = $db->lastInsertId();
echo "Created customer: ID $customerId\n";

// 2. Get an available plot
echo "\n2. Getting available plot...\n";
$plot = $db->fetch("SELECT id, plot_number, colony_id, price_per_sqft, area_sqft FROM plots WHERE status = 'available' LIMIT 1");
if (!$plot) {
    echo "No available plots found!\n";
    exit;
}
echo "Plot: {$plot['plot_number']} (ID: {$plot['id']}), Colony: {$plot['colony_id']}, Price: {$plot['price_per_sqft']}/sqft, Area: {$plot['area_sqft']} sqft\n";

$totalValue = $plot['price_per_sqft'] * $plot['area_sqft'];
echo "Total value: ₹" . number_format($totalValue, 2) . "\n";

// 3. Create booking via BookingLifecycleService
echo "\n3. Creating booking via BookingLifecycleService...\n";
require_once __DIR__ . '/../app/Services/Sales/BookingLifecycleService.php';

$service = new \App\Services\Sales\BookingLifecycleService();

$bookingData = [
    'plot_id' => $plot['id'],
    'customer_id' => $customerId,
    'total_plot_value' => $totalValue,
    'booking_amount' => 51000, // token amount
    'channel' => 'associate',
    'associate_id' => $referrer['id'],
    'commission_pct' => 2.0,
    'notes' => 'Test booking for commission flow'
];

$result = $service->createBooking($bookingData);
print_r($result);

if (!$result['success']) {
    echo "Booking creation failed: {$result['error']}\n";
    exit;
}

$bookingId = $result['id'];
echo "Booking created: ID $bookingId, Number: {$result['booking_number']}\n";

// 4. Check commission calculation
echo "\n4. Calculating commission...\n";
$commResult = $service->calculateCommission($bookingId);
print_r($commResult);

// 5. Check booking_commissions table
echo "\n5. Checking booking_commissions table...\n";
$commissions = $db->fetchAll("SELECT * FROM booking_commissions WHERE booking_id = ?", [$bookingId]);
echo "Commission entries: " . count($commissions) . "\n";
foreach ($commissions as $c) {
    echo "  Type: {$c['commission_type']}, User: {$c['beneficiary_user_id']}, Amount: {$c['amount']}, Rate: {$c['percent']}%\n";
}

// 6. Check mlm_commission_ledger
echo "\n6. Checking mlm_commission_ledger...\n";
$ledger = $db->fetchAll("SELECT * FROM mlm_commission_ledger WHERE booking_id = ?", [$bookingId]);
echo "Ledger entries: " . count($ledger) . "\n";
foreach ($ledger as $l) {
    echo "  Type: {$l['commission_type']}, Beneficiary: {$l['beneficiary_user_id']}, Amount: {$l['amount']}, Status: {$l['status']}\n";
}

// 7. Check wallet updates
echo "\n7. Checking wallet updates...\n";
$wallets = $db->fetchAll("SELECT user_id, balance, total_credited FROM user_wallets WHERE user_type = 'associate' AND balance > 0");
echo "Wallets with balance: " . count($wallets) . "\n";
foreach ($wallets as $w) {
    echo "  User: {$w['user_id']}, Balance: {$w['balance']}, Credited: {$w['total_credited']}\n";
}

echo "\n=== TEST COMPLETE ===\n";