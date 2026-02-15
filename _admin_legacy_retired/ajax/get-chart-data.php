<?php
/**
 * AJAX handler for chart data
 * Provides dynamic data for dashboard charts
 */

// Start session and include necessary files
session_start();
require_once '../config.php';
require_once '../includes/csrf_protection.php';

// Check if user is logged in
if (!isset($_SESSION['auser'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Invalid request',
    'data' => []
];

// Get chart type from request
$chartType = isset($_GET['chart']) ? $_GET['chart'] : '';

// Handle different chart types
switch ($chartType) {
    case 'sales_overview':
        getSalesOverviewData($conn, $response);
        break;
    
    case 'order_status':
        getOrderStatusData($conn, $response);
        break;
    
    default:
        $response['message'] = 'Unknown chart type';
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Get sales overview data for the past 6 years
 */
function getSalesOverviewData($conn, &$response) {
    try {
        // Get current year
        $currentYear = date('Y');
        $startYear = $currentYear - 5;
        
        // Initialize data array with zeros for all years
        $data = [];
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $data[] = ['y' => (string)$year, 'a' => 0, 'b' => 0];
        }
        
        // Query for total sales by year
        $salesQuery = "SELECT YEAR(booking_date) as year, COUNT(*) as total_sales 
                      FROM bookings 
                      WHERE YEAR(booking_date) >= ? 
                      GROUP BY YEAR(booking_date)";
        
        $stmt = $conn->prepare($salesQuery);
        $stmt->bind_param("i", $startYear);
        $stmt->execute();
        $salesResult = $stmt->get_result();
        
        // Query for total revenue by year
        $revenueQuery = "SELECT YEAR(date) as year, SUM(amount) as total_revenue 
                        FROM transactions 
                        WHERE status = 'completed' AND YEAR(date) >= ? 
                        GROUP BY YEAR(date)";
        
        $stmt = $conn->prepare($revenueQuery);
        $stmt->bind_param("i", $startYear);
        $stmt->execute();
        $revenueResult = $stmt->get_result();
        
        // Process sales data
        while ($row = $salesResult->fetch_assoc()) {
            $year = $row['year'];
            $index = $year - $startYear;
            if (isset($data[$index])) {
                $data[$index]['a'] = (int)$row['total_sales'];
            }
        }
        
        // Process revenue data
        while ($row = $revenueResult->fetch_assoc()) {
            $year = $row['year'];
            $index = $year - $startYear;
            if (isset($data[$index])) {
                $data[$index]['b'] = (int)$row['total_revenue'];
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Data retrieved successfully';
        $response['data'] = $data;
        
    } catch (Exception $e) {
        $response['message'] = 'Error retrieving sales data: ' . $e->getMessage();
        error_log('Chart data error: ' . $e->getMessage());
    }
}

/**
 * Get order status data for all months of the current year
 */
function getOrderStatusData($conn, &$response) {
    try {
        // Get current year
        $currentYear = date('Y');
        
        // Month names
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Initialize data array with zeros for all months
        $data = [];
        for ($i = 0; $i < 12; $i++) {
            $data[] = ['y' => $months[$i], 'a' => 0, 'b' => 0];
        }
        
        // Query for total orders by month
        $totalOrdersQuery = "SELECT MONTH(booking_date) as month, COUNT(*) as total_orders 
                           FROM bookings 
                           WHERE YEAR(booking_date) = ? 
                           GROUP BY MONTH(booking_date)";
        
        $stmt = $conn->prepare($totalOrdersQuery);
        $stmt->bind_param("i", $currentYear);
        $stmt->execute();
        $totalOrdersResult = $stmt->get_result();
        
        // Query for completed orders by month
        $completedOrdersQuery = "SELECT MONTH(booking_date) as month, COUNT(*) as completed_orders 
                               FROM bookings 
                               WHERE status = 'completed' AND YEAR(booking_date) = ? 
                               GROUP BY MONTH(booking_date)";
        
        $stmt = $conn->prepare($completedOrdersQuery);
        $stmt->bind_param("i", $currentYear);
        $stmt->execute();
        $completedOrdersResult = $stmt->get_result();
        
        // Process total orders data
        while ($row = $totalOrdersResult->fetch_assoc()) {
            $month = (int)$row['month'] - 1; // Adjust for 0-based array
            if (isset($data[$month])) {
                $data[$month]['a'] = (int)$row['total_orders'];
            }
        }
        
        // Process completed orders data
        while ($row = $completedOrdersResult->fetch_assoc()) {
            $month = (int)$row['month'] - 1; // Adjust for 0-based array
            if (isset($data[$month])) {
                $data[$month]['b'] = (int)$row['completed_orders'];
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Data retrieved successfully';
        $response['data'] = $data;
        
    } catch (Exception $e) {
        $response['message'] = 'Error retrieving order data: ' . $e->getMessage();
        error_log('Chart data error: ' . $e->getMessage());
    }
}