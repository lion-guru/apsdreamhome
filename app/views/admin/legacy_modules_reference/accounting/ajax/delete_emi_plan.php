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

// RBAC Protection - Only Super Admin and Manager can delete EMI plans
$currentRole = $_SESSION['admin_role'] ?? '';
if ($currentRole !== 'superadmin' && $currentRole !== 'manager') {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can delete EMI plans'))
    ]);
    exit;
}

try {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception(h($mlSupport->translate('Missing EMI plan ID')));
    }

    $emiPlanId = intval($_POST['id']);

    // Start transaction
    \App\Core\App::database()->beginTransaction();

    // Check if the plan has any paid installments
    $checkQuery = "SELECT COUNT(*) as paid_count FROM emi_installments WHERE emi_plan_id = ? AND payment_status = 'paid'";
    $paidRow = \App\Core\App::database()->fetchOne($checkQuery, [$emiPlanId]);
    $paidCount = $paidRow ? intval($paidRow['paid_count']) : 0;

    if ($paidCount > 0) {
        throw new Exception(h($mlSupport->translate('Cannot delete EMI plan with paid installments. Consider foreclosure instead.')));
    }

    // Delete installments first
    $deleteInstallments = "DELETE FROM emi_installments WHERE emi_plan_id = ?";
    \App\Core\App::database()->execute($deleteInstallments, [$emiPlanId]);

    // Delete the plan
    $deletePlan = "DELETE FROM emi_plans WHERE id = ?";
    \App\Core\App::database()->execute($deletePlan, [$emiPlanId]);

    // Audit Logging
    if (function_exists('log_admin_activity')) {
        log_admin_activity(
            'emi_plan_deleted',
            "EMI plan #" . intval($emiPlanId) . " deleted",
            ['emi_plan_id' => intval($emiPlanId)]
        );
    }

    \App\Core\App::database()->commit();

    echo json_encode([
        'success' => true,
        'message' => h($mlSupport->translate('EMI plan deleted successfully'))
    ]);

} catch (Exception $e) {
    \App\Core\App::database()->rollBack();

    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to delete EMI plan') . ': ' . $e->getMessage())
    ]);
}
?>
