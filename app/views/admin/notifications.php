<?php
/**
 * Notifications - Updated with Database Singleton and Session Management
 */

require_once 'admin-functions.php';
use App\Core\Database;

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();

// Fetch recent notifications (last 20)
try {
    $notifications = $db->fetchAll("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
} catch (Exception $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    $notifications = [];
}
?>
