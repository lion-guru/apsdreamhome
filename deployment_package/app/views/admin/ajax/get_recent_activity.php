<?php
/**
 * Get Recent Activity - AJAX Endpoint
 * Returns recent admin and system activities
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
    $activities = [];

    // Get recent user activities
    $user_activities = $db->fetchAll("
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

    foreach ($user_activities as $row) {
        $activities[] = [
            'title' => h($mlSupport->translate($row['title'])),
            'description' => h($row['description']),
            'time' => h(timeAgo($row['activity_time'])),
            'type' => 'user',
            'icon' => 'fas fa-user'
        ];
    }

    // Get recent bookings
    $booking_activities = $db->fetchAll("
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

    foreach ($booking_activities as $row) {
        $activities[] = [
            'title' => h($mlSupport->translate($row['title'])),
            'description' => h($row['description']),
            'time' => h(timeAgo($row['activity_time'])),
            'type' => 'booking',
            'icon' => 'fas fa-calendar-check'
        ];
    }

    // Get recent property additions
    $property_activities = $db->fetchAll("
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

    foreach ($property_activities as $row) {
        $activities[] = [
            'title' => h($mlSupport->translate($row['title'])),
            'description' => h($row['description']),
            'time' => h(timeAgo($row['activity_time'])),
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
        'message' => h($mlSupport->translate('Error retrieving recent activity'))
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
