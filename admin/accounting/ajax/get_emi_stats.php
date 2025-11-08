<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();
    
    // Get current month
    $currentMonth = date('Y-m');
    
    // Get active EMI plans count
    $activeQuery = "SELECT COUNT(*) as count FROM emi_plans WHERE status = 'active'";
    $activeCount = $conn->query($activeQuery)->fetch_assoc()['count'];
    
    // Get monthly EMI collection
    $collectionQuery = "SELECT COALESCE(SUM(amount), 0) as total 
                       FROM emi_installments ei 
                       JOIN emi_plans ep ON ei.emi_plan_id = ep.id 
                       WHERE ep.status = 'active' 
                       AND DATE_FORMAT(ei.due_date, '%Y-%m') = ?";
    $stmt = $conn->prepare($collectionQuery);
    $stmt->bind_param("s", $currentMonth);
    $stmt->execute();
    $monthlyCollection = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get pending EMIs count (due this month)
    $pendingQuery = "SELECT COUNT(*) as count 
                    FROM emi_installments ei 
                    JOIN emi_plans ep ON ei.emi_plan_id = ep.id 
                    WHERE ep.status = 'active' 
                    AND ei.payment_status = 'pending' 
                    AND DATE_FORMAT(ei.due_date, '%Y-%m') = ?";
    $stmt = $conn->prepare($pendingQuery);
    $stmt->bind_param("s", $currentMonth);
    $stmt->execute();
    $pendingCount = $stmt->get_result()->fetch_assoc()['count'];
    
    // Get overdue EMIs count
    $overdueQuery = "SELECT COUNT(*) as count 
                    FROM emi_installments ei 
                    JOIN emi_plans ep ON ei.emi_plan_id = ep.id 
                    WHERE ep.status = 'active' 
                    AND ei.payment_status IN ('pending', 'overdue') 
                    AND ei.due_date < CURDATE()";
    $overdueCount = $conn->query($overdueQuery)->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'active_count' => $activeCount,
            'monthly_collection' => '₹' . number_format($monthlyCollection, 2),
            'pending_count' => $pendingCount,
            'overdue_count' => $overdueCount
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch EMI statistics: ' . $e->getMessage()
    ]);
}
?>
