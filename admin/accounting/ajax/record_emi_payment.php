<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    global $con;
    $conn = $con;
    
    // Validate required fields
    $requiredFields = ['installment_id', 'payment_date', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    $installmentId = intval($_POST['installment_id']);
    
    // Get installment details
    $query = "SELECT ei.*, ep.customer_id, ep.property_id 
              FROM emi_installments ei
              JOIN emi_plans ep ON ei.emi_plan_id = ep.id
              WHERE ei.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $installmentId);
    $stmt->execute();
    $installment = $stmt->get_result()->fetch_assoc();
    
    if (!$installment) {
        throw new Exception('Installment not found');
    }
    
    if ($installment['payment_status'] === 'paid') {
        throw new Exception('This installment is already paid');
    }
    
    // Calculate late fee if any
    $lateFee = 0;
    if (strtotime($_POST['payment_date']) > strtotime($installment['due_date'])) {
        $daysLate = floor((strtotime($_POST['payment_date']) - strtotime($installment['due_date'])) / (60 * 60 * 24));
        
        // Get applicable late fee
        $lateFeeQuery = "SELECT * FROM emi_late_fee_config 
                        WHERE days_after_due <= ? AND is_active = 1 
                        ORDER BY days_after_due DESC LIMIT 1";
        $stmt = $conn->prepare($lateFeeQuery);
        $stmt->bind_param("i", $daysLate);
        $stmt->execute();
        $lateFeeConfig = $stmt->get_result()->fetch_assoc();
        
        if ($lateFeeConfig) {
            if ($lateFeeConfig['fee_type'] === 'fixed') {
                $lateFee = $lateFeeConfig['fee_amount'];
            } else {
                // Percentage based fee
                $lateFee = ($installment['amount'] * $lateFeeConfig['fee_amount']) / 100;
            }
        }
    }
    
    // Create payment record
    $paymentQuery = "INSERT INTO payments (
                        transaction_id,
                        customer_id,
                        property_id,
                        amount,
                        payment_type,
                        payment_method,
                        description,
                        status,
                        payment_date,
                        created_by
                    ) VALUES (?, ?, ?, ?, 'emi', ?, ?, 'completed', ?, ?)";
                    
    $transactionId = 'EMI' . time() . rand(1000, 9999);
    $totalAmount = $installment['amount'] + $lateFee;
    $description = "EMI Payment - Installment #" . $installment['installment_number'];
    if ($lateFee > 0) {
        $description .= " (Including Late Fee: ₹" . number_format($lateFee, 2) . ")";
    }
    
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param(
        "siidssssi",
        $transactionId,
        $installment['customer_id'],
        $installment['property_id'],
        $totalAmount,
        $_POST['payment_method'],
        $description,
        $_POST['payment_date'],
        $_SESSION['admin_id']
    );
    $stmt->execute();
    $paymentId = $stmt->insert_id;
    
    // Update installment status
    $updateQuery = "UPDATE emi_installments 
                   SET payment_status = 'paid',
                       payment_date = ?,
                       payment_id = ?,
                       late_fee = ?,
                       updated_at = NOW()
                   WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sidi", $_POST['payment_date'], $paymentId, $lateFee, $installmentId);
    $stmt->execute();
    
    // Check if all installments are paid
    $checkQuery = "SELECT COUNT(*) as pending_count 
                  FROM emi_installments 
                  WHERE emi_plan_id = ? AND payment_status != 'paid'";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $installment['emi_plan_id']);
    $stmt->execute();
    $pendingCount = $stmt->get_result()->fetch_assoc()['pending_count'];
    
    // If all installments are paid, update EMI plan status
    if ($pendingCount === 0) {
        $updatePlanQuery = "UPDATE emi_plans 
                           SET status = 'completed',
                               updated_at = NOW()
                           WHERE id = ?";
        $stmt = $conn->prepare($updatePlanQuery);
        $stmt->bind_param("i", $installment['emi_plan_id']);
        $stmt->execute();
    }
    
    // Create notification
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'emi_payment', 'EMI Payment Received', ?, ?, NOW())";
                         
    $notificationMessage = "Your EMI payment of ₹" . number_format($totalAmount, 2) . " has been recorded.";
    $notificationLink = "payments/view.php?id=" . $paymentId;
    
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bind_param("iss", $installment['customer_id'], $notificationMessage, $notificationLink);
    $stmt->execute();
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
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
        'message' => 'Failed to record payment: ' . $e->getMessage()
    ]);
}
?>
