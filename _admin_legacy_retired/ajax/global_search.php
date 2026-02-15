<?php
/**
 * Global Search - AJAX Endpoint
 * Searches across all admin modules and returns relevant results
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
    $query = trim($_GET['q'] ?? '');

    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => 'Search query too short']);
        exit();
    }

    $conn = $con;
    $results = [];

    // Search in users
    $stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE name LIKE ? OR email LIKE ? OR role LIKE ? LIMIT 5");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'title' => $row['name'] . ' (' . $row['role'] . ')',
            'description' => $row['email'] . ' - ' . ucfirst($row['status']),
            'url' => BASE_URL . 'admin/manage_users.php?user_id=' . $row['id'],
            'type' => 'User'
        ];
    }

    // Search in properties
    $stmt = $conn->prepare("SELECT id, title, location, price, status FROM properties WHERE title LIKE ? OR location LIKE ? OR description LIKE ? LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'title' => $row['title'],
            'description' => $row['location'] . ' - ₹' . number_format($row['price']) . ' - ' . ucfirst($row['status']),
            'url' => BASE_URL . 'admin/properties.php?property_id=' . $row['id'],
            'type' => 'Property'
        ];
    }

    // Search in projects
    $stmt = $conn->prepare("SELECT id, project_name, location, status FROM projects WHERE project_name LIKE ? OR location LIKE ? LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'title' => $row['project_name'],
            'description' => $row['location'] . ' - ' . ucfirst($row['status']),
            'url' => BASE_URL . 'admin/projects.php?project_id=' . $row['id'],
            'type' => 'Project'
        ];
    }

    // Search in bookings
    $stmt = $conn->prepare("SELECT id, customer_name, property_title, total_amount, status FROM bookings WHERE customer_name LIKE ? OR property_title LIKE ? LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'title' => $row['customer_name'] . ' - ' . $row['property_title'],
            'description' => '₹' . number_format($row['total_amount']) . ' - ' . ucfirst($row['status']),
            'url' => BASE_URL . 'admin/bookings.php?booking_id=' . $row['id'],
            'type' => 'Booking'
        ];
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'query' => $query,
        'total_results' => count($results)
    ]);

} catch (Exception $e) {
    error_log('Global search error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error performing search'
    ]);
}
?>
