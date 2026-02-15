<?php
require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin, Admin, and Manager can view EMI stats
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to view EMI statistics'))
    ]);
    exit;
}

try {
    // Get current month
    $currentMonth = date('Y-m');

    // Get active EMI plans count
    $activeQuery = "SELECT COUNT(*) as count FROM emi_plans WHERE status = 'active'";
    $activeData = \App\Core\App::database()->fetchOne($activeQuery);
    $activeCount = $activeData['count'] ?? 0;

    // Get monthly EMI collection
    $collectionQuery = "SELECT COALESCE(SUM(amount), 0) as total
                       FROM emi_installments ei
                       JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                       WHERE ep.status = 'active'
                       AND DATE_FORMAT(ei.due_date, '%Y-%m') = ?";
    $collectionData = \App\Core\App::database()->fetchOne($collectionQuery, [$currentMonth]);
    $monthlyCollection = $collectionData['total'] ?? 0;

    // Get pending EMIs count (due this month)
    $pendingQuery = "SELECT COUNT(*) as count
                    FROM emi_installments ei
                    JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                    WHERE ep.status = 'active'
                    AND ei.payment_status = 'pending'
                    AND DATE_FORMAT(ei.due_date, '%Y-%m') = ?";
    $pendingData = \App\Core\App::database()->fetchOne($pendingQuery, [$currentMonth]);
    $pendingCount = $pendingData['count'] ?? 0;

    // Get overdue EMIs count
    $overdueQuery = "SELECT COUNT(*) as count
                    FROM emi_installments ei
                    JOIN emi_plans ep ON ei.emi_plan_id = ep.id
                    WHERE ep.status = 'active'
                    AND ei.payment_status IN ('pending', 'overdue')
                    AND ei.due_date < CURDATE()";
    $overdueData = \App\Core\App::database()->fetchOne($overdueQuery);
    $overdueCount = $overdueData['count'] ?? 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'active_count' => h($activeCount),
            'monthly_collection' => '₹' . h(number_format($monthlyCollection, 2)),
            'pending_count' => h($pendingCount),
            'overdue_count' => h($overdueCount)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to fetch EMI statistics')) . ': ' . h($e->getMessage())
    ]);
}
?>
