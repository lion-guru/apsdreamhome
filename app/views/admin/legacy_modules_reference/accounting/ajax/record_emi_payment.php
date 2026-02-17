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

// RBAC Protection - Only Super Admin, Admin, and Manager can record EMI payments
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to record EMI payments'))
    ]);
    exit;
}

try {
    // Validate required fields
    $requiredFields = ['installment_id', 'payment_date', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception($mlSupport->translate(str_replace('_', ' ', $field) . " is required"));
        }
    }

    // Start transaction
    \App\Core\App::database()->beginTransaction();

    $installmentId = intval($_POST['installment_id']);

    // Get installment details
    $query = "SELECT ei.*, ep.customer_id, ep.property_id
              FROM emi_installments ei
              JOIN emi_plans ep ON ei.emi_plan_id = ep.id
              WHERE ei.id = ?";
    $installment = \App\Core\App::database()->fetchOne($query, [$installmentId]);

    if (!$installment) {
        throw new Exception($mlSupport->translate('Installment not found'));
    }

    if ($installment['payment_status'] === 'paid') {
        throw new Exception($mlSupport->translate('This installment is already paid'));
    }

    // Calculate late fee if any
    $lateFee = 0;
    if (strtotime($_POST['payment_date']) > strtotime($installment['due_date'])) {
        $daysLate = floor((strtotime($_POST['payment_date']) - strtotime($installment['due_date'])) / (60 * 60 * 24));

        // Get applicable late fee
        $lateFeeQuery = "SELECT * FROM emi_late_fee_config
                        WHERE days_after_due <= ? AND is_active = 1
                        ORDER BY days_after_due DESC LIMIT 1";
        $lateFeeConfig = \App\Core\App::database()->fetchOne($lateFeeQuery, [$daysLate]);

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

    $transactionId = 'EMI' . \time() . \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);
    $totalAmount = $installment['amount'] + $lateFee;
    $description = h($mlSupport->translate("EMI Payment - Installment #")) . h($installment['installment_number']);
    if ($lateFee > 0) {
        $description .= " (" . h($mlSupport->translate("Including Late Fee")) . ": ₹" . number_format($lateFee, 2) . ")";
    }

    \App\Core\App::database()->execute($paymentQuery, [
        $transactionId,
        $installment['customer_id'],
        $installment['property_id'],
        $totalAmount,
        $_POST['payment_method'],
        $description,
        $_POST['payment_date'],
        $_SESSION['admin_id']
    ]);
    $paymentId = \App\Core\App::database()->lastInsertId();

    // Update installment status
    $updateQuery = "UPDATE emi_installments
                   SET payment_status = 'paid',
                       payment_date = ?,
                       payment_id = ?,
                       late_fee = ?,
                       updated_at = NOW()
                   WHERE id = ?";
    \App\Core\App::database()->execute($updateQuery, [
        $_POST['payment_date'],
        $paymentId,
        $lateFee,
        $installmentId
    ]);

    // Check if all installments are paid
    $checkQuery = "SELECT COUNT(*) as pending_count
                  FROM emi_installments
                  WHERE emi_plan_id = ? AND payment_status != 'paid'";
    $pendingCount = \App\Core\App::database()->fetchOne($checkQuery, [$installment['emi_plan_id']])['pending_count'];

    // If all installments are paid, update EMI plan status
    if ($pendingCount === 0) {
        $updatePlanQuery = "UPDATE emi_plans
                           SET status = 'completed',
                               updated_at = NOW()
                           WHERE id = ?";
        \App\Core\App::database()->execute($updatePlanQuery, [$installment['emi_plan_id']]);
    }

    // Create notification
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'emi_payment', ?, ?, ?, NOW())";

    $notificationTitle = h($mlSupport->translate('EMI Payment Received'));
    $notificationMessage = h($mlSupport->translate("Your EMI payment of ₹")) . h(number_format($totalAmount, 2)) . " " . h($mlSupport->translate("has been recorded."));
    $notificationLink = "payments/view.php?id=" . intval($paymentId);

    \App\Core\App::database()->execute($notificationQuery, [
        $installment['customer_id'],
        $notificationTitle,
        $notificationMessage,
        $notificationLink
    ]);

    // Commit the transaction
    \App\Core\App::database()->commit();

    echo json_encode([
        'success' => true,
        'message' => h($mlSupport->translate('Payment recorded successfully')),
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
        'message' => h($mlSupport->translate('Failed to record payment') . ': ' . $e->getMessage())
    ]);
}
?>
