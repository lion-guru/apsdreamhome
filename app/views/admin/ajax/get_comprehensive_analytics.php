<?php
/**
 * Get Comprehensive Analytics Data - AJAX Endpoint
 * Returns filtered data for the main analytics dashboard
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../../includes/performance_manager.php';

// Verify admin authentication (handled by core/init.php, but good to be explicit)
if (!isAuthenticated() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

// CSRF validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

try {
    $perfManager = getPerformanceManager();

    $period = $_GET['period'] ?? 'year';
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;

    // Basic sanitization for period/dates if they are used in queries or output
    $period = h($period);
    $start_date = $start_date ? h($start_date) : null;
    $end_date = $end_date ? h($end_date) : null;

    $where_clause = "status='confirmed'";
    $params = [];
    if ($start_date && $end_date) {
        $where_clause .= " AND booking_date BETWEEN ? AND ?";
        $params = [$start_date, $end_date];
    } elseif ($period) {
        switch ($period) {
            case 'today':
                $where_clause .= " AND DATE(booking_date) = CURDATE()";
                break;
            case 'week':
                $where_clause .= " AND YEARWEEK(booking_date, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'month':
                $where_clause .= " AND MONTH(booking_date) = MONTH(CURDATE()) AND YEAR(booking_date) = YEAR(CURDATE())";
                break;
            case 'year':
                $where_clause .= " AND YEAR(booking_date) = YEAR(CURDATE())";
                break;
        }
    }

    // 1. Overview Metrics
    $revenue = $perfManager->executeCachedQuery("SELECT SUM(amount) as sum FROM bookings WHERE $where_clause", $params, 60)[0]['sum'] ?? 0;
    $props = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM bookings WHERE $where_clause AND property_id IS NOT NULL", $params, 60)[0]['cnt'] ?? 0;
    $plots = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM bookings WHERE $where_clause AND plot_id IS NOT NULL", $params, 60)[0]['cnt'] ?? 0;
    $associates = $perfManager->executeCachedQuery("SELECT COUNT(*) as cnt FROM associates WHERE status='active'", [], 300)[0]['cnt'] ?? 0;

    // 2. Sales Trend
    $salesTrend = $perfManager->executeCachedQuery("
        SELECT
            DATE_FORMAT(booking_date, '%b %Y') as month,
            SUM(CASE WHEN property_id IS NOT NULL THEN amount ELSE 0 END) as property_sales,
            SUM(CASE WHEN plot_id IS NOT NULL THEN amount ELSE 0 END) as plot_sales
        FROM bookings
        WHERE $where_clause
        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
        ORDER BY booking_date ASC
    ", $params, 60);

    // 3. Revenue Breakdown
    $rb = $perfManager->executeCachedQuery("
        SELECT
            SUM(CASE WHEN property_id IS NOT NULL THEN amount ELSE 0 END) as property_sales,
            SUM(CASE WHEN plot_id IS NOT NULL THEN amount ELSE 0 END) as plot_sales,
            (SELECT COALESCE(SUM(commission_amount), 0) FROM commission_transactions WHERE status='paid') as commission_income
        FROM bookings
        WHERE $where_clause
    ", $params, 60)[0];

    // Sanitize salesTrend data
    foreach ($salesTrend as &$trend) {
        $trend['month'] = h($trend['month']);
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'overview' => [
                'total_revenue' => (float)$revenue,
                'total_properties_sold' => (int)$props,
                'total_plots_sold' => (int)$plots,
                'total_associates' => (int)$associates
            ],
            'sales_performance' => $salesTrend,
            'revenue_breakdown' => $rb
        ]
    ]);

} catch (Exception $e) {
    error_log('Comprehensive analytics error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Internal Server Error'))]);
}
