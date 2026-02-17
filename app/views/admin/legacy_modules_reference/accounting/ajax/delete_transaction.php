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

// RBAC Protection - Only Super Admin and Manager can delete transactions
$currentRole = $_SESSION['admin_role'] ?? '';
if ($currentRole !== 'superadmin' && $currentRole !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can delete transactions'))
    ]);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid transaction ID'))
    ]);
    exit;
}

try {
    // Start transaction
    \App\Core\App::database()->beginTransaction();

    $transactionId = intval($_POST['id']);

    // First check if the transaction exists and get its details
    $checkQuery = "SELECT * FROM payments WHERE id = ?";
    $transaction = \App\Core\App::database()->fetchOne($checkQuery, [$transactionId]);

    if (!$transaction) {
        throw new Exception(h($mlSupport->translate('Transaction not found')));
    }

    // Delete the transaction
    $deleteQuery = "DELETE FROM payments WHERE id = ?";
    \App\Core\App::database()->execute($deleteQuery, [$transactionId]);

    // If this was a completed payment, we need to update related records
    if ($transaction['status'] === 'completed') {
        // Update any related records (e.g., booking status, property status, etc.)
        if (!empty($transaction['booking_id'])) {
            $updateBookingQuery = "UPDATE bookings SET payment_status = 'pending' WHERE id = ?";
            \App\Core\App::database()->execute($updateBookingQuery, [$transaction['booking_id']]);
        }
    }

    // Commit the transaction
    \App\Core\App::database()->commit();

    // Log the deletion
    $logQuery = "INSERT INTO activity_logs (user_id, action, table_name, record_id, details)
                 VALUES (?, 'delete', 'payments', ?, ?)";
    $details = json_encode([
        'transaction_id' => $transaction['transaction_id'],
        'amount' => $transaction['amount'],
        'status' => $transaction['status']
    ]);
    \App\Core\App::database()->execute($logQuery, [$_SESSION['admin_id'], $transactionId, $details]);

    echo json_encode([
        'success' => true,
        'message' => h($mlSupport->translate('Transaction deleted successfully'))
    ]);

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    \App\Core\App::database()->rollBack();

    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to delete transaction') . ': ' . $e->getMessage())
    ]);
}
?>
