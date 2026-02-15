<?php
require_once dirname(__DIR__, 2) . '/core/init.php';

header('Content-Type: application/json');

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin, Admin, and Manager can view accounting stats
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to view accounting statistics'))
    ]);
    exit;
}

try {
    $db = \App\Core\App::database();

    // Get current month's data
    $currentMonth = date('Y-m');

    // Get Monthly Revenue
    $revenueQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments
                     WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?
                     AND status = 'completed'";
    $revenue_data = $db->fetch($revenueQuery, [$currentMonth]);
    $revenue = $revenue_data['total'] ?? 0;

    // Get Monthly Expenses
    $expensesQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses
                      WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?";
    $expenses_data = $db->fetch($expensesQuery, [$currentMonth]);
    $expenses = $expenses_data['total'] ?? 0;

    // Get Pending Payments Count
    $pendingQuery = "SELECT COUNT(*) as total FROM payments WHERE status = 'pending'";
    $pending_data = $db->fetch($pendingQuery);
    $pendingPayments = $pending_data['total'] ?? 0;

    // Calculate Monthly Profit
    $monthlyProfit = $revenue - $expenses;

    echo json_encode([
        'success' => true,
        'data' => [
            'monthly_revenue' => '₹' . h(number_format($revenue, 2)),
            'monthly_expenses' => '₹' . h(number_format($expenses, 2)),
            'pending_payments' => h($pendingPayments),
            'monthly_profit' => '₹' . h(number_format($monthlyProfit, 2))
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to fetch dashboard statistics')),
        'error' => h($e->getMessage())
    ]);
}
?>
