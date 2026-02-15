<?php
/**
 * Get Dashboard Statistics - AJAX Endpoint
 * Returns real-time dashboard statistics
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
    // Get database connection
    global $con;
    $conn = $con;

    // Get total users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $users = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get total properties
    $stmt = $conn->query("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");
    $properties = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get total bookings
    $stmt = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'");
    $bookings = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get total revenue (sum of booking amounts)
    $stmt = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $revenue = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent activity count
    $stmt = $conn->query("SELECT COUNT(*) as total FROM user_activity WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $users['total'] ?? 0,
            'total_properties' => $properties['total'] ?? 0,
            'total_bookings' => $bookings['total'] ?? 0,
            'total_revenue' => $revenue['total'] ?? 0,
            'recent_activity' => $activity['total'] ?? 0
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving dashboard statistics'
    ]);
}
?>
