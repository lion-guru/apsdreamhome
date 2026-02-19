<?php

/**
 * Consolidated Dashboard API
 * Single endpoint for all dashboard AJAX requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../core/init.php';

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

// CSRF Validation
$csrf_token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Invalid CSRF token'))]);
    exit();
}

// Get request type
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$currentRole = $_SESSION['admin_role'] ?? '';

try {
    $db = \App\Core\App::database();

    switch ($action) {
        case 'get_stats':
            getDashboardStats($db);
            break;

        case 'get_analytics':
            getAnalyticsData($db);
            break;

        case 'get_financial':
            if (!in_array($currentRole, ['superadmin', 'manager'])) {
                echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can access financial data'))]);
                exit;
            }
            getFinancialData($db);
            break;

        case 'get_performance':
            if ($currentRole !== 'superadmin') {
                echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin can access performance data'))]);
                exit;
            }
            getPerformanceData($db);
            break;

        case 'get_users':
            if (!in_array($currentRole, ['superadmin', 'manager'])) {
                echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can access user data'))]);
                exit;
            }
            getUsersData($db);
            break;

        case 'get_properties':
            getPropertiesData($db);
            break;

        case 'get_bookings':
            getBookingsData($db);
            break;

        case 'global_search':
            globalSearch($db, $currentRole);
            break;

        case 'get_activity':
            getActivityData($db);
            break;

        case 'export_data':
            exportData($db, $currentRole);
            break;

        default:
            echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Invalid action'))]);
    }
} catch (Exception $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Server error occurred'))
    ]);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($db)
{
    $stats = [];

    // Total users
    $row = $db->fetch("SELECT COUNT(*) as total FROM user");
    $stats['total_users'] = $row['total'] ?? 0;

    // Total properties
    $row = $db->fetch("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");
    $stats['total_properties'] = $row['total'] ?? 0;

    // Total bookings
    $row = $db->fetch("SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'");
    $stats['total_bookings'] = $row['total'] ?? 0;

    // Monthly revenue
    $row = $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stats['monthly_revenue'] = $row['total'] ?? 0;

    // Recent activity
    $row = $db->fetch("SELECT COUNT(*) as total FROM user_activity WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stats['recent_activity'] = $row['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Get analytics data for charts
 */
function getAnalyticsData($db)
{
    // Revenue trend (last 6 months)
    $revenue_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $row = $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND DATE_FORMAT(created_at, '%Y-%m') = :month", ['month' => $month]);
        $revenue_data[] = $row['total'] ?? 0;
    }

    // Property types
    $property_types = [];
    $rows = $db->fetchAll("SELECT type, COUNT(*) as count FROM properties WHERE status = 'active' GROUP BY type");
    foreach ($rows as $row) {
        $property_types[] = [
            'type' => $row['type'],
            'count' => $row['count']
        ];
    }

    echo json_encode([
        'success' => true,
        'revenue_trend' => $revenue_data,
        'property_types' => $property_types
    ]);
}

/**
 * Get financial data
 */
function getFinancialData($db)
{
    $financial = [];

    // Cash in hand
    $row = $db->fetch("SELECT SUM(current_balance) as total FROM chart_of_accounts WHERE account_code = '1110'");
    $financial['cash_in_hand'] = $row['total'] ?? 0;

    // Bank balance
    $row = $db->fetch("SELECT SUM(current_balance) as total FROM bank_accounts WHERE status = 'active'");
    $financial['bank_balance'] = $row['total'] ?? 0;

    // Receivables
    $row = $db->fetch("SELECT SUM(current_balance) as total FROM customers_ledger WHERE current_balance > 0");
    $financial['receivables'] = $row['total'] ?? 0;

    // Payables
    $row = $db->fetch("SELECT SUM(current_balance) as total FROM suppliers WHERE current_balance > 0");
    $financial['payables'] = $row['total'] ?? 0;

    // Monthly income/expense
    $current_month = date('Y-m');
    $row = $db->fetch("SELECT SUM(amount) as total FROM income_records WHERE DATE_FORMAT(income_date, '%Y-%m') = :month AND status = 'received'", ['month' => $current_month]);
    $financial['monthly_income'] = $row['total'] ?? 0;

    $row = $db->fetch("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = :month AND status = 'paid'", ['month' => $current_month]);
    $financial['monthly_expense'] = $row['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'financial' => $financial
    ]);
}

/**
 * Get performance data
 */
function getPerformanceData($db)
{
    $performance = [
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        'file_count' => count(get_included_files()),
        'database_queries' => $GLOBALS['db_query_count'] ?? 0
    ];

    echo json_encode([
        'success' => true,
        'performance' => $performance
    ]);
}

/**
 * Get users data
 */
function getUsersData($db)
{
    $users = [];
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $search = $_GET['search'] ?? '';
    $where = $search ? "WHERE u.name LIKE :search OR u.email LIKE :search OR u.role LIKE :search" : "";
    $params = $search ? ['search' => "%$search%"] : [];

    $sql = "SELECT u.id as id, u.name as name, u.email as email, u.role as role,
                   COALESCE(a.status, 'active') as status, u.created_at as created_at
            FROM users u
            LEFT JOIN associates a ON u.id = a.user_id
            $where
            ORDER BY u.created_at DESC
            LIMIT $limit OFFSET $offset";

    $users = $db->fetchAll($sql, $params);

    // Sanitize data
    foreach ($users as &$user) {
        $user['name'] = h($user['name']);
        $user['email'] = h($user['email']);
        $user['role'] = h($user['role']);
        $user['status'] = h($user['status']);
    }

    // Get total count
    $row = $db->fetch("SELECT COUNT(*) as total FROM user u $where", $params);
    $total = $row['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => $total
        ]
    ]);
}

/**
 * Get properties data
 */
function getPropertiesData($db)
{
    $properties = [];
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $search = $_GET['search'] ?? '';
    $where = $search ? "WHERE title LIKE :search OR location LIKE :search OR description LIKE :search" : "";
    $params = $search ? ['search' => "%$search%"] : [];

    $sql = "SELECT id, title, location, price, status, type, created_at FROM properties $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $properties = $db->fetchAll($sql, $params);

    // Sanitize data
    foreach ($properties as &$property) {
        $property['title'] = h($property['title']);
        $property['location'] = h($property['location']);
        $property['status'] = h($property['status']);
        $property['type'] = h($property['type']);
    }

    // Get total count
    $row = $db->fetch("SELECT COUNT(*) as total FROM properties $where", $params);
    $total = $row['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'properties' => $properties,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => $total
        ]
    ]);
}

/**
 * Get bookings data
 */
function getBookingsData($db)
{
    $bookings = [];
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $search = $_GET['search'] ?? '';
    $where = $search ? "WHERE b.status LIKE :search OR u.uname LIKE :search OR p.title LIKE :search" : "";
    $params = $search ? ['search' => "%$search%"] : [];

    $sql = "SELECT b.*, u.uname as customer_name, p.title as property_title
            FROM bookings b
            LEFT JOIN user u ON b.user_id = u.uid
            LEFT JOIN properties p ON b.property_id = p.id
            $where
            ORDER BY b.created_at DESC
            LIMIT $limit OFFSET $offset";

    $bookings = $db->fetchAll($sql, $params);

    // Sanitize data
    foreach ($bookings as &$booking) {
        $booking['customer_name'] = h($booking['customer_name'] ?? '');
        $booking['property_title'] = h($booking['property_title'] ?? '');
        $booking['status'] = h($booking['status']);
    }

    // Get total count
    $row = $db->fetch("SELECT COUNT(*) as total FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN properties p ON b.property_id = p.id $where", $params);
    $total = $row['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => $total
        ]
    ]);
}

/**
 * Global search functionality
 */
function globalSearch($db, $currentRole)
{
    global $mlSupport;
    $query = trim($_GET['q'] ?? '');

    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Search query too short'))]);
        return;
    }

    $results = [];
    $searchTerm = "%$query%";

    // Search in users - Restricted to superadmin/manager
    if (in_array($currentRole, ['superadmin', 'manager'])) {
        $rows = $db->fetchAll("SELECT u.id as id, u.name as name, u.email as email, u.role as role, COALESCE(a.status, 'active') as status FROM users u LEFT JOIN associates a ON u.id = a.user_id WHERE u.name LIKE :search OR u.email LIKE :search OR u.role LIKE :search LIMIT 5", ['search' => $searchTerm]);

        foreach ($rows as $row) {
            $results[] = [
                'title' => h($row['name']) . ' (' . h($row['role']) . ')',
                'description' => h($row['email']) . ' - ' . h(ucfirst($row['status'])),
                'url' => BASE_URL . 'admin/consolidated_dashboard.php#users',
                'type' => 'User',
                'icon' => 'fas fa-user'
            ];
        }
    }

    // Search in properties
    $rows = $db->fetchAll("SELECT id, title, location, price, status FROM properties WHERE title LIKE :search OR location LIKE :search OR description LIKE :search LIMIT 5", ['search' => $searchTerm]);

    foreach ($rows as $row) {
        $results[] = [
            'title' => h($row['title']),
            'description' => h($row['location']) . ' - ₹' . number_format($row['price']) . ' - ' . h(ucfirst($row['status'])),
            'url' => BASE_URL . 'admin/consolidated_dashboard.php#properties',
            'type' => 'Property',
            'icon' => 'fas fa-building'
        ];
    }

    // Search in bookings
    $rows = $db->fetchAll("SELECT id, customer_name, property_title, total_amount, status FROM bookings WHERE customer_name LIKE :search OR property_title LIKE :search OR status LIKE :search LIMIT 5", ['search' => $searchTerm]);

    foreach ($rows as $row) {
        $results[] = [
            'title' => 'Booking #' . intval($row['id']),
            'description' => h($row['customer_name'] ?? '') . ' - ' . h($row['property_title'] ?? '') . ' - ₹' . number_format($row['total_amount']),
            'url' => BASE_URL . 'admin/consolidated_dashboard.php#bookings',
            'type' => 'Booking',
            'icon' => 'fas fa-calendar-check'
        ];
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'query' => h($query)
    ]);
}

/**
 * Get recent activity data
 */
function getActivityData($db)
{
    $activities = [];

    $rows = $db->fetchAll("SELECT * FROM user_activity ORDER BY created_at DESC LIMIT 10");
    foreach ($rows as $row) {
        $activities[] = [
            'id' => $row['id'],
            'user' => h($row['user_name'] ?? 'System'),
            'action' => h($row['action']),
            'description' => h($row['description']),
            'timestamp' => $row['created_at'],
            'icon' => getActivityIcon($row['action'])
        ];
    }

    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
}

/**
 * Get icon for activity type
 */
function getActivityIcon($action)
{
    $icons = [
        'login' => 'fas fa-sign-in-alt',
        'logout' => 'fas fa-sign-out-alt',
        'register' => 'fas fa-user-plus',
        'booking' => 'fas fa-calendar-check',
        'property_added' => 'fas fa-home',
        'payment' => 'fas fa-credit-card',
        'update' => 'fas fa-edit',
        'delete' => 'fas fa-trash'
    ];

    return $icons[$action] ?? 'fas fa-info-circle';
}

/**
 * Export data functionality
 */
function exportData($db, $currentRole)
{
    global $mlSupport;
    $format = $_GET['format'] ?? 'csv';
    $type = $_GET['type'] ?? 'stats';

    // RBAC for exports
    if ($type === 'users' && !in_array($currentRole, ['superadmin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can export user data'))]);
        return;
    }

    if ($type === 'financial' && !in_array($currentRole, ['superadmin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can export financial data'))]);
        return;
    }

    switch ($type) {
        case 'stats':
            exportStats($db, $format);
            break;
        case 'users':
            exportUsers($db, $format);
            break;
        case 'properties':
            exportProperties($db, $format);
            break;
        case 'bookings':
            exportBookings($db, $format);
            break;
        default:
            echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Invalid export type'))]);
    }
}

/**
 * Export statistics
 */
function exportStats($db, $format)
{
    $stats = [];

    // Get all statistics
    $row = $db->fetch("SELECT COUNT(*) as total FROM user u LEFT JOIN associates a ON u.uid = a.user_id WHERE COALESCE(a.status, 'active') = 'active'");
    $stats['users'] = $row['total'] ?? 0;

    $row = $db->fetch("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");
    $stats['properties'] = $row['total'] ?? 0;

    $row = $db->fetch("SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'");
    $stats['bookings'] = $row['total'] ?? 0;

    $row = $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stats['revenue'] = $row['total'] ?? 0;

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="dashboard_stats.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Metric', 'Value']);

        foreach ($stats as $key => $value) {
            fputcsv($output, [ucfirst(str_replace('_', ' ', $key)), $value]);
        }

        fclose($output);
    } else {
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
}

/**
 * Export users data
 */
function exportUsers($db, $format)
{
    $users = $db->fetchAll("SELECT u.uid as id, u.uname as name, u.uemail as email, u.job_role as role, COALESCE(a.status, 'active') as status, u.join_date as created_at FROM user u LEFT JOIN associates a ON u.uid = a.user_id ORDER BY u.join_date DESC");

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Status', 'Created At']);

        foreach ($users as $user) {
            fputcsv($output, [$user['id'], $user['name'], $user['email'], $user['role'], $user['status'], $user['created_at']]);
        }

        fclose($output);
    } else {
        echo json_encode(['success' => true, 'users' => $users]);
    }
}

/**
 * Export properties data
 */
function exportProperties($db, $format)
{
    $properties = $db->fetchAll("SELECT id, title, location, price, status, type, created_at FROM properties ORDER BY created_at DESC");

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="properties.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Title', 'Location', 'Price', 'Status', 'Type', 'Created At']);

        foreach ($properties as $property) {
            fputcsv($output, [$property['id'], $property['title'], $property['location'], $property['price'], $property['status'], $property['type'], $property['created_at']]);
        }

        fclose($output);
    } else {
        echo json_encode(['success' => true, 'properties' => $properties]);
    }
}

/**
 * Export bookings data
 */
function exportBookings($db, $format)
{
    $bookings = $db->fetchAll("SELECT b.*, u.uname as customer_name, p.title as property_title FROM bookings b LEFT JOIN user u ON b.user_id = u.uid LEFT JOIN properties p ON b.property_id = p.id ORDER BY b.created_at DESC");

    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Customer', 'Property', 'Amount', 'Status', 'Created At']);

        foreach ($bookings as $booking) {
            fputcsv($output, [$booking['id'], $booking['customer_name'], $booking['property_title'], $booking['total_amount'], $booking['status'], $booking['created_at']]);
        }

        fclose($output);
    } else {
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    }
}
