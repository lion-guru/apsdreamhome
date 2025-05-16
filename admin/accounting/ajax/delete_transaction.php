<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid transaction ID'
    ]);
    exit;
}

try {
    $conn = getDbConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    $transactionId = intval($_POST['id']);
    
    // First check if the transaction exists and get its details
    $checkQuery = "SELECT * FROM payments WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Transaction not found');
    }
    
    $transaction = $result->fetch_assoc();
    
    // Delete the transaction
    $deleteQuery = "DELETE FROM payments WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    
    // If this was a completed payment, we need to update related records
    if ($transaction['status'] === 'completed') {
        // Update any related records (e.g., booking status, property status, etc.)
        if (!empty($transaction['booking_id'])) {
            $updateBookingQuery = "UPDATE bookings SET payment_status = 'pending' WHERE id = ?";
            $stmt = $conn->prepare($updateBookingQuery);
            $stmt->bind_param("i", $transaction['booking_id']);
            $stmt->execute();
        }
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Log the deletion
    $logQuery = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details) 
                 VALUES (?, 'delete', 'payments', ?, ?)";
    $details = json_encode([
        'transaction_id' => $transaction['transaction_id'],
        'amount' => $transaction['amount'],
        'status' => $transaction['status']
    ]);
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param("iis", $_SESSION['admin_id'], $transactionId, $details);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaction deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete transaction: ' . $e->getMessage()
    ]);
}
?>
