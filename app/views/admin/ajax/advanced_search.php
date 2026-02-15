<?php
/**
 * Advanced Search - AJAX Endpoint
 * Advanced filtering and search across admin modules
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
if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Invalid CSRF token'))]);
    exit();
}

try {
    $module = $_GET['module'] ?? 'all';
    $dateRange = $_GET['date_range'] ?? 'all';
    $status = $_GET['status'] ?? 'all';
    $sortBy = $_GET['sort'] ?? 'date';
    $searchQuery = trim($_GET['q'] ?? '');

    // Establish database connection
    $db = \App\Core\App::database();
    $results = [];

    // RBAC Protection - Module specific restrictions
    $currentRole = $_SESSION['admin_role'] ?? '';
    if ($module === 'users' && !in_array($currentRole, ['superadmin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can search users'))]);
        exit;
    }

    // Build WHERE conditions
    $whereConditions = [];
    $params = [];

    if (!empty($searchQuery)) {
        $searchTerm = "%$searchQuery%";
        if ($module === 'users') {
            $whereConditions[] = "(u.uname LIKE ? OR u.uemail LIKE ? OR u.job_role LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        } else {
            $whereConditions[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
    }

    if ($status !== 'all') {
        if ($module === 'users') {
            $whereConditions[] = "COALESCE(a.status, 'active') = ?";
        } else {
            $whereConditions[] = "status = ?";
        }
        $params[] = $status;
    }

    // Date range filter
    if ($dateRange !== 'all') {
        $dateColumn = ($module === 'users') ? 'u.join_date' : 'created_at';
        switch ($dateRange) {
            case 'today':
                $whereConditions[] = "DATE($dateColumn) = CURDATE()";
                break;
            case 'week':
                $whereConditions[] = "$dateColumn >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $whereConditions[] = "$dateColumn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $whereConditions[] = "$dateColumn >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
    }

    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

    // Search based on module
    $sql = "";
    switch ($module) {
        case 'users':
            $sortColumn = ($sortBy === 'date') ? 'u.join_date' : 'u.uname';
            $sql = "SELECT u.uid as id, u.uname as name, u.uemail as email, u.job_role as role, COALESCE(a.status, 'active') as status, u.join_date as created_at FROM user u LEFT JOIN associates a ON u.uid = a.user_id $whereClause ORDER BY $sortColumn DESC LIMIT 20";
            break;
        case 'properties':
            $sortColumn = ($sortBy === 'date') ? 'created_at' : 'title';
            $sql = "SELECT id, title, location, price, status, created_at FROM properties $whereClause ORDER BY $sortColumn DESC LIMIT 20";
            break;
        case 'projects':
            $sortColumn = ($sortBy === 'date') ? 'created_at' : 'project_name';
            $sql = "SELECT id, project_name, location, status, created_at FROM projects $whereClause ORDER BY $sortColumn DESC LIMIT 20";
            break;
        case 'bookings':
            $sortColumn = ($sortBy === 'date') ? 'created_at' : 'customer_name';
            $sql = "SELECT id, customer_name, property_title, total_amount, status, created_at FROM bookings $whereClause ORDER BY $sortColumn DESC LIMIT 20";
            break;
        default: // All modules
            $allResults = [];

            // Users (Only for superadmin/manager)
            if (in_array($currentRole, ['superadmin', 'manager'])) {
                $userWhere = !empty($searchQuery) ? "WHERE (u.uname LIKE ? OR u.uemail LIKE ? OR u.job_role LIKE ?)" : "";
                $userParams = !empty($searchQuery) ? ["%$searchQuery%", "%$searchQuery%", "%$searchQuery%"] : [];
                $rows = $db->fetchAll("SELECT u.uid as id, u.uname as name, u.uemail as email, u.job_role as role, COALESCE(a.status, 'active') as status, u.join_date as created_at FROM user u LEFT JOIN associates a ON u.uid = a.user_id $userWhere ORDER BY u.join_date DESC LIMIT 5", $userParams);
                foreach ($rows as $row) {
                    $allResults[] = [
                        'title' => h($row['name']) . ' (' . h($row['role']) . ')',
                        'description' => h($row['email']) . ' - ' . h(ucfirst($mlSupport->translate($row['status']))),
                        'url' => BASE_URL . 'admin/manage_users.php?user_id=' . intval($row['id']),
                        'type' => h($mlSupport->translate('User'))
                    ];
                }
            }

            // Properties
            $propWhere = !empty($searchQuery) ? "WHERE (title LIKE ? OR location LIKE ? OR description LIKE ?)" : "";
            $propParams = !empty($searchQuery) ? ["%$searchQuery%", "%$searchQuery%", "%$searchQuery%"] : [];
            $rows = $db->fetchAll("SELECT id, title, location, price, status, created_at FROM properties $propWhere ORDER BY created_at DESC LIMIT 5", $propParams);
            foreach ($rows as $row) {
                $allResults[] = [
                    'title' => h($row['title']),
                    'description' => h($row['location']) . ' - ₹' . h(number_format($row['price'])) . ' - ' . h(ucfirst($mlSupport->translate($row['status']))),
                    'url' => BASE_URL . 'admin/properties.php?property_id=' . intval($row['id']),
                    'type' => h($mlSupport->translate('Property'))
                ];
            }

            echo json_encode([
                'success' => true,
                'results' => $allResults,
                'filters' => [
                    'module' => h($module),
                    'date_range' => h($dateRange),
                    'status' => h($status),
                    'sort' => h($sortBy)
                ]
            ]);
            exit();
    }

    // Execute specific module search
    $rows = $db->fetchAll($sql, $params);

    foreach ($rows as $row) {
        switch ($module) {
            case 'users':
                $results[] = [
                    'title' => h($row['name']) . ' (' . h($row['role']) . ')',
                    'description' => h($row['email']) . ' - ' . h(ucfirst($mlSupport->translate($row['status']))),
                    'url' => BASE_URL . 'admin/manage_users.php?user_id=' . intval($row['id']),
                    'type' => h($mlSupport->translate('User'))
                ];
                break;
            case 'properties':
                $results[] = [
                    'title' => h($row['title']),
                    'description' => h($row['location']) . ' - ₹' . h(number_format($row['price'])) . ' - ' . h(ucfirst($mlSupport->translate($row['status']))),
                    'url' => BASE_URL . 'admin/properties.php?property_id=' . intval($row['id']),
                    'type' => h($mlSupport->translate('Property'))
                ];
                break;
            case 'projects':
                $results[] = [
                    'title' => h($row['project_name']),
                    'description' => h($row['location']) . ' - ' . h(ucfirst($mlSupport->translate($row['status']))),
                    'url' => BASE_URL . 'admin/projects.php?project_id=' . intval($row['id']),
                    'type' => h($mlSupport->translate('Project'))
                ];
                break;
            case 'bookings':
                $results[] = [
                    'title' => h($row['customer_name']) . ' - ' . h($row['property_title']),
                    'description' => '₹' . h(number_format($row['total_amount'])) . ' - ' . h(ucfirst($mlSupport->translate($row['status']))),
                    'url' => BASE_URL . 'admin/bookings.php?booking_id=' . intval($row['id']),
                    'type' => h($mlSupport->translate('Booking'))
                ];
                break;
        }
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'total_results' => count($results),
        'filters' => [
            'module' => h($module),
            'date_range' => h($dateRange),
            'status' => h($status),
            'sort' => h($sortBy)
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
