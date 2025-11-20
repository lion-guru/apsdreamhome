<?php
/**
 * Advanced Search - AJAX Endpoint
 * Advanced filtering and search across admin modules
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../config.php';
global $con;

// Verify admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $module = $_GET['module'] ?? 'all';
    $dateRange = $_GET['date_range'] ?? 'all';
    $status = $_GET['status'] ?? 'all';
    $sortBy = $_GET['sort'] ?? 'date';
    $query = trim($_GET['q'] ?? '');

    // Establish database connection
    $conn = $con;
    $results = [];

    // Build WHERE conditions
    $whereConditions = [];
    $params = [];

    if (!empty($query)) {
        $searchTerm = "%$query%";
        $whereConditions[] = "(name LIKE ? OR email LIKE ? OR title LIKE ? OR description LIKE ?)";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    if ($status !== 'all') {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }

    // Date range filter
    if ($dateRange !== 'all') {
        switch ($dateRange) {
            case 'today':
                $whereConditions[] = "DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $whereConditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $whereConditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $whereConditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
    }

    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

    // Search based on module
    switch ($module) {
        case 'users':
            $query = "SELECT id, name, email, role, status, created_at FROM users $whereClause ORDER BY $sortBy DESC LIMIT 20";
            break;
        case 'properties':
            $query = "SELECT id, title, location, price, status, created_at FROM properties $whereClause ORDER BY $sortBy DESC LIMIT 20";
            break;
        case 'projects':
            $query = "SELECT id, project_name, location, status, created_at FROM projects $whereClause ORDER BY $sortBy DESC LIMIT 20";
            break;
        case 'bookings':
            $query = "SELECT id, customer_name, property_title, total_amount, status, created_at FROM bookings $whereClause ORDER BY $sortBy DESC LIMIT 20";
            break;
        default: // All modules
            $allResults = [];

            // Users
            $stmt = $conn->prepare("SELECT id, name, email, role, status, created_at FROM users $whereClause ORDER BY $sortBy DESC LIMIT 5");
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $allResults[] = [
                    'title' => $row['name'] . ' (' . $row['role'] . ')',
                    'description' => $row['email'] . ' - ' . ucfirst($row['status']),
                    'url' => BASE_URL . 'admin/manage_users.php?user_id=' . $row['id'],
                    'type' => 'User'
                ];
            }

            // Properties
            $stmt = $conn->prepare("SELECT id, title, location, price, status, created_at FROM properties $whereClause ORDER BY $sortBy DESC LIMIT 5");
            $stmt->execute($params);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $allResults[] = [
                    'title' => $row['title'],
                    'description' => $row['location'] . ' - ₹' . number_format($row['price']) . ' - ' . ucfirst($row['status']),
                    'url' => BASE_URL . 'admin/properties.php?property_id=' . $row['id'],
                    'type' => 'Property'
                ];
            }

            echo json_encode([
                'success' => true,
                'results' => $allResults,
                'filters' => [
                    'module' => $module,
                    'date_range' => $dateRange,
                    'status' => $status,
                    'sort' => $sortBy
                ]
            ]);
            exit();
    }

    // Execute specific module search
    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        switch ($module) {
            case 'users':
                $results[] = [
                    'title' => $row['name'] . ' (' . $row['role'] . ')',
                    'description' => $row['email'] . ' - ' . ucfirst($row['status']),
                    'url' => BASE_URL . 'admin/manage_users.php?user_id=' . $row['id'],
                    'type' => 'User'
                ];
                break;
            case 'properties':
                $results[] = [
                    'title' => $row['title'],
                    'description' => $row['location'] . ' - ₹' . number_format($row['price']) . ' - ' . ucfirst($row['status']),
                    'url' => BASE_URL . 'admin/properties.php?property_id=' . $row['id'],
                    'type' => 'Property'
                ];
                break;
            case 'projects':
                $results[] = [
                    'title' => $row['project_name'],
                    'description' => $row['location'] . ' - ' . ucfirst($row['status']),
                    'url' => BASE_URL . 'admin/projects.php?project_id=' . $row['id'],
                    'type' => 'Project'
                ];
                break;
            case 'bookings':
                $results[] = [
                    'title' => $row['customer_name'] . ' - ' . $row['property_title'],
                    'description' => '₹' . number_format($row['total_amount']) . ' - ' . ucfirst($row['status']),
                    'url' => BASE_URL . 'admin/bookings.php?booking_id=' . $row['id'],
                    'type' => 'Booking'
                ];
                break;
        }
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'total_results' => count($results),
        'filters' => [
            'module' => $module,
            'date_range' => $dateRange,
            'status' => $status,
            'sort' => $sortBy
        ]
    ]);

} catch (Exception $e) {
    error_log('Advanced search error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error performing advanced search'
    ]);
}
?>
