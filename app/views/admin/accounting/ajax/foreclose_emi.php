<?php
require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

// CSRF Protection
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin and Manager can foreclose EMI plans
$currentRole = $_SESSION['admin_role'] ?? '';
if ($currentRole !== 'superadmin' && $currentRole !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can foreclose EMI plans'))
    ]);
    exit;
}

try {
    // Validate required fields
    $requiredFields = ['emi_plan_id'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception($mlSupport->translate('Missing required field') . ': ' . $field);
        }
    }

    $emiPlanId = intval($_POST['emi_plan_id']);

    // Start transaction
    \App\Core\App::database()->beginTransaction();

    // Get EMI plan details with additional validation
    $query = "SELECT ep.*,
                     c.name as customer_name,
                     c.email as customer_email,
                     p.title as property_title,
                     (SELECT COUNT(*) FROM emi_installments
                      WHERE emi_plan_id = ep.id AND payment_status = 'paid') as paid_installments,
                     (SELECT COUNT(*) FROM emi_installments
                      WHERE emi_plan_id = ep.id AND payment_status = 'pending') as pending_installments
              FROM emi_plans ep
              JOIN customers c ON ep.customer_id = c.id
              JOIN properties p ON ep.property_id = p.id
              WHERE ep.id = ? AND ep.status = 'active'";
    $emiPlan = \App\Core\App::database()->fetchOne($query, [$emiPlanId]);

    // Validate EMI plan
    if (!$emiPlan) {
        throw new Exception($mlSupport->translate('EMI plan not found or not active'));
    }

    // Check minimum tenure for foreclosure
    $minTenureMonths = 6; // Minimum 6 months of EMI payments
    if ($emiPlan['paid_installments'] < $minTenureMonths) {
        throw new Exception($mlSupport->translate('Foreclosure not allowed') . ". " . $mlSupport->translate('Minimum') . " $minTenureMonths " . $mlSupport->translate('months of payments required') . ".");
    }

    // Prevent multiple foreclosure attempts
    $checkPreviousQuery = "SELECT COUNT(*) as foreclosure_count
                           FROM emi_plans
                           WHERE id = ? AND (foreclosure_date IS NOT NULL OR status = 'completed')";
    $previousForeclosure = \App\Core\App::database()->fetchOne($checkPreviousQuery, [$emiPlanId]);

    if ($previousForeclosure && $previousForeclosure['foreclosure_count'] > 0) {
        throw new Exception($mlSupport->translate('This EMI plan has already been foreclosed'));
    }

    // Calculate remaining principal
    $query = "SELECT
                SUM(CASE WHEN payment_status = 'paid' THEN principal_component ELSE 0 END) as total_principal_paid,
                SUM(CASE WHEN payment_status = 'pending' THEN principal_component ELSE 0 END) as remaining_principal
              FROM emi_installments
              WHERE emi_plan_id = ?";
    $result = \App\Core\App::database()->fetchOne($query, [$emiPlanId]);
    $totalPrincipalPaid = $result['total_principal_paid'] ?? 0;

    // Calculate remaining amount
    $remainingPrincipal = $emiPlan['total_amount'] - $totalPrincipalPaid;

    // Apply foreclosure charges (2% of remaining principal)
    $foreclosureCharge = $remainingPrincipal * 0.02;
    $totalForeclosureAmount = $remainingPrincipal + $foreclosureCharge;

    // Create foreclosure payment record
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
                    ) VALUES (?, ?, ?, ?, 'emi_foreclosure', ?, ?, 'completed', NOW(), ?)";

    $transactionId = 'FORE' . \time() . \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);
    $description = "EMI Plan Foreclosure Payment (Including 2% foreclosure charge)";
    $paymentMethod = $_POST['payment_method'] ?? 'bank_transfer';

    \App\Core\App::database()->execute($paymentQuery, [
        $transactionId,
        $emiPlan['customer_id'],
        $emiPlan['property_id'],
        $totalForeclosureAmount,
        $paymentMethod,
        $description,
        $_SESSION['admin_id']
    ]);
    $paymentId = \App\Core\App::database()->lastInsertId();

    // Update remaining installments
    $updateInstallments = "UPDATE emi_installments
                          SET payment_status = 'cancelled'
                          WHERE emi_plan_id = ? AND payment_status = 'pending'";
    \App\Core\App::database()->execute($updateInstallments, [$emiPlanId]);

    // Update EMI plan status
    $updatePlan = "UPDATE emi_plans
                   SET status = 'completed',
                       foreclosure_date = NOW(),
                       foreclosure_amount = ?,
                       foreclosure_payment_id = ?,
                       updated_at = NOW()
                   WHERE id = ?";
    \App\Core\App::database()->execute($updatePlan, [$totalForeclosureAmount, $paymentId, $emiPlanId]);

    // Create notification
    $notificationQuery = "INSERT INTO notifications (
                            user_id,
                            type,
                            title,
                            message,
                            link,
                            created_at
                         ) VALUES (?, 'emi_foreclosure', ?, ?, ?, NOW())";

    $notificationTitle = h($mlSupport->translate('EMI Plan Foreclosed'));
    $notificationMessage = h($mlSupport->translate("Your EMI plan has been foreclosed. Foreclosure amount paid: ₹")) .
                          h(number_format($totalForeclosureAmount, 2));
    $notificationLink = "payments/view.php?id=" . intval($paymentId);

    \App\Core\App::database()->execute($notificationQuery, [
        $emiPlan['customer_id'],
        $notificationTitle,
        $notificationMessage,
        $notificationLink
    ]);

    // Audit Logging
    if (function_exists('log_admin_activity')) {
        log_admin_activity(
            'emi_foreclosure',
            "EMI plan #" . intval($emiPlanId) . " foreclosed",
            ['emi_plan_id' => intval($emiPlanId), 'amount' => $totalForeclosureAmount, 'payment_id' => intval($paymentId)]
        );
    }

    // Commit transaction
    \App\Core\App::database()->commit();

    echo json_encode([
        'success' => true,
        'message' => h($mlSupport->translate('EMI plan foreclosed successfully')),
        'data' => [
            'payment_id' => $paymentId,
            'transaction_id' => $transactionId,
            'foreclosure_amount' => $totalForeclosureAmount
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction
    \App\Core\App::database()->rollBack();

    error_log('EMI Foreclosure Error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to foreclose EMI plan') . ': ' . $e->getMessage())
    ]);
}
?>
