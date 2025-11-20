<?php
require_once '../../../includes/config.php';
require_once '../../../includes/config/config.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    global $con; $conn = $con;
    
    // Validate required fields
    $requiredFields = ['customer_id', 'amount', 'payment_type', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Generate unique transaction ID
    $transactionId = 'TXN' . time() . rand(1000, 9999);
    
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
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sidsssssi",
        $transactionId,
        $customerId,
        $amount,
        $paymentType,
        $paymentMethod,
        $description,
        $status,
        $_SESSION['admin_id']
    );
    $stmt->execute();
    $paymentId = $stmt->insert_id;
    
    // If this is a booking payment, update the booking status
    if ($paymentType === 'booking' && isset($_POST['booking_id'])) {
        $bookingId = intval($_POST['booking_id']);
        $updateBookingQuery = "UPDATE bookings SET 
                             payment_status = 'completed',
                             payment_date = NOW()
                             WHERE id = ?";
        $stmt = $conn->prepare($updateBookingQuery);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
    }
    
    // Create notification for the customer
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'payment', 'Payment Received', ?, ?, NOW())";
                         
    $notificationMessage = "Your payment of ₹" . number_format($amount, 2) . " has been received.";
    $notificationLink = "payments/view.php?id=" . $paymentId;
    
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bind_param("iss", $customerId, $notificationMessage, $notificationLink);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment added successfully',
        'data' => [
            'payment_id' => $paymentId,
            'transaction_id' => $transactionId
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add payment: ' . $e->getMessage()
    ]);
}
?>
