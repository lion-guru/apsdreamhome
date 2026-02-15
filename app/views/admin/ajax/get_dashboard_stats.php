<?php
/**
 * Get Dashboard Statistics - AJAX Endpoint
 * Returns real-time dashboard statistics
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../core/init.php';

use App\Core\Database;
$db = \App\Core\App::database();

ensureSessionStarted();

// Verify admin authentication
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
    // Get total users
    $users = $db->fetch("SELECT COUNT(*) as total FROM user u LEFT JOIN associates a ON u.uid = a.user_id WHERE COALESCE(a.status, 'active') = 'active'");

    // Get total properties
    $properties = $db->fetch("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");

    // Get total bookings
    $bookings = $db->fetch("SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'");

    // Get total revenue (sum of booking amounts)
    $revenue = $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");

    // Get recent activity count
    $activity = $db->fetch("SELECT COUNT(*) as total FROM user_activity WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");

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
        'message' => h($mlSupport->translate('Error retrieving dashboard statistics'))
    ]);
}
?>
