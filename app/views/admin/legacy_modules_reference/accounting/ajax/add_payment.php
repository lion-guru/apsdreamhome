<?php
require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid request method'))
    ]);
    exit;
}

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin, Admin, and Manager can record payments
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to record payments'))
    ]);
    exit;
}

try {
    // Validate required fields
    $requiredFields = ['customer_id', 'amount', 'payment_type', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception($mlSupport->translate($field . ' is required'));
        }
    }

    // Start transaction
    \App\Core\App::database()->beginTransaction();

    // Generate unique transaction ID
    $transactionId = 'PAY' . \time() . \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);

    // Prepare payment data
    $customerId = intval($_POST['customer_id']);
    $amount = floatval($_POST['amount']);
    $paymentType = $_POST['payment_type'];
    $paymentMethod = $_POST['payment_method'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $status = 'completed'; // Default status for manually added payments

    // Insert payment record
    $query = "INSERT INTO payments (
                transaction_id,
                customer_id,
                amount,
                payment_type,
                payment_method,
                description,
                status,
                payment_date,
                created_by
              ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    \App\Core\App::database()->execute($query, [
        $transactionId,
        $customerId,
        $amount,
        $paymentType,
        $paymentMethod,
        $description,
        $status,
        $_SESSION['admin_id']
    ]);
    $paymentId = \App\Core\App::database()->lastInsertId();

    // If this is a booking payment, update the booking status
    if ($paymentType === 'booking' && isset($_POST['booking_id'])) {
        $bookingId = intval($_POST['booking_id']);
        $updateBookingQuery = "UPDATE bookings SET
                             payment_status = 'completed',
                             payment_date = NOW()
                             WHERE id = ?";
        \App\Core\App::database()->execute($updateBookingQuery, [$bookingId]);
    }

    // Create notification for the customer
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'payment', ?, ?, ?, NOW())";

    $notificationTitle = $mlSupport->translate('Payment Received');
    $notificationMessage = $mlSupport->translate("Your payment of ₹") . h(number_format($amount, 2)) . " " . $mlSupport->translate("has been received.");
    $notificationLink = "payments/view.php?id=" . intval($paymentId);

    \App\Core\App::database()->execute($notificationQuery, [
        $customerId,
        $notificationTitle,
        $notificationMessage,
        $notificationLink
    ]);

    // Commit transaction
    \App\Core\App::database()->commit();

    echo json_encode([
        'success' => true,
        'message' => h($mlSupport->translate('Payment added successfully')),
        'data' => [
            'payment_id' => $paymentId,
            'transaction_id' => $transactionId
        ]
    ]);

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    \App\Core\App::database()->rollBack();

    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to add payment') . ': ' . $e->getMessage())
    ]);
}
?>
