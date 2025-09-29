<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();
    
    // Get current month's data
    $currentMonth = date('Y-m');
    
    // Get Monthly Revenue
    $revenueQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
                     WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? 
                     AND status = 'completed'";
    $stmt = $conn->prepare($revenueQuery);
    $stmt->bind_param("s", $currentMonth);
    $stmt->execute();
    $revenue = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get Monthly Expenses
    $expensesQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses 
                      WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?";
    $stmt = $conn->prepare($expensesQuery);
    $stmt->bind_param("s", $currentMonth);
    $stmt->execute();
    $expenses = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get Pending Payments Count
    $pendingQuery = "SELECT COUNT(*) as total FROM payments WHERE status = 'pending'";
    $stmt = $conn->prepare($pendingQuery);
    $stmt->execute();
    $pendingPayments = $stmt->get_result()->fetch_assoc()['total'];
    
    // Calculate Monthly Profit
    $monthlyProfit = $revenue - $expenses;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'monthly_revenue' => '₹' . number_format($revenue, 2),
            'monthly_expenses' => '₹' . number_format($expenses, 2),
            'pending_payments' => $pendingPayments,
            'monthly_profit' => '₹' . number_format($monthlyProfit, 2)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch dashboard statistics',
        'error' => $e->getMessage()
    ]);
}
?>
