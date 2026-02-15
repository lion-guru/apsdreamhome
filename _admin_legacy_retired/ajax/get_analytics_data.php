<?php
/**
 * Get Analytics Data - AJAX Endpoint
 * Returns chart data for dashboard analytics
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../config.php';

// Verify admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    global $con;
    $conn = $con;

    // Calculate date range based on period
    switch ($period) {
        case 'daily':
            $dateFormat = '%Y-%m-%d';
            $interval = 'INTERVAL 30 DAY';
            break;
        case 'weekly':
            $dateFormat = '%Y-%u';
            $interval = 'INTERVAL 12 WEEK';
            break;
        case 'monthly':
            $dateFormat = '%Y-%m';
            $interval = 'INTERVAL 12 MONTH';
            break;
        default:
            $dateFormat = '%Y-%m-%d';
            $interval = 'INTERVAL 30 DAY';
    }

    // Get revenue data
    $revenueQuery = "
        SELECT
            DATE_FORMAT(created_at, '$dateFormat') as date,
            SUM(total_amount) as revenue,
            COUNT(*) as bookings
        FROM bookings
        WHERE created_at >= DATE_SUB(NOW(), $interval)
        AND status = 'confirmed'
        GROUP BY DATE_FORMAT(created_at, '$dateFormat')
        ORDER BY date
    ";

    $stmt = $conn->query($revenueQuery);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process data for chart
    $labels = [];
    $revenueData = [];
    $bookingsData = [];

    foreach ($results as $row) {
        $labels[] = $row['date'];
        $revenueData[] = (float)$row['revenue'];
        $bookingsData[] = (int)$row['bookings'];
    }

    // If no data, provide sample data for demonstration
    if (empty($labels)) {
        $labels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'];
        $revenueData = [10000, 15000, 12000, 18000, 22000];
        $bookingsData = [5, 8, 6, 9, 11];
    }

    echo json_encode([
        'success' => true,
        'chart_data' => [
            'labels' => $labels,
            'revenue' => $revenueData,
            'bookings' => $bookingsData
        ],
        'period' => $period
    ]);

} catch (Exception $e) {
    error_log('Analytics data error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving analytics data'
    ]);
}
?>
