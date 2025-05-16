<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();

// Total users
$total_users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
// Active users
$active_users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM users WHERE status='active'"))['cnt'];
// Users by role
$roles = ['admin','superadmin','associate','user','builder','agent','employee','customer'];
$users_by_role = [];
foreach($roles as $role) {
    $users_by_role[$role] = (int)mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM users WHERE utype='$role'"))['cnt'];
}
// Total properties
$total_properties = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM property"))['cnt'];
// Total bookings (assume property table has a 'booked' or similar field)
$total_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as cnt FROM property WHERE status='booked' OR booked=1"))['cnt'];
// Recent activity (last 10 logins)
$recent_logins = [];
$res = mysqli_query($con, "SELECT name, email, utype, last_login FROM users ORDER BY last_login DESC LIMIT 10");
while($row = mysqli_fetch_assoc($res)) $recent_logins[] = $row;

// Bookings over time (last 6 months)
$bookings_over_time = [];
$res = mysqli_query($con, "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as cnt FROM property WHERE status='booked' OR booked=1 GROUP BY month ORDER BY month DESC LIMIT 6");
while($row = mysqli_fetch_assoc($res)) $bookings_over_time[] = $row;

header('Content-Type: application/json');
echo json_encode([
    'total_users' => (int)$total_users,
    'active_users' => (int)$active_users,
    'users_by_role' => $users_by_role,
    'total_properties' => (int)$total_properties,
    'total_bookings' => (int)$total_bookings,
    'recent_logins' => array_map(function($row) {
        return [
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'utype' => htmlspecialchars($row['utype']),
            'last_login' => htmlspecialchars($row['last_login'])
        ];
    }, $recent_logins),
    'bookings_over_time' => array_map(function($row) {
        return [
            'month' => htmlspecialchars($row['month']),
            'cnt' => (int)$row['cnt']
        ];
    }, array_reverse($bookings_over_time))
]);
