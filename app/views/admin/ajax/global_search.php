<?php
/**
 * Global Search - AJAX Endpoint
 * Searches across all admin modules and returns relevant results
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

// CSRF validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

try {
    $db = \App\Core\App::database();
    $query = trim($_GET['q'] ?? '');

    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Search query too short'))]);
        exit();
    }

    $results = [];
    $searchTerm = "%$query%";

    // Search in users
    $userRows = $db->fetchAll("SELECT u.uid as id, u.uname as name, u.uemail as email, u.job_role as role, COALESCE(a.status, 'active') as status FROM user u LEFT JOIN associates a ON u.uid = a.user_id WHERE u.uname LIKE :search OR u.uemail LIKE :search OR u.job_role LIKE :search LIMIT 5", ['search' => $searchTerm]);

    foreach ($userRows as $row) {
        $results[] = [
            'title' => h($row['name']) . ' (' . h($row['role']) . ')',
            'description' => h($row['email']) . ' - ' . h($mlSupport->translate(ucfirst($row['status']))),
            'url' => BASE_URL . 'admin/manage_users.php?user_id=' . intval($row['id']),
            'type' => h($mlSupport->translate('User'))
        ];
    }

    // Search in properties
    $propertyRows = $db->fetchAll("SELECT id, title, location, price, status FROM properties WHERE title LIKE :search OR location LIKE :search OR description LIKE :search LIMIT 5", ['search' => $searchTerm]);

    foreach ($propertyRows as $row) {
        $results[] = [
            'title' => h($row['title']),
            'description' => h($row['location']) . ' - ₹' . number_format($row['price']) . ' - ' . h($mlSupport->translate(ucfirst($row['status']))),
            'url' => BASE_URL . 'admin/properties.php?property_id=' . intval($row['id']),
            'type' => h($mlSupport->translate('Property'))
        ];
    }

    // Search in projects
    $projectRows = $db->fetchAll("SELECT id, project_name, location, status FROM projects WHERE project_name LIKE :search OR location LIKE :search LIMIT 5", ['search' => $searchTerm]);

    foreach ($projectRows as $row) {
        $results[] = [
            'title' => h($row['project_name']),
            'description' => h($row['location']) . ' - ' . h($mlSupport->translate(ucfirst($row['status']))),
            'url' => BASE_URL . 'admin/projects.php?project_id=' . intval($row['id']),
            'type' => h($mlSupport->translate('Project'))
        ];
    }

    // Search in bookings
    $bookingRows = $db->fetchAll("SELECT id, customer_name, property_title, total_amount, status FROM bookings WHERE customer_name LIKE :search OR property_title LIKE :search LIMIT 5", ['search' => $searchTerm]);

    foreach ($bookingRows as $row) {
        $results[] = [
            'title' => h($row['customer_name']) . ' - ' . h($row['property_title']),
            'description' => '₹' . number_format($row['total_amount']) . ' - ' . h($mlSupport->translate(ucfirst($row['status']))),
            'url' => BASE_URL . 'admin/bookings.php?booking_id=' . intval($row['id']),
            'type' => h($mlSupport->translate('Booking'))
        ];
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'query' => h($query),
        'total_results' => count($results)
    ]);

} catch (Exception $e) {
    error_log('Global search error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Error performing search'))
    ]);
}
?>
