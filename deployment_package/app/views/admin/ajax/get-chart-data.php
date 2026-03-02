<?php
/**
 * AJAX handler for chart data
 * Provides dynamic data for dashboard charts
 */

// Start session and include necessary files
require_once __DIR__ . '/../core/init.php';

use App\Core\Database;
$db = \App\Core\App::database();

ensureSessionStarted();

// Check if user is logged in
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => h($mlSupport->translate('Unauthorized access'))
    ]);
    exit;
}

// CSRF validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => h($mlSupport->translate('Security validation failed'))
    ]);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => h($mlSupport->translate('Invalid request')),
    'data' => []
];

// Get chart type from request
$chartType = isset($_GET['chart']) ? $_GET['chart'] : '';

// Handle different chart types
switch ($chartType) {
    case 'sales_overview':
        getMonthlySalesData($db, $response, $mlSupport);
        break;

    case 'inventory_status':
        getInventoryStatusData($db, $response, $mlSupport);
        break;

    default:
        $response['message'] = h($mlSupport->translate('Unknown chart type'));
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Get monthly sales and bookings for the past 6 months
 */
function getMonthlySalesData($db, &$response, $mlSupport) {
    try {
        $data = [];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $months[$month] = ['month' => date('M Y', strtotime("-$i months")), 'sales' => 0, 'bookings' => 0];
        }

        // Monthly Bookings
        $sql = "SELECT DATE_FORMAT(booking_date, '%Y-%m') as m, COUNT(*) as cnt FROM bookings WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY m";
        $results = $db->fetchAll($sql);
        foreach ($results as $row) {
            if (isset($months[$row['m']])) $months[$row['m']]['bookings'] = (int)$row['cnt'];
        }

        // Monthly Sales (Revenue)
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as m, SUM(amount) as amt FROM transactions WHERE status = 'completed' AND date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY m";
        $results = $db->fetchAll($sql);
        foreach ($results as $row) {
            if (isset($months[$row['m']])) $months[$row['m']]['sales'] = (float)$row['amt'];
        }

        $response['status'] = 'success';
        $response['data'] = array_values($months);
        $response['message'] = h($mlSupport->translate('Data fetched successfully'));
    } catch (Exception $e) {
        $response['message'] = h($e->getMessage());
    }
}

/**
 * Get current inventory status distribution
 */
function getInventoryStatusData($db, &$response, $mlSupport) {
    try {
        $sql = "SELECT status, COUNT(*) as count FROM plots GROUP BY status";
        $results = $db->fetchAll($sql);
        $data = [];
        foreach ($results as $row) {
            $data[] = ['label' => h(ucfirst($row['status'])), 'value' => (int)$row['count']];
        }
        $response['status'] = 'success';
        $response['data'] = $data;
        $response['message'] = h($mlSupport->translate('Data fetched successfully'));
    } catch (Exception $e) {
        $response['message'] = h($e->getMessage());
    }
}
