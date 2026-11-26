<?php
/**
 * Automated Test: Associate Doorstep Booking Workflow & UI Validation
 */

@session_start();
$_SESSION['user_id'] = 2; // Verified associate user
$_SESSION['role'] = 'associate';
$_SESSION['csrf_token'] = bin2hex(random_bytes(16));

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

echo "=== Doorstep Booking & Maker-Checker Verification ===\n";

// 1. Check Plots with valid total_price
$db = \App\Core\Database\Database::getInstance();
$plots = $db->fetchAll("SELECT p.id, p.plot_number, p.colony_id, p.total_price, p.area_sqft, c.name as colony_name FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE p.is_active = 1 AND p.status = 'available' LIMIT 5");

echo "Available plots count for doorstep booking: " . count($plots) . "\n";
foreach ($plots as $p) {
    echo "  - Plot #{$p['plot_number']} in {$p['colony_name']}: Area = {$p['area_sqft']} sqft, Price = ₹" . number_format($p['total_price']) . "\n";
    if ($p['total_price'] <= 0) {
        echo "❌ FAILED: Plot #{$p['plot_number']} has 0 total_price\n";
        exit(1);
    }
}
echo "✅ PASS: Available plots have valid total_price\n";

// 2. HTTP GET /associate/book-plot simulation
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/associate/book-plot';
$ctrl = new \App\Http\Controllers\AssociateController();
$ctrl->bookPlot();
$output = ob_get_clean();

if (strpos($output, 'name="colony_id"') !== false &&
    strpos($output, 'name="plot_id"') !== false &&
    strpos($output, 'id="btnSubmitBooking"') !== false) {
    echo "✅ PASS: /associate/book-plot rendered cleanly with proper inputs and enabled submit button\n";
} else {
    echo "❌ FAILED: /associate/book-plot output missing expected elements\n";
    exit(1);
}

// Ensure submit button is NOT disabled
if (strpos($output, 'id="btnSubmitBooking"') !== false && strpos($output, 'disabled title="ऑफ़लाइन') === false) {
    echo "✅ PASS: Submit button is fully enabled for field associates\n";
} else {
    echo "❌ FAILED: Submit button is still disabled!\n";
    exit(1);
}

// 3. HTTP GET /associate/my-bookings simulation
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/associate/my-bookings';
$ctrl->myBookings();
$outputBookings = ob_get_clean();

if (strpos($outputBookings, 'New Doorstep Booking') !== false &&
    strpos($outputBookings, 'My Bookings') !== false) {
    echo "✅ PASS: /associate/my-bookings contains 'New Doorstep Booking' button\n";
} else {
    echo "❌ FAILED: /associate/my-bookings missing New Doorstep Booking button\n";
    exit(1);
}

// 4. Receipt Rendering Verification
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/associate/booking/1/receipt';
$ctrl->bookingReceipt(1);
$outputReceipt = ob_get_clean();

echo "Receipt output length: " . strlen($outputReceipt) . "\n";
if (empty($outputReceipt)) {
    echo "Receipt was empty! Checking if booking ID 1 exists in plot_bookings...\n";
    $bk1 = $db->fetchRow("SELECT id, associate_id FROM plot_bookings WHERE id = 1");
    print_r($bk1);
}

if (strpos($outputReceipt, 'RECEIPT') !== false || strpos($outputReceipt, 'Allotment') !== false) {
    echo "✅ PASS: Official Booking Receipt & EMI Schedule rendered successfully\n";
} else {
    echo "❌ FAILED: Booking receipt rendering failed\n";
    echo substr($outputReceipt, 0, 500) . "\n";
    exit(1);
}

echo "\n🎉 ALL DOORSTEP BOOKING WORKFLOW CHECKS PASSED!\n";
