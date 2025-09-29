<?php
// Enable error reporting for development
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
error_reporting(E_ALL);

// Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net \'unsafe-inline\'; img-src \'self\' data:;');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Start secure session
$session_name = 'secure_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

session_name($session_name);
session_start();

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Include configuration
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['auser'])) {
    $_SESSION['error'] = 'Please log in to access this page';
    header('Location: /login.php');
    exit();
}

try {
    // Validate and sanitize user ID
    $user_id = filter_var($_SESSION['auser'], FILTER_VALIDATE_INT);
    if ($user_id === false) {
        throw new Exception('Invalid user ID');
    }

    // Get user details
    $stmt = $conn->prepare("SELECT name, email, status FROM employees WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Get user roles
    $roles = [];
    $stmt = $conn->prepare("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $roles[] = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
    }
    $stmt->close();

    // Get user permissions
    $permissions = [];
    $stmt = $conn->prepare("SELECT p.action FROM user_roles ur JOIN role_permissions rp ON ur.role_id = rp.role_id JOIN permissions p ON rp.permission_id = p.id WHERE ur.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $permissions[] = htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8');
    }
    $stmt->close();

    // Get notifications
    $stmt = $conn->prepare("SELECT type, message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $notifications = $stmt->get_result();
    $stmt->close();

} catch (Exception $e) {
    error_log('Error in self_service_portal.php: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred. Please try again later.';
    header('Location: /error.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Self-Service Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" 
          crossorigin="anonymous">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <a href="/logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Status:</strong> <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                    <?= htmlspecialchars(ucfirst($user['status']), ENT_QUOTES, 'UTF-8') ?>
                </span></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Your Roles</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($roles)): ?>
                            <ul class="list-group">
                                <?php foreach ($roles as $role): ?>
                                    <li class="list-group-item"><?= $role ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No roles assigned.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Your Permissions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($permissions)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($permissions as $permission): ?>
                                    <span class="badge bg-primary"><?= $permission ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No permissions assigned.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Notifications</h5>
                <a href="/notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if ($notifications->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($notification = $notifications->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8') ?></h6>
                                    <small class="text-muted"><?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?></small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No notifications found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" 
            crossorigin="anonymous"></script>
</body>
</html>
