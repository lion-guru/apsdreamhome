<?php
/**
 * Get Recent Activity - AJAX Endpoint
 * Returns recent admin and system activities
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
    $conn = $con;
    $activities = [];

    // Get recent user activities
    $stmt = $conn->query("
        SELECT
            'User Login' as activity_type,
            CONCAT('User logged in: ', username) as title,
            CONCAT('IP: ', ip_address) as description,
            created_at as activity_time
        FROM user_activity
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY created_at DESC
        LIMIT 10
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'time' => $this->timeAgo($row['activity_time']),
            'type' => 'user',
            'icon' => 'fas fa-user'
        ];
    }

    // Get recent bookings
    $stmt = $conn->query("
        SELECT
            'New Booking' as activity_type,
            CONCAT('Booking created: ', customer_name) as title,
            CONCAT('Property: ', property_title, ' - ₹', total_amount) as description,
            created_at as activity_time
        FROM bookings
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY created_at DESC
        LIMIT 5
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'time' => $this->timeAgo($row['activity_time']),
            'type' => 'booking',
            'icon' => 'fas fa-calendar-check'
        ];
    }

    // Get recent property additions
    $stmt = $conn->query("
        SELECT
            'Property Added' as activity_type,
            CONCAT('New property: ', title) as title,
            CONCAT('Location: ', location, ' - ₹', price) as description,
            created_at as activity_time
        FROM properties
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY created_at DESC
        LIMIT 5
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'time' => $this->timeAgo($row['activity_time']),
            'type' => 'property',
            'icon' => 'fas fa-home'
        ];
    }

    // Sort activities by time (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Limit to top 10 activities
    $activities = array_slice($activities, 0, 10);

    echo json_encode([
        'success' => true,
        'activities' => $activities,
        'total_activities' => count($activities)
    ]);

} catch (Exception $e) {
    error_log('Recent activity error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving recent activity'
    ]);
}

/**
 * Convert datetime to human-readable time ago format
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0 && $diff->h == 0 && $diff->i == 0) {
        return $diff->s . ' seconds ago';
    } elseif ($diff->d == 0 && $diff->h == 0) {
        return $diff->i . ' minutes ago';
    } elseif ($diff->d == 0) {
        return $diff->h . ' hours ago';
    } elseif ($diff->d < 7) {
        return $diff->d . ' days ago';
    } else {
        return $ago->format('M d, Y');
    }
}
?>
