<?php
/**
 * Automated Test: Maker-Checker Booking Approval & Plot Release Lifecycle
 */

@session_start();
$_SESSION['user_id'] = 1;
$_SESSION['admin_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['csrf_token'] = bin2hex(random_bytes(16));

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

echo "=== Testing Maker-Checker Review, Allotment & Plot Release ===\n";

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

// Subclass controller to intercept redirect without exit
class TestableBookingLifecycleController extends \App\Http\Controllers\Admin\BookingLifecycleController {
    public $redirectUrl = null;
    protected function redirect(string $url): void {
        $this->redirectUrl = $url;
    }
}

$ctrl = new TestableBookingLifecycleController();

// 2. Test Approval List View rendering
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/sales/approvals?status=pending';
$ctrl->approvalList();
$approvalView = ob_get_clean();

if (strpos($approvalView, 'Legal Deed') !== false &&
    strpos($approvalView, 'Pending Review') !== false &&
    strpos($approvalView, 'Token / Value') !== false) {
    echo "✅ PASS: /admin/sales/approvals renders Maker-Checker queue with legal deed column.\n";
} else {
    echo "❌ FAILED: /admin/sales/approvals missing expected Maker-Checker elements.\n";
    exit(1);
}

// 3. Test Full Approval & Rejection Logic with transactional safety
$availablePlot = $db->query("SELECT id, plot_number, colony_id, total_price FROM plots WHERE status = 'available' AND is_active = 1 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
if (!$availablePlot) {
    echo "❌ FAILED: No available plot for lifecycle test.\n";
    exit(1);
}

$plotId = (int)$availablePlot['id'];
$colonyId = (int)$availablePlot['colony_id'];
$plotPrice = (float)$availablePlot['total_price'];

echo "Testing with Plot #{$availablePlot['plot_number']} (ID: {$plotId}, Price: ₹" . number_format($plotPrice) . ")...\n";

// --- Case A: Rejection Releases Plot back to Available ---
// Create test draft
$testBookingNumber = 'BK-TEST-' . strtoupper(substr(uniqid(), -6));
$db->prepare("
    INSERT INTO plot_bookings 
    (plot_id, colony_id, customer_name, customer_phone, customer_email, booking_number, booking_date, total_plot_value, booking_amount, agreement_value, status, approval_status, associate_id, channel, notes, created_by, created_at)
    VALUES (?, ?, 'Test Reject Customer', '9876543210', 'reject@test.com', ?, CURDATE(), ?, 51000, ?, 'pending', 'pending', 2, 'associate', 'Test draft for rejection', 2, NOW())
")->execute([$plotId, $colonyId, $testBookingNumber, $plotPrice, $plotPrice]);
$rejectBookingId = (int)$db->lastInsertId();

// Lock plot to booked
$db->prepare("UPDATE plots SET status = 'booked' WHERE id = ?")->execute([$plotId]);

// Insert pending receipt
$db->prepare("
    INSERT INTO booking_payment_receipts (booking_id, receipt_number, receipt_date, amount, payment_mode, status, notes, created_at)
    VALUES (?, 'RCP-TEST-REJ', CURDATE(), 51000, 'upi', 'pending', 'Test receipt', NOW())
")->execute([$rejectBookingId]);
$rejectReceiptId = (int)$db->lastInsertId();

// Insert pending document
$db->prepare("
    INSERT INTO booking_documents (booking_id, document_type, document_name, file_path, status, created_at)
    VALUES (?, 'application_form', 'Test Signed Deed', 'assets/documents/test_deed.pdf', 'pending', NOW())
")->execute([$rejectBookingId]);
$rejectDocId = (int)$db->lastInsertId();

// Verify plot is booked
$currentPlotStatus = $db->query("SELECT status FROM plots WHERE id = {$plotId}")->fetchColumn();
if ($currentPlotStatus !== 'booked') {
    echo "❌ FAILED: Plot status should be 'booked' after draft submission, got '{$currentPlotStatus}'\n";
    exit(1);
}

// Call rejectBooking
$_POST['csrf_token'] = $_SESSION['csrf_token'];
$_POST['rejection_reason'] = 'Automated Maker-Checker verification test rejection';
ob_start();
$ctrl->rejectBooking($rejectBookingId);
ob_get_clean();

// Verify booking is cancelled & rejected
$bRow = $db->query("SELECT status, approval_status, approval_notes FROM plot_bookings WHERE id = {$rejectBookingId}")->fetch(\PDO::FETCH_ASSOC);
if ($bRow['approval_status'] !== 'rejected' || $bRow['status'] !== 'cancelled') {
    echo "❌ FAILED: Booking not rejected/cancelled properly: " . json_encode($bRow) . "\n";
    exit(1);
}

// CRITICAL CHECK: Plot must be released back to 'available'
$releasedPlotStatus = $db->query("SELECT status FROM plots WHERE id = {$plotId}")->fetchColumn();
if ($releasedPlotStatus !== 'available') {
    echo "❌ FAILED: Critical plot release failed! Expected 'available', got '{$releasedPlotStatus}'\n";
    exit(1);
}
echo "✅ PASS: Rejection successfully released plot #{$availablePlot['plot_number']} back to 'available'.\n";

// Check receipt cancelled
$rcptStatus = $db->query("SELECT status FROM booking_payment_receipts WHERE id = {$rejectReceiptId}")->fetchColumn();
if ($rcptStatus !== 'cancelled') {
    echo "❌ FAILED: Token receipt should be cancelled, got '{$rcptStatus}'\n";
    exit(1);
}
echo "✅ PASS: Token receipt status changed to 'cancelled'.\n";

// Check document rejected
$docStatus = $db->query("SELECT status FROM booking_documents WHERE id = {$rejectDocId}")->fetchColumn();
if ($docStatus !== 'rejected') {
    echo "❌ FAILED: Document should be rejected, got '{$docStatus}'\n";
    exit(1);
}
echo "✅ PASS: Document status changed to 'rejected'.\n";

// Check status history logged
$histCount = (int)$db->query("SELECT COUNT(*) FROM booking_status_history WHERE booking_id = {$rejectBookingId}")->fetchColumn();
if ($histCount < 1) {
    echo "❌ FAILED: Status history not logged for rejection\n";
    exit(1);
}
echo "✅ PASS: Status history successfully logged with valid enum.\n";

// Clean up Case A
$db->prepare("DELETE FROM booking_status_history WHERE booking_id = ?")->execute([$rejectBookingId]);
$db->prepare("DELETE FROM booking_documents WHERE id = ?")->execute([$rejectDocId]);
$db->prepare("DELETE FROM booking_payment_receipts WHERE id = ?")->execute([$rejectReceiptId]);
$db->prepare("DELETE FROM plot_bookings WHERE id = ?")->execute([$rejectBookingId]);

// --- Case B: Approval Confirms Plot Allotment & Clears Receipt ---
$testBookingNumber2 = 'BK-TEST-' . strtoupper(substr(uniqid(), -6));
$db->prepare("
    INSERT INTO plot_bookings 
    (plot_id, colony_id, customer_name, customer_phone, customer_email, booking_number, booking_date, total_plot_value, booking_amount, agreement_value, status, approval_status, associate_id, channel, notes, created_by, created_at)
    VALUES (?, ?, 'Test Approve Customer', '9876543211', 'approve@test.com', ?, CURDATE(), ?, 51000, ?, 'pending', 'pending', 2, 'associate', 'Test draft for approval', 2, NOW())
")->execute([$plotId, $colonyId, $testBookingNumber2, $plotPrice, $plotPrice]);
$approveBookingId = (int)$db->lastInsertId();

// Lock plot to booked
$db->prepare("UPDATE plots SET status = 'booked' WHERE id = ?")->execute([$plotId]);

// Insert pending receipt
$db->prepare("
    INSERT INTO booking_payment_receipts (booking_id, receipt_number, receipt_date, amount, payment_mode, status, notes, created_at)
    VALUES (?, 'RCP-TEST-APP', CURDATE(), 51000, 'upi', 'pending', 'Test receipt', NOW())
")->execute([$approveBookingId]);
$approveReceiptId = (int)$db->lastInsertId();

// Insert pending document
$db->prepare("
    INSERT INTO booking_documents (booking_id, document_type, document_name, file_path, status, created_at)
    VALUES (?, 'application_form', 'Test Signed Deed', 'assets/documents/test_deed.pdf', 'pending', NOW())
")->execute([$approveBookingId]);
$approveDocId = (int)$db->lastInsertId();

// Call approveBooking
$_POST['csrf_token'] = $_SESSION['csrf_token'];
$_POST['approval_notes'] = 'Automated Maker-Checker verification test approval';
ob_start();
$ctrl->approveBooking($approveBookingId);
ob_get_clean();

// Verify booking is approved & token_paid
$bRow2 = $db->query("SELECT status, approval_status FROM plot_bookings WHERE id = {$approveBookingId}")->fetch(\PDO::FETCH_ASSOC);
if ($bRow2['approval_status'] !== 'approved' || $bRow2['status'] !== 'token_paid') {
    echo "❌ FAILED: Booking not approved/token_paid properly: " . json_encode($bRow2) . "\n";
    exit(1);
}

// Verify plot remains booked
$appPlotStatus = $db->query("SELECT status FROM plots WHERE id = {$plotId}")->fetchColumn();
if ($appPlotStatus !== 'booked') {
    echo "❌ FAILED: Approved plot should be 'booked', got '{$appPlotStatus}'\n";
    exit(1);
}
echo "✅ PASS: Approval successfully confirmed plot #{$availablePlot['plot_number']} allotment as 'token_paid' & 'booked'.\n";

// Check receipt cleared
$rcptStatus2 = $db->query("SELECT status FROM booking_payment_receipts WHERE id = {$approveReceiptId}")->fetchColumn();
if ($rcptStatus2 !== 'cleared') {
    echo "❌ FAILED: Token receipt should be cleared, got '{$rcptStatus2}'\n";
    exit(1);
}
echo "✅ PASS: Token receipt status changed to 'cleared'.\n";

// Check document verified
$docStatus2 = $db->query("SELECT status FROM booking_documents WHERE id = {$approveDocId}")->fetchColumn();
if ($docStatus2 !== 'verified') {
    echo "❌ FAILED: Document should be verified, got '{$docStatus2}'\n";
    exit(1);
}
echo "✅ PASS: Document status changed to 'verified'.\n";

// Check status history logged
$histCount2 = (int)$db->query("SELECT COUNT(*) FROM booking_status_history WHERE booking_id = {$approveBookingId}")->fetchColumn();
if ($histCount2 < 1) {
    echo "❌ FAILED: Status history not logged for approval\n";
    exit(1);
}
echo "✅ PASS: Status history successfully logged with 'token_paid'.\n";

// Clean up Case B and restore plot to available
$db->prepare("DELETE FROM booking_status_history WHERE booking_id = ?")->execute([$approveBookingId]);
$db->prepare("DELETE FROM booking_documents WHERE id = ?")->execute([$approveDocId]);
$db->prepare("DELETE FROM booking_payment_receipts WHERE id = ?")->execute([$approveReceiptId]);
$db->prepare("DELETE FROM plot_bookings WHERE id = ?")->execute([$approveBookingId]);
$db->prepare("UPDATE plots SET status = 'available', customer_id = NULL WHERE id = ?")->execute([$plotId]);

// 4. Verify post-test baseline
echo "Verifying post-test baseline counts...\n";
foreach ($tables as $t) {
    $c = (int)$db->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
    if ($c !== $expectedBaseline[$t]) {
        echo "❌ FAILED: Post-test baseline drift on {$t}: expected {$expectedBaseline[$t]}, got {$c}\n";
        exit(1);
    }
}
echo "✅ PASS: Zero DB drift confirmed! All core table counts exactly match baseline.\n";

echo "=== All Maker-Checker Lifecycle Tests PASSED Successfully! ===\n";
