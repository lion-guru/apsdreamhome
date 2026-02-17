<?php

/**
 * Fetch Analytics AJAX handler
 */
require_once __DIR__ . '/core/init.php';

// Access control
if (!SecurityUtility::hasRole(['admin', 'superadmin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized access.']));
}

$db = \App\Core\App::database();

// Total users
$row_total = $db->fetchOne("SELECT COUNT(*) as cnt FROM user");
$total_users = $row_total['cnt'] ?? 0;

// Active users
$active_users = $total_users;

// Users by role
$roles = ['admin', 'superadmin', 'associate', 'user', 'builder', 'agent', 'employee', 'customer'];
$users_by_role = [];
foreach ($roles as $role) {
    $row_role = $db->fetchOne("SELECT COUNT(*) as cnt FROM user WHERE utype = :role", ['role' => $role]);
    $users_by_role[$role] = (int)$row_role['cnt'];
}

// Total properties
$row_prop = $db->fetchOne("SELECT COUNT(*) as cnt FROM property");
$total_properties = $row_prop['cnt'] ?? 0;

// Total bookings
$row_book = $db->fetchOne("SELECT COUNT(*) as cnt FROM property WHERE status='booked' OR booked=1");
$total_bookings = $row_book['cnt'] ?? 0;

// Recent activity (last 10 joins)
$recent_logins = $db->fetchAll("SELECT uname as name, uemail as email, utype, join_date as last_login FROM user ORDER BY join_date DESC LIMIT 10");

// Bookings over time (last 6 months)
$bookings_over_time = $db->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as cnt FROM property WHERE status='booked' OR booked=1 GROUP BY month ORDER BY month DESC LIMIT 6");

header('Content-Type: application/json');
echo json_encode([
    'total_users' => (int)$total_users,
    'active_users' => (int)$active_users,
    'users_by_role' => $users_by_role,
    'total_properties' => (int)$total_properties,
    'total_bookings' => (int)$total_bookings,
    'recent_logins' => array_map(function ($row) {
        return [
            'name' => h($row['name']),
            'email' => h($row['email']),
            'utype' => h($row['utype']),
            'last_login' => h($row['last_login'])
        ];
    }, $recent_logins),
    'bookings_over_time' => array_map(function ($row) {
        return [
            'month' => h($row['month']),
            'cnt' => (int)$row['cnt']
        ];
    }, array_reverse($bookings_over_time))
]);
